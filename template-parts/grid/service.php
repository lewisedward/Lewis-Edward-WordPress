<?php
/**
 * Grid skin — single service. Same fields and outline as single-service.php
 * (h1 headline; h2 per section; h3 per capability / step / question / related
 * service), laid out as grid rows.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sv_id       = get_the_ID();
$sv_subtitle = le_field( 'service_subtitle', $sv_id );
$sv_headline = le_field( 'service_headline', $sv_id );
$sv_intro    = le_field( 'service_intro', $sv_id );
$sv_feats    = le_field( 'service_detail_features', $sv_id );
$sv_feats    = is_array( $sv_feats ) ? $sv_feats : array();
$sv_proc     = le_field( 'service_process', $sv_id );
$sv_proc     = is_array( $sv_proc ) ? $sv_proc : array();
$sv_bens     = le_field( 'service_benefits', $sv_id );
$sv_bens     = is_array( $sv_bens ) ? $sv_bens : array();
$sv_faq      = le_field( 'service_faq', $sv_id );
$sv_faq      = is_array( $sv_faq ) ? $sv_faq : array();
$sv_relwk    = le_field( 'service_related_work', $sv_id );
$sv_relwk    = is_array( $sv_relwk ) ? $sv_relwk : array();
$sv_rel      = le_field( 'service_related', $sv_id );
$sv_rel      = is_array( $sv_rel ) ? $sv_rel : array();
$sv_pid      = static function ( $x ) {
	return is_object( $x ) ? (int) $x->ID : (int) $x;
};

// Headline: white / grey / lime (see le_grid_tone_title(); [[lime]] and {{grey}} markers in the field win).
$sv_title_html = le_grid_tone_title( $sv_headline ? $sv_headline : get_the_title() );
$svc_crumb    = le_link( 'svc_hero_crumb', '/services', __( 'Services', 'lewisedward' ), $sv_id );
$svc_hero_cta = le_link( 'svc_hero_cta', '/contact', __( 'Start a project', 'lewisedward' ), $sv_id );
?>
<main id="main" class="site-main site-main--service-single g-main">

	<section class="g-row g-phero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
		<div class="g-cell g-phero__main">
			<div class="g-phero__top">
				<nav class="g-phero__crumbs g-mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'lewisedward' ); ?>"><a href="<?php echo esc_url( $svc_crumb['url'] ); ?>"<?php le_link_target_attr( $svc_crumb ); ?>><?php echo esc_html( $svc_crumb['title'] ); ?></a><span>/</span><span><?php echo esc_html( get_the_title() ); ?></span></nav>
				<?php if ( $sv_subtitle ) : ?><span class="g-mono"><?php echo esc_html( $sv_subtitle ); ?></span><?php endif; ?>
			</div>
			<h1 class="g-phero__title g-phero__title--long"><?php echo $sv_title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h1>
			<div class="g-phero__foot">
				<?php if ( $sv_intro ) : ?><p class="g-phero__lede"><?php echo esc_html( $sv_intro ); ?></p><?php endif; ?>
				<a class="g-btn g-mono" href="<?php echo esc_url( $svc_hero_cta['url'] ); ?>"<?php le_link_target_attr( $svc_hero_cta ); ?>><span><?php echo esc_html( $svc_hero_cta['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
		</div>
		<div class="g-cell g-dark g-phero__aside">
			<div class="g-rail__head"><span class="g-label g-mono"><?php echo esc_html( le_field( 'svc_hero_status', $sv_id, __( 'LDN / WordPress / 51.5074° N', 'lewisedward' ) ) ); ?></span></div>
			<div class="g-phero__wire" aria-hidden="true"><?php echo le_grid_wire( get_post_field( 'post_name', $sv_id ) . ' ' . get_the_title(), 0 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="g-rail__head g-rail__head--foot"><span class="g-mono g-accent"><?php echo esc_html( le_field( 'svc_hero_scroll', $sv_id, __( 'Scroll to explore', 'lewisedward' ) ) ); ?></span></div>
		</div>
	</section>

	<?php if ( $sv_feats ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'What is included', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'svc_included_eyebrow', $sv_id, __( "What's included", 'lewisedward' ) ), 'count' => str_pad( (string) count( $sv_feats ), 2, '0', STR_PAD_LEFT ), 'title' => wp_kses_post( (string) le_field( 'whats_included_title', $sv_id ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell g-dark g-sec__body">
			<?php foreach ( $sv_feats as $i => $row ) : $t = trim( (string) ( $row['title'] ?? '' ) ); if ( '' === $t ) { continue; } ?>
			<div class="g-li g-li--3">
				<span class="g-li__num g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<h3 class="g-li__title"><?php echo esc_html( $t ); ?></h3>
				<?php if ( ! empty( $row['description'] ) ) : ?><p class="g-li__desc"><?php echo esc_html( $row['description'] ); ?></p><?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $sv_proc ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Our approach', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'svc_process_eyebrow', $sv_id, __( 'Our approach', 'lewisedward' ) ), 'count' => str_pad( (string) count( $sv_proc ), 2, '0', STR_PAD_LEFT ) . ' · ' . le_field( 'svc_process_note', $sv_id, __( 'Step by step', 'lewisedward' ) ), 'title' => esc_html( le_field( 'svc_process_title', $sv_id, __( 'How we deliver.', 'lewisedward' ) ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell <?php echo esc_attr( le_grid_cells_class( count( $sv_proc ) ) ); ?>">
			<?php foreach ( $sv_proc as $i => $row ) : $st = trim( (string) ( $row['title'] ?? '' ) ); if ( '' === $st ) { continue; } ?>
			<div class="g-step"><span class="g-step__num"><?php echo esc_html( ! empty( $row['step'] ) ? $row['step'] : str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3 class="g-step__title"><?php echo esc_html( $st ); ?></h3><?php if ( ! empty( $row['description'] ) ) : ?><p class="g-step__desc"><?php echo esc_html( $row['description'] ); ?></p><?php endif; ?></div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $sv_bens ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Why work with us', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'svc_benefits_eyebrow', $sv_id, __( 'Why work with us', 'lewisedward' ) ), 'title' => esc_html( le_field( 'svc_benefits_title', $sv_id, __( 'The benefits of partnering with us.', 'lewisedward' ) ) ), 'text' => le_field( 'svc_benefits_intro', $sv_id, __( 'We pair deep WordPress expertise with a transparent, collaborative approach, so every project lands with clarity and confidence.', 'lewisedward' ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<ul class="g-cell g-dark g-check">
			<?php foreach ( $sv_bens as $row ) : $b = trim( (string) ( $row['benefit'] ?? '' ) ); if ( '' === $b ) { continue; } ?>
			<li><?php echo esc_html( $b ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php endif; ?>

	<?php if ( $sv_faq ) : ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'svc_faq_eyebrow', $sv_id, __( 'Common questions', 'lewisedward' ) ), 'count' => str_pad( (string) count( $sv_faq ), 2, '0', STR_PAD_LEFT ), 'title' => esc_html( le_field( 'svc_faq_title', $sv_id, __( 'Frequently asked.', 'lewisedward' ) ) ), 'text' => le_field( 'svc_faq_intro', $sv_id, __( 'Quick answers to the things we get asked most. Still need clarity? Drop us a line.', 'lewisedward' ) ), 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell g-dark g-sec__body">
			<?php foreach ( $sv_faq as $i => $row ) : $q = trim( (string) ( $row['question'] ?? '' ) ); if ( '' === $q ) { continue; } ?>
			<details class="g-acc"<?php echo 0 === $i ? ' open' : ''; ?>>
				<summary><span class="g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3 class="g-acc__q"><?php echo esc_html( $q ); ?></h3><span class="g-acc__x" aria-hidden="true"></span></summary>
				<?php if ( ! empty( $row['answer'] ) ) : ?><div class="g-acc__a"><?php echo wp_kses_post( wpautop( $row['answer'] ) ); ?></div><?php endif; ?>
			</details>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php
	$sv_relwk = array_values( array_filter( array_map( $sv_pid, $sv_relwk ), static function ( $wid ) {
		return $wid && 'publish' === get_post_status( $wid ) && ( has_post_thumbnail( $wid ) || le_field( 'work_home_image', $wid ) );
	} ) );
	if ( $sv_relwk ) :
		$svc_work_viewall = le_link( 'svc_work_viewall', '/work', __( 'View all', 'lewisedward' ), $sv_id );
		?>
	<section class="g-row g-tiles g-tiles--intro" aria-label="<?php esc_attr_e( 'Featured work', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'svc_work_eyebrow', $sv_id, __( 'Case studies', 'lewisedward' ) ), 'count' => str_pad( (string) min( 5, count( $sv_relwk ) ), 2, '0', STR_PAD_LEFT ), 'title' => esc_html( le_field( 'svc_work_title', $sv_id, __( 'Featured work', 'lewisedward' ) ) ), 'link' => $svc_work_viewall ) ); ?>
		<?php foreach ( array_slice( $sv_relwk, 0, 5 ) as $wid ) { le_grid_work_tile( $wid ); } ?>
	</section>
	<?php endif; ?>

	<?php if ( $sv_rel ) : $svc_related_all = le_link( 'svc_related_all', '/services', __( 'All services', 'lewisedward' ), $sv_id ); ?>
	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Related services', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'label' => le_field( 'svc_related_eyebrow', $sv_id, __( 'Related services', 'lewisedward' ) ), 'title' => esc_html( le_field( 'svc_related_title', $sv_id, __( 'You might also need.', 'lewisedward' ) ) ), 'link' => $svc_related_all, 'class' => 'g-sec__sticky' ) ); ?>
		<div class="g-cell g-dark g-sec__body">
			<?php foreach ( $sv_rel as $i => $r ) : $rid = $sv_pid( $r ); if ( ! $rid ) { continue; } $rs = le_field( 'service_subtitle', $rid ); ?>
			<a class="g-li g-li--2" href="<?php echo esc_url( get_permalink( $rid ) ); ?>">
				<span class="g-li__num g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span><h3 class="g-li__title g-li__title--lg"><?php echo esc_html( get_the_title( $rid ) ); ?></h3><span class="g-li__sub g-mono"><?php echo esc_html( $rs ? $rs : le_field( 'svc_related_explore', $sv_id, __( 'Explore', 'lewisedward' ) ) ); ?></span></span>
				<span class="g-li__go"><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/grid/testimonials' ); ?>
	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
