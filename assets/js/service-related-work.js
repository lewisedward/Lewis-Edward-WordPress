/**
 * Single Service — "Featured work" slider.
 *
 * Horizontal scroll of related Work case studies with prev/next arrows,
 * click-drag to pan and a scroll-progress bar. Vanilla port of the React
 * ServiceRelatedWork component.
 */
(function () {
	'use strict';

	function init(root) {
		var track = root.querySelector('[data-svc-work-track]');
		var prev = root.querySelector('[data-svc-work-prev]');
		var next = root.querySelector('[data-svc-work-next]');
		var progress = root.querySelector('[data-svc-work-progress]');
		if (!track) return;

		function updateProgress() {
			var max = track.scrollWidth - track.clientWidth;
			var pct = max > 0 ? (track.scrollLeft / max) * 100 : 0;
			if (progress) progress.style.width = pct + '%';
		}

		function step(dir) {
			var card = track.firstElementChild;
			var cardW = card ? card.offsetWidth : 500;
			var gap = parseFloat(getComputedStyle(track).columnGap) || 32;
			track.scrollBy({ left: dir === 'left' ? -(cardW + gap) : cardW + gap, behavior: 'smooth' });
		}

		if (prev) prev.addEventListener('click', function () { step('left'); });
		if (next) next.addEventListener('click', function () { step('right'); });
		track.addEventListener('scroll', updateProgress, { passive: true });
		window.addEventListener('resize', updateProgress);

		// Click-drag to pan (desktop).
		var dragging = false, startX = 0, startScroll = 0, moved = false;
		track.addEventListener('mousedown', function (e) {
			dragging = true; moved = false;
			startX = e.pageX; startScroll = track.scrollLeft;
			track.classList.add('is-grabbing');
		});
		window.addEventListener('mousemove', function (e) {
			if (!dragging) return;
			var dx = e.pageX - startX;
			if (Math.abs(dx) > 5) moved = true;
			track.scrollLeft = startScroll - dx;
		});
		window.addEventListener('mouseup', function () {
			dragging = false;
			track.classList.remove('is-grabbing');
		});
		track.addEventListener('click', function (e) {
			if (moved) { e.preventDefault(); e.stopPropagation(); }
		}, true);

		updateProgress();
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-svc-work]').forEach(init);
	});
})();
