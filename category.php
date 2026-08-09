<?php get_header(); ?>

	<div class="catgry-pg blog-page">
		<div class="hdr-sectn">
			<span><?php _e( 'Category', 'ldavis' );  ?></span>
			<h1><?php single_cat_title(); ?></h1>
		</div>
		<?php
			$category = get_queried_object();
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$blogs = new WP_Query(
				array(
					'post_type' => 'post',
					'cat' => $category->term_id,
					'posts_per_page' => 25,
					'paged' => $paged
				)
			);
		#print_r($blogs); die;
		if ( $blogs->have_posts() ):
			echo '<div class="blog-grid">';
			while ( $blogs->have_posts() ) : $blogs->the_post();
		?>
				<?php
				$getDate = get_the_date();
				#print_r(get_the_category( ));
				$allCat = get_the_category();
				$catName = [];
				$catId = [];
				foreach( $allCat as $key=>$cat ){
					$catName[$key] = $cat->name;
					$catId[$key] = $cat->cat_ID;
				}
				#print_r($catName);die;
				echo '<div class="grid-item animateblock" data-animate-class="fadeIn">';
				if( ( count( $catName ) == 1 ) && ( $catName[0] == 'Quotes' ) ){
				?>
				<a>
					<div class="quote">
						<div class="quote-inner">
							<span class="icon"></span>
							<?php the_content(); ?>
						</div>
					</div>
				</a>
				<?php
				}else{
				?>
				<div class="blog-item">
					<div class="img-box">
						<?php
							$feauredIm = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
						?>
						<img src="<?php echo $feauredIm[0]; ?>">
					</div>
					<?php if( !empty( get_field('youtube_video_code',$post->ID) ) ){ ?>
						<a href="<?php echo get_permalink(); ?>">
							<div class="video-icon"></div>
						</a>
					<?php } ?>
					<div class="text-box">
						<?php
							foreach( $catId as $singleCat ){
								echo '<a href="'.get_category_link($singleCat).'">'.get_cat_name( $singleCat ).'</a>';
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
				}
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
