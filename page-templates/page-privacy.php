<?php
/**
 * Template Name: Privacy Policy
 *
 * Fully ACF-driven (field group "Privacy Policy Page"). Same two-column layout
 * as the FAQ page — a sticky left heading beside the content — but on the dark
 * surface, with the policy body coming from a single WYSIWYG field. Then the
 * shared Contact CTA.
 *
 * SEO: the page heading is the single <h1>; body headings are <h2>/<h3>.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$pp_body    = le_field( 'privacy_body' );
$pp_updated = le_field( 'privacy_updated' );
?>

<main id="main" class="site-main site-main--privacy">

	<section class="section section--legal" aria-label="<?php esc_attr_e( 'Privacy Policy', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="legal-grid">

				<div class="legal-intro" data-reveal>
					<span class="eyebrow legal-intro__eyebrow"><?php echo esc_html( le_field( 'privacy_eyebrow' ) ); ?></span>
					<h1 class="legal-intro__title"><?php echo esc_html( le_field( 'privacy_h1' ) ); ?></h1>
					<?php if ( $pp_updated ) : ?><p class="legal-intro__updated">Last Updated: <strong><?php echo esc_html( $pp_updated ); ?></strong></p><?php endif; ?>
				</div>

				<?php if ( $pp_body ) : ?>
					<div class="legal-body" data-reveal><?php echo wp_kses_post( $pp_body ); ?></div>
				<?php endif; ?>

			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

</main>

<?php
get_footer();
