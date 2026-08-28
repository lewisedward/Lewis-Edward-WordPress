<?php
/**
 * Home section: Client Logos.
 *
 * Fully ACF-driven. Text prefilled via ACF defaults; the marquee logos come
 * from the `home_clients_logos` repeater (empty until populated — the marquee
 * simply doesn't render if there are no logos).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$le_logos = le_field( 'home_clients_logos' );
$le_logos = is_array( $le_logos ) ? $le_logos : array();

// Split the logos into two marquee rows.
$le_half  = (int) ceil( count( $le_logos ) / 2 );
$le_row_1 = array_slice( $le_logos, 0, $le_half );
$le_row_2 = array_slice( $le_logos, $le_half );

/**
 * Render one marquee row (doubled for a seamless loop).
 *
 * @param array  $logos     Repeater rows: each has 'logo' (image array) + 'name'.
 * @param string $direction 'left' | 'right'.
 */
function le_render_logo_row( $logos, $direction ) {
	if ( empty( $logos ) ) {
		return;
	}
	$doubled = array_merge( $logos, $logos );
	echo '<div class="client-marquee__row client-marquee__row--' . esc_attr( $direction ) . '">';
	foreach ( $doubled as $idx => $row ) {
		$logo = isset( $row['logo'] ) ? $row['logo'] : null;
		if ( ! $logo || empty( $logo['url'] ) ) {
			continue;
		}
		$name = isset( $row['name'] ) ? $row['name'] : '';
		$dupe = $idx >= count( $logos ) ? ' aria-hidden="true"' : '';
		echo '<div class="client-logo"' . $dupe . '>';
		// Intrinsic dimensions let the browser reserve the box before the file
		// lands. CSS still controls the rendered size. SVGs may report none.
		$le_w = isset( $logo['width'] ) ? (int) $logo['width'] : 0;
		$le_h = isset( $logo['height'] ) ? (int) $logo['height'] : 0;
		$le_dims = ( $le_w > 0 && $le_h > 0 )
			? sprintf( ' width="%d" height="%d"', $le_w, $le_h )
			: '';
		printf(
			'<img class="client-logo__img" src="%1$s" alt="%2$s" loading="lazy"%3$s />',
			esc_url( $logo['url'] ),
			esc_attr( $name ? $name . ' logo' : ( $logo['alt'] ?? '' ) ),
			$le_dims // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from ints above.
		);
		echo '</div>';
	}
	echo '</div>';
}
?>
<section class="section section--clients" aria-label="<?php esc_attr_e( 'Our clients', 'lewisedward' ); ?>">
	<div class="section__inner">
		<div class="clients-card glass" data-reveal>

			<div class="clients-card__head">
				<span class="eyebrow clients-card__eyebrow">
					<?php echo esc_html( le_field( 'home_clients_eyebrow' ) ); ?><sup class="clients-card__count"><?php echo esc_html( le_field( 'home_clients_sup' ) ); ?></sup>
				</span>
				<span class="clients-card__rule" aria-hidden="true"></span>
				<span class="clients-card__dot" aria-hidden="true"></span>
			</div>

			<div class="clients-card__intro">
				<h2 class="clients-card__title">
					<?php echo esc_html( le_field( 'home_clients_title_pre' ) ); ?>
					<?php $le_counter = (int) le_field( 'home_clients_counter' ); ?>
					<span class="text-primary"><span data-counter="<?php echo esc_attr( (string) $le_counter ); ?>"><?php echo esc_html( (string) $le_counter ); ?></span>+</span>
					<?php echo esc_html( le_field( 'home_clients_title_post' ) ); ?>
				</h2>

				<div class="clients-card__aside">
					<p class="clients-card__lede"><?php echo esc_html( le_field( 'home_clients_lede' ) ); ?></p>
					<?php $home_clients_selected = le_link( 'home_clients_selected', '/work', __( 'Selected work', 'lewisedward' ), false ); ?>
					<a class="arrow-link" href="<?php echo esc_url( $home_clients_selected['url'] ); ?>"<?php le_link_target_attr( $home_clients_selected ); ?> aria-label="<?php esc_attr_e( 'See selected work', 'lewisedward' ); ?>">
						<span class="arrow-link__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="arrow-link__label"><?php echo esc_html( $home_clients_selected['title'] ); ?></span>
					</a>
				</div>
			</div>

			<?php if ( ! empty( $le_logos ) ) : ?>
				<div class="client-marquee">
					<span class="client-marquee__fade client-marquee__fade--left" aria-hidden="true"></span>
					<span class="client-marquee__fade client-marquee__fade--right" aria-hidden="true"></span>
					<?php
					le_render_logo_row( $le_row_1, 'left' );
					le_render_logo_row( $le_row_2, 'right' );
					?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
