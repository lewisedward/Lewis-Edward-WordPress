<?php
/**
 * Journal (built-in Posts) shared helpers.
 *
 * Used by the blog index (home.php) and archives (archive.php) now that the
 * Journal listing is the WordPress "Posts page" for SEO. Keeps the row markup
 * and category pills identical across both.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True only for a genuine category term (rejects fake objects some
 * hook-profiler/debug plugins inject, which always have a numeric name).
 *
 * @param mixed $t Candidate term.
 * @return bool
 */
function le_is_real_category( $t ) {
	return ( $t instanceof WP_Term )
		&& 'category' === $t->taxonomy
		&& (int) $t->term_id > 0
		&& '' !== trim( (string) $t->name )
		&& ! is_numeric( trim( (string) $t->name ) );
}

/**
 * Real category terms for a post.
 *
 * @param int $id Post ID.
 * @return WP_Term[]
 */
function le_post_cats( $id ) {
	$terms = get_the_category( (int) $id );
	return is_array( $terms ) ? array_filter( $terms, 'le_is_real_category' ) : array();
}

/**
 * The Journal (Posts page) ID, or 0.
 *
 * @return int
 */
function le_journal_page_id() {
	return (int) get_option( 'page_for_posts' );
}

/**
 * Render the category filter pills as real links (SEO-friendly archive URLs).
 *
 * @param string $active_slug Slug of the current category ('' = All).
 */
function le_journal_pills( $active_slug = '' ) {
	$pp        = le_journal_page_id();
	$all_url   = $pp ? get_permalink( $pp ) : home_url( '/' );
	$all_label = $pp ? le_field( 'journalp_all_label', $pp ) : '';
	if ( '' === $all_label ) {
		$all_label = __( 'All entries', 'lewisedward' );
	}

	$terms = get_terms( array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	echo '<div class="jr-filters">';
	printf(
		'<a class="jr-filter%s" href="%s">%s</a>',
		'' === $active_slug ? ' is-active' : '',
		esc_url( $all_url ),
		esc_html( $all_label )
	);
	if ( is_array( $terms ) ) {
		foreach ( $terms as $t ) {
			if ( ! le_is_real_category( $t ) ) {
				continue;
			}
			printf(
				'<a class="jr-filter%s" href="%s">%s</a>',
				$t->slug === $active_slug ? ' is-active' : '',
				esc_url( get_category_link( $t ) ),
				esc_html( $t->name )
			);
		}
	}
	echo '</div>';
}

/**
 * Render one Journal index row (<li>) for the current post in the loop.
 *
 * @param int $num Display number.
 */
function le_journal_row( $num ) {
	$eid   = get_the_ID();
	$ecats = le_post_cats( $eid );
	$ecat  = ! empty( $ecats ) ? reset( $ecats )->name : '';
	$date  = get_the_date( 'M Y', $eid );
	$read  = le_reading_time( $eid ) . ' ' . __( 'min', 'lewisedward' );
	?>
	<li class="jr-item">
		<a class="jr-item__link" href="<?php echo esc_url( get_permalink( $eid ) ); ?>" data-cursor="Read">
			<span class="jr-item__num"><?php echo esc_html( str_pad( (string) $num, 2, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="jr-item__main">
				<h3 class="jr-item__title"><?php echo esc_html( get_the_title( $eid ) ); ?></h3>
				<span class="jr-item__meta-m">
					<?php if ( $ecat ) : ?><span><?php echo esc_html( $ecat ); ?></span><span class="jr-dot" aria-hidden="true"></span><?php endif; ?>
					<span><?php echo esc_html( $date ); ?></span>
				</span>
				<p class="jr-item__excerpt"><?php echo esc_html( get_the_excerpt( $eid ) ); ?></p>
			</span>
			<?php if ( $ecat ) : ?><span class="eyebrow jr-item__cat"><?php echo esc_html( $ecat ); ?></span><?php endif; ?>
			<span class="eyebrow jr-item__date"><?php echo esc_html( $date . ' · ' . $read ); ?></span>
			<span class="jr-item__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="jr-item__underline" aria-hidden="true"></span>
		</a>
	</li>
	<?php
}

/**
 * Render the styled numbered pagination for Journal loops.
 */
function le_journal_pagination() {
	the_posts_pagination( array(
		'mid_size'           => 1,
		'prev_text'          => '&larr;',
		'next_text'          => '&rarr;',
		'screen_reader_text' => __( 'Journal navigation', 'lewisedward' ),
		'class'              => 'jr-pagination',
	) );
}
