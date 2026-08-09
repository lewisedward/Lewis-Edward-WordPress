<?php get_header(); ?>

	<div class="blog-page">
		<div class="hdr-sectn">
			<span><?php _e( 'Category', 'ldavis' );  ?></span>
			<h1><?php single_cat_title(); ?></h1>
		</div>
		<?php
			$category = get_queried_object();
			#print_r($category); die;
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$blogs = new WP_Query(
				array(
					'post_type' => 'faq',
					'tax_query' => array(
						array(
							'taxonomy' => 'faq_category',
							'field' => 'slug',
							'terms' => $category->slug,
						),
					),
					'posts_per_page' => 25,
					'paged' => $paged
				)
			);
			
		if ( $blogs->have_posts() ):
			echo '<div class="blog-grid">';
			while ( $blogs->have_posts() ) : $blogs->the_post();
		?>
				<?php
				$getDate = get_the_date();
				echo '<div class="grid-item">';
				?>
				<div class="blog-item">
					<?php
						$feauredIm = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
						if( !empty( $feauredIm[0] ) ){
					?>
						<div class="img-sectn">
							<img src="<?php echo $feauredIm[0]; ?>">
						</div>
						<?php } ?>
					<div class="text-box">
						<?php
							$allCat = get_the_terms($post->ID, 'faq_category' );
							foreach( $allCat as $singleCat ){
								echo '<a href="'.get_category_link($singleCat->term_taxonomy_id).'">'.$singleCat->name.'</a>';
							}
						?>
						<a href="<?php echo get_permalink(); ?>">
							<h3><?php the_title(); ?></h3>
							<p><?php the_excerpt(); ?></p>
						</a>
						<div class="auther-desc">
							<?php $author_id=$post->post_author; ?>
							<?php echo get_avatar( $author_id ); ?>
							<a href="<?php echo get_author_posts_url($author_id); ?>"><?php the_author_meta( 'user_nicename' , $author_id ); ?> </a>
							<span><?php echo $getDate; ?></span>
						</div>
					</div>
				</div>
				<?php
				
				echo '</div>';
				?>
			<?php endwhile; ?>
			</div>
			<nav class="pagination">
				<?php pagination_bar( $blogs ); ?>
			</nav>
		<?php 
		wp_reset_postdata();
		endif; 
		?>
	</div>


<?php get_footer(); ?>
