/**
 * Work archive category filter.
 *
 * Filters the project grid by Work Category (data-categories on each item),
 * toggles the active pill, and updates the visible count. "All" shows every
 * project.
 */
(function () {
	'use strict';

	function init(root) {
		var filters = root.querySelector('[data-work-filters]');
		var grid = root.querySelector('[data-work-grid]');
		if (!filters || !grid) return;

		// Remove any bogus filter pills injected by hook-profiler/debug plugins
		// (or served from stale cache) — their label is always a bare number,
		// while real category names never are. "All" is always kept.
		Array.prototype.slice.call(filters.querySelectorAll('.work-filter')).forEach(function (btn) {
			var label = (btn.textContent || '').trim();
			var isAll = btn.getAttribute('data-filter') === 'all';
			if (!isAll && /^\d+$/.test(label)) {
				btn.parentNode && btn.parentNode.removeChild(btn);
			}
		});

		var buttons = Array.prototype.slice.call(filters.querySelectorAll('[data-filter]'));
		var items = Array.prototype.slice.call(grid.querySelectorAll('.work-item'));
		var countEl = root.querySelector('[data-work-visible]');

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
		document.querySelectorAll('.site-main--work').forEach(init);
	});
})();
