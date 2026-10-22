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
$g_word       = le_field( 'grid_hero_word', false, le_option( 'grid_hero_word', 'Lewis' ) );
$g_eyebrow    = le_field( 'home_hero_eyebrow' );
$g_lede       = le_field( 'home_hero_lede' );
$g_hero_cta   = le_link( 'home_hero_editorial_cta', '/contact', __( 'Get in touch', 'lewisedward' ) );
$g_poster     = LE_URI . '/assets/videos/macbook-easyjet-poster.jpg?ver=' . le_asset_ver( 'assets/videos/macbook-easyjet-poster.jpg' );
$g_video      = LE_URI . '/assets/videos/macbook-easyjet.mp4?ver=' . le_asset_ver( 'assets/videos/macbook-easyjet.mp4' );
$g_ov_file    = le_field( 'grid_hero_media_video' );
$g_ov_img     = le_field( 'grid_hero_media_poster' );
if ( is_array( $g_ov_file ) && ! empty( $g_ov_file['url'] ) ) { $g_video = $g_ov_file['url']; }
if ( is_array( $g_ov_img ) && ! empty( $g_ov_img['url'] ) ) { $g_poster = $g_ov_img['url']; }
// Portraits: the hero and About photos have their own fields, each falling back to the classic About portrait.
$g_photo = static function ( $field ) {
	$img = le_field( $field );
	if ( ! is_array( $img ) || empty( $img['url'] ) ) {
		$img = le_field( 'home_about_portrait' );
	}
	$url = ( is_array( $img ) && ! empty( $img['url'] ) ) ? $img : LE_URI . '/assets/images/lewis-wordpress-developer.jpg';
	$alt = ( is_array( $img ) && ! empty( $img['alt'] ) ) ? $img['alt'] : __( 'Lewis Edward, London WordPress web designer and developer', 'lewisedward' );
	return array( $url, $alt ); // $url is the ACF image array when set (gives srcset), else the theme fallback URL.
};
list( $g_portrait, $g_portrait_alt )       = $g_photo( 'grid_hero_portrait' );
list( $g_about_photo, $g_about_photo_alt ) = $g_photo( 'grid_about_photo' );
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
		<div class="g-cell g-hero__word">
			<div class="g-hero__intro">
			<div class="g-hero__lede"><?php echo wp_kses_post( $g_lede ); ?></div>
			<a class="g-line-link g-mono" href="<?php echo esc_url( $g_quote_url ); ?>"><span><?php echo esc_html( le_field( 'home_hero_cta_headline', false, __( 'Start a project', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
			<span aria-hidden="true"><?php echo esc_html( $g_word ); ?></span>
		</div>
		<?php $g_ejt_id = (int) le_field( 'grid_hero_media_link' ); $g_ejt = $g_ejt_id ? get_post( $g_ejt_id ) : get_page_by_path( 'easyjet-traveller', OBJECT, 'work' ); $g_ejt_url = $g_ejt ? get_permalink( $g_ejt ) : home_url( '/work/' ); $g_ejt_cap = le_field( 'grid_hero_media_caption', false, $g_ejt ? get_the_title( $g_ejt ) : __( 'easyJet Traveller', 'lewisedward' ) ); ?>
		<a class="g-cell g-ph g-ph--mono g-hero__media" href="<?php echo esc_url( $g_ejt_url ); ?>" aria-label="<?php echo esc_attr( $g_ejt_cap ); ?>">
			<video class="g-ph__video" autoplay muted loop playsinline preload="auto" poster="<?php echo esc_url( $g_poster ); ?>"><?php if ( empty( $g_ov_file['url'] ) ) : ?><source src="<?php echo esc_url( LE_URI . '/assets/videos/macbook-easyjet-960.mp4?ver=' . le_asset_ver( 'assets/videos/macbook-easyjet-960.mp4' ) ); ?>" type="video/mp4" media="(max-width: 720px)"><?php endif; ?><source src="<?php echo esc_url( $g_video ); ?>" type="video/mp4"></video>
			<span class="g-cap g-mono"><?php echo esc_html( $g_ejt_cap ); ?><?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
		<div class="g-cell g-ph g-ph--mono g-hero__portrait"><?php echo le_grid_img( $g_portrait, $g_portrait_alt, '(max-width: 720px) 100vw, (max-width: 1100px) 50vw, 25vw', array( 'fetchpriority' => 'high', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="g-cap g-mono"><?php echo esc_html( le_field( 'grid_hero_portrait_caption', false, __( 'Lewis – London', 'lewisedward' ) ) ); ?></span></div>
		<div class="g-cell g-lime g-hero__title">
			<?php if ( $g_eyebrow ) : ?><span class="g-mono g-hero__eyebrow"><?php echo esc_html( $g_eyebrow ); ?></span><?php endif; ?>
			<h1><?php echo esc_html( le_field( 'home_hero_title_1' ) ); ?> <?php echo esc_html( le_field( 'home_hero_title_1_accent' ) ); ?> <?php echo esc_html( le_field( 'home_hero_title_2_pre' ) ); ?> &amp; <?php echo esc_html( le_field( 'home_hero_title_2_post' ) ); ?></h1>
			<a class="g-line-link g-mono" href="<?php echo esc_url( $g_hero_cta['url'] ); ?>"<?php le_link_target_attr( $g_hero_cta ); ?>><span><?php echo esc_html( $g_hero_cta['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
			<?php $g_ticker = array_filter( $g_logos, function ( $r ) { return ! empty( $r['logo']['url'] ); } ); if ( $g_ticker ) : ?>
		<div class="g-cell g-hero__ticker" aria-label="<?php esc_attr_e( 'Clients', 'lewisedward' ); ?>">
			<span class="g-hero__ticker-label g-mono"><?php echo esc_html( le_field( 'grid_ticker_label', false, __( 'Trusted by', 'lewisedward' ) ) ); ?></span>
			<div class="g-ticker"><div class="g-ticker__track">
				<?php for ( $pass = 0; $pass < 2; $pass++ ) : ?>
				<div class="g-ticker__set"<?php echo $pass ? ' aria-hidden="true"' : ''; ?>>
					<?php foreach ( $g_ticker as $row ) : ?>
					<img src="<?php echo esc_url( le_grid_logo( $row['logo']['url'] ) ); ?>" alt="<?php echo esc_attr( $pass ? '' : ( ! empty( $row['name'] ) ? $row['name'] : ( $row['logo']['alt'] ?? '' ) ) ); ?>" width="<?php echo esc_attr( (string) ( $row['logo']['width'] ?? '' ) ); ?>" height="<?php echo esc_attr( (string) ( $row['logo']['height'] ?? '' ) ); ?>" />
					<?php endforeach; ?>
				</div>
				<?php endfor; ?>
			</div></div>
		</div>
		<?php endif; ?>
	</section>

	<?php /* ---- Metrics + capabilities (the hero's second and third tiles) ---- */ ?>
	<section class="g-row g-facts" aria-label="<?php echo esc_attr( le_field( 'home_hero_metrics_label', false, __( 'Metrics', 'lewisedward' ) ) ); ?>">
		<div class="g-cell g-fact g-fact--big">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_hero_metrics_label', false, __( 'Metrics', 'lewisedward' ) ) ); ?></span>
			<span class="g-fact__num"><?php echo esc_html( le_field( 'home_hero_metric_value' ) ); ?></span>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_metric_label' ) ); ?></span>
		</div>
		<div class="g-cell g-fact">
			<div class="g-stack" aria-hidden="true"><?php foreach ( array_slice( $g_hero_logos, 0, 4 ) as $c ) { $l = isset( $c['logo'] ) ? $c['logo'] : null; if ( $l && ! empty( $l['url'] ) ) { echo '<span class="g-stack__item g-stack__item--logo"><img src="' . esc_url( le_grid_logo( $l['url'] ) ) . '" alt="" loading="lazy" /></span>'; } } ?><span class="g-stack__item g-stack__item--count"><?php echo esc_html( le_field( 'home_hero_projects_count' ) ); ?></span></div>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_projects_label' ) ); ?></span>
		</div>
		<div class="g-cell g-fact">
			<div class="g-stack" aria-hidden="true"><?php foreach ( $g_team as $m ) { $mid = $g_id( $m ); if ( has_post_thumbnail( $mid ) ) { echo '<span class="g-stack__item g-stack__item--team">' . get_the_post_thumbnail( $mid, 'le_avatar', array( 'alt' => esc_attr( get_the_title( $mid ) ), 'loading' => 'lazy' ) ) . '</span>'; } } ?></div>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_team_label' ) ); ?></span>
		</div>
		<div class="g-cell g-fact">
			<span class="g-fact__num"><?php echo esc_html( le_field( 'grid_hero_ai_value', false, '5%' ) ); ?></span>
			<span class="g-mono"><?php echo esc_html( le_field( 'home_hero_ai_label' ) ); ?></span>
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
		<?php
		// Five case studies: the chosen ones first, topped up with the latest.
		$g_work_ids = array_values( array_filter( array_map( $g_id, $g_work ) ) );
		if ( count( $g_work_ids ) < 5 ) {
			$g_more = get_posts( array( 'post_type' => 'work', 'post_status' => 'publish', 'numberposts' => 5 - count( $g_work_ids ), 'post__not_in' => array_merge( $g_work_ids, le_work_showcase_ids() ), 'fields' => 'ids' ) );
			$g_work_ids = array_merge( $g_work_ids, $g_more );
		}
		foreach ( array_slice( $g_work_ids, 0, 5 ) as $wid ) {
			le_grid_work_tile( $wid );
		}
		?>
		<a class="g-cell g-work__all" href="<?php echo esc_url( home_url( '/work' ) ); ?>">
			<span class="g-mono"><?php echo esc_html( str_pad( (string) wp_count_posts( 'work' )->publish, 2, '0', STR_PAD_LEFT ) ); ?> <?php echo esc_html( le_field( 'grid_work_count_label', false, __( 'projects', 'lewisedward' ) ) ); ?></span>
			<span class="g-work__all-title"><?php echo esc_html( le_field( 'grid_work_all_label', false, __( 'All work', 'lewisedward' ) ) ); ?></span>
			<svg class="g-work__all-arrow" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><path d="M8 8h104v104M112 8 8 112"/></svg>
		</a>
	</section>
	<?php endif; ?>

	<?php /* ---- Clients (h2) ---- */ ?>
	<section class="g-row g-clients" aria-label="<?php esc_attr_e( 'Clients', 'lewisedward' ); ?>">
		<div class="g-cell g-clients__side">
			<?php
			// Industries: the grid_clients_sectors repeater, or failing that one entry per client
			// logo read from its name ("Banking and Finance WordPress Website…" → "Banking and Finance").
			$g_sectors = array();
			$g_sec_set = le_field( 'grid_clients_sectors' );
			foreach ( is_array( $g_sec_set ) ? $g_sec_set : array() as $row ) {
				$n = trim( (string) ( $row['name'] ?? '' ) );
				if ( $n && ! in_array( $n, $g_sectors, true ) ) {
					$g_sectors[] = $n;
				}
			}
			foreach ( $g_sectors ? array() : $g_logos as $row ) {
				$src = ! empty( $row['name'] ) ? $row['name'] : ( $row['logo']['alt'] ?? '' );
				$n   = trim( preg_replace( '/\s*(WordPress|Website|Web)\b.*$/i', '', (string) $src ) );
				if ( $n && ! in_array( $n, $g_sectors, true ) ) {
					$g_sectors[] = $n;
				}
			}
			?>
			<span class="g-clients__lbl g-mono"><span><?php echo esc_html( le_field( 'home_clients_eyebrow', false, __( 'Clients', 'lewisedward' ) ) ); ?></span><span>01 &ndash; <?php echo esc_html( str_pad( (string) count( $g_sectors ), 2, '0', STR_PAD_LEFT ) ); ?></span></span>
			<?php if ( $g_sectors ) : ?>
			<span class="g-clients__side-title"><?php echo esc_html( le_field( 'grid_clients_side_title', false, __( 'Sectors we build for', 'lewisedward' ) ) ); ?></span>
			<ul class="g-clients__sectors">
				<?php foreach ( $g_sectors as $i => $sec ) : ?>
				<li><span class="g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><span><?php echo esc_html( $sec ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
		<div class="g-cell g-clients__copy">
			<h2><?php echo esc_html( le_field( 'home_clients_title_pre' ) ); ?> <span class="g-accent"><?php echo esc_html( (string) (int) le_field( 'home_clients_counter' ) ); ?>+</span> <?php echo esc_html( le_field( 'home_clients_title_post' ) ); ?></h2>
			<p><?php echo esc_html( le_field( 'home_clients_lede' ) ); ?></p>
			<a class="g-btn g-mono" href="<?php echo esc_url( home_url( '/work' ) ); ?>"><span><?php echo esc_html( le_field( 'grid_clients_button', false, __( 'Selected work', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<div class="g-cell g-clients__list">
			<?php foreach ( $g_logos as $row ) : $img = isset( $row['logo'] ) ? $row['logo'] : null; if ( ! $img || empty( $img['url'] ) ) { continue; } $name = ! empty( $row['name'] ) ? $row['name'] : ( $img['alt'] ?? '' ); ?>
			<div class="g-crow"><img src="<?php echo esc_url( le_grid_logo( $img['url'] ) ); ?>" alt="<?php echo esc_attr( $name ); ?>"<?php echo ! empty( $img['width'] ) && ! empty( $img['height'] ) ? ' width="' . (int) $img['width'] . '" height="' . (int) $img['height'] . '"' : ''; ?> loading="lazy" decoding="async" /></div>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---- Services (h2 + h3s) ---- */ ?>
	<section class="g-row g-svc" aria-label="<?php esc_attr_e( 'Services', 'lewisedward' ); ?>">
		<div class="g-cell g-svc__left">
			<?php $g_sv = le_field( 'grid_services_video' ); $g_sp = le_field( 'grid_services_poster' ); $g_sv = ( is_array( $g_sv ) && ! empty( $g_sv['url'] ) ) ? $g_sv['url'] : LE_URI . '/assets/videos/typing-macbook-sm.mp4?ver=' . le_asset_ver( 'assets/videos/typing-macbook-sm.mp4' ); $g_sp = ( is_array( $g_sp ) && ! empty( $g_sp['url'] ) ) ? $g_sp['url'] : LE_URI . '/assets/videos/typing-macbook-poster.jpg'; ?>
			<span class="g-svc__bg g-ph g-ph--mono" aria-hidden="true"><video class="g-ph__video" muted loop playsinline preload="none" poster="<?php echo esc_url( $g_sp ); ?>"><source src="<?php echo esc_url( $g_sv ); ?>" type="video/mp4"></video></span>
			<span class="g-label g-mono g-svc__eyebrow"><span><?php echo esc_html( le_field( 'home_services_eyebrow', false, __( 'Services', 'lewisedward' ) ) ); ?></span><span>01 &ndash; <?php echo esc_html( str_pad( (string) count( $g_services ), 2, '0', STR_PAD_LEFT ) ); ?></span></span>
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
				<div class="g-srv__main"><h3 class="g-srv__title"><?php echo esc_html( get_the_title( $sid ) ); ?></h3><span class="g-srv__wire" aria-hidden="true"><?php echo le_grid_wire( get_post_field( 'post_name', $sid ) . ' ' . get_the_title( $sid ), $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></div>
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
			<video class="g-ph__video" muted loop playsinline preload="none" aria-hidden="true"<?php echo $g_ov_poster ? ' poster="' . esc_url( $g_ov_poster ) . '"' : ''; ?>><source src="<?php echo esc_url( $g_ov_video ); ?>" type="video/mp4"></video>
			<?php elseif ( $g_ov_poster ) : ?>
			<img src="<?php echo esc_url( $g_ov_poster ); ?>" alt="" loading="lazy" />
			<?php endif; ?>
			<span class="g-cap g-mono"><?php echo esc_html( le_field( 'home_overlap_wordmark', false, __( 'Services', 'lewisedward' ) ) ); ?></span>
		</div>
		<div class="g-cell g-ov__list">
			<?php foreach ( $g_ov_items as $i => $item ) : $oid = $g_id( $item ); ?>
			<a class="g-ov__item" href="<?php echo esc_url( get_permalink( $oid ) ); ?>">
				<span class="g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<div class="g-ov__body"><h3 class="g-ov__title"><?php echo esc_html( get_the_title( $oid ) ); ?></h3><span class="g-ov__desc"><?php echo esc_html( le_field( 'service_short_description', $oid ) ); ?></span></div>
				<?php echo le_grid_arrow_diag(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<?php endforeach; ?>
		</div>
	</section>

	<?php /* ---- About (h2 + stats) ---- */ ?>
	<section class="g-row g-about" aria-label="<?php esc_attr_e( 'About Lewis Edward', 'lewisedward' ); ?>">
		<div class="g-cell g-about__side">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'home_about_eyebrow', false, __( 'About', 'lewisedward' ) ) ); ?> <?php echo esc_html( le_field( 'home_about_year' ) ); ?></span>
			<div class="g-ph g-ph--mono g-about__photo"><?php echo le_grid_img( $g_about_photo, $g_about_photo_alt, '(max-width: 1100px) 100vw, 40vw', array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		</div>
		<div class="g-cell g-about__prose">
			<div class="g-about__text">
			<h2><?php echo esc_html( le_field( 'home_about_headline_1' ) ); ?> <?php echo esc_html( le_field( 'home_about_headline_muted' ) ); ?> <?php echo esc_html( le_field( 'home_about_headline_since' ) ); ?> <?php echo esc_html( le_field( 'home_about_headline_year' ) ); ?></h2>
			<p><?php echo esc_html( le_field( 'home_about_lede' ) ); ?></p>
			<?php $g_more = le_link( 'grid_about_more', '/about/', __( 'More about Lewis', 'lewisedward' ), false ); ?>
			<a class="g-line-link g-mono g-about__more" href="<?php echo esc_url( $g_more['url'] ); ?>"<?php le_link_target_attr( $g_more ); ?>><span><?php echo esc_html( $g_more['title'] ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
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

	<?php get_template_part( 'template-parts/grid/testimonials' ); ?>

	<?php /* ---- Contact (h2) ---- */ ?>
	<?php /* ---- Journal: the three latest articles ---- */ ?>
	<?php $g_pick = le_field( 'grid_journal_posts' ); $g_pick = is_array( $g_pick ) ? array_values( array_filter( array_map( $g_id, $g_pick ) ) ) : array(); $g_posts = $g_pick ? get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'post__in' => $g_pick, 'orderby' => 'post__in', 'numberposts' => 3, 'no_found_rows' => true ) ) : get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 3, 'no_found_rows' => true ) ); if ( $g_posts ) : $g_journal_url = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/journal/' ); ?>
	<section class="g-row g-journal" aria-label="<?php esc_html_e( 'Journal', 'lewisedward' ); ?>">
		<div class="g-cell g-journal__intro">
			<span class="g-label g-mono"><?php echo esc_html( le_field( 'grid_journal_eyebrow', false, __( 'Journal', 'lewisedward' ) ) ); ?></span>
			<h2><?php echo esc_html( le_field( 'grid_journal_title', false, __( 'Notes from the studio', 'lewisedward' ) ) ); ?></h2>
			<a class="g-line-link g-mono" href="<?php echo esc_url( $g_journal_url ); ?>"><span><?php echo esc_html( le_field( 'grid_journal_all', false, __( 'All articles', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
		</div>
		<?php foreach ( $g_posts as $g_post ) : ?>
		<a class="g-cell g-journal__item" href="<?php echo esc_url( get_permalink( $g_post ) ); ?>">
			<span class="g-journal__meta g-mono"><time datetime="<?php echo esc_attr( get_the_date( 'c', $g_post ) ); ?>"><?php echo esc_html( get_the_date( 'M Y', $g_post ) ); ?></time><?php $g_cat = get_the_category( $g_post->ID ); if ( $g_cat ) : ?><span><?php echo esc_html( $g_cat[0]->name ); ?></span><?php endif; ?></span>
			<span class="g-journal__title"><?php echo esc_html( get_the_title( $g_post ) ); ?></span>
			<span class="g-journal__foot g-mono"><span><?php echo esc_html( le_field( 'grid_journal_read', false, __( 'Read', 'lewisedward' ) ) ); ?></span><?php echo le_grid_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</a>
		<?php endforeach; ?>
	</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
