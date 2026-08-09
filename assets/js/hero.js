/**
 * Hero — halftone dot sphere.
 *
 * Faithful port of the React HeroDotSphere: a Fibonacci-distributed point
 * cloud rendered to a 2D canvas as soft lime circles, lit by a slowly drifting
 * light spot. Rotation pauses offscreen and under reduced-motion. Plus a
 * subtle mouse/scroll parallax on the sphere, matching the original.
 */
(function () {
	'use strict';

	var POINT_COUNT = 1400;
	var ROT_SPEED = 0.16; // rad/s

	function fibonacciSphere(n) {
		var pts = [];
		var golden = Math.PI * (3 - Math.sqrt(5));
		for (var i = 0; i < n; i++) {
			var y = 1 - (i / (n - 1)) * 2;
			var r = Math.sqrt(Math.max(0, 1 - y * y));
			var theta = golden * i;
			pts.push({ x: Math.cos(theta) * r, y: y, z: Math.sin(theta) * r });
		}
		return pts;
	}

	function initSphere(wrap) {
		var canvas = wrap.querySelector('.hero-sphere__canvas');
		if (!canvas) return;
		var ctx = canvas.getContext('2d');
		if (!ctx) return;

		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var pts = fibonacciSphere(POINT_COUNT);
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var w = 0, h = 0, raf = 0, visible = true, angle = 0, last = 0;

		function resize() {
			var rect = wrap.getBoundingClientRect();
			w = rect.width; h = rect.height;
			dpr = Math.min(window.devicePixelRatio || 1, 2);
			canvas.width = Math.max(1, Math.floor(w * dpr));
			canvas.height = Math.max(1, Math.floor(h * dpr));
			canvas.style.width = w + 'px';
			canvas.style.height = h + 'px';
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
		}
		resize();
		if ('ResizeObserver' in window) { new ResizeObserver(resize).observe(wrap); }
		else { window.addEventListener('resize', resize); }

		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (entries) {
				visible = entries[0].isIntersecting;
				if (visible && !raf) { last = performance.now(); raf = requestAnimationFrame(draw); }
			}, { threshold: 0.01 }).observe(wrap);
		}

		function draw(t) {
			var dt = Math.min((t - last) / 1000, 0.05);
			last = t;
			if (!reduced) angle += dt * ROT_SPEED;

			ctx.clearRect(0, 0, w, h);
			var cx = w / 2, cy = h / 2;
			var radius = Math.min(w, h) * 0.44;

			var lt = t * 0.00018;
			var lx = Math.cos(lt * 1.3) * 0.75;
			var ly = Math.sin(lt) * 0.45;
			var lz = 0.6;
			var ln = Math.hypot(lx, ly, lz);

			var sinA = Math.sin(angle), cosA = Math.cos(angle);
			var tiltS = Math.sin(-0.32), tiltC = Math.cos(-0.32);

			for (var i = 0; i < pts.length; i++) {
				var p = pts[i];
				var x1 = p.x * cosA - p.z * sinA;
				var z1 = p.x * sinA + p.z * cosA;
				var y2 = p.y * tiltC - z1 * tiltS;
				var z2 = p.y * tiltS + z1 * tiltC;
				if (z2 < -0.02) continue;

				var sx = cx + x1 * radius;
				var sy = cy + y2 * radius;
				var dot = (x1 * lx + y2 * ly + z2 * lz) / ln;
				var lit = Math.max(0, dot);
				var intensity = Math.pow(lit, 2.6);
				var depth = 0.35 + z2 * 0.65;
				var size = (0.9 + intensity * 5.2) * depth;
				var alpha = (0.16 + intensity * 0.84) * depth;
				if (alpha <= 0.02 || size <= 0.15) continue;

				ctx.globalAlpha = Math.min(1, alpha);
				ctx.fillStyle = '#bfff00';
				ctx.beginPath();
				ctx.arc(sx, sy, size / 2, 0, Math.PI * 2);
				ctx.fill();
			}
			ctx.globalAlpha = 1;

			if (visible) raf = requestAnimationFrame(draw);
			else raf = 0;
		}

		last = performance.now();
		raf = requestAnimationFrame(draw);
	}

	/* Subtle mouse + scroll parallax on the sphere (matches the React feel). */
	function initParallax(tile, wrap) {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		var tx = 0, ty = 0, cxp = 0, cyp = 0, sy = 0, running = false;

		function apply() {
			cxp += (tx - cxp) * 0.1;
			cyp += (ty - cyp) * 0.1;
			wrap.style.transform = 'translate(' + cxp.toFixed(2) + 'px,' + (cyp + sy).toFixed(2) + 'px)';
			if (Math.abs(tx - cxp) > 0.1 || Math.abs(ty - cyp) > 0.1) {
				requestAnimationFrame(apply);
			} else { running = false; }
		}
		function kick() { if (!running) { running = true; requestAnimationFrame(apply); } }

		tile.addEventListener('mousemove', function (e) {
			var r = tile.getBoundingClientRect();
			tx = ((e.clientX - r.left) / r.width - 0.5) * 18;
			ty = ((e.clientY - r.top) / r.height - 0.5) * 18;
			kick();
		});
		tile.addEventListener('mouseleave', function () { tx = 0; ty = 0; kick(); });
		window.addEventListener('scroll', function () {
			var r = tile.getBoundingClientRect();
			var vh = window.innerHeight;
			var progress = Math.max(-1, Math.min(1, (r.top + r.height / 2 - vh / 2) / vh));
			sy = progress * -20;
			kick();
		}, { passive: true });
	}

	document.addEventListener('DOMContentLoaded', function () {
		var wrap = document.querySelector('[data-hero-sphere]');
		if (!wrap) return;
		initSphere(wrap);
		var tile = wrap.closest('.hero-tile--editorial');
		if (tile) initParallax(tile, wrap);
	});
})();
