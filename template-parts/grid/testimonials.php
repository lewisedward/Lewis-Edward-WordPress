<?php
/**
 * Grid skin — testimonials (shared). Reads the homepage's fields so every page shows
 * the same block; the front page is the source of truth in wp-admin.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$g_pid = (int) get_option( 'page_on_front' );
$g_pid = $g_pid ? $g_pid : false;
$g_id  = static function ( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
};
$g_tsts  = le_field( 'home_tst_items', $g_pid );
$g_tsts  = is_array( $g_tsts ) ? $g_tsts : array();
$g_logos = le_field( 'home_clients_logos', $g_pid );
$g_logos = is_array( $g_logos ) ? $g_logos : array();
?>
	<?php if ( $g_tsts ) : ?>
	<section class="g-row g-tst" aria-label="<?php esc_attr_e( 'Testimonials', 'lewisedward' ); ?>">
		<div class="g-cell g-tst__side">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_tst_eyebrow', $g_pid, __( 'Testimonials', 'lewisedward' ) ) ); ?></span>
			<div class="g-tst__nav">
				<span class="g-tst__count g-mono"><span data-tst-current>01</span> / <?php echo esc_html( str_pad( (string) count( $g_tsts ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span class="g-tst__btns"><button type="button" class="g-tst__btn" data-tst-prev aria-label="<?php esc_attr_e( 'Previous testimonial', 'lewisedward' ); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg></button><button type="button" class="g-tst__btn" data-tst-next aria-label="<?php esc_attr_e( 'Next testimonial', 'lewisedward' ); ?>"><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button></span>
				<span class="g-tst__bar" aria-hidden="true"><i></i></span>
			</div>
		</div>
		<div class="g-cell g-tst__stage" data-tst>
			<?php foreach ( $g_tsts as $i => $t ) : $tid = $g_id( $t ); $role = le_field( 'testimonial_role', $tid ); $co = le_field( 'testimonial_company', $tid ); ?>
			<?php
			// Company logo: the testimonial's own, else a client logo whose file/alt/name mentions the company.
			$g_tlogo = le_field( 'testimonial_logo', $tid );
			$g_tlogo = ( is_array( $g_tlogo ) && ! empty( $g_tlogo['url'] ) ) ? $g_tlogo['url'] : '';
			if ( ! $g_tlogo && $co ) {
				$needle = sanitize_title( $co );
				foreach ( $g_logos as $row ) {
					$img = $row['logo'] ?? null;
					if ( ! $img || empty( $img['url'] ) ) { continue; }
					$hay = sanitize_title( implode( ' ', array( $row['name'] ?? '', $img['alt'] ?? '', $img['title'] ?? '', $img['filename'] ?? '' ) ) );
					if ( false !== strpos( $hay, $needle ) ) { $g_tlogo = $img['url']; break; }
				}
			}
			?>
			<figure class="g-tst__item<?php echo 0 === $i ? ' is-active' : ''; ?>">
				<blockquote class="g-tst__quote"><span class="g-tst__mark" aria-hidden="true">&ldquo;</span><p><?php echo esc_html( le_field( 'testimonial_quote', $tid ) ); ?></p></blockquote>
				<figcaption class="g-tst__meta"><?php if ( $g_tlogo ) : ?><img class="g-tst__logo" src="<?php echo esc_url( le_grid_logo( $g_tlogo ) ); ?>" alt="<?php echo esc_attr( $co ); ?>" loading="lazy" /><?php endif; ?><?php if ( has_post_thumbnail( $tid ) ) { echo get_the_post_thumbnail( $tid, 'le_avatar', array( 'class' => 'g-tst__avatar', 'loading' => 'lazy' ) ); } ?><span><?php echo esc_html( le_field( 'testimonial_author', $tid ) ); ?></span><span class="g-mono"><?php echo esc_html( trim( $role . ( $co ? ' — ' . $co : '' ) ) ); ?></span></figcaption>
			</figure>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

