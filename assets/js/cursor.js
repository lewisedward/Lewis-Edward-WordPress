/**
 * Custom cursor.
 *
 * Scaffold: a dot + trailing ring that swap to the lime accent over
 * interactive elements. Disabled for touch / reduced-motion users. Colours are
 * read from the CSS custom properties (--cursor-*) so light surfaces invert
 * automatically.
 */
(function () {
	'use strict';

	var fine = window.matchMedia('(pointer: fine)').matches;
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (!fine || reduce) return;

	document.addEventListener('DOMContentLoaded', function () {
		var dot = document.createElement('div');
		var ring = document.createElement('div');
		dot.className = 'le-cursor-dot';
		ring.className = 'le-cursor-ring';
		document.body.appendChild(ring);
		document.body.appendChild(dot);
		document.body.classList.add('has-custom-cursor');

		var mx = 0, my = 0, rx = 0, ry = 0;

		window.addEventListener('mousemove', function (e) {
			mx = e.clientX; my = e.clientY;
			dot.style.transform = 'translate(' + mx + 'px,' + my + 'px)';
		});

		(function loop() {
			rx += (mx - rx) * 0.18;
			ry += (my - ry) * 0.18;
			ring.style.transform = 'translate(' + rx + 'px,' + ry + 'px)';
			requestAnimationFrame(loop);
		})();

		document.addEventListener('mouseover', function (e) {
			if (e.target.closest('a, button, [data-cursor="hover"]')) {
				document.body.classList.add('is-cursor-hover');
			}
		});
		document.addEventListener('mouseout', function (e) {
			if (e.target.closest('a, button, [data-cursor="hover"]')) {
				document.body.classList.remove('is-cursor-hover');
			}
		});
	});
})();
