/**
 * Contact CTA interactions.
 *
 * Port of ContactCTA.tsx: hovering (or focusing) the word "project" flickers
 * through recent project thumbnails at ~400ms; and the heading clip-reveals up
 * when it scrolls into view. Both respect reduced motion.
 */
(function () {
	'use strict';

	function initFlicker(root) {
		var trigger = root.querySelector('[data-cta-project]');
		var preview = root.querySelector('[data-cta-preview]');
		if (!trigger || !preview) return;
		var imgs = Array.prototype.slice.call(preview.querySelectorAll('.cta-card__preview-img'));
		if (!imgs.length) return;
		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var idx = 0, timer = null;

		function set(i) {
			imgs.forEach(function (im, n) { im.classList.toggle('is-active', n === i); });
		}
		function start() {
			preview.classList.add('is-visible');
			if (reduced) { set(0); return; }
			idx = 0; set(0);
			timer = setInterval(function () {
				idx = (idx + 1) % imgs.length;
				set(idx);
			}, 400);
		}
		function stop() {
			preview.classList.remove('is-visible');
			if (timer) { clearInterval(timer); timer = null; }
		}

		trigger.addEventListener('mouseenter', start);
		trigger.addEventListener('mouseleave', stop);
		trigger.addEventListener('focus', start);
		trigger.addEventListener('blur', stop);
	}

	function initClip(root) {
		var title = root.querySelector('[data-reveal-clip]');
		if (!title) return;
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { title.classList.add('is-revealed'); return; }
		if (!('IntersectionObserver' in window)) { title.classList.add('is-revealed'); return; }
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) { title.classList.add('is-revealed'); io.unobserve(e.target); }
			});
		}, { threshold: 0.2 });
		io.observe(title);
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.cta-card').forEach(function (root) {
			initFlicker(root);
			initClip(root);
		});
	});
})();
