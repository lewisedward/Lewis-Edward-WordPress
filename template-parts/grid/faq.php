<?php
/**
 * Grid skin — FAQ page: intro + accordion; FAQPage JSON-LD stays in the
 * classic template file (printed before this part).
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faq_items = le_field( 'faq_items' );
$faq_items = is_array( $faq_items ) ? $faq_items : array();
?>
<main id="main" class="site-main site-main--faq g-main">

	<section class="g-row g-sec" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'lewisedward' ); ?>">
		<?php le_grid_intro( array( 'tag' => 'h1', 'label' => le_field( 'faq_eyebrow' ), 'count' => str_pad( (string) count( $faq_items ), 2, '0', STR_PAD_LEFT ), 'title' => esc_html( le_field( 'faq_h1' ) ), 'link' => array( 'url' => home_url( '/contact' ), 'title' => __( 'Still have a question?', 'lewisedward' ) ), 'class' => 'g-sec__sticky g-sec__intro--h1' ) ); ?>
		<div class="g-cell g-dark g-sec__body">
			<?php foreach ( $faq_items as $i => $row ) : $q = trim( (string) ( $row['question'] ?? '' ) ); if ( '' === $q ) { continue; } ?>
			<details class="g-acc"<?php echo 0 === $i ? ' open' : ''; ?>>
				<summary><span class="g-mono"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3 class="g-acc__q"><?php echo esc_html( $q ); ?></h3><span class="g-acc__x" aria-hidden="true"></span></summary>
				<?php if ( ! empty( $row['answer'] ) ) : ?><div class="g-acc__a"><?php echo wp_kses_post( $row['answer'] ); ?></div><?php endif; ?>
			</details>
			<?php endforeach; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/grid/cta' ); ?>

</main>
