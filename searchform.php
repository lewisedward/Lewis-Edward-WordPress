<?php
/**
 * Search form.
 *
 * @package LewisEdward
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="search-form__label">
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'lewisedward' ); ?></span>
		<input type="search" class="search-form__input" placeholder="<?php esc_attr_e( 'Search…', 'lewisedward' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	</label>
	<button type="submit" class="search-form__submit btn btn--primary">
		<?php esc_html_e( 'Search', 'lewisedward' ); ?>
	</button>
</form>
