/**
 * Single Work — "Explore some features" slider.
 *
 * Horizontal, scroll-snapping track with prev/next arrows and click-drag to
 * pan. Arrows enable/disable at the track ends. Vanilla port of the React
 * ExploreFeatures component.
 */
(function () {
	'use strict';

	function init(root) {
		var track = root.querySelector('[data-features-track]');
		var prev = root.querySelector('[data-features-prev]');
		var next = root.querySelector('[data-features-next]');
		if (!track) return;

		function updateArrows() {
			var max = track.scrollWidth - track.clientWidth;
			if (prev) prev.disabled = track.scrollLeft <= 10;
			if (next) next.disabled = track.scrollLeft >= max - 10;
		}

		function step(dir) {
			var amount = track.clientWidth * 0.7;
			track.scrollBy({ left: dir === 'left' ? -amount : amount, behavior: 'smooth' });
		}

		if (prev) prev.addEventListener('click', function () { step('left'); });
		if (next) next.addEventListener('click', function () { step('right'); });
		track.addEventListener('scroll', updateArrows, { passive: true });
		window.addEventListener('resize', updateArrows);

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
			if (Math.abs(dx) > 4) moved = true;
			track.scrollLeft = startScroll - dx;
		});
		window.addEventListener('mouseup', function () {
			dragging = false;
			track.classList.remove('is-grabbing');
		});
		// Prevent a drag from also triggering link/image clicks.
		track.addEventListener('click', function (e) {
			if (moved) { e.preventDefault(); e.stopPropagation(); }
		}, true);

		updateArrows();
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-features]').forEach(init);
	});
})();
