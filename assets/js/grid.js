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
	var top = document.querySelector('.skin-grid .g-hero');
	if (top) { top.classList.add('is-in'); }
	var rev = new IntersectionObserver(function (entries) {
		entries.forEach(function (e) {
			if (e.isIntersecting) { e.target.classList.add('is-in'); rev.unobserve(e.target); }
		});
	}, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
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
	void root;
})();
