<?php
/**
 * Single sector — /sectors/{slug}. Industry landing pages share the service
 * fields ("Service — Details") and the grid service template.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
while ( have_posts() ) {
	the_post();
	get_template_part( 'template-parts/grid/service' );
}
get_footer();
