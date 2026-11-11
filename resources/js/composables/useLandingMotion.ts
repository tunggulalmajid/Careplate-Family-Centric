import { onMounted, onUnmounted, type Ref } from 'vue';

/** Landing-only motion; the document stays readable before JS and on load failure. */
export function useLandingMotion(root: Ref<HTMLElement | null>) {
    let disposed = false;
    let media: { revert: () => void } | undefined;

    onMounted(async () => {
        // Keep the animation engine out of the critical render path and other pages.
        if (
            !window.matchMedia('(prefers-reduced-motion: no-preference)')
                .matches
        ) {
            return;
        }

        try {
            const [{ gsap }, { ScrollTrigger }] = await Promise.all([
                import('gsap'),
                import('gsap/ScrollTrigger'),
            ]);
            if (disposed || !root.value) return;

            gsap.registerPlugin(ScrollTrigger);
            const matches = gsap.matchMedia();
            media = matches;

            matches.add(
                '(prefers-reduced-motion: no-preference)',
                () => {
                    const container = root.value;
                    if (!container) return;
                    const select = gsap.utils.selector(container);

                    // One short welcome, never replayed over a restored scroll position.
                    if (window.scrollY < 80) {
                        gsap.timeline({ defaults: { ease: 'power3.out' } })
                            .from(select('[data-hero-line]'), {
                                y: 24,
                                opacity: 0.25,
                                duration: 0.7,
                                stagger: 0.12,
                            })
                            .from(
                                select('[data-hero-pill]'),
                                {
                                    scale: 0.9,
                                    rotation: -4,
                                    duration: 0.65,
                                    ease: 'back.out(1.4)',
                                },
                                0.12,
                            )
                            .from(
                                select('[data-hero-support]'),
                                {
                                    y: 12,
                                    opacity: 0.4,
                                    duration: 0.55,
                                    stagger: 0.08,
                                },
                                0.2,
                            )
                            .from(
                                select('[data-hero-sticker]'),
                                {
                                    y: 28,
                                    opacity: 0,
                                    scale: 0.94,
                                    duration: 0.75,
                                    stagger: 0.12,
                                    ease: 'back.out(1.2)',
                                    // GSAP folds the CSS tilt (rotate) into its transform; clearing hands it back to CSS.
                                    clearProps:
                                        'transform,translate,rotate,scale,opacity',
                                },
                                0.35,
                            )
                            .from(
                                select('[data-hero-sparkle]'),
                                {
                                    scale: 0.4,
                                    rotation: -35,
                                    opacity: 0,
                                    duration: 0.6,
                                    stagger: 0.08,
                                },
                                0.3,
                            );
                    }

                    // Only the illustrations move: headings, links and copy stay put.
                    select('[data-market-scene]').forEach(
                        (scene: HTMLElement) => {
                            const image = scene.querySelector('img');
                            if (!image) return;
                            gsap.fromTo(
                                image,
                                { scale: 1.08, yPercent: 3 },
                                {
                                    scale: 1,
                                    yPercent: 0,
                                    ease: 'none',
                                    scrollTrigger: {
                                        trigger: scene,
                                        start: 'top 90%',
                                        end: 'center 45%',
                                        scrub: 0.6,
                                        invalidateOnRefresh: true,
                                    },
                                },
                            );
                        },
                    );

                    // Scroll-linked reading progress, a thin bar under the top edge.
                    const bar = document.createElement('div');
                    bar.setAttribute('aria-hidden', 'true');
                    bar.style.cssText =
                        'position:fixed;inset:0 0 auto 0;height:3px;z-index:60;background:#0b8043;transform-origin:0 50%;pointer-events:none';
                    document.body.appendChild(bar);
                    gsap.fromTo(
                        bar,
                        { scaleX: 0 },
                        {
                            scaleX: 1,
                            ease: 'none',
                            scrollTrigger: {
                                trigger: document.documentElement,
                                start: 'top top',
                                end: 'bottom bottom',
                                scrub: 0.2,
                            },
                        },
                    );

                    // The hero scene drifts and zooms a little as it scrolls away.
                    const heroArt = select('[data-hero-art]')[0];
                    if (heroArt) {
                        gsap.to(heroArt, {
                            scale: 1.07,
                            yPercent: 4,
                            ease: 'none',
                            scrollTrigger: {
                                trigger: heroArt,
                                start: 'top bottom',
                                end: 'bottom top',
                                scrub: 0.6,
                            },
                        });
                    }

                    // The closing hill scene settles into place as the page ends.
                    const ctaArt = select('[data-cta-art]')[0];
                    if (ctaArt) {
                        gsap.fromTo(
                            ctaArt,
                            { scale: 1.06, yPercent: 4 },
                            {
                                scale: 1,
                                yPercent: 0,
                                ease: 'none',
                                scrollTrigger: {
                                    trigger: ctaArt,
                                    start: 'top 95%',
                                    end: 'center 55%',
                                    scrub: 0.6,
                                },
                            },
                        );
                    }

                    // Generic reveals: a block fades up once; a group staggers its direct children.
                    select('[data-reveal]').forEach((el: HTMLElement) => {
                        gsap.from(el, {
                            y: 28,
                            opacity: 0,
                            duration: 0.7,
                            ease: 'power3.out',
                            clearProps: 'transform,opacity',
                            scrollTrigger: {
                                trigger: el,
                                start: 'top 88%',
                                once: true,
                            },
                        });
                    });
                    select('[data-reveal-group]').forEach(
                        (group: HTMLElement) => {
                            gsap.from(group.children, {
                                y: 28,
                                opacity: 0,
                                duration: 0.65,
                                stagger: 0.09,
                                ease: 'power3.out',
                                clearProps: 'transform,opacity',
                                scrollTrigger: {
                                    trigger: group,
                                    start: 'top 85%',
                                    once: true,
                                },
                            });
                        },
                    );

                    // Waiting state: lazy images fade in when they arrive instead of popping.
                    container
                        .querySelectorAll<HTMLImageElement>(
                            'img[loading="lazy"]',
                        )
                        .forEach((img) => {
                            if (img.complete) return;
                            gsap.set(img, { opacity: 0 });
                            const show = () =>
                                gsap.to(img, {
                                    opacity: 1,
                                    duration: 0.6,
                                    ease: 'power2.out',
                                    clearProps: 'opacity',
                                });
                            img.addEventListener('load', show, { once: true });
                            img.addEventListener('error', show, { once: true });
                            // Never leave an image hidden if its events are missed.
                            window.setTimeout(show, 6000);
                        });

                    // Onboarding-style entrance for "Kebiasaan kecil": title, then the three cards one by one
                    // (card, its copy, its illustration rising into the arch), then the controls. Plays once.
                    const habits = select('[data-habits]')[0];
                    if (habits) {
                        const cards = select('[data-habit-card]');
                        const entrance = gsap.timeline({
                            scrollTrigger: {
                                trigger: habits,
                                start: 'top 70%',
                                once: true,
                            },
                            defaults: { ease: 'power3.out' },
                        });
                        entrance.from(select('[data-habit-title]'), {
                            y: 24,
                            opacity: 0,
                            duration: 0.6,
                        });
                        cards.forEach((card: HTMLElement, index: number) => {
                            const at = 0.2 + index * 0.16;
                            entrance
                                .from(
                                    card,
                                    {
                                        y: 56,
                                        scale: 0.94,
                                        opacity: 0,
                                        duration: 0.75,
                                        ease: 'back.out(1.25)',
                                        clearProps: 'transform,opacity',
                                    },
                                    at,
                                )
                                .from(
                                    card.querySelectorAll('[data-habit-copy]'),
                                    {
                                        y: 14,
                                        opacity: 0,
                                        duration: 0.5,
                                        stagger: 0.07,
                                        clearProps: 'transform,opacity',
                                    },
                                    at + 0.25,
                                )
                                .from(
                                    card.querySelector('[data-habit-art]'),
                                    {
                                        yPercent: 22,
                                        duration: 0.85,
                                        clearProps: 'transform',
                                    },
                                    at + 0.3,
                                );
                        });
                        entrance.from(
                            select('[data-habit-ui]'),
                            {
                                y: 12,
                                opacity: 0,
                                duration: 0.5,
                                stagger: 0.08,
                                clearProps: 'transform,opacity',
                            },
                            '-=0.35',
                        );
                    }

                    const table = select('[data-family-demo]')[0];
                    if (table) {
                        gsap.timeline({
                            scrollTrigger: {
                                trigger: table,
                                start: 'top 85%',
                                once: true,
                            },
                            defaults: { ease: 'power3.out' },
                        })
                            .from(select('[data-family-progress]'), {
                                scaleX: 0,
                                transformOrigin: 'left center',
                                duration: 0.85,
                            })
                            .from(
                                select('[data-family-check]'),
                                {
                                    scale: 0.65,
                                    opacity: 0.35,
                                    duration: 0.3,
                                    stagger: 0.025,
                                },
                                0.1,
                            );
                    }

                    // Lazy images and web fonts can change trigger positions after mount.
                    const refresh = () => ScrollTrigger.refresh();
                    const images = Array.from(
                        container.querySelectorAll('img'),
                    );
                    images.forEach((image) =>
                        image.addEventListener('load', refresh),
                    );
                    void document.fonts.ready.then(() => {
                        if (!disposed && root.value === container) refresh();
                    });
                    return () => {
                        bar.remove();
                        images.forEach((image) =>
                            image.removeEventListener('load', refresh),
                        );
                    };
                },
                root.value,
            );
        } catch {
            // Animation is progressive enhancement; a failed chunk must not hide content.
            media?.revert();
        }
    });

    onUnmounted(() => {
        disposed = true;
        media?.revert();
    });
}
