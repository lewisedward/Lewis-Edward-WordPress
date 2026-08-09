<?php get_header(); ?>

	<?php if (have_posts()): while (have_posts()) : the_post(); ?>
	<?php
		$allCat = get_the_terms($post->ID, 'faq_category' );
	?>
	<div class="single-faq blog-post">
		<div class="container">
			<div class="meta-category">
				<?php
				foreach( $allCat as $singleCat ){
					echo '<a href="'.get_category_link($singleCat->term_taxonomy_id).'">'. $singleCat->name .'</a>';
				}
				?>
			</div>
		
		
			<div class="blog-post-head">
				<h1><?php the_title(); ?></h1>
			</div>
			<div class="blog-post-content">
				<div class="content">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
		
			<?php 
				$previous = get_previous_post(false,'','faq_category');
				$next = get_next_post(false,'','faq_category');
			?>
			
		<?php if( !$previous || !$next){ ?>
			<div class="next-prev content-align-center">
		<?php }else{?>
		 	<div class="next-prev">
		<?php } ?>
			
			<?php if( get_previous_post(false,'','faq_category') ){ 
			$prvsIm = wp_get_attachment_image_src( get_post_thumbnail_id($previous->ID), 'full' );
			if( !empty( $prvsIm ) ){
				$imageP = '<img src="'.$prvsIm[0].'">';
			}
			?>
			<a href="<?php echo get_the_permalink($previous->ID); ?>">
				<div class="button prev">
					<?php echo $imageP; ?>
					<div class="info">
						<p>Previous Post</p>
						<h3><?php echo get_the_title($previous->ID); ?></h3>
						<i class="arrow-icons"></i>
					</div>
				</div>
			</a>
			<?php } ?>
			<?php if( get_next_post(false,'','faq_category') ){ 
			$nxtIm = wp_get_attachment_image_src( get_post_thumbnail_id($next->ID), 'full' );
			if( !empty( $nxtIm ) ){
				$imageN = '<img src="'.$nxtIm[0].'">';
			}
			?>
			<a href="<?php echo get_the_permalink($next->ID); ?>">
				<div class="button next">
					<div class="info">
						<?php echo $imageN; ?>
						<p>Next Post</p>
						<h3><?php echo get_the_title($next->ID); ?></h3>
						<i class="arrow-icons"></i>
					</div>
				</div>
			</a>
			<?php } ?>
		</div>
	</div>
	<?php endwhile; ?>
	<?php endif; ?>


<?php get_footer(); ?>
