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

		// Custom eased scroll — one card per click, smooth glide (instead of the
		// browser's abrupt native "smooth", which also jumped ~1.5 cards).
		var animId = 0;
		function easeInOutCubic(t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; }
		function animateTo(to, dur) {
			cancelAnimationFrame(animId);
			var max = track.scrollWidth - track.clientWidth;
			to = Math.max(0, Math.min(to, max));
			var start = track.scrollLeft, change = to - start, startTime = 0;
			if (Math.abs(change) < 1) return;
			function frame(now) {
				if (!startTime) startTime = now;
				var t = Math.min((now - startTime) / dur, 1);
				track.scrollLeft = start + change * easeInOutCubic(t);
				if (t < 1) animId = requestAnimationFrame(frame);
			}
			animId = requestAnimationFrame(frame);
		}
		function cardStep() {
			var card = track.querySelector('.wk-feature');
			var styles = getComputedStyle(track);
			var gap = parseFloat(styles.columnGap || styles.gap) || 0;
			return card ? card.getBoundingClientRect().width + gap : track.clientWidth * 0.5;
		}
		function step(dir) {
			var amount = cardStep();
			animateTo(track.scrollLeft + (dir === 'left' ? -amount : amount), 550);
		}

		if (prev) prev.addEventListener('click', function () { step('left'); });
		if (next) next.addEventListener('click', function () { step('right'); });
		track.addEventListener('scroll', updateArrows, { passive: true });
		window.addEventListener('resize', updateArrows);

		// Click-drag to pan (desktop).
		var dragging = false, startX = 0, startScroll = 0, moved = false;
		track.addEventListener('mousedown', function (e) {
			cancelAnimationFrame(animId); // stop any arrow glide before dragging
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
