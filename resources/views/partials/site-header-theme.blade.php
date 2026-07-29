<script>
(function () {
    var storageKey = 'alpha-theme-mode';

    function currentMode() {
        try {
            return localStorage.getItem(storageKey) === 'dark' ? 'dark' : 'light';
        } catch (e) {
            return 'light';
        }
    }

    function setMode(mode) {
        var dark = mode === 'dark';
        document.body.classList.toggle('alpha-dark', dark);
        document.querySelectorAll('.alpha-logo-img--light').forEach(function (img) {
            img.hidden = dark;
        });
        document.querySelectorAll('.alpha-logo-img--dark').forEach(function (img) {
            img.hidden = !dark;
        });
        document.querySelectorAll('.alpha-theme-toggle').forEach(function (button) {
            button.setAttribute('aria-pressed', dark ? 'true' : 'false');
            button.setAttribute('title', dark ? 'Switch to day mode' : 'Switch to night mode');
        });
        try {
            localStorage.setItem(storageKey, dark ? 'dark' : 'light');
        } catch (e) {}
    }

    document.addEventListener('DOMContentLoaded', function () {
        setMode(currentMode());
        document.querySelectorAll('.alpha-theme-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                setMode(document.body.classList.contains('alpha-dark') ? 'light' : 'dark');
            });
        });
    });
})();
</script>
