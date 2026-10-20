/**
 * Custom cursor — a dot + trailing ring that grows over interactive elements
 * and shows a label for elements carrying a data-cursor value ("View", "Read"…).
 * Disabled for touch / reduced-motion users. Elements are position:fixed (see
 * CSS) so they never affect page layout or scroll height.
 */
(function () {
	'use strict';

	var fine = window.matchMedia('(pointer: fine)').matches;
	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (!fine || reduce) return;

	document.addEventListener('DOMContentLoaded', function () {
		var dot = document.createElement('div');
		var ring = document.createElement('div');
		var label = document.createElement('span');
		dot.className = 'le-cursor-dot';
		ring.className = 'le-cursor-ring';
		label.className = 'le-cursor__label';
		dot.appendChild(label); // Label lives inside the dot — the dot is what morphs into "MORE".
		document.body.appendChild(ring);
		document.body.appendChild(dot);
		document.body.classList.add('has-custom-cursor', 'is-cursor-hidden');

		// mx/my = mouse. rx/ry = ring position (eased trail). The dot is locked
		// to the pointer for crispness; the ring follows with a frame-rate-
		// independent spring and a subtle stretch when moving fast.
		var mx = 0, my = 0, rx = 0, ry = 0, shown = false, placed = false, last = 0;

		window.addEventListener('mousemove', function (e) {
			mx = e.clientX; my = e.clientY;
			if (!placed) { rx = mx; ry = my; placed = true; } // no swoop from (0,0)
			if (!shown) { shown = true; document.body.classList.remove('is-cursor-hidden'); }
		}, { passive: true });

		function loop(now) {
			var dt = last ? Math.min((now - last) / 1000, 0.05) : 0.016;
			last = now;

			// Frame-rate-independent easing: same feel at 60Hz and 120Hz+.
			var k = 1 - Math.exp(-13 * dt);
			rx += (mx - rx) * k;
			ry += (my - ry) * k;

			// Gentle elastic scale — the ring swells slightly while it catches up.
			var lag = Math.min(Math.hypot(mx - rx, my - ry) * 0.012, 0.32);

			// Dot sits exactly on the pointer; ring trails and breathes.
			dot.style.transform = 'translate(' + mx + 'px,' + my + 'px)';
			ring.style.transform = 'translate(' + rx + 'px,' + ry + 'px) scale(' + (1 + lag).toFixed(3) + ')';
			requestAnimationFrame(loop);
		}
		requestAnimationFrame(loop);

		function target(el) {
			return el && el.closest ? el.closest('a, button, [role="button"], [data-cursor]') : null;
		}

		// Zones that suppress the "View" label so the plain DOT cursor shows
		// instead of the big labelled disc (e.g. the project text panel).
		// Marked with [data-cursor-ignore].
		function noLabelZone(el) {
			return el && el.closest ? el.closest('[data-cursor-ignore]') : null;
		}

		// Lime / light surfaces where the white cursor would be invisible; flip
		// it to near black. [data-surface="light"] already exists in the theme;
		// [data-cursor-invert] marks lime tiles that aren't full light surfaces.
		// Two checks: a selector fast-path for surfaces that only turn light on
		// hover (their computed background is still mid-transition when the
		// mouseover fires), then a luminance read of the first painted
		// background colour under the pointer, so any white / pale cell, card
		// or button is caught without having to be listed here.
		var LIGHT_SELECTOR = '[data-surface="light"], [data-cursor-invert], .work-filter.is-active, .jr-filter.is-active, .arrow-link__badge, .btn--primary, .btn-cta, .contact-quote__cta, .gform_button, .page-numbers.current, .g-lime, .g-header__cta, .g-header__logo, .g-btn';
		function lightByBackground(el) {
			var node = el, depth = 0, cs, m, p, a, l;
			while (node && node.nodeType === 1 && depth < 14) {
				cs = window.getComputedStyle(node);
				m = /rgba?\(([^)]+)\)/.exec(cs.backgroundColor || '');
				if (m) {
					p = m[1].split(/[\s,\/]+/).map(parseFloat);
					a = p.length > 3 ? p[3] : 1;
					if (a >= 0.5) {
						l = (0.2126 * p[0] + 0.7152 * p[1] + 0.0722 * p[2]) / 255;
						return l > 0.55;
					}
				}
				// Photo / video backgrounds: assume dark (the site's default).
				if (cs.backgroundImage && cs.backgroundImage !== 'none' && cs.backgroundImage.indexOf('gradient') === -1) return false;
				node = node.parentNode;
				depth++;
			}
			return false;
		}
		function onLight(el) {
			if (!el || !el.closest) return false;
			if (el.closest(LIGHT_SELECTOR)) return true;
			return lightByBackground(el);
		}

		document.addEventListener('mouseover', function (e) {
			// Recolour the cursor dark whenever it's over a lime / light surface.
			if (onLight(e.target)) {
				document.body.classList.add('is-cursor-onlight');
			} else {
				document.body.classList.remove('is-cursor-onlight');
			}

			// Inside a no-label zone: keep the custom cursor but force the plain
			// dot+ring (no "View" disc, and no accent/hover growth).
			if (noLabelZone(e.target)) {
				document.body.classList.remove('is-cursor-hover', 'is-cursor-label');
				label.textContent = '';
				return;
			}

			var t = target(e.target);
			if (!t) return;
			document.body.classList.add('is-cursor-hover');
			var lab = t.getAttribute('data-cursor');
			if (lab && lab.toLowerCase() !== 'hover') {
				label.textContent = lab;
				document.body.classList.add('is-cursor-label');
			}
		});
		document.addEventListener('mouseout', function (e) {
			var t = target(e.target);
			if (!t) return;
			if (e.relatedTarget && t.contains(e.relatedTarget)) return;
			document.body.classList.remove('is-cursor-hover', 'is-cursor-label');
			label.textContent = '';
		});

		document.documentElement.addEventListener('mouseleave', function () {
			document.body.classList.add('is-cursor-hidden');
		});
		document.documentElement.addEventListener('mouseenter', function () {
			document.body.classList.remove('is-cursor-hidden');
		});
	});
})();
