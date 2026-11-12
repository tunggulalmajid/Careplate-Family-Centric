import { onBeforeUnmount, onMounted, ref, type Ref } from 'vue';

/**
 * State and controls for a scroll-snap carousel.
 *
 * The track scrolls natively (touch swipe, trackpad, keyboard focus all work without JS); this only adds
 * the prev/next/dot controls and keeps the "active" slide in step. When every slide already fits (wide
 * desktop), nothing scrolls and the controls move the spotlight instead, so they are never dead.
 */
export function useSnapCarousel(track: Ref<HTMLElement | null>, count: number) {
    const active = ref(0);
    /** True once the visitor used a control or swiped; the resting layout stays exactly as designed until then. */
    const engaged = ref(false);
    const overflowing = ref(false);

    let lockUntil = 0;
    let frame = 0;
    let observer: ResizeObserver | undefined;

    const slides = () =>
        Array.from(track.value?.children ?? []) as HTMLElement[];
    const reducedMotion = () =>
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function measure() {
        const el = track.value;
        overflowing.value = !!el && el.scrollWidth > el.clientWidth + 1;
    }

    function go(index: number, byUser = true) {
        const next = ((index % count) + count) % count;
        active.value = next;
        if (byUser) engaged.value = true;

        const el = track.value;
        const slide = slides()[next];
        if (!el || !slide || !overflowing.value) return;

        // Ignore the scroll events caused by this move so the active slide does not flicker on the way.
        lockUntil = performance.now() + 700;
        el.scrollTo({
            left: slide.offsetLeft - (el.clientWidth - slide.offsetWidth) / 2,
            behavior: reducedMotion() ? 'auto' : 'smooth',
        });
    }

    const next = () => go(active.value + 1);
    const prev = () => go(active.value - 1);

    function syncFromScroll() {
        frame = 0;
        const el = track.value;
        if (!el || performance.now() < lockUntil) return;

        let nearest = active.value;
        if (el.scrollLeft <= 2) {
            nearest = 0;
        } else if (el.scrollLeft + el.clientWidth >= el.scrollWidth - 2) {
            nearest = count - 1;
        } else {
            const middle = el.scrollLeft + el.clientWidth / 2;
            let best = Infinity;
            slides().forEach((slide, i) => {
                const distance = Math.abs(
                    slide.offsetLeft + slide.offsetWidth / 2 - middle,
                );
                if (distance < best) {
                    best = distance;
                    nearest = i;
                }
            });
        }
        if (nearest !== active.value) {
            active.value = nearest;
            engaged.value = true;
        }
    }

    function onScroll() {
        if (!frame) frame = requestAnimationFrame(syncFromScroll);
    }

    function onKeydown(event: KeyboardEvent) {
        const target = event.target as HTMLElement | null;
        if (target?.closest('input, textarea, select')) return;
        const map: Record<string, () => void> = {
            ArrowRight: next,
            ArrowLeft: prev,
            Home: () => go(0),
            End: () => go(count - 1),
        };
        const action = map[event.key];
        if (!action) return;
        event.preventDefault();
        action();
    }

    onMounted(() => {
        measure();
        track.value?.addEventListener('scroll', onScroll, { passive: true });
        if (typeof ResizeObserver !== 'undefined' && track.value) {
            observer = new ResizeObserver(measure);
            observer.observe(track.value);
        }
    });

    onBeforeUnmount(() => {
        track.value?.removeEventListener('scroll', onScroll);
        observer?.disconnect();
        if (frame) cancelAnimationFrame(frame);
    });

    return { active, engaged, overflowing, go, next, prev, onKeydown };
}
