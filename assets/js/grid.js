/* Grid skin behaviours: reveal on scroll, counters, videos that play only in view. */
(function () {
	'use strict';
	var root = document.documentElement;
	var body = document.body;
	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (!('IntersectionObserver' in window)) { return; }
	body.classList.add('g-js');

	/* Reveal: cells of a row stagger in the first time the row enters the viewport. */
	var rows = document.querySelectorAll('.skin-grid .g-row');
	rows.forEach(function (row) {
		var cells = row.querySelectorAll(':scope > .g-cell');
		cells.forEach(function (c, i) { c.style.setProperty('--i', String(Math.min(i, 8))); });
	});
	var top = document.querySelector('.skin-grid .g-hero') || document.querySelector('.skin-grid .g-main > .g-row');
	if (top) { top.classList.add('is-in'); }
	var rev = new IntersectionObserver(function (entries) {
		entries.forEach(function (e) {
			if (e.isIntersecting) { e.target.classList.add('is-in'); rev.unobserve(e.target); }
		});
	}, { rootMargin: '0px', threshold: 0.05 });
	rows.forEach(function (r) { if (!r.classList.contains('is-in')) { rev.observe(r); } });

	/* Counters: "15+", "100+", "1.5" count up from zero, keeping any prefix/suffix. */
	var nums = document.querySelectorAll('.skin-grid .g-fact__num, .skin-grid .g-stat__num, .skin-grid .g-accent');
	function animate(el) {
		var text = el.textContent.trim();
		var m = text.match(/^([^0-9]*)(\d[\d,]*(?:\.\d+)?)(.*)$/);
		if (!m) { return; }
		var target = parseFloat(m[2].replace(/,/g, ''));
		var decimals = (m[2].split('.')[1] || '').length;
		var start = null, dur = 1200;
		function step(ts) {
			if (start === null) { start = ts; }
			var p = Math.min((ts - start) / dur, 1);
			var eased = 1 - Math.pow(1 - p, 3);
			var v = (target * eased).toFixed(decimals);
			el.textContent = m[1] + v + m[3];
			if (p < 1) { requestAnimationFrame(step); } else { el.textContent = text; }
		}
		requestAnimationFrame(step);
	}
	if (!reduce && nums.length) {
		var cnt = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) { animate(e.target); cnt.unobserve(e.target); }
			});
		}, { threshold: 0.4 });
		nums.forEach(function (n) { cnt.observe(n); });
	}

	/* Videos: play while visible, pause otherwise (saves battery, keeps loops in sync with the eye). */
	var vids = document.querySelectorAll('.skin-grid video.g-ph__video');
	if (vids.length) {
		var vo = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				var v = e.target;
				if (e.isIntersecting) { var p = v.play(); if (p && p.catch) { p.catch(function () {}); } }
				else { v.pause(); }
			});
		}, { threshold: 0.2 });
		vids.forEach(function (v) { vo.observe(v); });
	}
	/* Testimonials: one at a time, auto-advancing, with prev/next and a progress hairline. */
	var tst = document.querySelector('.skin-grid .g-tst');
	var stage = tst && tst.querySelector('[data-tst]');
	var items = stage ? Array.prototype.slice.call(stage.querySelectorAll('.g-tst__item')) : [];
	if (items.length > 1) {
		var cur = 0, timer = null, dur = 7000;
		var counter = tst.querySelector('[data-tst-current]');
		var bar = tst.querySelector('.g-tst__bar i');
		function show(n) {
			cur = (n + items.length) % items.length;
			items.forEach(function (it, i) { it.classList.toggle('is-active', i === cur); });
			if (counter) { counter.textContent = (cur < 9 ? '0' : '') + (cur + 1); }
			if (bar) { bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = ''; }
		}
		function start() {
			if (reduce) { return; }
			stop(); tst.classList.add('is-running');
			timer = setInterval(function () { show(cur + 1); }, dur);
		}
		function stop() { if (timer) { clearInterval(timer); timer = null; } tst.classList.remove('is-running'); }
		tst.querySelector('[data-tst-prev]').addEventListener('click', function () { show(cur - 1); start(); });
		tst.querySelector('[data-tst-next]').addEventListener('click', function () { show(cur + 1); start(); });
		tst.addEventListener('mouseenter', stop);
		tst.addEventListener('mouseleave', start);
		var to = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) { if (e.isIntersecting) { start(); } else { stop(); } });
		}, { threshold: 0.3 });
		to.observe(tst);
	}
	/* Parallax: media inside .g-ph frames drifts a few percent as the frame
	   crosses the viewport (the frame is overflow-hidden and the media is
	   scaled up 12% in CSS, so nothing shows through). Only in-view frames
	   are measured. Off under reduced motion and on coarse pointers. */
	var coarse = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
	var media = Array.prototype.slice.call(document.querySelectorAll('.skin-grid .g-ph:not(.g-svc__bg) > img, .skin-grid .g-ph:not(.g-svc__bg) > video'));
	if (!reduce && !coarse && media.length) {
		body.classList.add('g-parallax');
		var live = [];
		var pending = false;
		function paint() {
			pending = false;
			var vh = window.innerHeight || root.clientHeight;
			for (var i = 0; i < live.length; i++) {
				var el = live[i];
				var r = el.parentNode.getBoundingClientRect();
				var p = (vh - r.top) / (vh + r.height);
				p = p < 0 ? 0 : (p > 1 ? 1 : p);
				var shift = (p - 0.5) * 0.1 * r.height; // -5% .. +5% of the frame height
				el.style.setProperty('--py', shift.toFixed(1) + 'px');
			}
		}
		function queue() { if (!pending) { pending = true; window.requestAnimationFrame(paint); } }
		var po = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				var idx = live.indexOf(e.target);
				if (e.isIntersecting && idx === -1) { live.push(e.target); }
				else if (!e.isIntersecting && idx > -1) { live.splice(idx, 1); }
			});
			queue();
		}, { rootMargin: '10% 0px' });
		media.forEach(function (m) { po.observe(m); });
		window.addEventListener('scroll', queue, { passive: true });
		window.addEventListener('resize', queue);
		queue();
	}

	/* Filters (work page): buttons toggle tiles by their data-categories within the same row. */
	document.querySelectorAll('.skin-grid [data-g-filterable]').forEach(function (rootEl) {
		var btns = rootEl.querySelectorAll('[data-g-filter]');
		var items = rootEl.querySelectorAll('[data-categories]');
		var out = rootEl.querySelector('[data-g-visible]');
		btns.forEach(function (b) {
			b.addEventListener('click', function () {
				var f = b.getAttribute('data-g-filter');
				btns.forEach(function (x) { x.classList.toggle('is-active', x === b); });
				var n = 0;
				items.forEach(function (it) {
					var show = f === 'all' || (' ' + it.getAttribute('data-categories') + ' ').indexOf(' ' + f + ' ') > -1;
					it.classList.toggle('is-hidden', !show);
					if (show) { n++; }
				});
				if (out) { out.textContent = (n < 10 ? '0' : '') + n; }
			});
		});
	});
	void root;
})();
