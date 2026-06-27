import './bootstrap';

// Page-builder entrance animations. Mark the document ready (so the CSS hidden
// state kicks in), then reveal each animated block as it scrolls into view.
function initPageBuilderAnimations() {
    const animated = document.querySelectorAll('.pb-anim');

    if (animated.length === 0) {
        return;
    }

    document.documentElement.classList.add('pb-anim-ready');

    if (!('IntersectionObserver' in window)) {
        animated.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -10% 0px' }
    );

    animated.forEach((el) => observer.observe(el));
}

// Count-up numbers when they scroll into view.
function initCounters() {
    const counters = document.querySelectorAll('.pb-counter');

    if (counters.length === 0 || !('IntersectionObserver' in window)) {
        return;
    }

    const run = (el) => {
        const target = parseInt(el.dataset.target || '0', 10);
        const duration = 1400;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased).toLocaleString();
            if (progress < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                run(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    counters.forEach((el) => observer.observe(el));
}

// Lightweight image carousels.
function initCarousels() {
    document.querySelectorAll('.pb-carousel').forEach((root) => {
        const track = root.querySelector('.pb-carousel-track');
        const slides = root.querySelectorAll('.pb-carousel-slide');
        const dots = root.querySelectorAll('[data-carousel-dot]');
        if (!track || slides.length <= 1) return;

        let index = 0;
        let timer = null;

        const show = (i) => {
            index = (i + slides.length) % slides.length;
            track.style.transform = `translateX(-${index * 100}%)`;
            dots.forEach((d, di) => di === index ? d.setAttribute('data-active', '') : d.removeAttribute('data-active'));
        };

        const start = () => {
            if (root.dataset.autoplay !== '1') return;
            stop();
            timer = setInterval(() => show(index + 1), parseInt(root.dataset.interval || '5000', 10));
        };
        const stop = () => timer && clearInterval(timer);

        root.querySelector('[data-carousel-next]')?.addEventListener('click', () => { show(index + 1); start(); });
        root.querySelector('[data-carousel-prev]')?.addEventListener('click', () => { show(index - 1); start(); });
        dots.forEach((d) => d.addEventListener('click', () => { show(parseInt(d.dataset.carouselDot, 10)); start(); }));
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);

        show(0);
        start();
    });
}

// Live countdown timers.
function initCountdowns() {
    document.querySelectorAll('.pb-countdown').forEach((root) => {
        const until = root.dataset.until ? new Date(root.dataset.until).getTime() : NaN;
        if (Number.isNaN(until)) return;

        const fields = {
            days: root.querySelector('[data-unit=days]'),
            hours: root.querySelector('[data-unit=hours]'),
            minutes: root.querySelector('[data-unit=minutes]'),
            seconds: root.querySelector('[data-unit=seconds]'),
        };
        const pad = (n) => String(n).padStart(2, '0');

        const update = () => {
            const diff = until - Date.now();
            if (diff <= 0) {
                root.replaceChildren();
                const expired = root.dataset.expired;
                if (expired) {
                    const p = document.createElement('p');
                    p.className = 'text-2xl font-semibold text-stone-950';
                    p.textContent = expired; // textContent — never parsed as HTML
                    root.appendChild(p);
                }
                clearInterval(timer);
                return;
            }
            const s = Math.floor(diff / 1000);
            if (fields.days) fields.days.textContent = pad(Math.floor(s / 86400));
            if (fields.hours) fields.hours.textContent = pad(Math.floor((s % 86400) / 3600));
            if (fields.minutes) fields.minutes.textContent = pad(Math.floor((s % 3600) / 60));
            if (fields.seconds) fields.seconds.textContent = pad(s % 60);
        };

        update();
        const timer = setInterval(update, 1000);
    });
}

function initPageBuilder() {
    // Inside the visual designer everything is shown statically: entrance
    // animations, counters, carousels and countdowns are for the live site.
    // The editor re-renders the canvas on every edit, and the entrance-animation
    // CSS would otherwise hide freshly re-rendered elements (opacity 0) — making
    // animated elements vanish while you build. They still play on the live site.
    if (document.body.hasAttribute('data-designer')) {
        return;
    }

    initPageBuilderAnimations();
    initCounters();
    initCarousels();
    initCountdowns();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageBuilder);
} else {
    initPageBuilder();
}
