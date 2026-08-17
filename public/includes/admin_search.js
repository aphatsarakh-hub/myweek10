(function () {
    function defaultDisplay(el) {
        if (el.dataset.searchDisplay) {
            return el.dataset.searchDisplay;
        }
        if (el.tagName === 'TR') {
            return 'table-row';
        }
        if (el.classList.contains('payment-card') || el.classList.contains('service-card')) {
            return 'flex';
        }
        return '';
    }

    function bindSearch(input) {
        var selector = input.getAttribute('data-admin-search') || '.admin-search-item';
        var items = document.querySelectorAll(selector);
        var emptySel = input.getAttribute('data-admin-search-empty');
        var emptyEl = emptySel ? document.querySelector(emptySel) : null;

        function run() {
            var keyword = (input.value || '').toLowerCase().trim();
            var visible = 0;

            items.forEach(function (el) {
                var haystack = (el.getAttribute('data-searchable') || el.textContent || '').toLowerCase();
                var match = !keyword || haystack.indexOf(keyword) !== -1;
                el.style.display = match ? defaultDisplay(el) : 'none';
                if (match) {
                    visible += 1;
                }
            });

            if (emptyEl) {
                emptyEl.classList.toggle('hidden', visible > 0);
            }
        }

        input.addEventListener('input', run);
        return run;
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-admin-search-input]').forEach(bindSearch);
    });

    window.AdminSearch = { bind: bindSearch };
})();
