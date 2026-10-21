<?php
/**
 * Grid skin — Services page. Same fields and heading outline as
 * page-templates/page-services.php (h1 in the hero; h2 for process and
 * specialised services; h3 per service / step).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sp_id = static function ( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
};
$sp_hero    = le_field( 'services_hero_items' );
$sp_hero    = is_array( $sp_hero ) ? $sp_hero : array();
$sp_items   = le_field( 'services_items' );
$sp_items   = is_array( $sp_items ) ? $sp_items : array();
$sp_count   = count( $sp_items );
$sp_steps   = le_field( 'services_process_steps' );
$sp_steps   = is_array( $sp_steps ) ? $sp_steps : array();
$sp_sub     = le_field( 'services_sub_items' );
$sp_sub     = is_array( $sp_sub ) ? $sp_sub : array();
$sp_explore = le_field( 'services_explore_label' );

// The specialised list repeats the core services in ACF; show only the ones
// not already listed above it.
$sp_core_ids = array_map( $sp_id, $sp_items );
$sp_sub      = array_values( array_filter( $sp_sub, static function ( $item ) use ( $sp_id, $sp_core_ids ) {
	return ! in_array( $sp_id( $item ), $sp_core_ids, true );
} ) );
$sp_pad = static function ( $n ) {
	return str_pad( (string) $n, 2, '0', STR_PAD_LEFT );
};
?>
<main id="main" class="site-main site-main--services g-main">

	<section class="g-row g-phero" aria-label="<?php esc_attr_e( 'WordPress services', 'lewisedward' ); ?>">
		<div class="g-cell g-phero__main">
			<div class="g-phero__top">
				<span class="g-label g-mono"><?php echo esc_html( le_field( 'services_eyebrow' ) ); ?><?php if ( $sp_count ) : ?> <span class="g-sec__count"><?php echo esc_html( str_pad( (string) $sp_count, 2, '0', STR_PAD_LEFT ) ); ?></span><?php endif; ?></span>
				<span class="g-mono"><?php echo esc_html( le_field( 'services_location' ) ); ?></span>
			</div>
			<h1 class="g-phero__title"><?php echo le_grid_heading( array( array( le_field( 'services_h1_1' ) ), array( le_field( 'services_h1_2' ), 'accent' ), array( le_field( 'services_h1_3' ), 'muted' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<div class="g-phero__foot">
				<div class="g-phero__lede">
					<?php $il = le_field( 'services_intro_label' ); if ( $il ) : ?><span class="g-mono g-phero__lede-label"><?php echo esc_html( $il ); ?></span><?php endif; ?>
					<?php echo wp_kses_post( le_field( 'services_intro' ) ); ?>
				</div>
				<?php if ( $sp_explore ) : ?>
				<a class="g-line-link g-mono" href="#services"><span><?php echo esc_html( $sp_explore ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="g-cell g-lime g-phero__aside">
			<div class="g-rail__head"><span class="g-mono"><?php echo esc_html( le_field( 'services_coords' ) ); ?></span><span class="g-mono"><?php echo esc_html( le_field( 'services_scroll_label' ) ); ?></span></div>
			<nav class="g-rail" aria-label="<?php esc_attr_e( 'On this page', 'lewisedward' ); ?>">
				<?php if ( $sp_count ) : ?><a href="#services"><span class="g-mono"><?php echo esc_html( $sp_pad( $sp_count ) ); ?></span><span><?php echo esc_html( le_field( 'services_core_eyebrow', false, __( 'Core services', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
				<?php if ( $sp_steps ) : ?><a href="#process"><span class="g-mono"><?php echo esc_html( $sp_pad( count( $sp_steps ) ) ); ?></span><span><?php echo esc_html( le_field( 'services_process_eyebrow', false, __( 'How we work', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
				<?php if ( $sp_sub ) : ?><a href="#specialised"><span class="g-mono"><?php echo esc_html( $sp_pad( count( $sp_sub ) ) ); ?></span><span><?php echo esc_html( le_field( 'services_sub_eyebrow', false, __( 'Specialised services', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
			</nav>
		</div>
	</section>

	<?php if ( $sp_count ) : ?>
	<section id="services" class="g-row g-sec" aria-label="<?php esc_attr_e( 'Core services', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'services_core_eyebrow' ), 'count' => '01 – ' . str_pad( (string) $sp_count, 2, '0', STR_PAD_LEFT ), 'big' => str_pad( (string) $sp_count, 2, '0', STR_PAD_LEFT ), 'text' => le_field( 'services_core_note' ), 'link' => le_link( 'services_process_cta', '/contact', __( 'Get in touch', 'lewisedward' ), false ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell g-dark g-sec__body">
			<?php foreach ( $sp_items as $i => $item ) : $id = $sp_id( $item ); $excerpt = le_field( 'service_excerpt', $id ); $desc = ( '' !== $excerpt ) ? $excerpt : le_field( 'service_short_description', $id ); $tags = le_field( 'service_features', $id ); $tags = is_array( $tags ) ? $tags : array(); ?>
			<a class="g-srv" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
				<span class="g-srv__num g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<div class="g-srv__main"><h3 class="g-srv__title"><?php echo esc_html( get_the_title( $id ) ); ?></h3><span class="g-srv__wire" aria-hidden="true"><?php echo le_grid_wire( get_post_field( 'post_name', $id ) . ' ' . get_the_title( $id ), $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></div>
				<span class="g-srv__body"><span class="g-srv__desc"><?php echo esc_html( wp_strip_all_tags( $desc ) ); ?></span><?php if ( $tags ) : ?><span class="g-li__tags"><?php foreach ( $tags as $row ) { $tag = isset( $row['tag'] ) ? trim( $row['tag'] ) : ''; if ( $tag ) { echo '<span class="g-tag">' . esc_html( $tag ) . '</span>'; } } ?></span><?php endif; ?><span class="g-srv__go"><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
			</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $sp_steps ) : ?>
	<section id="process" class="g-row g-sec" aria-label="<?php esc_attr_e( 'Our process', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'services_process_eyebrow' ), 'count' => str_pad( (string) count( $sp_steps ), 2, '0', STR_PAD_LEFT ) . ' · ' . le_field( 'services_process_note' ), 'title' => le_grid_heading( array( array( le_field( 'services_process_h1' ) ), array( le_field( 'services_process_h_muted' ), 'muted' ), array( le_field( 'services_process_h_accent' ), 'accent' ) ) ), 'text' => le_field( 'services_process_intro' ), 'link' => le_link( 'services_process_cta', '/contact', __( 'Get in touch', 'lewisedward' ), false ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell <?php echo esc_attr( le_grid_cells_class( count( $sp_steps ) ) ); ?>">
			<?php foreach ( $sp_steps as $s => $step ) : ?>
			<div class="g-step"><span class="g-step__num"><?php echo esc_html( str_pad( (string) ( $s + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3 class="g-step__title"><?php echo esc_html( $step['title'] ?? '' ); ?></h3><p class="g-step__desc"><?php echo esc_html( $step['description'] ?? '' ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $sp_sub ) : ?>
	<section id="specialised" class="g-row g-sec" aria-label="<?php esc_attr_e( 'Specialised services', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'services_sub_eyebrow' ), 'count' => str_pad( (string) count( $sp_sub ), 2, '0', STR_PAD_LEFT ) . ' · ' . le_field( 'services_sub_note' ), 'title' => le_grid_heading( array( array( le_field( 'services_sub_h1' ) ), array( le_field( 'services_sub_h_muted' ), 'muted' ), array( le_field( 'services_sub_h_accent' ), 'accent' ) ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell g-dark g-sec__body">
			<?php foreach ( $sp_sub as $i => $item ) : $id = $sp_id( $item ); $sub = le_field( 'service_subtitle', $id ); ?>
			<a class="g-li g-li--2" href="<?php echo esc_url( get_permalink( $id ) ); ?>">
				<span class="g-li__num g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span><h3 class="g-li__title g-li__title--lg"><?php echo esc_html( get_the_title( $id ) ); ?></h3><?php if ( $sub ) : ?><span class="g-li__sub g-mono"><?php echo esc_html( $sub ); ?></span><?php endif; ?></span>
				<span class="g-li__go"><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/grid/testimonials' ); ?>
	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
