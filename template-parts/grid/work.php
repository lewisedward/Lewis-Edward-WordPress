<?php
/**
 * Grid skin — Work page. Same fields and outline as page-templates/page-work.php
 * (h1 in the hero; featured tile and archive heading h2; tile titles h3).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wp_id = static function ( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
};
$wp_featured = le_field( 'workp_featured' );
$wp_fid      = $wp_id( $wp_featured );
$wp_items    = le_field( 'work_items' );
$wp_items    = is_array( $wp_items ) ? array_values( array_filter( array_map( $wp_id, $wp_items ) ) ) : array();
$wp_count    = count( $wp_items );

$wp_real  = static function ( $t ) {
	return ( $t instanceof WP_Term ) && '' !== trim( (string) $t->name ) && ! is_numeric( trim( (string) $t->name ) );
};
$wp_terms = static function ( $id ) use ( $wp_real ) {
	$terms = wp_get_post_terms( (int) $id, 'work_category' );
	return is_array( $terms ) ? array_filter( $terms, $wp_real ) : array();
};
$wp_filters = array();
$wp_all     = get_terms( array( 'taxonomy' => 'work_category', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
if ( is_array( $wp_all ) ) {
	foreach ( $wp_all as $t ) {
		if ( $wp_real( $t ) ) {
			$wp_filters[ $t->slug ] = $t->name;
		}
	}
}
?>
<main id="main" class="site-main site-main--work g-main">

	<?php $wp_has_feat = $wp_fid && ( has_post_thumbnail( $wp_fid ) || le_field( 'work_home_image', $wp_fid ) ); ?>
	<section class="g-row g-phero<?php echo $wp_has_feat ? ' g-phero--solo' : ''; ?>" aria-label="<?php esc_attr_e( 'Selected work', 'lewisedward' ); ?>">
		<div class="g-cell g-phero__main">
			<div class="g-phero__top">
				<span class="g-label g-mono"><?php echo esc_html( le_field( 'workp_eyebrow' ) ); ?><?php if ( $wp_count ) : ?> <span class="g-sec__count"><?php echo esc_html( str_pad( (string) $wp_count, 2, '0', STR_PAD_LEFT ) ); ?></span><?php endif; ?></span>
				<span class="g-mono"><?php echo esc_html( le_field( 'workp_right_label' ) ); ?></span>
			</div>
			<h1 class="g-phero__title"><?php echo le_grid_heading( array( array( le_field( 'workp_h1_1' ) ), array( le_field( 'workp_h1_2' ), 'muted' ), array( le_field( 'workp_h1_3' ), 'accent' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<div class="g-phero__foot">
				<p class="g-phero__lede"><?php echo esc_html( le_field( 'workp_lead' ) ); ?></p>
				<?php if ( $wp_count ) : ?><a class="g-line-link g-mono" href="#archive"><span><?php echo esc_html( le_field( 'workp_archive_eyebrow' ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
			</div>
		</div>
	</section>
	<?php if ( $wp_has_feat ) : ?>
	<section class="g-row g-featrow" aria-label="<?php esc_attr_e( 'Featured project', 'lewisedward' ); ?>">
		<a class="g-cell g-ph g-feature"<?php echo le_grid_work_href( $wp_fid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php echo le_grid_work_img( $wp_fid, 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="g-feature__label g-mono"><?php echo esc_html( le_field( 'workp_featured_label' ) ); ?></span>
			<span class="g-feature__foot">
				<span><h2 class="g-feature__title"><?php echo esc_html( get_the_title( $wp_fid ) ); ?></h2><p class="g-feature__desc"><?php echo esc_html( le_field( 'work_card_description', $wp_fid ) ); ?></p><?php $ft = $wp_terms( $wp_fid ); if ( $ft ) : ?><span class="g-li__tags"><?php foreach ( $ft as $t ) { echo '<span class="g-tag">' . esc_html( $t->name ) . '</span>'; } ?></span><?php endif; ?></span>
				<?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</a>
	</section>
	<?php endif; ?>

	<?php if ( $wp_count ) : ?>
	<section id="archive" class="g-row g-tiles g-tiles--side" style="--span: <?php echo (int) ceil( $wp_count / 2 ); ?>" aria-label="<?php esc_attr_e( 'Project archive', 'lewisedward' ); ?>" data-g-filterable>
		<div class="g-cell g-sec__intro g-sec__sticky"><div class="g-sec__in">
			<div class="g-sec__top"><span class="g-label g-mono"><?php echo esc_html( le_field( 'workp_archive_eyebrow' ) ); ?></span><span class="g-mono g-sec__count"><span data-g-visible><?php echo esc_html( str_pad( (string) $wp_count, 2, '0', STR_PAD_LEFT ) ); ?></span> <?php echo esc_html( le_field( 'workp_count_label', false, __( 'projects', 'lewisedward' ) ) ); ?></span></div>
			<h2 class="g-sec__title"><?php $wp_h = le_grid_heading( array( array( le_field( 'workp_archive_h1' ) ), array( le_field( 'workp_archive_accent' ), 'accent' ), array( le_field( 'workp_archive_h3' ) ) ) ); echo $wp_h ? $wp_h : esc_html( le_field( 'workp_archive_eyebrow', false, __( 'All projects', 'lewisedward' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>
			<?php if ( $wp_filters ) : ?>
			<div class="g-filters g-filters--stack" role="group" aria-label="<?php esc_attr_e( 'Filter projects', 'lewisedward' ); ?>">
				<button class="g-filter is-active" type="button" data-g-filter="all"><?php echo esc_html( le_field( 'workp_filter_all', false, __( 'All', 'lewisedward' ) ) ); ?></button>
				<?php foreach ( $wp_filters as $slug => $name ) : ?><button class="g-filter" type="button" data-g-filter="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></button><?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div></div>
		<?php foreach ( $wp_items as $id ) { $cats = implode( ' ', wp_list_pluck( $wp_terms( $id ), 'slug' ) ); le_grid_work_tile( $id, array( 'data' => 'data-categories="' . esc_attr( $cats ) . '"' ) ); } ?>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
