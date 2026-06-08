/* =========================================================================
   Admin UI Enhancement — AdminLTE
   Sidebar memory, lazy-load, confirm delete, cover preview, clickable rows
   ========================================================================= */
(function () {
    'use strict';

    /* 1. Nhớ trạng thái sidebar (mở/đóng) */
    function sidebarMemory() {
        try {
            if (localStorage.getItem('adm_sidebar') === 'closed') {
                document.body.classList.add('sidebar-collapse');
            }
        } catch (e) {}
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-widget="pushmenu"]')) {
                setTimeout(function () {
                    var closed = document.body.classList.contains('sidebar-collapse');
                    try { localStorage.setItem('adm_sidebar', closed ? 'closed' : 'open'); } catch (e) {}
                }, 50);
            }
        });
    }

    /* 2. Lazy-load ảnh có data-src */
    function lazyImages() {
        var imgs = document.querySelectorAll('img[data-src]');
        if (!imgs.length || !('IntersectionObserver' in window)) {
            imgs.forEach(function (i) { i.src = i.dataset.src; });
            return;
        }
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.src = e.target.dataset.src;
                    e.target.classList.add('loaded');
                    obs.unobserve(e.target);
                }
            });
        }, { rootMargin: '200px' });
        imgs.forEach(function (i) { obs.observe(i); });
    }

    /* 3. Confirm xoá */
    function confirmDelete() {
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form.matches('form.form-delete, form[data-confirm]') ||
                form.querySelector('[name="_method"][value="DELETE"]')) {
                var msg = form.getAttribute('data-confirm') || 'Bạn chắc chắn muốn xoá? Hành động này không thể hoàn tác.';
                if (!window.confirm(msg)) { e.preventDefault(); }
            }
        }, true);
        document.querySelectorAll('.btn-delete').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (!window.confirm('Bạn chắc chắn muốn xoá?')) { e.preventDefault(); }
            });
        });
    }

    /* 4. Preview ảnh bìa trước khi upload (mọi input ảnh) */
    function coverPreview() {
        document.querySelectorAll('input[type="file"][accept*="image"], #cover-input, input[name="cover_image"]').forEach(function (input) {
            input.addEventListener('change', function () {
                var file = this.files && this.files[0];
                if (!file) return;
                // Tìm/ tạo preview ngay sau input
                var prev = this.parentNode.querySelector('.cover-preview, #cover-preview');
                if (!prev) {
                    prev = document.createElement('img');
                    prev.className = 'cover-preview';
                    prev.style.marginTop = '10px';
                    this.parentNode.appendChild(prev);
                }
                var reader = new FileReader();
                reader.onload = function (e) { prev.src = e.target.result; prev.style.display = 'block'; };
                reader.readAsDataURL(file);
            });
        });
    }

    /* 5. Row click -> chi tiết (cho table.clickable-row) */
    function clickableRows() {
        document.querySelectorAll('table.clickable-row tbody tr[data-href]').forEach(function (row) {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function (e) {
                if (e.target.closest('a, button, form, input, .no-row-click')) return;
                window.location.href = this.dataset.href;
            });
        });
    }

    /* 6. Bật tooltip Bootstrap nếu có jQuery */
    function tooltips() {
        if (window.jQuery && jQuery.fn.tooltip) {
            try { jQuery('[data-toggle="tooltip"], [title]').tooltip(); } catch (e) {}
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        sidebarMemory();
        lazyImages();
        confirmDelete();
        coverPreview();
        clickableRows();
        tooltips();
    });
})();
