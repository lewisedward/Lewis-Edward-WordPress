<?php
/**
 * 404 — Not Found.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="main" class="site-main site-main--404">
	<div class="container error-404">
		<p class="error-404__code">404</p>
		<h1 class="error-404__title"><?php esc_html_e( 'Page not found', 'lewisedward' ); ?></h1>
		<p class="error-404__text">
			<?php esc_html_e( "The page you're after has moved or never existed.", 'lewisedward' ); ?>
		</p>
		<p class="error-404__actions">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to home', 'lewisedward' ); ?>
			</a>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/work' ) ); ?>">
				<?php esc_html_e( 'View our work', 'lewisedward' ); ?>
			</a>
		</p>
	</div>
</main>

<?php
get_footer();
