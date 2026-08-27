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
		initScrollProgress();
	});

	/* Top-of-page reading progress bar — fills as the page scrolls. */
	function initScrollProgress() {
		var bar = document.querySelector('[data-scroll-progress]');
		if (!bar) return;
		var ticking = false;
		function update() {
			var doc = document.documentElement;
			var max = (doc.scrollHeight - doc.clientHeight);
			var y = window.scrollY || doc.scrollTop || 0;
			var p = max > 0 ? y / max : 0;
			bar.style.width = (Math.min(Math.max(p, 0), 1) * 100).toFixed(2) + '%';
			ticking = false;
		}
		window.addEventListener('scroll', function () {
			if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
		}, { passive: true });
		window.addEventListener('resize', update);
		update();
	}

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

	/* Reveal elements as they enter the viewport ([data-reveal]).
	   Each container's content children fade + rise in a staggered sequence
	   (matching the live site's per-element entrance animations). Decorative
	   children (aria-hidden) and the CTA clip-reveal title are skipped. */
	function initScrollReveal() {
		var els = document.querySelectorAll('[data-reveal]');
		if (!els.length) return;

		function stagger(container) {
			var kids = container.children;
			var i = 0;
			for (var k = 0; k < kids.length; k++) {
				var c = kids[k];
				if (c.getAttribute('aria-hidden') === 'true') continue;
				if (c.hasAttribute('data-reveal-clip')) continue;
				c.style.transitionDelay = (Math.min(i, 10) * 0.09).toFixed(2) + 's';
				i++;
			}
		}

		if (!('IntersectionObserver' in window)) {
			els.forEach(function (el) { el.classList.add('is-revealed'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					stagger(entry.target);
					entry.target.classList.add('is-revealed');
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
		els.forEach(function (el) { io.observe(el); });

		// Safety net: never leave content hidden if something misfires.
		setTimeout(function () {
			els.forEach(function (el) {
				if (!el.classList.contains('is-revealed')) {
					stagger(el);
					el.classList.add('is-revealed');
				}
			});
		}, 2500);
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
			var raw = el.getAttribute('data-counter') || '';
			var target = parseFloat(raw) || 0;
			// Preserve decimals (e.g. 1.5 must not round up to 2).
			var dp = ( raw.split('.')[1] || '' ).length;
			var suffix = el.getAttribute('data-counter-suffix') || '';
			var dur = parseFloat(el.getAttribute('data-counter-duration')) || 1400;
			var start = performance.now();
			(function tick(now) {
				var p = Math.min((now - start) / dur, 1);
				var eased = 1 - Math.pow(1 - p, 3);
				var val = target * eased;
				el.textContent = ( dp ? val.toFixed(dp) : Math.round(val).toLocaleString() ) + suffix;
				if (p < 1) requestAnimationFrame(tick);
			})(start);
		}
	}

	/* FAQ accordion — smooth open/close. The slide is pure CSS (grid-rows on
	   .faq-a); JS just wraps the answer once and toggles the .is-open class. */
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('details.faq-item').forEach(function (d) {
			var summary = d.querySelector('summary');
			var body = summary && summary.nextElementSibling; // .faq-a
			if (!summary || !body) return;

			// Move the answer content into a single wrapper the grid can collapse.
			var inner = document.createElement('div');
			inner.className = 'faq-a__inner';
			while (body.firstChild) { inner.appendChild(body.firstChild); }
			body.appendChild(inner);

			// Keep the content rendered so the collapse can animate; visibility
			// is driven by the .is-open class instead of the native open state.
			d.open = true;
			d.classList.remove('is-open');

			summary.addEventListener('click', function (e) {
				e.preventDefault();
				d.classList.toggle('is-open');
			});
		});
	});
})();
