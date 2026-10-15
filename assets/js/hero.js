/**
 * Hero — dotted Earth, broadcasting from London.
 *
 * Real continents: Fibonacci surface points are kept only where a baked-in
 * land mask says there's land, so the dots form the actual landmasses, lit by
 * a drifting sun (day/night terminator) with a soft body and atmospheric rim.
 * London sits on the UK as a bright anchor that pings, and connection arcs fire
 * out along great-circle paths from London to brands across the globe.
 *
 * Front hemisphere only (solid). Rotation/arcs pause offscreen and under
 * reduced-motion. Desktop only (container hidden <=1024px).
 */
(function () {
	'use strict';

	var GEN = 5200;          // candidate points; land ones are kept
	var ROT_SPEED = 0.15;
	var ARCS = 6;
	var LIME = '191,255,0';
	var LONDON = { x: 0.6225, y: 0.7826, z: 0 }; // ~51.5N, lon 0 (Greenwich)
	var MW = 150, MH = 75;
	var MASK = '649,15,1,2,47,1,63,1,11,2,4,21,14,1,103,1,2,2,1,1,9,15,47,2,1,5,12,1,40,1,1,1,6,2,17,11,34,1,6,2,1,1,1,20,3,5,22,3,14,6,8,6,8,9,2,1,20,1,10,2,3,40,8,2,4,21,3,2,2,4,2,1,2,4,6,8,21,9,2,1,2,54,2,1,4,17,2,13,5,1,1,1,1,1,6,1,11,1,12,4,1,4,3,58,7,30,9,2,6,3,9,1,12,71,6,7,1,2,1,19,7,3,9,1,21,5,2,55,2,5,14,1,8,13,1,4,6,6,23,2,4,1,1,2,3,48,8,1,28,19,4,7,23,1,4,3,3,48,7,3,29,20,1,6,1,2,20,4,3,55,7,1,31,29,22,43,1,16,41,14,1,9,3,2,20,60,41,15,3,7,25,6,1,51,42,22,28,4,1,1,2,5,4,4,1,35,43,21,27,4,4,2,1,4,1,2,2,3,2,32,4,1,40,20,27,4,6,1,2,1,1,6,1,2,2,28,2,1,5,1,41,19,34,1,10,36,2,1,3,2,43,17,29,7,11,35,4,3,45,14,30,10,2,3,4,36,3,1,49,6,36,17,1,6,1,30,53,6,6,1,27,20,1,5,4,26,55,4,8,1,26,20,2,6,6,21,1,1,55,4,7,1,25,22,1,8,5,18,60,3,2,2,6,1,22,23,1,7,6,5,4,5,1,1,62,5,30,23,2,4,8,4,5,5,6,1,61,4,27,24,1,2,11,2,8,4,5,1,63,1,28,25,13,2,9,3,70,1,3,6,19,27,10,3,9,1,72,1,1,8,19,25,13,1,7,1,9,1,66,11,17,2,4,18,20,3,5,1,69,12,24,15,22,2,3,3,68,13,24,14,23,2,2,4,1,2,65,15,22,13,25,2,1,3,2,1,4,1,1,1,58,19,20,11,27,1,5,1,6,5,56,18,20,11,29,2,7,1,2,4,57,17,20,12,41,1,59,16,22,8,1,2,38,1,63,15,21,12,3,1,31,4,3,1,60,14,21,11,2,2,31,9,13,1,47,13,21,10,3,2,30,10,61,12,23,9,3,2,28,14,59,9,26,9,3,2,27,16,58,9,26,7,34,16,57,10,27,6,35,15,57,9,29,4,36,6,1,8,57,8,30,1,39,1,8,6,57,7,81,4,58,6,96,2,45,4,87,2,9,1,47,4,97,1,48,3,97,1,49,3,147,3,148,2,900,1,1,1,44,1,2,1,1,2,1,1,92,1,1,2,48,7,35,2,3,1,38,2,10,5,22,9,1,1,1,4,7,6,7,1,3,6,6,7,6,7,21,33,17,36,1,5,2,34,7,1,9,42,1,3,5,42,3,22,1,15,1,3,1,129,2,6,3,2,1,65,1,72,1,1,3,2,1,17,1,39,1,97,2,305';

	function buildMask() {
		var runs = MASK.split(',');
		var bits = new Uint8Array(MW * MH);
		var idx = 0, val = 0, i, j;
		for (i = 0; i < runs.length; i++) {
			var c = +runs[i];
			for (j = 0; j < c; j++) { bits[idx++] = val; }
			val ^= 1;
		}
		return bits;
	}

	function initSphere(wrap) {
		var canvas = wrap.querySelector('.hero-sphere__canvas');
		if (!canvas) { return; }
		var ctx = canvas.getContext('2d');
		if (!ctx) { return; }

		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var bits = buildMask();
		function isLand(vx, vy, vz) {
			var lat = Math.asin(vy < -1 ? -1 : vy > 1 ? 1 : vy) * 57.29578;
			var lon = Math.atan2(vz, vx) * 57.29578;
			var x = ((lon + 180) / 360 * MW) | 0; if (x < 0) { x = 0; } else if (x >= MW) { x = MW - 1; }
			var y = ((90 - lat) / 180 * MH) | 0; if (y < 0) { y = 0; } else if (y >= MH) { y = MH - 1; }
			return bits[y * MW + x] === 1;
		}

		// All surface points; land ones form continents, ocean ones are faint fill.
		var ax = [], ay = [], az = [], isL = [], landIdx = [], i;
		var golden = Math.PI * (3 - Math.sqrt(5));
		for (i = 0; i < GEN; i++) {
			var y = 1 - (i / (GEN - 1)) * 2;
			var r = Math.sqrt(Math.max(0, 1 - y * y));
			var th = golden * i;
			var vx = Math.cos(th) * r, vz = Math.sin(th) * r;
			ax.push(vx); ay.push(y); az.push(vz);
			var ld = isLand(vx, y, vz) ? 1 : 0; isL.push(ld);
			if (ld) { landIdx.push(ax.length - 1); }
		}
		var N = ax.length;
		var px = new Float32Array(N), py = new Float32Array(N), pz = new Float32Array(N), pl = new Float32Array(N);

		// Global cities — markers + labels; London is the broadcast origin.
		var CITY_DEFS = [
			{ n: 'London', lat: 51.5, lon: 0 },
			{ n: 'New York', lat: 40.71, lon: -74.0 },
			{ n: 'Barcelona', lat: 41.39, lon: 2.17 },
			{ n: 'Los Angeles', lat: 34.05, lon: -118.24 },
			{ n: 'Zurich', lat: 47.37, lon: 8.54 },
			{ n: 'Hong Kong', lat: 22.32, lon: 114.17 },
			{ n: 'Melbourne', lat: -37.81, lon: 144.96 },
			{ n: 'Auckland', lat: -36.85, lon: 174.76 }
		];
		var cities = [];
		for (i = 0; i < CITY_DEFS.length; i++) {
			var cd = CITY_DEFS[i];
			var cla = cd.lat * Math.PI / 180, clo = cd.lon * Math.PI / 180, ccr = Math.cos(cla);
			var cvx = ccr * Math.cos(clo), cvy = Math.sin(cla), cvz = ccr * Math.sin(clo);
			var cidx = landIdx[0], cbest = 1e9;
			for (var cj = 0; cj < landIdx.length; cj++) {
				var li = landIdx[cj];
				var ex = ax[li] - cvx, ey = ay[li] - cvy, ez = az[li] - cvz;
				var ed = ex * ex + ey * ey + ez * ez;
				if (ed < cbest) { cbest = ed; cidx = li; }
			}
			cities.push({ n: cd.n, idx: cidx });
		}
		var london = cities[0].idx;
		var Lx = ax[london], Ly = ay[london], Lz = az[london];

		function spawnArc() {
			var to, ang, tries = 0;
			do {
				to = cities[1 + ((Math.random() * (cities.length - 1)) | 0)].idx;
				var dot = Lx * ax[to] + Ly * ay[to] + Lz * az[to];
				ang = Math.acos(dot < -1 ? -1 : dot > 1 ? 1 : dot);
				tries++;
			} while (ang < 0.5 && tries < 8);
			return { to: to, ang: ang, t: 0, speed: 0.45 + Math.random() * 0.45, arrived: false };
		}
		var arcs = [];
		if (!reduced) { for (i = 0; i < ARCS; i++) { arcs.push(spawnArc()); } }
		var pings = [], pingTimer = 0.3, arrivals = [];

		var dpr = Math.min(window.devicePixelRatio || 1, 2);
		var w = 0, h = 0, raf = 0, visible = true, angle = 0, last = 0;
		var sinA = 0, cosA = 1, tiltS = Math.sin(-0.32), tiltC = Math.cos(-0.32);
		var cx = 0, cy = 0, radius = 0;

		function project(vx, vy, vz, out) {
			var x1 = vx * cosA - vz * sinA;
			var z1 = vx * sinA + vz * cosA;
			var y2 = vy * tiltC - z1 * tiltS;
			var z2 = vy * tiltS + z1 * tiltC;
			out[0] = cx + x1 * radius; out[1] = cy + y2 * radius; out[2] = z2;
		}

		function resize() {
			var rect = wrap.getBoundingClientRect();
			w = rect.width; h = rect.height;
			dpr = Math.min(window.devicePixelRatio || 1, 2);
			canvas.width = Math.max(1, Math.floor(w * dpr));
			canvas.height = Math.max(1, Math.floor(h * dpr));
			canvas.style.width = w + 'px'; canvas.style.height = h + 'px';
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

		var tmp = [0, 0, 0], a0 = [0, 0, 0], a1 = [0, 0, 0];
		function slerp(u, ang, out) {
			var s = Math.sin(ang);
			if (s < 1e-4) { out[0] = Lx; out[1] = Ly; out[2] = Lz; return; }
			var ca = Math.sin((1 - u) * ang) / s, cb = Math.sin(u * ang) / s;
			var to = this.to;
			out[0] = Lx * ca + ax[to] * cb;
			out[1] = Ly * ca + ay[to] * cb;
			out[2] = Lz * ca + az[to] * cb;
		}

		function draw(t) {
			var dt = Math.min((t - last) / 1000, 0.05);
			last = t;
			if (!reduced) { angle += dt * ROT_SPEED; }

			ctx.clearRect(0, 0, w, h);
			cx = w / 2; cy = h / 2; radius = Math.min(w, h) * 0.45;
			var lt = t * 0.00018;
			var lx = Math.cos(lt * 1.3) * 0.92, ly = Math.sin(lt) * 0.5, lz = 0.38, ln = Math.hypot(lx, ly, lz);
			sinA = Math.sin(angle); cosA = Math.cos(angle);

			var k;
			for (k = 0; k < N; k++) {
				var x1 = ax[k] * cosA - az[k] * sinA;
				var z1 = ax[k] * sinA + az[k] * cosA;
				var y2 = ay[k] * tiltC - z1 * tiltS;
				var z2 = ay[k] * tiltS + z1 * tiltC;
				px[k] = cx + x1 * radius; py[k] = cy + y2 * radius; pz[k] = z2;
				pl[k] = Math.max(0, (x1 * lx + y2 * ly + z2 * lz) / ln);
			}

			// Planet body.
			var hx = cx + lx * radius * 0.42, hy = cy + ly * radius * 0.42;
			var body = ctx.createRadialGradient(hx, hy, radius * 0.06, cx, cy, radius);
			body.addColorStop(0, 'rgba(40,50,18,0.5)');
			body.addColorStop(0.5, 'rgba(15,19,10,0.46)');
			body.addColorStop(0.9, 'rgba(8,10,6,0.5)');
			body.addColorStop(1, 'rgba(6,8,5,0.0)');
			ctx.globalAlpha = 1; ctx.fillStyle = body;
			ctx.beginPath(); ctx.arc(cx, cy, radius, 0, Math.PI * 2); ctx.fill();
			// Atmosphere rim.
			var rim = ctx.createRadialGradient(cx, cy, radius * 0.72, cx, cy, radius * 1.08);
			rim.addColorStop(0, 'rgba(191,255,0,0)');
			rim.addColorStop(0.8, 'rgba(191,255,0,0)');
			rim.addColorStop(0.93, 'rgba(191,255,0,0.07)');
			rim.addColorStop(0.985, 'rgba(191,255,0,0.03)');
			rim.addColorStop(1, 'rgba(191,255,0,0)');
			ctx.fillStyle = rim;
			ctx.beginPath(); ctx.arc(cx, cy, radius * 1.07, 0, Math.PI * 2); ctx.fill();

			// Surface dots — faint oceans fill the sphere, continents glow; day/night terminator.
			for (k = 0; k < N; k++) {
				if (pz[k] < -0.02) { continue; }
				var bright = Math.pow(pl[k], 1.4);
				var alpha, size, col;
				if (isL[k]) {
					alpha = 0.34 + bright * 0.66; size = 0.9 + bright * 1.9;
					col = bright > 0.72 ? 'rgb(228,255,160)' : 'rgb(' + LIME + ')';
				} else {
					alpha = 0.05 + bright * 0.11; size = 0.6 + bright * 0.5;
					col = 'rgb(' + LIME + ')';
				}
				if (alpha <= 0.03) { continue; }
				ctx.globalAlpha = alpha < 1 ? alpha : 1;
				ctx.fillStyle = col;
				ctx.beginPath(); ctx.arc(px[k], py[k], size / 2, 0, Math.PI * 2); ctx.fill();
			}

			if (!reduced) {
				pingTimer -= dt;
				if (pingTimer <= 0) { pings.push({ t: 0 }); pingTimer = 1.8; }
				for (k = pings.length - 1; k >= 0; k--) { pings[k].t += dt; if (pings[k].t > 1.4) { pings.splice(k, 1); } }
				for (k = arrivals.length - 1; k >= 0; k--) { arrivals[k].t += dt; if (arrivals[k].t > 1.0) { arrivals.splice(k, 1); } }
			}

			// Connection arcs from London along great-circle paths.
			for (k = 0; k < arcs.length; k++) {
				var arc = arcs[k];
				if (!reduced) {
					arc.t += arc.speed * dt;
					if (arc.t >= 1 && !arc.arrived) { arrivals.push({ to: arc.to, t: 0 }); arc.arrived = true; }
					if (arc.t >= 1.25) { arcs[k] = spawnArc(); continue; }
				}
				var tt = arc.t < 1 ? arc.t : 1;
				var steps = Math.max(2, Math.ceil(tt / 0.045));
				var prevOk = false, prx = 0, pry = 0;
				var slf = slerp.bind(arc);
				for (var s = 0; s <= steps; s++) {
					var u = (s / steps) * tt;
					slf(u, arc.ang, tmp);
					project(tmp[0], tmp[1], tmp[2], a1);
					var ok = a1[2] > -0.02;
					if (ok && prevOk) {
						var ua = u / (tt || 1);
						var al = Math.pow(ua, 1.3) * 0.62 * ((a1[2] + 1) * 0.5);
						if (al > 0.015) {
							ctx.globalAlpha = al < 0.75 ? al : 0.75;
							ctx.strokeStyle = 'rgb(' + LIME + ')';
							ctx.lineWidth = 1.1;
							ctx.beginPath(); ctx.moveTo(prx, pry); ctx.lineTo(a1[0], a1[1]); ctx.stroke();
						}
					}
					prevOk = ok; prx = a1[0]; pry = a1[1];
				}
				// Bright head of the signal.
				if (arc.t <= 1) {
					slf(tt, arc.ang, tmp); project(tmp[0], tmp[1], tmp[2], a1);
					if (a1[2] > -0.02) {
						var hdf = (a1[2] + 1) * 0.5;
						ctx.globalAlpha = 0.22 * hdf; ctx.fillStyle = 'rgb(' + LIME + ')';
						ctx.beginPath(); ctx.arc(a1[0], a1[1], 4.5 * hdf, 0, Math.PI * 2); ctx.fill();
						ctx.globalAlpha = 0.95 * hdf; ctx.fillStyle = 'rgb(236,255,190)';
						ctx.beginPath(); ctx.arc(a1[0], a1[1], 1.7 * hdf + 0.4, 0, Math.PI * 2); ctx.fill();
					}
				}
			}

			// Arrival ripples — a signal landing at its destination.
			for (k = 0; k < arrivals.length; k++) {
				var av = arrivals[k], ti = av.to;
				if (pz[ti] < -0.02) { continue; }
				var adf = (pz[ti] + 1) * 0.5, at = av.t;
				var ease = 1 - Math.pow(1 - at, 2);
				var ga = Math.max(0, 0.5 * (1 - at)) * adf;
				if (ga > 0.02) {
					var gr = (18 + ease * 26) * (0.6 + adf * 0.4);
					var gg = ctx.createRadialGradient(px[ti], py[ti], 0, px[ti], py[ti], gr);
					gg.addColorStop(0, 'rgba(191,255,0,' + ga.toFixed(3) + ')');
					gg.addColorStop(1, 'rgba(191,255,0,0)');
					ctx.globalAlpha = 1; ctx.fillStyle = gg;
					ctx.beginPath(); ctx.arc(px[ti], py[ti], gr, 0, Math.PI * 2); ctx.fill();
				}
				ctx.strokeStyle = 'rgb(' + LIME + ')';
				var ra = Math.max(0, 0.65 * (1 - at)) * adf;
				if (ra > 0.012) { ctx.globalAlpha = ra < 0.8 ? ra : 0.8; ctx.lineWidth = 1.4; ctx.beginPath(); ctx.arc(px[ti], py[ti], 2 + ease * 38, 0, Math.PI * 2); ctx.stroke(); }
				var at2 = at - 0.18;
				if (at2 > 0) { var e2 = 1 - Math.pow(1 - at2, 2); var ra2 = Math.max(0, 0.4 * (1 - at2)) * adf; if (ra2 > 0.012) { ctx.globalAlpha = ra2 < 0.6 ? ra2 : 0.6; ctx.lineWidth = 1; ctx.beginPath(); ctx.arc(px[ti], py[ti], 2 + e2 * 40, 0, Math.PI * 2); ctx.stroke(); } }
				var fa = Math.max(0, 1 - at * 1.7) * adf;
				if (fa > 0.02) {
					ctx.globalAlpha = 0.3 * fa; ctx.fillStyle = 'rgb(' + LIME + ')';
					ctx.beginPath(); ctx.arc(px[ti], py[ti], 6 * adf, 0, Math.PI * 2); ctx.fill();
					ctx.globalAlpha = 0.95 * fa; ctx.fillStyle = 'rgb(240,255,200)';
					ctx.beginPath(); ctx.arc(px[ti], py[ti], 2.4 * adf + 0.5, 0, Math.PI * 2); ctx.fill();
				}
			}

			// London (origin) pings.
			var lzL = pz[london], ldf = (lzL + 1) * 0.5;
			if (lzL > -0.02) {
				for (k = 0; k < pings.length; k++) {
					var pr = pings[k].t * 54;
					var pa = Math.max(0, 0.4 * (1 - pings[k].t / 1.4)) * ldf;
					if (pa <= 0.01) { continue; }
					ctx.globalAlpha = pa; ctx.strokeStyle = 'rgb(' + LIME + ')'; ctx.lineWidth = 1;
					ctx.beginPath(); ctx.arc(px[london], py[london], pr, 0, Math.PI * 2); ctx.stroke();
				}
			}

			// City markers + labels (London is the origin, slightly larger).
			ctx.font = '600 10px Arial, sans-serif';
			ctx.textBaseline = 'bottom'; ctx.textAlign = 'left';
			for (k = 0; k < cities.length; k++) {
				var ci = cities[k].idx;
				if (pz[ci] < -0.02) { continue; }
				var cdf = (pz[ci] + 1) * 0.5;
				var origin = (ci === london);
				var gr = (origin ? 16 : 11) * cdf + 3;
				var cg = ctx.createRadialGradient(px[ci], py[ci], 0, px[ci], py[ci], gr);
				cg.addColorStop(0, 'rgba(191,255,0,' + ((origin ? 0.33 : 0.22) * cdf).toFixed(3) + ')');
				cg.addColorStop(1, 'rgba(191,255,0,0)');
				ctx.globalAlpha = 1; ctx.fillStyle = cg;
				ctx.beginPath(); ctx.arc(px[ci], py[ci], gr, 0, Math.PI * 2); ctx.fill();
				ctx.globalAlpha = 0.96; ctx.fillStyle = 'rgb(244,255,212)';
				ctx.beginPath(); ctx.arc(px[ci], py[ci], (origin ? 2.9 : 2.1) * cdf + 0.7, 0, Math.PI * 2); ctx.fill();
				if (cdf > 0.42) {
					var la = (cdf - 0.42) / 0.58; if (la > 1) { la = 1; }
					var nm = cities[k].n.toUpperCase();
					var ox = px[ci] + 10, oy = py[ci] - 9;
					try { ctx.letterSpacing = '1.5px'; } catch (e) {}
					var tw = ctx.measureText(nm).width;
					ctx.globalAlpha = 0.42 * la; ctx.strokeStyle = 'rgb(' + LIME + ')'; ctx.lineWidth = 1;
					ctx.beginPath(); ctx.moveTo(px[ci] + 2.5, py[ci] - 2.5); ctx.lineTo(ox, oy); ctx.lineTo(ox + tw + 6, oy); ctx.stroke();
					ctx.globalAlpha = (origin ? 0.95 : 0.78) * la; ctx.fillStyle = 'rgb(214,255,120)';
					ctx.fillText(nm, ox + 3, oy - 2);
					try { ctx.letterSpacing = '0px'; } catch (e) {}
				}
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
			var r = tile.getBoundingClientRect(), vh = window.innerHeight;
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
