<?php
/**
 * Template Name: FAQ
 *
 * The FAQ page — fully ACF-driven (field group "FAQ Page"). A sticky heading
 * beside an accordion built from the `faq_items` Repeater (question + answer).
 * Then the shared Contact CTA. Layout matches the live site's FAQ page.
 *
 * SEO: the page heading is the single <h1>; each question is an <h3>. FAQ
 * structured data is emitted for rich results.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$faq_items = le_field( 'faq_items' );
$faq_items = is_array( $faq_items ) ? $faq_items : array();

// FAQPage JSON-LD for rich results.
if ( ! empty( $faq_items ) ) {
	$faq_ld = array();
	foreach ( $faq_items as $row ) {
		$q = isset( $row['question'] ) ? trim( wp_strip_all_tags( $row['question'] ) ) : '';
		$a = isset( $row['answer'] ) ? trim( wp_strip_all_tags( $row['answer'] ) ) : '';
		if ( '' === $q ) { continue; }
		$faq_ld[] = array(
			'@type'          => 'Question',
			'name'           => $q,
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ),
		);
	}
	if ( ! empty( $faq_ld ) ) {
		echo '<script type="application/ld+json">' . wp_json_encode( array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $faq_ld,
		) ) . '</script>';
	}
}
?>

<main id="main" class="site-main site-main--faq">

	<section class="section section--faq" data-surface="light" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'lewisedward' ); ?>">
		<div class="section__inner">
			<div class="faq-grid">

				<div class="faq-intro" data-reveal>
					<span class="eyebrow faq-intro__eyebrow"><?php echo esc_html( le_field( 'faq_eyebrow' ) ); ?></span>
					<h1 class="faq-intro__title"><?php echo esc_html( le_field( 'faq_h1' ) ); ?></h1>
				</div>

				<?php if ( ! empty( $faq_items ) ) : ?>
					<div class="faq-list" data-reveal>
						<?php
						foreach ( $faq_items as $row ) :
							$q = isset( $row['question'] ) ? trim( $row['question'] ) : '';
							$a = isset( $row['answer'] ) ? $row['answer'] : '';
							if ( '' === $q ) { continue; }
							?>
							<details class="faq-item">
								<summary class="faq-q">
									<h3 class="faq-q__text"><?php echo esc_html( $q ); ?></h3>
									<span class="faq-q__chevron" aria-hidden="true">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
									</span>
								</summary>
								<?php if ( $a ) : ?>
									<div class="faq-a"><?php echo wp_kses_post( $a ); ?></div>
								<?php endif; ?>
							</details>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/home/contact-cta' ); ?>

</main>

<?php
get_footer();
