/**
 * Smooth scroll.
 *
 * Scaffold: enables smooth in-page anchor scrolling and exposes a hook for a
 * future Lenis upgrade (the wrapper element carries [data-lenis-wrapper]).
 * Kept dependency-free for now; swap in Lenis here if 1:1 inertia is required.
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		document.addEventListener('click', function (e) {
			var link = e.target.closest('a[href^="#"]');
			if (!link) return;
			var id = link.getAttribute('href');
			if (!id || id === '#') return;
			var target = document.querySelector(id);
			if (!target) return;
			e.preventDefault();
			target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
			history.pushState(null, '', id);
		});
	});
})();
