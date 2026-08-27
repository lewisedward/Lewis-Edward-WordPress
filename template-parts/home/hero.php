<?php
/**
 * Home section: Hero (bento grid).
 *
 * Faithful port of the React Hero. Four glass tiles: editorial (h1 + intro +
 * decorative halftone dot-sphere), metrics, capabilities list, and the lime
 * CTA. Content is baked to match the live site; ACF wiring comes in a later
 * pass. SEO: this section carries the page's single <h1>; every other tile
 * label is a paragraph, keeping a clean heading outline.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// All content is ACF-driven. Lists are curated via ACF; when empty the
// relevant markup simply doesn't render (no static fallbacks).
$le_capabilities = le_field( 'home_hero_capabilities' ); // service posts
$le_clients      = le_field( 'home_hero_clients' );      // repeater rows (logo image)
$le_team         = le_field( 'home_hero_team' );         // team posts
?>
<section class="hero" aria-label="<?php esc_attr_e( 'Homepage hero', 'lewisedward' ); ?>">

	<div class="hero__glows" aria-hidden="true">
		<span class="hero__glow hero__glow--1"></span>
		<span class="hero__glow hero__glow--2"></span>
	</div>

	<div class="hero__inner">
		<div class="hero__grid">

			<?php /* ---- Editorial tile ---- */ ?>
			<div class="hero-tile hero-tile--editorial glass">
				<div class="hero-sphere" aria-hidden="true" data-hero-sphere>
					<canvas class="hero-sphere__canvas"></canvas>
				</div>

				<p class="eyebrow eyebrow--faint hero-editorial__eyebrow"><?php echo esc_html( le_field( 'home_hero_eyebrow' ) ); ?></p>

				<div class="hero-editorial__body">
					<h1 class="hero-editorial__title">
						<span class="hero-title__line">
							<span class="hero-title__l1"><?php echo esc_html( le_field( 'home_hero_title_1' ) ); ?> <span class="text-primary"><?php echo esc_html( le_field( 'home_hero_title_1_accent' ) ); ?></span></span>
						</span>
						<span class="hero-title__line">
							<span class="hero-title__l2"><?php echo esc_html( le_field( 'home_hero_title_2_pre' ) ); ?> <span class="text-primary hero-title__amp">&amp;</span> <?php echo esc_html( le_field( 'home_hero_title_2_post' ) ); ?></span>
						</span>
					</h1>

					<div class="hero-editorial__lede">
						<?php
						$le_hero_lede = le_field( 'home_hero_lede' );
						echo wp_kses_post( $le_hero_lede );
						?>
					</div>

					<?php
					$le_ed_cta_label = le_field( 'home_hero_editorial_cta_label', false, __( 'Get in touch', 'lewisedward' ) );
					$le_ed_cta_url   = le_field( 'home_hero_editorial_cta_url', false, '/contact' );
					$le_ed_cta_url   = ( 0 === strpos( (string) $le_ed_cta_url, 'http' ) ) ? $le_ed_cta_url : home_url( $le_ed_cta_url );
					?>
					<a class="arrow-link hero-editorial__cta" href="<?php echo esc_url( $le_ed_cta_url ); ?>" aria-label="<?php echo esc_attr( $le_ed_cta_label ); ?>">
						<span class="arrow-link__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="arrow-link__label"><?php echo esc_html( $le_ed_cta_label ); ?></span>
					</a>
				</div>
			</div>

			<?php /* ---- Metrics tile ---- */ ?>
			<div class="hero-tile hero-tile--metrics glass">
				<div class="hero-metrics__head">
					<p class="eyebrow"><?php esc_html_e( 'Metrics', 'lewisedward' ); ?></p>
					<span class="pulse-dot" aria-hidden="true"></span>
				</div>

				<div class="hero-metrics__figure">
					<span class="hero-metrics__num"><?php echo esc_html( le_field( 'home_hero_metric_value' ) ); ?></span>
					<p class="eyebrow hero-metrics__num-label"><?php echo esc_html( le_field( 'home_hero_metric_label' ) ); ?></p>
				</div>

				<div class="hero-metrics__row">
					<div class="avatar-stack">
						<?php
						if ( is_array( $le_clients ) ) :
							foreach ( $le_clients as $c ) :
								$logo = isset( $c['logo'] ) ? $c['logo'] : null;
								if ( ! $logo || empty( $logo['url'] ) ) {
									continue;
								}
								$bg    = isset( $c['bg_color'] ) ? trim( (string) $c['bg_color'] ) : '';
								$style = $bg ? ' style="background-color:' . esc_attr( $bg ) . ';"' : '';
								?>
								<span class="avatar avatar--client"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
									<img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( $logo['alt'] ?? '' ); ?>" class="avatar__logo" loading="lazy" width="20" height="20" />
								</span>
								<?php
							endforeach;
						endif;
						?>
						<span class="avatar avatar--count"><?php echo esc_html( le_field( 'home_hero_projects_count' ) ); ?></span>
					</div>
					<p class="eyebrow eyebrow--t2"><?php echo esc_html( le_field( 'home_hero_projects_label' ) ); ?></p>
				</div>

				<div class="hero-metrics__row">
					<div class="avatar-stack">
						<span class="avatar avatar--zap" aria-hidden="true">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9.5"/><path d="M2.5 12h19M12 2.5c2.6 2.6 4 6 4 9.5s-1.4 6.9-4 9.5c-2.6-2.6-4-6-4-9.5s1.4-6.9 4-9.5z"/></svg>
						</span>
					</div>
					<p class="eyebrow eyebrow--t2"><?php echo esc_html( le_field( 'home_hero_ai_label' ) ); ?></p>
				</div>

				<div class="hero-metrics__row">
					<div class="avatar-stack">
						<?php
						if ( is_array( $le_team ) ) :
							foreach ( $le_team as $member ) :
								$mid = is_object( $member ) ? $member->ID : (int) $member;
								?>
								<span class="avatar avatar--team">
									<?php
									if ( has_post_thumbnail( $mid ) ) {
										echo get_the_post_thumbnail( $mid, 'le_avatar', array( 'alt' => esc_attr( get_the_title( $mid ) ), 'loading' => 'lazy' ) );
									}
									?>
								</span>
								<?php
							endforeach;
						endif;
						?>
					</div>
					<p class="eyebrow eyebrow--t2"><?php echo esc_html( le_field( 'home_hero_team_label' ) ); ?></p>
				</div>
			</div>

			<?php /* ---- Capabilities tile ---- */ ?>
			<div class="hero-tile hero-tile--caps">
				<div class="hero-caps__head">
					<p class="eyebrow"><?php esc_html_e( 'Capabilities', 'lewisedward' ); ?></p>
					<span class="hero-caps__rule" aria-hidden="true"></span>
					<a class="eyebrow hero-caps__all" href="<?php echo esc_url( home_url( '/services' ) ); ?>"><?php esc_html_e( 'All services', 'lewisedward' ); ?></a>
				</div>

				<div class="hero-caps__list">
					<?php if ( is_array( $le_capabilities ) ) : foreach ( $le_capabilities as $i => $cap ) : $cid = is_object( $cap ) ? $cap->ID : (int) $cap; ?>
						<a class="cap<?php echo 0 === $i ? '' : ' cap--divided'; ?>" href="<?php echo esc_url( get_permalink( $cid ) ); ?>">
							<span class="cap__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span class="cap__main">
								<span class="cap__name"><?php echo esc_html( get_the_title( $cid ) ); ?></span>
								<span class="eyebrow eyebrow--t2 cap__tagline"><?php echo esc_html( le_field( 'service_subtitle', $cid ) ); ?></span>
							</span>
							<span class="cap__arrow" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</a>
					<?php endforeach; endif; ?>
				</div>
			</div>

			<?php /* ---- CTA tile ---- */ ?>
			<?php
			$le_hero_cta_url      = le_field( 'home_hero_cta_url' );
			$le_hero_cta_url      = ( 0 === strpos( (string) $le_hero_cta_url, 'http' ) ) ? $le_hero_cta_url : home_url( $le_hero_cta_url );
			$le_hero_cta_eyebrow  = le_field( 'home_hero_cta_eyebrow' );
			$le_hero_cta_headline = le_field( 'home_hero_cta_headline' );
			?>
			<div class="hero-tile hero-tile--cta" data-surface="light">
				<a class="hero-cta__link" href="<?php echo esc_url( $le_hero_cta_url ); ?>" aria-label="<?php echo esc_attr( $le_hero_cta_headline . ' — ' . $le_hero_cta_eyebrow ); ?>">
					<span class="hero-cta__wash" aria-hidden="true"></span>
					<span class="hero-cta__inner">
						<span class="hero-cta__top">
							<span class="hero-cta__badge" aria-hidden="true"><?php echo le_arrow_diagonal_svg( 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="eyebrow eyebrow--strong"><?php echo esc_html( $le_hero_cta_eyebrow ); ?></span>
						</span>
						<span class="hero-cta__bottom">
							<span class="hero-cta__headline"><?php echo esc_html( $le_hero_cta_headline ); ?></span>
							<span class="hero-cta__email" data-obfuscated-email data-user="hello" data-domain="lewisedward" data-tld="com">hello<span aria-hidden="true"> [at] </span>lewisedward.com</span>
						</span>
					</span>
				</a>
			</div>

		</div>
	</div>
</section>
<?php
// Load the hero canvas script only where the hero renders.
wp_enqueue_script( 'le-hero' );
