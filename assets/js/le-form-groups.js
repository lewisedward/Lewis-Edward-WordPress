/**
 * Form section cards.
 *
 * Gravity Forms outputs a flat field list. When a form uses Section fields, this
 * wraps each Section + the fields that follow it into a `.le-fieldgroup` card,
 * reproducing the design's numbered section cards. Runs on load and after every
 * Gravity Forms AJAX re-render. Forms without Section fields are left untouched.
 */
(function () {
	'use strict';

	function wrapFields(fields) {
		if (!fields || fields.dataset.leGrouped === '1') return;
		if (!fields.querySelector('.gsection')) return; // Only sectioned forms.

		var kids = Array.prototype.slice.call(fields.children);
		var groups = [];
		var current = null;

		kids.forEach(function (el) {
			if (el.classList && el.classList.contains('gsection')) {
				current = document.createElement('div');
				current.className = 'le-fieldgroup';
				groups.push(current);
			} else if (!current) {
				current = document.createElement('div');
				current.className = 'le-fieldgroup le-fieldgroup--nohead';
				groups.push(current);
			}
			current.appendChild(el); // Moves el out of `fields` into the group.
		});

		groups.forEach(function (g) { fields.appendChild(g); });
		fields.classList.add('is-grouped');
		fields.dataset.leGrouped = '1';
	}

	function run() {
		document.querySelectorAll('.le-form-page .gform_fields').forEach(wrapFields);
	}

	document.addEventListener('DOMContentLoaded', run);
	// Re-wrap after Gravity Forms AJAX renders (validation / confirmation swaps).
	if (window.jQuery) {
		window.jQuery(document).on('gform_post_render', run);
	}
	document.addEventListener('gform/postRender', run);
})();
