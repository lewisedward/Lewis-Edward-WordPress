<?php get_header(); ?>
		<!-- article -->
	
			<div id="post-404" class="not-found">			
				<img src="<?php echo site_url(); ?>/wp-content/uploads/2020/04/404.jpg">
				<div class="content">
					<span>404</span>
					<h1><?php _e( 'Page not found', 'ldavis' ); ?></h1>				
					<a class="btn" href="<?php echo home_url(); ?>"><?php _e( 'Back to home', 'ldavis' ); ?></a>
				</div>
			</div>
			<!-- /article -->


<?php get_footer(); ?>
