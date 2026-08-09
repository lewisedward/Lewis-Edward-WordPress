/**
 * Testimonials slider.
 *
 * Auto-advancing quote slider with blur-fade between slides, prev/next, a
 * play/pause toggle, an autoplay progress bar and a position counter.
 *
 * Icon state is class-based (.tst-btn.is-paused) rather than the `hidden`
 * attribute, because the theme's base `svg { display:block }` overrides the
 * UA `[hidden]` rule. While playing it shows the PAUSE icon; once paused it
 * shows the PLAY icon. Autoplay does NOT pause on hover. Respects reduced motion.
 */
(function () {
	'use strict';

	function init(root) {
		var slides = Array.prototype.slice.call(root.querySelectorAll('[data-tst-slide]'));
		if (!slides.length) return;

		var prevBtn = root.querySelector('[data-tst-prev]');
		var nextBtn = root.querySelector('[data-tst-next]');
		var toggle = root.querySelector('[data-tst-toggle]');
		var bar = root.querySelector('[data-tst-progress]');
		var current = root.querySelector('[data-tst-current]');

		var total = slides.length;
		var active = 0;
		var progress = 0;
		var paused = false;
		var interval = parseInt(root.getAttribute('data-interval'), 10) || 7000;
		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var step = 100 / (interval / 50);

		function pad(n) { return String(n).padStart(2, '0'); }

		function show(i) {
			active = (i + total) % total;
			slides.forEach(function (s, idx) {
				var on = idx === active;
				s.classList.toggle('is-active', on);
				s.hidden = !on;
			});
			if (current) current.textContent = pad(active + 1);
			progress = 0;
			if (bar) bar.style.width = '0%';
		}

		function go(dir) { show(active + dir); }

		function setPaused(p) {
			paused = p;
			if (toggle) {
				toggle.classList.toggle('is-paused', p);
				toggle.setAttribute('aria-pressed', String(p));
				toggle.setAttribute('aria-label', p ? 'Play autoplay' : 'Pause autoplay');
			}
		}

		if (prevBtn) prevBtn.addEventListener('click', function () { go(-1); });
		if (nextBtn) nextBtn.addEventListener('click', function () { go(1); });
		if (toggle) toggle.addEventListener('click', function () { setPaused(!paused); });

		show(0);
		if (!reduced) {
			setInterval(function () {
				if (paused) return;
				progress += step;
				if (progress >= 100) { go(1); return; }
				if (bar) bar.style.width = progress + '%';
			}, 50);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-testimonials]').forEach(init);
	});
})();
