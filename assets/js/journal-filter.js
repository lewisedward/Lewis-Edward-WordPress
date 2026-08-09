/**
 * Journal archive category filter.
 *
 * Filters the entry list by category (data-categories on each <li>), toggles the
 * active pill, updates the visible count and shows an empty message when a
 * filter has no entries. "All" shows every entry.
 */
(function () {
	'use strict';

	function init(root) {
		var filters = root.querySelector('[data-jr-filters]');
		var list = root.querySelector('[data-jr-list]');
		if (!filters || !list) return;

		// Drop bogus pills injected by hook-profiler/debug plugins (numeric labels).
		Array.prototype.slice.call(filters.querySelectorAll('.jr-filter')).forEach(function (btn) {
			var label = (btn.textContent || '').trim();
			if (btn.getAttribute('data-filter') !== 'all' && /^\d+$/.test(label)) {
				btn.parentNode && btn.parentNode.removeChild(btn);
			}
		});

		var buttons = Array.prototype.slice.call(filters.querySelectorAll('[data-filter]'));
		var items = Array.prototype.slice.call(list.querySelectorAll('.jr-item'));
		var countEl = root.querySelector('[data-jr-visible]');
		var emptyEl = root.querySelector('[data-jr-empty]');

		function pad(n) { return String(n).padStart(2, '0'); }

		function apply(filter) {
			var visible = 0;
			items.forEach(function (item) {
				var cats = (item.getAttribute('data-categories') || '').split(/\s+/);
				var show = filter === 'all' || cats.indexOf(filter) !== -1;
				item.hidden = !show;
				if (show) visible++;
			});
			if (countEl) countEl.textContent = pad(visible);
			if (emptyEl) emptyEl.hidden = visible !== 0;
		}

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				buttons.forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				apply(btn.getAttribute('data-filter'));
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.site-main--journal').forEach(init);
	});
})();
