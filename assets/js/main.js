/**
 * Lewis Edward — main behaviours.
 *
 * Scaffold: mobile-nav toggle, reveal-on-scroll, and animated counters. These
 * are lightweight, framework-free replacements for the small React wrappers.
 * Heavier motion (Three.js hero, page transitions) is enqueued separately as
 * those parts are ported.
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initHeaderScroll();
		initMobileNav();
		initMegaHoverIntent();
		initLondonClock();
		initObfuscatedEmail();
		initScrollReveal();
		initCounters();
		initParallaxImages();
		initBackToTop();
	});

	/* Back-to-top control(s). */
	function initBackToTop() {
		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		document.querySelectorAll('[data-scroll-top]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
			});
		});
	}

	/* Subtle vertical parallax for [data-parallax-img] images (taller than
	   their overflow-hidden wrapper). Shifts translateY across the wrapper's
	   pass through the viewport. Disabled under reduced motion. */
	function initParallaxImages() {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		var imgs = document.querySelectorAll('[data-parallax-img]');
		if (!imgs.length) return;
		var ticking = false;
		function update() {
			var vh = window.innerHeight || document.documentElement.clientHeight;
			imgs.forEach(function (img) {
				var wrap = img.parentElement;
				var r = wrap.getBoundingClientRect();
				var p = Math.min(Math.max((vh - r.top) / (vh + r.height), 0), 1);
				var shift = (-6 + 12 * p); // -6% .. 6%
				img.style.transform = 'translateY(' + shift.toFixed(2) + '%)';
			});
			ticking = false;
		}
		window.addEventListener('scroll', function () {
			if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
		}, { passive: true });
		update();
	}

	/* Assemble obfuscated emails on the client so the address never appears as
	   a literal string in the HTML source (scraper-resistant). */
	function initObfuscatedEmail() {
		var els = document.querySelectorAll('[data-obfuscated-email]');
		els.forEach(function (el) {
			var user = el.getAttribute('data-user');
			var domain = el.getAttribute('data-domain');
			var tld = el.getAttribute('data-tld');
			if (!user || !domain || !tld) return;
			var addr = user + '@' + domain + '.' + tld;
			el.textContent = addr;
			if (el.tagName === 'A') { el.setAttribute('href', 'mailto:' + addr); }
		});
	}

	/* Hide the header when scrolling down, reveal when scrolling up. */
	function initHeaderScroll() {
		var header = document.querySelector('[data-header]');
		if (!header) return;
		var prev = window.scrollY || 0;
		var ticking = false;

		function update() {
			var y = window.scrollY || 0;
			// Only hide once past a small threshold and when moving down.
			if (y > 10 && y > prev) {
				header.classList.add('is-hidden');
			} else {
				header.classList.remove('is-hidden');
			}
			prev = y;
			ticking = false;
		}
		window.addEventListener('scroll', function () {
			if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
		}, { passive: true });
	}

	/* Desktop mega dropdown: hover-intent with a 150ms close delay (matches
	   the React behaviour) plus click-outside close. CSS :hover is the
	   baseline; this adds the delay and keyboard/touch support. */
	function initMegaHoverIntent() {
		var items = document.querySelectorAll('.navbar--desktop .nav-item--mega');
		items.forEach(function (item) {
			var timer;
			item.addEventListener('mouseenter', function () {
				clearTimeout(timer);
				item.classList.add('is-open');
			});
			item.addEventListener('mouseleave', function () {
				timer = setTimeout(function () { item.classList.remove('is-open'); }, 150);
			});
			// Close on Escape.
			item.addEventListener('keyup', function (e) {
				if (e.key === 'Escape') item.classList.remove('is-open');
			});
		});
		document.addEventListener('click', function (e) {
			items.forEach(function (item) {
				if (!item.contains(e.target)) item.classList.remove('is-open');
			});
		});
	}

	/* Live London time in the status widget, updated every 30s. */
	function initLondonClock() {
		var els = document.querySelectorAll('[data-london-clock]');
		if (!els.length) return;
		function render() {
			var t = new Date().toLocaleTimeString('en-GB', {
				timeZone: 'Europe/London', hour: '2-digit', minute: '2-digit'
			});
			els.forEach(function (el) { el.textContent = t; });
		}
		render();
		setInterval(render, 30000);
	}

	/* Mobile navigation toggle. */
	function initMobileNav() {
		var toggle = document.querySelector('[data-nav-toggle]');
		var panel = document.querySelector('[data-mobile-nav]');
		if (!toggle || !panel) return;

		toggle.addEventListener('click', function () {
			var open = toggle.getAttribute('aria-expanded') === 'true';
			toggle.setAttribute('aria-expanded', String(!open));
			panel.hidden = open;
			document.body.classList.toggle('nav-open', !open);
		});

		// Close the drawer when a link inside it is tapped.
		panel.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				toggle.setAttribute('aria-expanded', 'false');
				panel.hidden = true;
				document.body.classList.remove('nav-open');
			}
		});
	}

	/* Reveal elements as they enter the viewport ([data-reveal]). */
	function initScrollReveal() {
		var els = document.querySelectorAll('[data-reveal]');
		if (!els.length || !('IntersectionObserver' in window)) {
			els.forEach(function (el) { el.classList.add('is-revealed'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-revealed');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });
		els.forEach(function (el) { io.observe(el); });
	}

	/* Count-up animation for [data-counter] (target in data-counter). */
	function initCounters() {
		var counters = document.querySelectorAll('[data-counter]');
		if (!counters.length || !('IntersectionObserver' in window)) return;

		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;
				animate(entry.target);
				io.unobserve(entry.target);
			});
		}, { threshold: 0.5 });
		counters.forEach(function (el) { io.observe(el); });

		function animate(el) {
			var target = parseFloat(el.getAttribute('data-counter')) || 0;
			var suffix = el.getAttribute('data-counter-suffix') || '';
			var dur = parseFloat(el.getAttribute('data-counter-duration')) || 1400;
			var start = performance.now();
			(function tick(now) {
				var p = Math.min((now - start) / dur, 1);
				var eased = 1 - Math.pow(1 - p, 3);
				el.textContent = Math.round(target * eased).toLocaleString() + suffix;
				if (p < 1) requestAnimationFrame(tick);
			})(start);
		}
	}
})();
