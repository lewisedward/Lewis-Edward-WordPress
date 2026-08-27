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

		// mx/my = mouse. dx/dy = dot position (fast). rx/ry = ring position (slow,
		// so the ring trails behind the dot as it moves).
		var mx = 0, my = 0, dx = 0, dy = 0, rx = 0, ry = 0, shown = false;

		window.addEventListener('mousemove', function (e) {
			mx = e.clientX; my = e.clientY;
			if (!shown) { shown = true; document.body.classList.remove('is-cursor-hidden'); }
		}, { passive: true });

		(function loop() {
			dx += (mx - dx) * 0.9;   // dot tracks the mouse almost exactly
			dy += (my - dy) * 0.9;
			rx += (mx - rx) * 0.15;  // ring lags, trailing the dot
			ry += (my - ry) * 0.15;
			dot.style.transform = 'translate(' + dx + 'px,' + dy + 'px)';
			ring.style.transform = 'translate(' + rx + 'px,' + ry + 'px)';
			requestAnimationFrame(loop);
		})();

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
		function onLight(el) {
			return el && el.closest ? el.closest('[data-surface="light"], [data-cursor-invert], .work-filter.is-active, .jr-filter.is-active, .arrow-link__badge, .btn--primary, .btn-cta, .contact-quote__cta, .gform_button, .page-numbers.current') : null;
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
