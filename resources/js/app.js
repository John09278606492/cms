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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageBuilderAnimations);
} else {
    initPageBuilderAnimations();
}
