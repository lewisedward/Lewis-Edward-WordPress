<?php
/**
 * Site footer — closes the document and renders the footer part.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- .site-content -->

	<?php get_template_part( 'template-parts/footer/footer' ); ?>

</div><!-- .site-wrapper -->

<?php wp_footer(); ?>
</body>
</html>
