<?php
/**
 * Grid skin — homepage.
 *
 * The classic homepage's content and heading hierarchy, laid out as a
 * hairline grid of cells. Sections, their order and their headings match the
 * classic template one for one (hero h1; recent work, clients, services,
 * partnership, about, testimonials and contact h2s; service and partnership
 * titles h3), so search engines see the same page. Every field is the one the
 * classic template reads; nothing new is entered in wp-admin.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$g_pid = (int) get_option( 'page_on_front' );
$g_pid = $g_pid ? $g_pid : false;

$g_id = static function ( $item ) {
	return is_object( $item ) ? (int) $item->ID : (int) $item;
};

// Hero.
$g_word       = le_option( 'grid_hero_word', 'Lewis' );
$g_eyebrow    = le_field( 'home_hero_eyebrow' );
$g_lede       = le_field( 'home_hero_lede' );
$g_hero_cta   = le_link( 'home_hero_editorial_cta', '/contact', __( 'Get in touch', 'lewisedward' ) );
$g_poster     = LE_URI . '/assets/videos/macbook-easyjet-poster.jpg?ver=' . le_asset_ver( 'assets/videos/macbook-easyjet-poster.jpg' );
$g_video      = LE_URI . '/assets/videos/macbook-easyjet.mp4?ver=' . le_asset_ver( 'assets/videos/macbook-easyjet.mp4' );
$g_portrait   = le_field( 'home_about_portrait' );
$g_portrait   = ( is_array( $g_portrait ) && ! empty( $g_portrait['url'] ) ) ? $g_portrait['url'] : LE_URI . '/assets/images/lewis-wordpress-developer.jpg';
$g_caps       = le_field( 'home_hero_capabilities' );
$g_caps       = is_array( $g_caps ) ? $g_caps : array();
$g_hero_logos = le_field( 'home_hero_clients' );
$g_hero_logos = is_array( $g_hero_logos ) ? $g_hero_logos : array();
$g_team       = le_field( 'home_hero_team' );
$g_team       = is_array( $g_team ) ? $g_team : array();
$g_quote      = le_link( 'home_hero_cta', '/quotes', le_field( 'home_hero_cta_headline', false, __( 'Start a project', 'lewisedward' ) ) );
$g_quote_url  = le_field( 'home_hero_cta_url', false, '/quotes' );
$g_quote_url  = ( 0 === strpos( (string) $g_quote_url, 'http' ) ) ? $g_quote_url : home_url( $g_quote_url );

// Recent work.
$g_work       = le_field( 'home_work_projects' );
$g_work       = is_array( $g_work ) ? $g_work : array();

// Clients.
$g_logos      = le_field( 'home_clients_logos' );
$g_logos      = is_array( $g_logos ) ? $g_logos : array();

// Services.
$g_services   = le_field( 'home_services_items' );
$g_services   = is_array( $g_services ) ? $g_services : array();
$g_svc_all    = le_link( 'home_services_all', '/services', __( 'All services', 'lewisedward' ), false );

// Partnership.
$g_ov_items   = le_field( 'home_overlap_items' );
$g_ov_items   = is_array( $g_ov_items ) ? $g_ov_items : array();
$g_ov_video   = le_field( 'home_overlap_video' );
$g_ov_poster  = le_field( 'home_overlap_poster' );
$g_ov_video   = is_array( $g_ov_video ) ? ( $g_ov_video['url'] ?? '' ) : (string) $g_ov_video; // the field returns a URL on this site
$g_ov_poster  = ( is_array( $g_ov_poster ) && ! empty( $g_ov_poster['url'] ) ) ? $g_ov_poster['url'] : '';

// About.
$g_stats      = le_field( 'home_about_stats' );
$g_stats      = is_array( $g_stats ) ? $g_stats : array();

// Testimonials.
$g_tsts       = le_field( 'home_tst_items', $g_pid );
$g_tsts       = is_array( $g_tsts ) ? $g_tsts : array();

// Contact.
$g_cta_url    = le_field( 'home_cta_url', $g_pid, '/quotes' );
$g_cta_url    = ( 0 === strpos( (string) $g_cta_url, 'http' ) ) ? $g_cta_url : home_url( $g_cta_url );
?>
<main id="main" class="site-main site-main--home g-main">

	<?php /* ---- Hero: h1 is the real title; the giant word is decoration ---- */ ?>
	<section class="g-row g-hero" aria-label="<?php esc_attr_e( 'Homepage hero', 'lewisedward' ); ?>">
		<div class="g-cell g-hero__word" aria-hidden="true"><span><?php echo esc_html( $g_word ); ?></span></div>
		<div class="g-cell g-ph g-ph--mono g-hero__media">
			<video class="g-ph__video" autoplay muted loop playsinline preload="metadata" poster="<?php echo esc_url( $g_poster ); ?>"><source src="<?php echo esc_url( $g_video ); ?>" type="video/mp4"></video>
			<span class="g-cap g-mono"><?php esc_html_e( 'easyJet Traveller — WordPress', 'lewisedward' ); ?></span>
		</div>
		<div class="g-cell g-ph g-ph--mono g-hero__portrait"><img src="<?php echo esc_url( $g_portrait ); ?>" alt="" loading="lazy" /><span class="g-cap g-mono"><?php esc_html_e( 'Lewis — London', 'lewisedward' ); ?></span></div>
		<div class="g-cell g-lime g-hero__title">
			<?php if ( $g_eyebrow ) : ?><span class="g-mono g-hero__eyebrow"><?php echo esc_html( $g_eyebrow ); ?></span><?php endif; ?>
			<h1><?php echo esc_html( le_field( 'home_hero_title_1' ) ); ?> <?php echo esc_html( le_field( 'home_hero_title_1_accent' ) ); ?> <?php echo esc_html( le_field( 'home_hero_title_2_pre' ) ); ?> &amp; <?php echo esc_html( le_field( 'home_hero_title_2_post' ) ); ?></h1>
			<a class="g-line-link g-mono" href="<?php echo esc_url( $g_hero_cta['url'] ); ?>"<?php le_link_target_attr( $g_hero_cta ); ?>><span><?php echo esc_html( $g_hero_cta['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<div class="g-cell g-dark g-hero__stmt">
			<div class="g-hero__lede"><?php echo wp_kses_post( $g_lede ); ?></div>
			<a class="g-line-link g-mono" href="<?php echo esc_url( $g_quote_url ); ?>"><span><?php echo esc_html( le_field( 'home_hero_cta_headline', false, __( 'Start a project', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
	</section>

	<?php /* ---- Metrics + capabilities (the hero's second and third tiles) ---- */ ?>
	<section class="g-row g-facts" aria-label="<?php echo esc_attr( le_field( 'home_hero_metrics_label', false, __( 'Metrics', 'lewisedward' ) ) ); ?>">
		<div class="g-cell g-fact g-fact--big">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_hero_metrics_label', false, __( 'Metrics', 'lewisedward' ) ) ); ?></span>
			<span class="g-fact__num"><?php echo esc_html( le_field( 'home_hero_metric_value' ) ); ?></span>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_metric_label' ) ); ?></span>
		</div>
		<div class="g-cell g-fact">
			<div class="g-stack"><?php foreach ( array_slice( $g_hero_logos, 0, 4 ) as $c ) { $l = isset( $c['logo'] ) ? $c['logo'] : null; if ( $l && ! empty( $l['url'] ) ) { $bg = ! empty( $c['bg_color'] ) ? ' style="background-color:' . esc_attr( trim( (string) $c['bg_color'] ) ) . '"' : ''; echo '<span class="g-stack__item"' . $bg . '><img src="' . esc_url( $l['url'] ) . '" alt="" loading="lazy" /></span>'; } } ?><span class="g-stack__item g-stack__item--count"><?php echo esc_html( le_field( 'home_hero_projects_count' ) ); ?></span></div>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_projects_label' ) ); ?></span>
		</div>
		<div class="g-cell g-fact">
			<div class="g-stack"><span class="g-stack__item g-stack__item--zap"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9.5"/><path d="M2.5 12h19M12 2.5c3 3 3 16 0 19M12 2.5c-3 3-3 16 0 19"/></svg></span></div>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_ai_label' ) ); ?></span>
		</div>
		<div class="g-cell g-fact">
			<div class="g-stack"><?php foreach ( $g_team as $m ) { $mid = $g_id( $m ); if ( has_post_thumbnail( $mid ) ) { echo '<span class="g-stack__item g-stack__item--team">' . get_the_post_thumbnail( $mid, 'le_avatar', array( 'alt' => esc_attr( get_the_title( $mid ) ), 'loading' => 'lazy' ) ) . '</span>'; } } ?></div>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_team_label' ) ); ?></span>
		</div>
		<div class="g-cell g-caps">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_hero_caps_label', false, __( 'Capabilities', 'lewisedward' ) ) ); ?></span>
			<ul class="g-caps__list">
				<?php foreach ( $g_caps as $i => $c ) : $cid = $g_id( $c ); ?>
				<li><a href="<?php echo esc_url( get_permalink( $cid ) ); ?>"><span class="g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><span><?php echo esc_html( get_the_title( $cid ) ); ?></span><span class="g-mono g-caps__tag"><?php echo esc_html( le_field( 'service_subtitle', $cid ) ); ?></span></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<?php /* ---- Recent work (h2) ---- */ ?>
	<?php if ( $g_work ) : ?>
	<section class="g-row g-work" aria-label="<?php esc_attr_e( 'Recent work', 'lewisedward' ); ?>">
		<div class="g-cell g-work__intro">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_recentwork_title', false, __( 'Recent Work', 'lewisedward' ) ) ); ?></span>
			<h2><?php echo esc_html( le_field( 'home_work_title_pre' ) ); ?> <?php echo esc_html( le_field( 'home_work_title_accent' ) ); ?> <?php echo esc_html( le_field( 'home_work_title_post' ) ); ?></h2>
			<p><?php echo esc_html( le_field( 'home_work_lede' ) ); ?></p>
			<a class="g-line-link g-mono" href="<?php echo esc_url( home_url( '/work' ) ); ?>"><span><?php echo esc_html( le_field( 'home_recentwork_discover', false, __( 'Discover all work', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<?php foreach ( array_slice( $g_work, 0, 3 ) as $w ) : $wid = $g_id( $w ); ?>
		<a class="g-cell g-ph g-work__item" href="<?php echo esc_url( get_permalink( $wid ) ); ?>">
			<?php echo get_the_post_thumbnail( $wid, 'large', array( 'loading' => 'lazy' ) ); ?>
			<span class="g-cap g-mono"><?php echo esc_html( get_the_title( $wid ) ); ?></span>
		</a>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<?php /* ---- Clients (h2) ---- */ ?>
	<section class="g-row g-clients" aria-label="<?php esc_attr_e( 'Clients', 'lewisedward' ); ?>">
		<div class="g-cell g-clients__side">
			<span class="g-clients__lbl g-mono"><span><?php echo esc_html( le_field( 'home_clients_eyebrow', false, __( 'Clients', 'lewisedward' ) ) ); ?></span><span>01 — <?php echo esc_html( str_pad( (string) count( $g_logos ), 2, '0', STR_PAD_LEFT ) ); ?></span></span>
			<?php $w4 = isset( $g_work[3] ) ? $g_id( $g_work[3] ) : 0; if ( $w4 && has_post_thumbnail( $w4 ) ) : ?>
			<a class="g-ph g-clients__photo" href="<?php echo esc_url( get_permalink( $w4 ) ); ?>"><?php echo get_the_post_thumbnail( $w4, 'large', array( 'loading' => 'lazy' ) ); ?><span class="g-cap g-mono"><?php echo esc_html( get_the_title( $w4 ) ); ?></span></a>
			<?php endif; ?>
		</div>
		<div class="g-cell g-clients__copy">
			<h2><?php echo esc_html( le_field( 'home_clients_title_pre' ) ); ?> <span class="g-accent"><?php echo esc_html( (string) (int) le_field( 'home_clients_counter' ) ); ?>+</span> <?php echo esc_html( le_field( 'home_clients_title_post' ) ); ?></h2>
			<p><?php echo esc_html( le_field( 'home_clients_lede' ) ); ?></p>
			<a class="g-btn g-mono" href="<?php echo esc_url( home_url( '/work' ) ); ?>"><span><?php esc_html_e( 'Selected work', 'lewisedward' ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<div class="g-cell g-clients__list">
			<?php foreach ( $g_logos as $row ) : $img = isset( $row['logo'] ) ? $row['logo'] : null; if ( ! $img || empty( $img['url'] ) ) { continue; } $name = ! empty( $row['name'] ) ? $row['name'] : ( $img['alt'] ?? '' ); ?>
			<div class="g-crow"><img src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" /></div>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---- Services (h2 + h3s) ---- */ ?>
	<section class="g-row g-svc" aria-label="<?php esc_attr_e( 'Services', 'lewisedward' ); ?>">
		<div class="g-cell g-svc__left">
			<span class="g-label g-mono g-svc__eyebrow"><?php echo esc_html( le_field( 'home_services_eyebrow', false, __( 'Services', 'lewisedward' ) ) ); ?></span>
			<h2 class="g-svc__title"><?php echo esc_html( le_field( 'home_services_title' ) ); ?></h2>
			<div class="g-svc__foot">
				<p><?php echo esc_html( le_field( 'home_services_lede' ) ); ?></p>
				<a class="g-btn g-mono" href="<?php echo esc_url( $g_svc_all['url'] ); ?>"<?php le_link_target_attr( $g_svc_all ); ?>><span><?php echo esc_html( $g_svc_all['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
		</div>
		<div class="g-cell g-dark g-svc__list">
			<?php foreach ( $g_services as $i => $svc ) : $sid = $g_id( $svc ); $sub = le_field( 'service_subtitle', $sid ); $desc = le_field( 'service_short_description', $sid ); if ( ! $desc ) { $desc = get_the_excerpt( $sid ); } ?>
			<a class="g-srv" href="<?php echo esc_url( get_permalink( $sid ) ); ?>">
				<span class="g-srv__num g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span class="g-srv__main"><span class="g-srv__title" role="heading" aria-level="3"><?php echo esc_html( get_the_title( $sid ) ); ?></span><span class="g-srv__wire" aria-hidden="true"><?php echo le_grid_wire( get_post_field( 'post_name', $sid ) . ' ' . get_the_title( $sid ), $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
				<span class="g-srv__body"><span class="g-srv__desc"><?php echo esc_html( wp_strip_all_tags( $desc ) ); ?></span><?php if ( $sub ) : ?><span class="g-srv__tag g-mono"><i></i><?php echo esc_html( $sub ); ?></span><?php endif; ?><span class="g-srv__go"><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></span>
			</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---- Partnership (h2 + h3s) ---- */ ?>
	<section class="g-row g-ov" aria-label="<?php esc_attr_e( 'Partnership and additional services', 'lewisedward' ); ?>">
		<div class="g-cell g-ov__intro">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_overlap_eyebrow', false, __( 'Partnership', 'lewisedward' ) ) ); ?></span>
			<h2><?php echo wp_kses_post( le_field( 'home_overlap_title' ) ); ?></h2>
			<p><?php echo esc_html( le_field( 'home_overlap_lede' ) ); ?></p>
		</div>
		<div class="g-cell g-ph g-ov__media">
			<?php if ( $g_ov_video ) : ?>
			<video class="g-ph__video" autoplay muted loop playsinline preload="metadata"<?php echo $g_ov_poster ? ' poster="' . esc_url( $g_ov_poster ) . '"' : ''; ?>><source src="<?php echo esc_url( $g_ov_video ); ?>" type="video/mp4"></video>
			<?php elseif ( $g_ov_poster ) : ?>
			<img src="<?php echo esc_url( $g_ov_poster ); ?>" alt="" loading="lazy" />
			<?php endif; ?>
			<span class="g-cap g-mono"><?php echo esc_html( le_field( 'home_overlap_wordmark', false, __( 'Services', 'lewisedward' ) ) ); ?></span>
		</div>
		<div class="g-cell g-ov__list">
			<?php foreach ( $g_ov_items as $i => $item ) : $oid = $g_id( $item ); ?>
			<a class="g-ov__item" href="<?php echo esc_url( get_permalink( $oid ) ); ?>">
				<span class="g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<span class="g-ov__body"><span class="g-ov__title" role="heading" aria-level="3"><?php echo esc_html( get_the_title( $oid ) ); ?></span><span class="g-ov__desc"><?php echo esc_html( le_field( 'service_short_description', $oid ) ); ?></span></span>
				<?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---- About (h2 + stats) ---- */ ?>
	<section class="g-row g-about" aria-label="<?php esc_attr_e( 'About Lewis Edward', 'lewisedward' ); ?>">
		<div class="g-cell g-about__side">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_about_eyebrow', false, __( 'About', 'lewisedward' ) ) ); ?> <?php echo esc_html( le_field( 'home_about_year' ) ); ?></span>
			<div class="g-ph g-about__photo"><img src="<?php echo esc_url( $g_portrait ); ?>" alt="" loading="lazy" /></div>
		</div>
		<div class="g-cell g-about__prose">
			<div class="g-about__text">
			<h2><?php echo esc_html( le_field( 'home_about_headline_1' ) ); ?> <?php echo esc_html( le_field( 'home_about_headline_muted' ) ); ?> <?php echo esc_html( le_field( 'home_about_headline_since' ) ); ?> <?php echo esc_html( le_field( 'home_about_headline_year' ) ); ?></h2>
			<p><?php echo esc_html( le_field( 'home_about_lede' ) ); ?></p>
			<a class="g-line-link g-mono g-about__more" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><span><?php esc_html_e( 'More about Lewis', 'lewisedward' ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
			<?php if ( $g_stats ) : ?>
			<div class="g-stats">
				<?php foreach ( $g_stats as $st ) : ?>
				<div class="g-stat"><span class="g-stat__num"><?php echo esc_html( ( $st['value'] ?? '' ) . ( $st['suffix'] ?? '' ) ); ?></span><span class="g-mono"><?php echo esc_html( $st['label'] ?? '' ); ?></span></div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* ---- Testimonials ---- */ ?>
	<?php if ( $g_tsts ) : ?>
	<section class="g-row g-tst" aria-label="<?php esc_attr_e( 'Testimonials', 'lewisedward' ); ?>">
		<div class="g-cell g-tst__side"><span class="g-label g-mono"><?php echo esc_html( le_field( 'home_tst_eyebrow', $g_pid, __( 'Testimonials', 'lewisedward' ) ) ); ?> <?php echo esc_html( str_pad( (string) count( $g_tsts ), 2, '0', STR_PAD_LEFT ) ); ?></span></div>
		<div class="g-cell g-tst__stage">
			<?php foreach ( $g_tsts as $i => $t ) : $tid = $g_id( $t ); $role = le_field( 'testimonial_role', $tid ); $co = le_field( 'testimonial_company', $tid ); ?>
			<figure class="g-tst__item">
				<blockquote class="g-tst__quote"><span class="g-tst__mark" aria-hidden="true">&ldquo;</span><p><?php echo esc_html( le_field( 'testimonial_quote', $tid ) ); ?></p></blockquote>
				<figcaption class="g-tst__meta"><?php if ( has_post_thumbnail( $tid ) ) { echo get_the_post_thumbnail( $tid, 'le_avatar', array( 'class' => 'g-tst__avatar', 'loading' => 'lazy' ) ); } ?><span><?php echo esc_html( le_field( 'testimonial_author', $tid ) ); ?></span><span class="g-mono"><?php echo esc_html( trim( $role . ( $co ? ' — ' . $co : '' ) ) ); ?></span></figcaption>
			</figure>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php /* ---- Contact (h2) ---- */ ?>
	<section class="g-row g-cta" aria-label="<?php esc_attr_e( 'Contact', 'lewisedward' ); ?>">
		<a class="g-cell g-lime g-cta__go" href="<?php echo esc_url( $g_cta_url ); ?>">
			<h2><?php echo esc_html( le_field( 'home_cta_line1', $g_pid ) ); ?><br><?php echo esc_html( le_field( 'home_cta_line2_pre', $g_pid ) ); ?> <em><?php echo esc_html( le_field( 'home_cta_word', $g_pid ) ); ?></em></h2>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_cta_ring', $g_pid, __( 'Request a quote', 'lewisedward' ) ) ); ?></span>
			<svg class="g-cta__arrow" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><path d="M8 8h104v104M112 8 8 112"/></svg>
		</a>
		<div class="g-cell g-cta__previews">
			<?php $g_prev = le_field( 'home_cta_previews', $g_pid ); $g_prev = is_array( $g_prev ) ? $g_prev : array(); foreach ( array_slice( $g_prev, 0, 4 ) as $pv ) : $pvid = $g_id( $pv ); if ( ! has_post_thumbnail( $pvid ) ) { continue; } ?>
			<a class="g-ph g-cta__preview" href="<?php echo esc_url( get_permalink( $pvid ) ); ?>"><?php echo get_the_post_thumbnail( $pvid, 'medium_large', array( 'loading' => 'lazy' ) ); ?><span class="g-cap g-mono"><?php echo esc_html( get_the_title( $pvid ) ); ?></span></a>
			<?php endforeach; ?>
		</div>
	</section>

</main>
