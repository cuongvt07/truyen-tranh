/**
 * Site-wide effects: enhanced Swiper, hover animations, scroll reveal
 * Loads AFTER indexee8b.js so it can upgrade existing Swiper instances
 */
(function () {
    'use strict';

    /* ── Re-init Swiper with navigation + autoplay ─────────────────────── */
    function initSwipers() {
        // Index tags (thể loại) — slider 1 hàng
        if (document.querySelector('.index-tags-swiper .swiper-container')) {
            new Swiper('.index-tags-swiper .swiper-container', {
                slidesPerView: 2,
                spaceBetween: 10,
                loop: false,
                autoplay: { delay: 3000, disableOnInteraction: false, pauseOnMouseEnter: true },
                breakpoints: { 1040: { slidesPerView: 6 }, 768: { slidesPerView: 4 }, 480: { slidesPerView: 3 } }
            });
        }

        // Popular
        if (document.querySelector('.popular .swiper-container')) {
            new Swiper('.popular .swiper-container', {
                slidesPerView: 2,
                slidesPerGroup: 1,
                spaceBetween: 10,
                loop: true,
                autoplay: { delay: 3500, disableOnInteraction: false },
                breakpoints: { 1040: { slidesPerView: 6 }, 550: { slidesPerView: 4 }, 350: { slidesPerView: 3 } }
            });
        }

        // Translation requests
        if (document.querySelector('.translation-requests .swiper-container')) {
            new Swiper('.translation-requests .swiper-container', {
                slidesPerView: 2,
                slidesPerGroup: 1,
                spaceBetween: 10,
                loop: true,
                autoplay: { delay: 2800, disableOnInteraction: false },
                breakpoints: { 1040: { slidesPerView: 8 }, 550: { slidesPerView: 5 }, 320: { slidesPerView: 3 } }
            });
        }

        // New Releases
        if (document.querySelector('.new-realeses .swiper-container')) {
            new Swiper('.new-realeses .swiper-container', {
                slidesPerView: 1,
                spaceBetween: 10,
                loop: true,
                autoplay: { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true },
                effect: 'fade',
                fadeEffect: { crossFade: true }
            });
        }

        // Single book page swipers (manga-list.swp)
        document.querySelectorAll('.manga-list.swp .swiper-container').forEach(function (el) {
            new Swiper(el, {
                slidesPerView: 2,
                spaceBetween: 10,
                loop: false,
                navigation: {
                    nextEl: el.closest('.manga-list').querySelector('.swiper-right'),
                    prevEl: el.closest('.manga-list').querySelector('.swiper-left'),
                },
                breakpoints: { 1040: { slidesPerView: 6 }, 550: { slidesPerView: 4 }, 350: { slidesPerView: 3 } }
            });
        });
    }

    /* ── (đã bỏ) chèn nút điều hướng / dots — slider tự chạy ───────────── */
    function injectNav() { /* no-op: ẩn nút next/prev và dots */ }

    /* ── Scroll-reveal fade-in on sections ──────────────────────────────── */
    function initScrollReveal() {
        if (!('IntersectionObserver' in window)) return;
        var sections = document.querySelectorAll('.section');
        var style = document.createElement('style');
        style.textContent = [
            '.section { opacity: 0; transform: translateY(24px); transition: opacity 0.5s ease, transform 0.5s ease; }',
            '.section.visible { opacity: 1; transform: none; }'
        ].join('');
        document.head.appendChild(style);

        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
            });
        }, { threshold: 0.06 });

        sections.forEach(function (s) { obs.observe(s); });
    }

    /* ── Smooth hover scale on manga-item posters ───────────────────────── */
    function injectHoverCSS() {
        var s = document.createElement('style');
        s.textContent = [
            '.manga-item .poster img { transition: transform 0.35s ease, opacity 0.25s ease; }',
            '.manga-item:hover .poster img { transform: scale(1.06); }',
            '.manga-item .poster { overflow: hidden; border-radius: 5px; }',
            '.new-realeses__item .poster img { transition: transform 0.4s ease; }',
            '.new-realeses__item:hover .poster img { transform: scale(1.04); }',
            '.tag .background { transition: opacity 0.45s ease, transform 0.45s ease; }',
            '.tag:hover .background { opacity: 1 !important; transform: scale(1.08); }',
            '.comment-block { transition: transform 0.2s ease, box-shadow 0.2s ease; }',
            '.comment-block:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.18); }',
            '.manga-line-item { transition: opacity 0.2s ease, transform 0.2s ease; }',
            '.manga-line-item:hover { opacity: 1 !important; transform: translateX(4px); }',
            '.collection-item { transition: transform 0.2s ease; }',
            '.collection-item:hover { transform: translateY(-4px); }',
        ].join('\n');
        document.head.appendChild(s);
    }

    /* ── Section headers animated underline ─────────────────────────────── */
    function injectHeadingCSS() {
        var s = document.createElement('style');
        s.textContent = [
            '.section h2 { position: relative; display: inline-block; padding-bottom: 6px; }',
            '.section h2::after { content:""; position:absolute; left:0; bottom:0; height:2px; width:0; background:transparent; transition: width 0.4s ease; border-radius:2px; }',
            '.section.visible h2::after { width: 100%; }'
        ].join('\n');
        document.head.appendChild(s);
    }

    /* ── Page load progress bar ─────────────────────────────────────────── */
    function initProgressBar() {
        var bar = document.createElement('div');
        bar.id = 'page-load-bar';
        bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;background:var(--primary,#e84040);z-index:99999;transition:width 0.3s ease,opacity 0.5s ease 0.2s;pointer-events:none';
        document.body.appendChild(bar);
        bar.style.width = '80%';
        window.addEventListener('load', function () {
            bar.style.width = '100%';
            setTimeout(function () { bar.style.opacity = '0'; }, 400);
        });
    }

    /* ── Language dropdown (tippy) ─────────────────────────────────────── */
    function initLangDropdown() {
        if (typeof window.tippy !== 'function') return;
        var list = document.getElementById('header-lang-list');
        var btn = document.querySelector('.tippy-lang');
        if (!list || !btn) return;
        window.tippy(btn, {
            content: list.outerHTML,
            allowHTML: true,
            interactive: true,
            trigger: 'click',
            placement: 'bottom-end',
            appendTo: document.body,
            theme: 'light',
            arrow: false,
            offset: [0, 6],
        });
    }

    /* ── Submenu dropdown (mục menu cha-con kiểu WP) ───────────────────────── */
    function initSubmenus() {
        if (typeof window.tippy !== 'function') return;
        document.querySelectorAll('.tippy-submenu').forEach(function (btn) {
            var id = btn.getAttribute('data-submenu');
            var content = document.getElementById(id);
            if (!content) return;
            window.tippy(btn, {
                content: content.outerHTML,
                allowHTML: true,
                interactive: true,
                trigger: 'mouseenter focus click',
                placement: 'bottom-start',
                appendTo: document.body,
                theme: 'light',
                arrow: false,
                offset: [0, 6],
            });
        });
    }

    /* ── Run ──────────────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        injectNav();
        injectHoverCSS();
        injectHeadingCSS();
        initScrollReveal();
        // Swiper init runs after indexee8b.js, slight delay to override
        setTimeout(initSwipers, 50);
        setTimeout(initLangDropdown, 60);
        setTimeout(initSubmenus, 70);
    });
    initProgressBar();

})();
