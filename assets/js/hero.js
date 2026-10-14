/**
 * Hero — rotating network globe, broadcasting from London.
 *
 * Fibonacci points wired to nearest neighbours form a glowing lime mesh.
 * London sits at its real latitude as a bright anchor node that pings, and
 * signal pulses fire outward from London hop-to-hop along the edges toward
 * brands across the globe, flaring each edge as they pass. Meaning: a London
 * studio connected to forward-thinking brands worldwide.
 *
 * Rotation, pulses and pings pause offscreen and under reduced-motion.
 * Desktop only (container hidden <=1024px).
 */
(function () {
	'use strict';

	var POINT_COUNT = 680;
	var NEIGHBORS = 3;
	var ROT_SPEED = 0.16;
	var PULSES = 6;
	var LIME = '191,255,0';
	// London at ~51.5N on the sphere (longitude is arbitrary — the globe spins).
	var LONDON = { x: 0.6225, y: 0.7826, z: 0 };

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

	function buildGraph(pts, k) {
		var edges = [], seen = {}, adj = [], i;
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

		// London = nearest point to the London vector; distance of every node
		// from it drives the outward bias.
		var london = 0, best = 1e9, i;
		for (i = 0; i < pts.length; i++) {
			var dx = pts[i].x - LONDON.x, dy = pts[i].y - LONDON.y, dz = pts[i].z - LONDON.z;
			var dd = dx * dx + dy * dy + dz * dz;
			if (dd < best) { best = dd; london = i; }
		}
		var distL = new Float32Array(pts.length);
		for (i = 0; i < pts.length; i++) {
			var ex = pts[i].x - pts[london].x, ey = pts[i].y - pts[london].y, ez = pts[i].z - pts[london].z;
			distL[i] = Math.sqrt(ex * ex + ey * ey + ez * ez);
		}

		var px = new Float32Array(pts.length), py = new Float32Array(pts.length);
		var pz = new Float32Array(pts.length), pl = new Float32Array(pts.length);
		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var w = 0, h = 0, raf = 0, visible = true, angle = 0, last = 0;

		function spawnPulse() {
			var nbrs = adj[london];
			var to = nbrs[(Math.random() * nbrs.length) | 0];
			return { from: london, to: to, t: 0, speed: 1.5 + Math.random() * 1.0, hops: 5 + ((Math.random() * 7) | 0) };
		}
		var pulses = [];
		if (!reduced) { for (var pi = 0; pi < PULSES; pi++) { pulses.push(spawnPulse()); } }

		function advancePulse(p, dt) {
			p.t += p.speed * dt;
			while (p.t >= 1) {
				p.t -= 1;
				p.hops--;
				var nbrs = adj[p.to];
				// prefer outward neighbours (farther from London), never backtrack
				var cur = distL[p.to], out = [], n;
				for (n = 0; n < nbrs.length; n++) { if (nbrs[n] !== p.from && distL[nbrs[n]] > cur - 0.03) { out.push(nbrs[n]); } }
				if (p.hops <= 0 || !out.length) { var np = spawnPulse(); p.from = np.from; p.to = np.to; p.speed = np.speed; p.hops = np.hops; continue; }
				var next = out[(Math.random() * out.length) | 0];
				p.from = p.to; p.to = next;
			}
			var ei = edgeIndex[key(p.from, p.to)];
			if (ei !== undefined) { heat[ei] = 1; }
		}

		var pings = [], pingTimer = 0.4;

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
			var cx = w / 2, cy = h / 2, radius = Math.min(w, h) * 0.46;
			var lt = t * 0.00018;
			var lx = Math.cos(lt * 1.3) * 0.75, ly = Math.sin(lt) * 0.45, lz = 0.6, ln = Math.hypot(lx, ly, lz);
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

			var decay = Math.exp(-3.2 * dt);
			for (i = 0; i < heat.length; i++) { heat[i] *= decay; }
			if (!reduced) {
				for (i = 0; i < pulses.length; i++) { advancePulse(pulses[i], dt); }
				pingTimer -= dt;
				if (pingTimer <= 0) { pings.push({ t: 0 }); pingTimer = 1.7; }
				for (i = pings.length - 1; i >= 0; i--) { pings[i].t += dt; if (pings[i].t > 1.4) { pings.splice(i, 1); } }
			}

			// Edges — brighter at front and where a signal passes.
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
				ctx.beginPath(); ctx.moveTo(px[a], py[a]); ctx.lineTo(px[b], py[b]); ctx.stroke();
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
				ctx.beginPath(); ctx.arc(px[i], py[i], size / 2, 0, Math.PI * 2); ctx.fill();
			}

			// London pings — expanding rings from the origin.
			var lzL = pz[london], ldf = (lzL + 1) * 0.5;
			if (lzL > -0.1) {
				for (i = 0; i < pings.length; i++) {
					var pr = pings[i].t * 52;
					var pa = Math.max(0, 0.45 * (1 - pings[i].t / 1.4)) * ldf;
					if (pa <= 0.01) { continue; }
					ctx.globalAlpha = pa;
					ctx.strokeStyle = 'rgb(' + LIME + ')';
					ctx.lineWidth = 1;
					ctx.beginPath(); ctx.arc(px[london], py[london], pr, 0, Math.PI * 2); ctx.stroke();
				}
			}

			// Signal pulses firing outward from London.
			for (i = 0; i < pulses.length; i++) {
				var pu = pulses[i], f = pu.from, to = pu.to, ptt = pu.t;
				var sx = px[f] + (px[to] - px[f]) * ptt;
				var sy = py[f] + (py[to] - py[f]) * ptt;
				var pzv = pz[f] + (pz[to] - pz[f]) * ptt;
				if (pzv < -0.1) { continue; }
				var pdf = (pzv + 1) * 0.5;
				ctx.globalAlpha = 0.22 * pdf; ctx.fillStyle = 'rgb(' + LIME + ')';
				ctx.beginPath(); ctx.arc(sx, sy, 4.5 * pdf, 0, Math.PI * 2); ctx.fill();
				ctx.globalAlpha = 0.95 * pdf; ctx.fillStyle = 'rgb(236,255,190)';
				ctx.beginPath(); ctx.arc(sx, sy, 1.7 * pdf + 0.4, 0, Math.PI * 2); ctx.fill();
			}

			// London anchor node — the brightest, steadiest point on the globe.
			if (lzL > -0.1) {
				ctx.globalAlpha = 0.3 * ldf; ctx.fillStyle = 'rgb(' + LIME + ')';
				ctx.beginPath(); ctx.arc(px[london], py[london], 7 * ldf, 0, Math.PI * 2); ctx.fill();
				ctx.globalAlpha = Math.min(1, 0.9 * (0.6 + ldf * 0.4)); ctx.fillStyle = 'rgb(240,255,205)';
				ctx.beginPath(); ctx.arc(px[london], py[london], 2.6 * ldf + 0.6, 0, Math.PI * 2); ctx.fill();
			}
			ctx.globalAlpha = 1;

			if (visible) { raf = requestAnimationFrame(draw); } else { raf = 0; }
		}

		last = performance.now();
		raf = requestAnimationFrame(draw);
	}

	function initParallax(tile, wrap) {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
		var tx = 0, ty = 0, cxp = 0, cyp = 0, sy = 0, running = false;
		function apply() {
			cxp += (tx - cxp) * 0.1; cyp += (ty - cyp) * 0.1;
			wrap.style.transform = 'translate(' + cxp.toFixed(2) + 'px,' + (cyp + sy).toFixed(2) + 'px)';
			if (Math.abs(tx - cxp) > 0.1 || Math.abs(ty - cyp) > 0.1) { requestAnimationFrame(apply); } else { running = false; }
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
			var r = tile.getBoundingClientRect(); var vh = window.innerHeight;
			var progress = Math.max(-1, Math.min(1, (r.top + r.height / 2 - vh / 2) / vh));
			sy = progress * -20; kick();
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
