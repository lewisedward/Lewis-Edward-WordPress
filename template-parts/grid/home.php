<?php
/**
 * Grid skin — homepage.
 *
 * The same homepage content (hero fields, services, work, clients, about,
 * testimonials) laid out as a hairline grid of cells. Every section reads
 * from the fields the classic homepage already uses, so nothing new needs
 * entering in wp-admin.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$g_pid = (int) get_option( 'page_on_front' );
$g_pid = $g_pid ? $g_pid : false;

// Hero.
$g_word       = le_option( 'grid_hero_word', 'Lewis' );
$g_lede       = le_field( 'home_hero_lede' );
$g_title      = trim( le_field( 'home_hero_title_1' ) . ' ' . le_field( 'home_hero_title_1_accent' ) . ' ' . le_field( 'home_hero_title_2_pre' ) . ' & ' . le_field( 'home_hero_title_2_post' ) );
$g_hero_cta   = le_link( 'home_hero_editorial_cta', '/contact', __( 'Start a conversation', 'lewisedward' ) );
$g_poster     = LE_URI . '/assets/videos/macbook-easyjet-poster.jpg?ver=' . le_asset_ver( 'assets/videos/macbook-easyjet-poster.jpg' );
$g_video      = LE_URI . '/assets/videos/macbook-easyjet.mp4?ver=' . le_asset_ver( 'assets/videos/macbook-easyjet.mp4' );
$g_portrait   = le_field( 'home_about_portrait' );
$g_portrait   = ( is_array( $g_portrait ) && ! empty( $g_portrait['url'] ) ) ? $g_portrait['url'] : LE_URI . '/assets/images/lewis-wordpress-developer.jpg';
$g_logos      = le_field( 'home_clients_logos' );
$g_logos      = is_array( $g_logos ) ? $g_logos : array();

// Services.
$g_services   = le_field( 'home_services_items' );
$g_services   = is_array( $g_services ) ? $g_services : array();
$g_svc_all    = le_link( 'home_services_all', '/services', __( 'All services', 'lewisedward' ), false );

// Work.
$g_work       = le_field( 'home_work_projects' );
$g_work       = is_array( $g_work ) ? $g_work : array();
$g_team       = le_field( 'home_hero_team' );
$g_team       = is_array( $g_team ) ? $g_team : array();

// Testimonial (first).
$g_tsts       = le_field( 'home_tst_items', $g_pid );
$g_tst        = ( is_array( $g_tsts ) && $g_tsts ) ? $g_tsts[0] : null;
$g_tst_id     = $g_tst ? ( is_object( $g_tst ) ? $g_tst->ID : (int) $g_tst ) : 0;
?>
<main id="main" class="site-main site-main--home g-main">

	<?php /* ---- Hero ---- */ ?>
	<section class="g-row g-hero" aria-label="<?php esc_attr_e( 'Homepage hero', 'lewisedward' ); ?>">
		<div class="g-cell g-hero__word"><h1><?php echo esc_html( $g_word ); ?></h1><span class="g-sr"><?php echo esc_html( $g_title ); ?></span></div>
		<div class="g-cell g-ph g-ph--mono g-hero__media">
			<video class="g-ph__video" autoplay muted loop playsinline preload="metadata" poster="<?php echo esc_url( $g_poster ); ?>"><source src="<?php echo esc_url( $g_video ); ?>" type="video/mp4"></video>
			<span class="g-cap g-mono"><?php esc_html_e( 'easyJet Traveller — WordPress', 'lewisedward' ); ?></span>
		</div>
		<div class="g-cell g-ph g-ph--mono g-hero__portrait"><img src="<?php echo esc_url( $g_portrait ); ?>" alt="" loading="lazy" /><span class="g-cap g-mono"><?php esc_html_e( 'Lewis — London', 'lewisedward' ); ?></span></div>
		<div class="g-cell g-dark g-hero__stmt">
			<div class="g-hero__lede"><?php echo wp_kses_post( $g_lede ); ?></div>
			<a class="g-line-link g-mono" href="<?php echo esc_url( $g_hero_cta['url'] ); ?>"<?php le_link_target_attr( $g_hero_cta ); ?>><span><?php echo esc_html( $g_hero_cta['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<div class="g-cell g-lime g-hero__stmt">
			<p><?php echo esc_html( $g_title ); ?></p>
			<?php if ( $g_logos ) : ?>
			<span class="g-hero__for"><?php esc_html_e( 'Recent work for:', 'lewisedward' ); ?></span>
			<div class="g-hero__logos">
				<?php foreach ( array_slice( $g_logos, 0, 5 ) as $row ) : $img = isset( $row['logo'] ) ? $row['logo'] : null; if ( ! $img || empty( $img['url'] ) ) { continue; } ?>
					<img src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ?? '' ); ?>" loading="lazy" />
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* ---- About ---- */ ?>
	<section class="g-row g-about" aria-label="<?php esc_attr_e( 'About Lewis Edward', 'lewisedward' ); ?>">
		<div class="g-cell g-about__side">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_about_eyebrow', false, __( 'About', 'lewisedward' ) ) ); ?></span>
			<div class="g-ph g-about__photo"><img src="<?php echo esc_url( $g_portrait ); ?>" alt="" loading="lazy" /></div>
		</div>
		<div class="g-cell g-about__prose">
			<h2><?php echo esc_html( trim( le_field( 'home_about_headline_1' ) . ' ' . le_field( 'home_about_headline_muted' ) . ' ' . le_field( 'home_about_headline_since' ) . ' ' . le_field( 'home_about_headline_year' ) ) ); ?></h2>
			<p><?php echo esc_html( le_field( 'home_about_lede' ) ); ?></p>
		</div>
	</section>

	<?php /* ---- Services ---- */ ?>
	<section class="g-row g-svc" aria-label="<?php esc_attr_e( 'Services', 'lewisedward' ); ?>">
		<div class="g-cell g-svc__left">
			<h2 class="g-svc__title"><?php echo esc_html( le_field( 'home_services_title', false, __( 'Design, build & support', 'lewisedward' ) ) ); ?></h2>
			<div class="g-svc__foot">
				<p><?php echo esc_html( le_field( 'home_services_lede' ) ); ?></p>
				<a class="g-btn g-mono" href="<?php echo esc_url( $g_svc_all['url'] ); ?>"<?php le_link_target_attr( $g_svc_all ); ?>><span><?php echo esc_html( $g_svc_all['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
		</div>
		<div class="g-cell g-dark g-svc__list">
			<?php foreach ( $g_services as $i => $svc ) : $sid = is_object( $svc ) ? $svc->ID : (int) $svc; $sub = le_field( 'service_subtitle', $sid ); $desc = le_field( 'service_short_description', $sid ); if ( ! $desc ) { $desc = get_the_excerpt( $sid ); } ?>
			<a class="g-srv" href="<?php echo esc_url( get_permalink( $sid ) ); ?>">
				<span class="g-srv__num g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span class="g-srv__main"><span class="g-srv__title"><?php echo esc_html( get_the_title( $sid ) ); ?></span><span class="g-srv__wire" aria-hidden="true"></span></span>
				<span class="g-srv__body"><span class="g-srv__desc"><?php echo esc_html( wp_strip_all_tags( $desc ) ); ?></span><?php if ( $sub ) : ?><span class="g-srv__tag g-mono"><i></i><?php echo esc_html( $sub ); ?></span><?php endif; ?><span class="g-srv__go"><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
			</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---- Work ---- */ ?>
	<?php if ( $g_work ) : ?>
	<section class="g-row g-work" aria-label="<?php esc_attr_e( 'Selected work', 'lewisedward' ); ?>">
		<div class="g-cell g-work__intro">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_recentwork_title', false, __( 'Selected work', 'lewisedward' ) ) ); ?></span>
			<h2><?php echo esc_html( trim( le_field( 'home_work_title_pre' ) . ' ' . le_field( 'home_work_title_accent' ) . ' ' . le_field( 'home_work_title_post' ) ) ); ?></h2>
			<?php if ( $g_team ) : ?><div class="g-team"><?php foreach ( $g_team as $m ) : $mid = is_object( $m ) ? $m->ID : (int) $m; if ( has_post_thumbnail( $mid ) ) { echo get_the_post_thumbnail( $mid, 'le_avatar', array( 'alt' => esc_attr( get_the_title( $mid ) ), 'loading' => 'lazy' ) ); } endforeach; ?></div><?php endif; ?>
		</div>
		<?php foreach ( array_slice( $g_work, 0, 3 ) as $w ) : $wid = is_object( $w ) ? $w->ID : (int) $w; ?>
		<a class="g-cell g-ph g-work__item" href="<?php echo esc_url( get_permalink( $wid ) ); ?>">
			<?php echo get_the_post_thumbnail( $wid, 'large', array( 'loading' => 'lazy' ) ); ?>
			<span class="g-cap g-mono"><?php echo esc_html( get_the_title( $wid ) ); ?></span>
		</a>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<?php /* ---- Clients ---- */ ?>
	<section class="g-row g-clients" aria-label="<?php esc_attr_e( 'Clients', 'lewisedward' ); ?>">
		<div class="g-cell g-clients__side">
			<span class="g-clients__lbl g-mono"><span><?php echo esc_html( le_field( 'home_clients_eyebrow', false, __( 'Clients', 'lewisedward' ) ) ); ?></span><span>01 — <?php echo esc_html( str_pad( (string) count( $g_logos ), 2, '0', STR_PAD_LEFT ) ); ?></span></span>
			<?php $w4 = isset( $g_work[3] ) ? ( is_object( $g_work[3] ) ? $g_work[3]->ID : (int) $g_work[3] ) : 0; if ( $w4 && has_post_thumbnail( $w4 ) ) : ?>
			<a class="g-ph g-clients__photo" href="<?php echo esc_url( get_permalink( $w4 ) ); ?>"><?php echo get_the_post_thumbnail( $w4, 'large', array( 'loading' => 'lazy' ) ); ?><span class="g-cap g-mono"><?php echo esc_html( get_the_title( $w4 ) ); ?></span></a>
			<?php endif; ?>
		</div>
		<div class="g-cell g-clients__copy">
			<p><?php echo esc_html( le_field( 'home_clients_lede' ) ); ?></p>
			<a class="g-btn g-mono" href="<?php echo esc_url( home_url( '/work' ) ); ?>"><span><?php esc_html_e( 'Work with Lewis', 'lewisedward' ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<div class="g-cell g-clients__list">
			<?php foreach ( $g_logos as $i => $row ) : $img = isset( $row['logo'] ) ? $row['logo'] : null; if ( ! $img || empty( $img['url'] ) ) { continue; } $name = ! empty( $row['name'] ) ? $row['name'] : ( ! empty( $img['alt'] ) ? $img['alt'] : ( $img['title'] ?? '' ) ); ?>
			<div class="g-crow"><span class="g-crow__name"><?php echo esc_html( $name ); ?></span><img src="<?php echo esc_url( $img['url'] ); ?>" alt="" loading="lazy" /></div>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---- Testimonial ---- */ ?>
	<?php if ( $g_tst_id ) : ?>
	<section class="g-row g-tst" aria-label="<?php esc_attr_e( 'Testimonial', 'lewisedward' ); ?>">
		<div class="g-cell"><span class="g-label g-mono"><?php echo esc_html( le_field( 'home_tst_eyebrow', $g_pid, __( 'Testimonials', 'lewisedward' ) ) ); ?></span></div>
		<blockquote class="g-cell g-tst__quote"><span class="g-tst__mark" aria-hidden="true">&ldquo;</span><?php echo esc_html( le_field( 'testimonial_quote', $g_tst_id ) ); ?></blockquote>
		<div class="g-cell g-dark g-tst__meta">
			<?php if ( has_post_thumbnail( $g_tst_id ) ) { echo get_the_post_thumbnail( $g_tst_id, 'le_avatar', array( 'class' => 'g-tst__avatar', 'loading' => 'lazy' ) ); } ?>
			<p><?php echo esc_html( le_field( 'testimonial_author', $g_tst_id ) ); ?><span class="g-mono"><?php echo esc_html( le_field( 'testimonial_role', $g_tst_id ) ); ?></span></p>
			<p><?php esc_html_e( 'Company', 'lewisedward' ); ?><span class="g-mono"><?php echo esc_html( le_field( 'testimonial_company', $g_tst_id ) ); ?></span></p>
		</div>
	</section>
	<?php endif; ?>

</main>
