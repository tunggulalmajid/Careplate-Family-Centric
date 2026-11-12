// Run against a local app serving a current production build:
// node tools/check-landing-motion.mjs
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import {
    closeSync,
    existsSync,
    openSync,
    readFileSync,
    unlinkSync,
} from 'node:fs';
import { tmpdir } from 'node:os';
import { delimiter, join } from 'node:path';

const binary =
    process.env.AGENT_BROWSER_BIN ||
    (process.platform === 'win32'
        ? process.env.PATH.split(delimiter)
              .map((dir) =>
                  join(
                      dir,
                      'node_modules/agent-browser/bin/agent-browser-win32-x64.exe',
                  ),
              )
              .find(existsSync)
        : 'agent-browser');
assert.ok(binary, 'Install agent-browser or set AGENT_BROWSER_BIN');
const session = 'careplate-motion-check';
const url = process.env.LANDING_URL || 'http://127.0.0.1:8000';
assert.match(
    url,
    /^http:\/\/(127\.0\.0\.1|localhost)(:\d+)?\/?$/,
    'Use a local test app',
);
let passed = 0;
const outputs = [];
function browser(args, input) {
    // Windows browser daemons inherit pipes. Use a regular output file so a
    // newly launched daemon cannot hold the CLI's output stream open.
    const output = join(
        tmpdir(),
        `careplate-motion-${process.pid}-${outputs.length}.json`,
    );
    outputs.push(output);
    const fd = openSync(output, 'w');
    try {
        execFileSync(binary, ['--session', session, '--json', ...args], {
            encoding: 'utf8',
            input,
            timeout: 45000,
            stdio: ['pipe', fd, 'inherit'],
            windowsHide: true,
        });
    } finally {
        closeSync(fd);
    }
    const response = JSON.parse(readFileSync(output, 'utf8').trim());
    assert.equal(
        response.success,
        true,
        response.error || JSON.stringify(response),
    );
    return response.data;
}
function evaluate(code) {
    return browser(['eval', '--stdin'], code).result;
}
function check(name, assertion) {
    assertion();
    console.log(`PASS ${++passed}: ${name}`);
}
try {
    browser(['set', 'media', 'light', 'reduced-motion']);
    browser(['open', url]);
    browser(['wait', '--fn', "!!document.querySelector('.careplate-landing')"]);
    check('Reduced motion disables smooth anchor scrolling', () => {
        assert.equal(
            evaluate(
                'getComputedStyle(document.documentElement).scrollBehavior',
            ),
            'auto',
        );
    });
    check(
        'Reduced motion keeps scene images and the family demo static',
        () => {
            assert.ok(
                evaluate(
                    `[...document.querySelectorAll('[data-market-scene] img, [data-family-progress], [data-family-check]')].every(el => getComputedStyle(el).transform === 'none')`,
                ),
            );
        },
    );
    check('Info arrow targets are at least 44px', () => {
        assert.ok(
            evaluate(
                `[...document.querySelectorAll('button[aria-label^="Info "]')].every(el => el.getBoundingClientRect().height >= 44 && el.getBoundingClientRect().width >= 44)`,
            ),
        );
    });
    for (const width of [390, 768, 1440, 1728]) {
        browser(['set', 'viewport', String(width), '900']);
        check(
            `All seven sections remain readable without page overflow at ${width}px`,
            () => {
                const result = evaluate(
                    `({sections:document.querySelectorAll('section').length, overflow:document.documentElement.scrollWidth > innerWidth, hidden:[...document.querySelectorAll('h1,h2,h3')].some(el => getComputedStyle(el).visibility === 'hidden' || Number(getComputedStyle(el).opacity) === 0)})`,
                );
                assert.equal(result.sections, 7);
                assert.equal(result.overflow, false);
                assert.equal(result.hidden, false);
            },
        );
    }
    browser(['set', 'viewport', '390', '844']);
    browser(['click', '[aria-controls="landing-mobile-menu"]']);
    check('Mobile menu opens and reports its expanded state', () => {
        assert.equal(
            evaluate(
                `document.querySelector('[aria-controls="landing-mobile-menu"]').getAttribute('aria-expanded')`,
            ),
            'true',
        );
        assert.ok(evaluate(`!!document.querySelector('#landing-mobile-menu')`));
    });
    browser(['press', 'Escape']);
    check('Escape closes navigation and restores focus', () => {
        assert.equal(
            evaluate(
                `document.querySelector('[aria-controls="landing-mobile-menu"]').getAttribute('aria-expanded')`,
            ),
            'false',
        );
        assert.equal(
            evaluate(`document.activeElement.getAttribute('aria-controls')`),
            'landing-mobile-menu',
        );
    });
    const info = evaluate(
        `document.querySelector('[aria-live="polite"]').textContent.trim()`,
    );
    browser(['click', '[aria-label="Info berikutnya"]']);
    browser([
        'wait',
        '--fn',
        `!document.querySelector('[aria-live="polite"]').textContent.includes('Nilai gizi diverifikasi')`,
    ]);
    check('Info advances without truncating its message', () => {
        assert.notEqual(
            evaluate(
                `document.querySelector('[aria-live="polite"]').textContent.trim()`,
            ),
            info,
        );
        assert.equal(
            evaluate(
                `getComputedStyle(document.querySelector('[aria-live="polite"] p')).textOverflow`,
            ),
            'clip',
        );
    });
    browser(['click', '[aria-label="Info sebelumnya"]']);
    browser([
        'wait',
        '--fn',
        `document.querySelector('[aria-live="polite"]').textContent.includes('Nilai gizi diverifikasi')`,
    ]);
    check('Reverse info input returns to the original message', () => {
        assert.equal(
            evaluate(
                `document.querySelector('[aria-live="polite"]').textContent.trim()`,
            ),
            info,
        );
    });
    browser(['set', 'media', 'light']);
    browser(['open', url]);
    browser([
        'wait',
        '--fn',
        "!!document.querySelector('[data-market-scene] img')?.style.transform",
    ]);
    browser(['set', 'viewport', '1440', '1000']);
    check('Scrolling advances the market illustration animation', () => {
        const result = evaluate(`(async () => {
            const image = document.querySelector('[data-market-scene] img');
            const before = getComputedStyle(image).transform;
            image.closest('[data-market-scene]').scrollIntoView({behavior:'instant', block:'center'});
            for(let frame=0;frame<90;frame++) {
                await new Promise(requestAnimationFrame);
                if(getComputedStyle(image).transform !== before) return true;
            }
            return false;
        })()`);
        assert.equal(result, true);
    });
    browser(['set', 'media', 'light', 'reduced-motion']);
    browser([
        'wait',
        '--fn',
        `[...document.querySelectorAll('[data-market-scene] img, [data-family-progress], [data-family-check]')].every(el => getComputedStyle(el).transform === 'none')`,
    ]);
    check('Changing motion preference removes running animation styles', () => {
        assert.ok(
            evaluate(
                `[...document.querySelectorAll('[data-market-scene] img, [data-family-progress], [data-family-check]')].every(el => getComputedStyle(el).transform === 'none')`,
            ),
        );
    });
    browser(['set', 'media', 'light']);
    browser(['open', url]);
    browser([
        'wait',
        '--fn',
        "!!document.querySelector('[data-market-scene] img')?.style.transform",
    ]);
    evaluate(
        `(async () => { const url=performance.getEntriesByType('resource').find(e => e.name.includes('/gsap-')).name; window.__landingTestGsap=(await import(url)).gsap; })()`,
    );
    browser(['click', '.careplate-landing a[href$="/register"]']);
    browser(['wait', '--url', '**/register']);
    check('Registration navigation removes landing animation work', () => {
        assert.equal(
            evaluate(`!!document.querySelector('.careplate-landing')`),
            false,
        );
        assert.equal(
            evaluate(
                `window.__landingTestGsap.globalTimeline.getChildren(true,true,true).filter(t => t.targets && t.targets().some(el => el instanceof Element && !el.isConnected)).length`,
            ),
            0,
        );
    });
    browser(['back']);
    browser(['wait', '--fn', "!!document.querySelector('.careplate-landing')"]);
    check(
        'Returning from registration restores readable landing content',
        () => {
            assert.equal(
                evaluate(`document.querySelectorAll('section').length`),
                7,
            );
            assert.ok(
                evaluate(`!!document.querySelector('a[href="#kebiasaan"]')`),
            );
        },
    );
    console.log(`${passed}/${passed} landing motion checks passed`);
} finally {
    browser(['close']);
    outputs.forEach((output) => unlinkSync(output));
}
