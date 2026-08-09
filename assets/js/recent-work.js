/**
 * Recent Work slider.
 *
 * Transform-based, one project per view. The scope element is the whole
 * .work-card (it contains BOTH the track and the prev/next buttons, which sit
 * in the footer — not inside the track wrapper). Prev/next slide the track with
 * a CSS transform, the progress bar reflects the current slide, and pointer
 * swipe/drag + keyboard arrows are supported. Percentage-based transforms keep
 * it responsive. Respects reduced motion.
 */
(function () {
	'use strict';

	function init(root) {
		var track = root.querySelector('[data-work-track]');
		if (!track) return;

		var slides = Array.prototype.slice.call(track.children);
		var total = slides.length;
		if (!total) return;

		var viewport = track.parentElement; // .work-carousel (overflow: hidden)
		var prevBtn = root.querySelector('[data-work-prev]');
		var nextBtn = root.querySelector('[data-work-next]');
		var progress = root.querySelector('[data-work-progress]');
		var loop = track.hasAttribute('data-work-loop') && total > 1;
		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		var index = 0;
		var dragging = false, pointerId = null, startX = 0, deltaX = 0, moved = false, vpWidth = 0;

		function render(animate) {
			track.style.transition = ( animate && !reduced ) ? 'transform 0.55s cubic-bezier(0.25,0.1,0.25,1)' : 'none';
			track.style.transform = 'translateX(' + ( -index * 100 ) + '%)';
			if (progress) progress.style.width = ( ( ( index + 1 ) / total ) * 100 ).toFixed(2) + '%';
			if (!loop) {
				if (prevBtn) prevBtn.disabled = ( index === 0 );
				if (nextBtn) nextBtn.disabled = ( index === total - 1 );
			}
		}

		function goTo(i) {
			index = loop ? ( ( i % total ) + total ) % total : Math.max(0, Math.min(total - 1, i));
			render(true);
		}

		if (prevBtn) prevBtn.addEventListener('click', function () { goTo(index - 1); });
		if (nextBtn) nextBtn.addEventListener('click', function () { goTo(index + 1); });

		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { e.preventDefault(); goTo(index + 1); }
			else if (e.key === 'ArrowLeft') { e.preventDefault(); goTo(index - 1); }
		});

		// Pointer swipe / drag.
		track.addEventListener('pointerdown', function (e) {
			if (e.button !== 0) return;
			dragging = true; moved = false; pointerId = e.pointerId;
			startX = e.clientX; deltaX = 0;
			vpWidth = viewport.getBoundingClientRect().width || 1;
			track.style.transition = 'none';
			if (track.setPointerCapture) track.setPointerCapture(e.pointerId);
			viewport.classList.add('is-grabbing');
		});
		track.addEventListener('pointermove', function (e) {
			if (!dragging || e.pointerId !== pointerId) return;
			deltaX = e.clientX - startX;
			if (Math.abs(deltaX) > 5) moved = true;
			track.style.transform = 'translateX(' + ( ( -index * 100 ) + ( deltaX / vpWidth ) * 100 ) + '%)';
		});
		function endDrag(e) {
			if (!dragging || e.pointerId !== pointerId) return;
			dragging = false; pointerId = null;
			viewport.classList.remove('is-grabbing');
			var threshold = vpWidth * 0.15;
			if (deltaX <= -threshold) goTo(index + 1);
			else if (deltaX >= threshold) goTo(index - 1);
			else render(true);
		}
		track.addEventListener('pointerup', endDrag);
		track.addEventListener('pointercancel', endDrag);
		track.addEventListener('lostpointercapture', endDrag);

		// Suppress the click that ends a drag so the card doesn't navigate.
		track.addEventListener('click', function (e) {
			if (moved) { e.preventDefault(); e.stopPropagation(); }
		}, true);

		render(false);
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-work-carousel]').forEach(init);
	});
})();
