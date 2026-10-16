import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

/**
 * Horizontal scroll-snap carousel. Markup: x-data="carousel" with an
 * x-ref="track" scroller; prev()/next() move one "page" at a time.
 */
Alpine.data('carousel', () => ({
    atStart: true,
    atEnd: false,

    init() {
        this.update();
        this.$refs.track.addEventListener('scroll', () => this.update(), { passive: true });
        window.addEventListener('resize', () => this.update());
    },

    update() {
        const t = this.$refs.track;
        this.atStart = t.scrollLeft <= 4;
        this.atEnd = t.scrollLeft + t.clientWidth >= t.scrollWidth - 4;
    },

    prev() {
        this.$refs.track.scrollBy({ left: -this.$refs.track.clientWidth, behavior: 'smooth' });
    },

    next() {
        this.$refs.track.scrollBy({ left: this.$refs.track.clientWidth, behavior: 'smooth' });
    },
}));

/**
 * One-at-a-time fading slider with optional autoplay (paused on hover/focus).
 */
Alpine.data('slider', (count, interval = 8000) => ({
    current: 0,
    count,
    timer: null,

    init() {
        this.play();
    },

    play() {
        if (this.count < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        this.stop();
        this.timer = setInterval(() => this.next(), interval);
    },

    stop() {
        clearInterval(this.timer);
    },

    go(i) {
        this.current = (i + this.count) % this.count;
    },

    next() {
        this.go(this.current + 1);
    },

    prev() {
        this.go(this.current - 1);
    },
}));

window.Alpine = Alpine;
Alpine.start();
