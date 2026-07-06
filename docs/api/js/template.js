(function(){
    window.addEventListener('load', () => {
        const el = document.querySelector('.phpdocumentor-on-this-page__content')
        if (!el) {
            return;
        }

        const observer = new IntersectionObserver(
            ([e]) => {
                e.target.classList.toggle("-stuck", e.intersectionRatio < 1);
            },
            {threshold: [1]}
        );

        observer.observe(el);
    })
})();
function openSvg(svg) {
    // convert to a valid XML source
    const as_text = new XMLSerializer().serializeToString(svg);
    // store in a Blob
    const blob = new Blob([as_text], { type: "image/svg+xml" });
    // create an URI pointing to that blob
    const url = URL.createObjectURL(blob);
    const win = open(url);
    // so the Garbage Collector can collect the blob
    win.onload = (evt) => URL.revokeObjectURL(url);
};


var svgs = document.querySelectorAll(".phpdocumentor-uml-diagram svg");
for( var i=0,il = svgs.length; i< il; i ++ ) {
    svgs[i].onclick = (evt) => openSvg(evt.target);
}
(function () {
    'use strict';

    var STORAGE_KEY = 'dlstorage-docs-theme';
    var root = document.documentElement;

    function systemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function getTheme() {
        try {
            return localStorage.getItem(STORAGE_KEY) || systemTheme();
        } catch (e) {
            return systemTheme();
        }
    }

    function updatePrismTheme(theme) {
        var light = document.getElementById('prism-theme-light');
        var dark = document.getElementById('prism-theme-dark');
        if (!light || !dark) return;
        light.disabled = theme === 'dark';
        dark.disabled = theme !== 'dark';
    }

    function updateToggleState(theme) {
        var isDark = theme === 'dark';
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            btn.setAttribute('aria-label', isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
            btn.title = isDark ? 'Modo oscuro activo' : 'Modo claro activo';
        });
    }

    function updateMetaTheme(theme) {
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.setAttribute('content', theme === 'dark' ? '#161618' : '#ffffff');
        }
        root.style.colorScheme = theme;
    }

    function applyTheme(theme, persist) {
        root.setAttribute('data-theme', theme);
        updateMetaTheme(theme);
        updatePrismTheme(theme);
        updateToggleState(theme);
        if (persist) {
            try {
                localStorage.setItem(STORAGE_KEY, theme);
            } catch (e) { /* noop */ }
        }
        if (typeof cssVars === 'function') {
            cssVars({});
        }
    }

    function toggleTheme() {
        applyTheme(getTheme() === 'dark' ? 'light' : 'dark', true);
    }

    function bindToggles() {
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';
            btn.addEventListener('click', toggleTheme);
        });
    }

    applyTheme(root.getAttribute('data-theme') || getTheme(), false);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindToggles);
    } else {
        bindToggles();
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (event) {
        try {
            if (!localStorage.getItem(STORAGE_KEY)) {
                applyTheme(event.matches ? 'dark' : 'light', false);
            }
        } catch (e) {
            applyTheme(event.matches ? 'dark' : 'light', false);
        }
    });

    window.dlstorageTheme = {
        get: getTheme,
        set: function (theme) { applyTheme(theme, true); },
        toggle: toggleTheme
    };
})();