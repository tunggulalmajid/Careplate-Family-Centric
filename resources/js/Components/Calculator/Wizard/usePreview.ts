import { onBeforeUnmount, ref, watch } from 'vue';

/** "54,5" and "54.5" both become 54.5; anything else becomes null. */
export function parseDecimal(value: string | number | null | undefined) {
    const text = String(value ?? '')
        .trim()
        .replace(',', '.');

    return /^\d+(\.\d+)?$/.test(text) ? Number(text) : null;
}

function xsrfToken() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

/**
 * Debounced, abortable POST to a server preview endpoint. `payload` returns
 * null while there is nothing to ask. A 422 or a network failure clears the
 * data and never throws, so typing is never blocked.
 */
export function usePreview<T>(
    url: string,
    payload: () => Record<string, unknown> | null,
) {
    const data = ref<T | null>(null);
    const errors = ref<Record<string, string>>({});
    let timer: ReturnType<typeof setTimeout> | undefined;
    let controller: AbortController | undefined;

    async function run(body: Record<string, unknown>) {
        controller?.abort();
        controller = new AbortController();
        const mine = controller;

        try {
            const response = await fetch(url, {
                method: 'POST',
                signal: mine.signal,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': xsrfToken(),
                },
                body: JSON.stringify(body),
            });

            if (mine.signal.aborted) return;

            if (response.ok) {
                data.value = (await response.json()) as T;
                errors.value = {};
            } else {
                data.value = null;
                const json = await response.json().catch(() => null);
                errors.value = Object.fromEntries(
                    Object.entries(json?.errors ?? {}).map(([k, v]) => [
                        k,
                        String(Array.isArray(v) ? v[0] : v),
                    ]),
                );
            }
        } catch {
            if (!mine.signal.aborted) data.value = null;
        }
    }

    watch(
        payload,
        (body) => {
            clearTimeout(timer);
            controller?.abort();

            if (body === null) {
                data.value = null;
                errors.value = {};

                return;
            }

            timer = setTimeout(() => run(body), 250);
        },
        { immediate: true, deep: true },
    );

    onBeforeUnmount(() => {
        clearTimeout(timer);
        controller?.abort();
    });

    return { data, errors };
}
