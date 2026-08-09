<?php
/**
 * Single: journal.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content/content', 'journal' );
endwhile;

get_footer();
