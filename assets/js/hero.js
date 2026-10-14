/**
 * Hero — rotating network globe with firing connections.
 *
 * Fibonacci points wired to their nearest neighbours form a glowing lime
 * wireframe mesh, depth-shaded and lit by a drifting light spot. Signal
 * pulses travel hop-to-hop along the edges, flaring each edge as they pass,
 * so the globe reads as a live network. Rotation + pulses pause offscreen
 * and under reduced-motion. Desktop only (container hidden <=1024px).
 */
(function () {
	'use strict';

	var POINT_COUNT = 680;
	var NEIGHBORS = 3;
	var ROT_SPEED = 0.16;
	var PULSES = 7;
	var LIME = '191,255,0';

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

	function key(a, b) { return a < b ? a + '_' + b : b + '_' + a; }

	// Nearest-neighbour edges + adjacency list + edge lookup.
	function buildGraph(pts, k) {
		var edges = [];
		var seen = {};
		var adj = [];
		var i;
		for (i = 0; i < pts.length; i++) { adj.push([]); }
		for (i = 0; i < pts.length; i++) {
			var d = [];
			for (var j = 0; j < pts.length; j++) {
				if (j === i) { continue; }
				var dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y, dz = pts[i].z - pts[j].z;
				d.push({ j: j, d: dx * dx + dy * dy + dz * dz });
			}
			d.sort(function (a, b) { return a.d - b.d; });
			for (var m = 0; m < k && m < d.length; m++) {
				var a = i, b = d[m].j, kk = key(a, b);
				if (seen[kk] !== undefined) { continue; }
				seen[kk] = edges.length;
				edges.push([a, b]);
				adj[a].push(b);
				adj[b].push(a);
			}
		}
		return { edges: edges, adj: adj, index: seen };
	}

	function initSphere(wrap) {
		var canvas = wrap.querySelector('.hero-sphere__canvas');
		if (!canvas) { return; }
		var ctx = canvas.getContext('2d');
		if (!ctx) { return; }

		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var pts = fibonacciSphere(POINT_COUNT);
		var g = buildGraph(pts, NEIGHBORS);
		var edges = g.edges, adj = g.adj, edgeIndex = g.index;
		var heat = new Float32Array(edges.length);

		var px = new Float32Array(pts.length);
		var py = new Float32Array(pts.length);
		var pz = new Float32Array(pts.length);
		var pl = new Float32Array(pts.length);
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var w = 0, h = 0, raf = 0, visible = true, angle = 0, last = 0;

		// Signal pulses travelling hop-to-hop along the mesh.
		function spawnPulse() {
			var from = (Math.random() * pts.length) | 0;
			if (!adj[from].length) { from = 0; }
			var to = adj[from][(Math.random() * adj[from].length) | 0];
			return { from: from, to: to, prev: from, t: 0, speed: 1.6 + Math.random() * 1.1, hops: 4 + ((Math.random() * 6) | 0) };
		}
		var pulses = [];
		if (!reduced) { for (var pi = 0; pi < PULSES; pi++) { pulses.push(spawnPulse()); } }

		function advancePulse(p, dt) {
			p.t += p.speed * dt;
			while (p.t >= 1) {
				p.t -= 1;
				p.hops--;
				if (p.hops <= 0) { var np = spawnPulse(); p.from = np.from; p.to = np.to; p.prev = np.prev; p.speed = np.speed; p.hops = np.hops; continue; }
				var nbrs = adj[p.to];
				var next = nbrs[(Math.random() * nbrs.length) | 0];
				if (nbrs.length > 1 && next === p.from) { next = nbrs[(nbrs.indexOf(next) + 1) % nbrs.length]; } // avoid instant backtrack
				p.prev = p.from; p.from = p.to; p.to = next;
			}
			var ei = edgeIndex[key(p.from, p.to)];
			if (ei !== undefined) { heat[ei] = 1; }
		}

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
			if (!reduced) { angle += dt * ROT_SPEED; }

			ctx.clearRect(0, 0, w, h);
			var cx = w / 2, cy = h / 2;
			var radius = Math.min(w, h) * 0.46;

			var lt = t * 0.00018;
			var lx = Math.cos(lt * 1.3) * 0.75, ly = Math.sin(lt) * 0.45, lz = 0.6;
			var ln = Math.hypot(lx, ly, lz);
			var sinA = Math.sin(angle), cosA = Math.cos(angle);
			var tiltS = Math.sin(-0.32), tiltC = Math.cos(-0.32);

			var i;
			for (i = 0; i < pts.length; i++) {
				var p = pts[i];
				var x1 = p.x * cosA - p.z * sinA;
				var z1 = p.x * sinA + p.z * cosA;
				var y2 = p.y * tiltC - z1 * tiltS;
				var z2 = p.y * tiltS + z1 * tiltC;
				px[i] = cx + x1 * radius; py[i] = cy + y2 * radius; pz[i] = z2;
				pl[i] = Math.max(0, (x1 * lx + y2 * ly + z2 * lz) / ln);
			}

			// Decay edge heat, advance pulses (they re-light their current edge).
			var decay = Math.exp(-3.2 * dt);
			for (i = 0; i < heat.length; i++) { heat[i] *= decay; }
			if (!reduced) { for (i = 0; i < pulses.length; i++) { advancePulse(pulses[i], dt); } }

			// Wireframe edges — brighter at the front and where a signal is passing.
			ctx.strokeStyle = 'rgb(' + LIME + ')';
			for (var e = 0; e < edges.length; e++) {
				var a = edges[e][0], b = edges[e][1];
				var df = ((pz[a] + pz[b]) * 0.5 + 1) * 0.5;
				var litE = (pl[a] + pl[b]) * 0.5;
				var hot = heat[e];
				var alpha = 0.03 + df * 0.13 + Math.pow(litE, 2) * 0.5 * df + hot * 0.6 * df;
				if (alpha <= 0.015) { continue; }
				ctx.globalAlpha = alpha < 0.95 ? alpha : 0.95;
				ctx.lineWidth = 1 + hot * 1.4 * df;
				ctx.beginPath();
				ctx.moveTo(px[a], py[a]);
				ctx.lineTo(px[b], py[b]);
				ctx.stroke();
			}
			ctx.lineWidth = 1;

			// Nodes.
			for (i = 0; i < pts.length; i++) {
				var z = pz[i];
				if (z < -0.05) { continue; }
				var d2 = (z + 1) * 0.5;
				var intensity = Math.pow(pl[i], 2.4);
				var size = (0.6 + intensity * 3.4) * (0.5 + d2 * 0.6);
				var na = (0.12 + intensity * 0.8) * (0.4 + d2 * 0.6);
				if (na <= 0.02 || size <= 0.2) { continue; }
				ctx.globalAlpha = na < 1 ? na : 1;
				ctx.fillStyle = intensity > 0.7 ? 'rgb(226,255,156)' : 'rgb(' + LIME + ')';
				ctx.beginPath();
				ctx.arc(px[i], py[i], size / 2, 0, Math.PI * 2);
				ctx.fill();
			}

			// Signal pulses — a bright near-white head with a lime glow.
			for (i = 0; i < pulses.length; i++) {
				var pu = pulses[i];
				var f = pu.from, to = pu.to, pt = pu.t;
				var sx = px[f] + (px[to] - px[f]) * pt;
				var sy = py[f] + (py[to] - py[f]) * pt;
				var pzv = pz[f] + (pz[to] - pz[f]) * pt;
				if (pzv < -0.1) { continue; }
				var pdf = (pzv + 1) * 0.5;
				ctx.globalAlpha = 0.22 * pdf;
				ctx.fillStyle = 'rgb(' + LIME + ')';
				ctx.beginPath();
				ctx.arc(sx, sy, (4.5 * pdf), 0, Math.PI * 2);
				ctx.fill();
				ctx.globalAlpha = 0.95 * pdf;
				ctx.fillStyle = 'rgb(236,255,190)';
				ctx.beginPath();
				ctx.arc(sx, sy, (1.7 * pdf) + 0.4, 0, Math.PI * 2);
				ctx.fill();
			}
			ctx.globalAlpha = 1;

			if (visible) { raf = requestAnimationFrame(draw); } else { raf = 0; }
		}

		last = performance.now();
		raf = requestAnimationFrame(draw);
	}

	/* Subtle mouse + scroll parallax on the sphere. */
	function initParallax(tile, wrap) {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
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
		document.querySelectorAll('[data-hero-sphere]').forEach(function (wrap) {
			initSphere(wrap);
			var tile = wrap.closest('.hero-tile--editorial');
			if (tile) { initParallax(tile, wrap); }
		});
	});
})();
