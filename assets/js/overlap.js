/**
 * Text / Image Overlap — desktop scroll effects.
 *
 * Port of TextImageOverlap.tsx interactions:
 *  - Parallax on the video (translate Y and X driven by the section's scroll
 *    progress through the viewport).
 *  - A lime "Services" wordmark revealed via clip-path wherever the video
 *    rectangle overlaps the wordmark rectangle (computed each frame).
 *
 * Desktop only; disabled under reduced motion (the wordmark then shows solid).
 */
(function () {
	'use strict';

	function clamp(v, min, max) { return Math.min(Math.max(v, min), max); }

	// Piecewise map matching the React imageX keyframes.
	function mapX(p) {
		if (p <= 0.2) return lerp(40, -40, p / 0.2);
		if (p <= 0.8) return lerp(-40, -200, (p - 0.2) / 0.6);
		return lerp(-200, -320, (p - 0.8) / 0.2);
	}
	function lerp(a, b, t) { return a + (b - a) * clamp(t, 0, 1); }

	function init(root) {
		var image = root.querySelector('[data-overlap-image]');
		var heading = root.querySelector('[data-overlap-heading]');
		var clip = root.querySelector('[data-overlap-clip]');
		if (!image || !heading || !clip) return;

		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduced) { clip.style.clipPath = 'inset(0 0 0 0)'; return; }

		var raf = 0;

		function progress() {
			var r = root.getBoundingClientRect();
			var vh = window.innerHeight || document.documentElement.clientHeight;
			// 0 when section top hits viewport bottom, 1 when section bottom hits top.
			return clamp((vh - r.top) / (vh + r.height), 0, 1);
		}

		function frame() {
			var p = progress();

			// Parallax the video.
			var y = lerp(36, -28, p);
			var x = mapX(p);
			image.style.transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px)';

			// Clip the lime wordmark to the overlap rectangle.
			var hr = heading.getBoundingClientRect();
			var ir = image.getBoundingClientRect();
			var l = Math.max(hr.left, ir.left);
			var t = Math.max(hr.top, ir.top);
			var rt = Math.min(hr.right, ir.right);
			var b = Math.min(hr.bottom, ir.bottom);

			if (rt > l && b > t && hr.width && hr.height) {
				var top = clamp(((t - hr.top) / hr.height) * 100, 0, 100);
				var left = clamp(((l - hr.left) / hr.width) * 100, 0, 100);
				var right = clamp(100 - ((rt - hr.left) / hr.width) * 100, 0, 100);
				var bottom = clamp(100 - ((b - hr.top) / hr.height) * 100, 0, 100);
				clip.style.clipPath = 'inset(' + top + '% ' + right + '% ' + bottom + '% ' + left + '%)';
			} else {
				clip.style.clipPath = 'inset(0 100% 0 0)';
			}

			raf = requestAnimationFrame(frame);
		}

		// Only run while the section is anywhere near the viewport.
		var active = false;
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (entries) {
				var vis = entries[0].isIntersecting;
				if (vis && !active) { active = true; raf = requestAnimationFrame(frame); }
				else if (!vis && active) { active = false; cancelAnimationFrame(raf); }
			}, { rootMargin: '200px 0px' }).observe(root);
		} else {
			raf = requestAnimationFrame(frame);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-overlap]').forEach(init);
	});
})();
