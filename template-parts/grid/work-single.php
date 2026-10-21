<?php
/**
 * Grid skin — case study. Same fields and outline as single-work.php (h1 title;
 * h2 per section; h3 per next project).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wk_id        = get_the_ID();
$wk_hero_desc = le_field( 'work_hero_description', $wk_id );
$wk_desc      = le_field( 'work_card_description', $wk_id );
$wk_year      = le_field( 'work_year', $wk_id );
$wk_dur       = le_field( 'work_duration', $wk_id );
$wk_plat      = le_field( 'work_platform', $wk_id );
$wk_live      = le_field( 'work_live_url', $wk_id );
$wk_live_on   = function_exists( 'get_field' ) ? get_field( 'work_live_show', $wk_id ) : null;
if ( null !== $wk_live_on && ! $wk_live_on ) {
	$wk_live = '';
}
$wk_services = le_field( 'work_services', $wk_id );
$wk_services = is_array( $wk_services ) ? $wk_services : array();
$wk_tech     = le_field( 'work_technologies', $wk_id );
$wk_tech     = is_array( $wk_tech ) ? $wk_tech : array();
$wk_chall    = le_field( 'work_challenge', $wk_id );
$wk_sol      = le_field( 'work_solution', $wk_id );
$wk_res      = le_field( 'work_results', $wk_id );
$wk_res      = is_array( $wk_res ) ? $wk_res : array();
$wk_feats    = le_field( 'work_features', $wk_id );
$wk_feats    = is_array( $wk_feats ) ? array_values( array_filter( $wk_feats, static function ( $row ) { return ! empty( $row['image']['url'] ); } ) ) : array();
$wk_post     = static function ( $s ) {
	return is_object( $s ) ? $s : get_post( (int) $s );
};
$wk_back       = le_link( 'wk_hero_back', '/work', __( 'All Projects', 'lewisedward' ), $wk_id );
$wk_has_glance = ( $wk_year || $wk_dur || $wk_plat || $wk_services || $wk_tech );
?>
<main id="main" class="site-main site-main--work-single g-main">

	<section class="g-row g-phero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
		<div class="g-cell g-phero__main">
			<div class="g-phero__top">
				<nav class="g-phero__crumbs g-mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'lewisedward' ); ?>"><a href="<?php echo esc_url( $wk_back['url'] ); ?>"<?php le_link_target_attr( $wk_back ); ?>>&larr; <?php echo esc_html( $wk_back['title'] ); ?></a></nav>
				<?php if ( $wk_year ) : ?><span class="g-mono"><?php echo esc_html( $wk_year ); ?></span><?php endif; ?>
			</div>
			<?php if ( $wk_services ) : ?>
			<div class="g-phero__tags"><?php foreach ( $wk_services as $s ) { $sp = $wk_post( $s ); if ( $sp ) { echo '<a class="g-tag" href="' . esc_url( get_permalink( $sp ) ) . '">' . esc_html( get_the_title( $sp ) ) . '</a>'; } } ?></div>
			<?php endif; ?>
			<h1 class="g-phero__title"><?php the_title(); ?></h1>
			<div class="g-phero__foot">
				<?php if ( $wk_hero_desc ) : ?><p class="g-phero__lede"><?php echo esc_html( $wk_hero_desc ); ?></p><?php endif; ?>
				<?php if ( $wk_live ) : ?><a class="g-btn g-mono" href="<?php echo esc_url( $wk_live ); ?>" target="_blank" rel="noopener noreferrer"><span><?php echo esc_html( le_field( 'wk_visit_label', $wk_id, __( 'Visit website', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a><?php endif; ?>
			</div>
		</div>
		<?php if ( $wk_has_glance ) : ?>
		<div class="g-cell g-dark g-phero__aside">
			<div class="g-rail__head"><span class="g-label g-mono"><?php echo esc_html( le_field( 'wk_glance_eyebrow', $wk_id, __( 'At a glance', 'lewisedward' ) ) ); ?></span></div>
			<div class="g-kv g-kv--stack">
				<?php if ( $wk_year ) : ?><div><span class="g-mono"><?php echo esc_html( le_field( 'wk_glance_year', $wk_id, __( 'Year', 'lewisedward' ) ) ); ?></span><p><?php echo esc_html( $wk_year ); ?></p></div><?php endif; ?>
				<?php if ( $wk_dur ) : ?><div><span class="g-mono"><?php echo esc_html( le_field( 'wk_glance_duration', $wk_id, __( 'Duration', 'lewisedward' ) ) ); ?></span><p><?php echo esc_html( $wk_dur ); ?></p></div><?php endif; ?>
				<?php if ( $wk_plat ) : ?><div><span class="g-mono"><?php echo esc_html( le_field( 'wk_glance_platform', $wk_id, __( 'Platform', 'lewisedward' ) ) ); ?></span><p><?php echo esc_html( $wk_plat ); ?></p></div><?php endif; ?>
				<?php if ( $wk_services ) : ?><div><span class="g-mono"><?php echo esc_html( le_field( 'wk_glance_services', $wk_id, __( 'Services', 'lewisedward' ) ) ); ?></span><p><?php $l = array(); foreach ( $wk_services as $s ) { $sp = $wk_post( $s ); if ( $sp ) { $l[] = '<a href="' . esc_url( get_permalink( $sp ) ) . '">' . esc_html( get_the_title( $sp ) ) . '</a>'; } } echo wp_kses_post( implode( ', ', $l ) ); ?></p></div><?php endif; ?>
				<?php if ( $wk_tech ) : ?><div class="g-kv__wide"><span class="g-mono"><?php echo esc_html( le_field( 'wk_glance_stack', $wk_id, __( 'Stack', 'lewisedward' ) ) ); ?> <?php echo esc_html( str_pad( (string) count( $wk_tech ), 2, '0', STR_PAD_LEFT ) ); ?></span><span class="g-li__tags" style="margin:0"><?php foreach ( $wk_tech as $row ) { $t = trim( (string) ( $row['name'] ?? '' ) ); if ( $t ) { echo '<span class="g-tag">' . esc_html( $t ) . '</span>'; } } ?></span></div><?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
	</section>

	<?php if ( has_post_thumbnail() ) : ?>
	<section class="g-row g-media" aria-label="<?php esc_attr_e( 'Project image', 'lewisedward' ); ?>">
		<div class="g-cell g-ph"><?php the_post_thumbnail( 'le_hero', array( 'alt' => esc_attr( get_the_title() ), 'loading' => 'eager', 'decoding' => 'async', 'fetchpriority' => 'high' ) ); ?><span class="g-cap g-mono"><?php echo esc_html( get_the_title() ); ?></span></div>
	</section>
	<?php endif; ?>

	<?php if ( $wk_chall || $wk_sol ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Challenge and solution', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'wk_brief_eyebrow', $wk_id, __( 'Brief', 'lewisedward' ) ), 'count' => '02', 'title' => le_grid_heading( array( array( le_field( 'wk_brief_title_pre', $wk_id, __( 'The', 'lewisedward' ) ) ), array( le_field( 'wk_brief_title_mid', $wk_id, __( 'challenge', 'lewisedward' ) ), 'muted' ), array( le_field( 'wk_brief_title_end', $wk_id, __( '& the solution.', 'lewisedward' ) ), 'accent' ) ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell g-cells g-cells--2">
			<?php if ( $wk_chall ) : ?><div><span class="g-label g-mono"><?php echo esc_html( le_field( 'wk_challenge_label', $wk_id, __( 'The Challenge', 'lewisedward' ) ) ); ?></span><div class="g-prose g-prose--sm"><?php echo wp_kses_post( wpautop( $wk_chall ) ); ?></div></div><?php endif; ?>
			<?php if ( $wk_sol ) : ?><div><span class="g-label g-mono"><?php echo esc_html( le_field( 'wk_solution_label', $wk_id, __( 'The Solution', 'lewisedward' ) ) ); ?></span><div class="g-prose g-prose--sm"><?php echo wp_kses_post( wpautop( $wk_sol ) ); ?></div></div><?php endif; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $wk_res ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Results', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'wk_results_eyebrow', $wk_id, __( 'Results', 'lewisedward' ) ), 'count' => str_pad( (string) count( $wk_res ), 2, '0', STR_PAD_LEFT ), 'title' => le_grid_heading( array( array( le_field( 'wk_results_title_pre', $wk_id, __( 'What we', 'lewisedward' ) ) ), array( le_field( 'wk_results_title_mid', $wk_id, __( 'delivered for', 'lewisedward' ) ), 'muted' ), array( get_the_title() . '.', 'accent' ) ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<ul class="g-cell g-dark g-check">
			<?php foreach ( $wk_res as $row ) : $r = trim( (string) ( $row['result'] ?? '' ) ); if ( '' === $r ) { continue; } ?><li><?php echo esc_html( $r ); ?></li><?php endforeach; ?>
		</ul>
	</section>
	<?php endif; ?>

	<?php if ( $wk_feats ) : ?>
	<section class="g-row" aria-label="<?php esc_attr_e( 'Visual journey', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'wk_features_eyebrow', $wk_id, __( 'Visual journey', 'lewisedward' ) ), 'count' => str_pad( (string) count( $wk_feats ), 2, '0', STR_PAD_LEFT ), 'title' => le_grid_heading( array( array( le_field( 'wk_features_title_pre', $wk_id, __( 'Explore', 'lewisedward' ) ) ), array( le_field( 'wk_features_title_mid', $wk_id, __( 'some', 'lewisedward' ) ), 'muted' ), array( le_field( 'wk_features_title_end', $wk_id, __( 'features.', 'lewisedward' ) ), 'accent' ) ) ), 'class' => 'g-sec__intro--bar' ) ); ?>
	</section>
	<section class="g-row g-journey" data-g-rail aria-label="<?php esc_attr_e( 'Feature images', 'lewisedward' ); ?>">
		<div class="g-journey__track" data-g-rail-track data-cursor="<?php esc_attr_e( 'Drag', 'lewisedward' ); ?>" tabindex="0">
		<?php foreach ( $wk_feats as $i => $row ) : $img = $row['image']; ?>
		<div class="g-cell g-journey__item">
			<?php $img_w = (int) ( isset( $img['sizes']['large'] ) ? ( $img['sizes']['large-width'] ?? 0 ) : ( $img['width'] ?? 0 ) ); $img_h = (int) ( isset( $img['sizes']['large'] ) ? ( $img['sizes']['large-height'] ?? 0 ) : ( $img['height'] ?? 0 ) ); ?>
			<span class="g-ph"><img src="<?php echo esc_url( $img['sizes']['large'] ?? $img['url'] ); ?>"<?php echo ( $img_w && $img_h ) ? ' width="' . esc_attr( $img_w ) . '" height="' . esc_attr( $img_h ) . '"' : ''; ?> alt="<?php echo esc_attr( ! empty( $img['alt'] ) ? $img['alt'] : ( $row['description'] ?? get_the_title() ) ); ?>" loading="lazy" decoding="async" /></span>
			<?php if ( ! empty( $row['description'] ) ) : ?><div class="g-journey__cap"><span class="g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><p><?php echo esc_html( $row['description'] ); ?></p></div><?php endif; ?>
		</div>
		<?php endforeach; ?>
		</div>
		<?php if ( count( $wk_feats ) > 1 ) : ?>
		<div class="g-journey__bar">
			<span class="g-journey__count g-mono" data-g-rail-count aria-live="polite">01 / <?php echo esc_html( str_pad( (string) count( $wk_feats ), 2, '0', STR_PAD_LEFT ) ); ?></span>
			<span class="g-journey__progress" aria-hidden="true"><span data-g-rail-progress></span></span>
			<span class="g-tst__btns"><button type="button" class="g-tst__btn" data-g-rail-prev aria-label="<?php esc_attr_e( 'Previous image', 'lewisedward' ); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg></button><button type="button" class="g-tst__btn" data-g-rail-next aria-label="<?php esc_attr_e( 'Next image', 'lewisedward' ); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button></span>
		</div>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<?php
	$wk_next = get_posts( array( 'post_type' => 'work', 'posts_per_page' => 2, 'post__not_in' => array_merge( array( $wk_id ), le_work_showcase_ids() ), 'orderby' => 'menu_order date', 'order' => 'ASC', 'no_found_rows' => true, 'fields' => 'ids' ) );
	if ( $wk_next ) :
		?>
	<section class="g-row g-tiles g-tiles--intro" aria-label="<?php esc_attr_e( 'More projects', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'wk_next_eyebrow', $wk_id, __( 'More projects', 'lewisedward' ) ), 'title' => le_grid_heading( array( array( le_field( 'wk_next_title_pre', $wk_id, __( 'Keep exploring', 'lewisedward' ) ) ), array( le_field( 'wk_next_title_end', $wk_id, __( 'our work.', 'lewisedward' ) ), 'accent' ) ) ), 'link' => array( 'url' => home_url( '/work' ), 'title' => __( 'All work', 'lewisedward' ) ) ) ); ?>
		<?php foreach ( $wk_next as $nid ) { le_grid_work_tile( $nid ); } ?>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
