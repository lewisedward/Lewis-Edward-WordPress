<?php get_header(); ?>
	<?php if (have_posts()): while (have_posts()) : the_post(); ?>
	<?php
		$allCat = get_the_category();
		$catName = [];
		$catId = [];
		foreach( $allCat as $key=>$cat ){
			$catName[$key] = $cat->name;
			$catId[$key] = $cat->cat_ID;
		}
	?>
	<div class="blog-post">
		<div class="container">
			<div class="meta-category">
				<?php
				
				foreach( $allCat as $singleCat ){
					echo '<a href="'.get_category_link($singleCat->cat_ID).'">'.get_cat_name( $singleCat->cat_ID ).'</a>';
				}
				?>
			</div>
			<div class="blog-post-head">
				<h1><?php the_title(); ?></h1>
				<div class="meta"> 
					<span class="meta-date"><?php the_date(); ?></span> 
				</div>
			</div>
			<?php 
			if( ( count( $catName ) == 1 ) && ( $catName[0] == 'Quotes' ) ){
			?>
				<div class="blog-post-quote">
					<span class="quote"></span>
					<?php the_content(); ?>
				</div>
			<?php
			}
			else{
			?>
			<div class="blog-post-content">
				<?php
					$youtube_video_code = get_field('youtube_video_code',$post->ID);
					if( !empty( $youtube_video_code ) ){
				?>
					<div class="video">
						<iframe src="https://www.youtube.com/embed/<?php echo $youtube_video_code; ?>"></iframe>
					</div>
				<?php
					}else{
						$feauredIm = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
				?>
					<div class="faturd-img">
						<img src="<?php echo $feauredIm[0]; ?>">
					</div>
				<?php
					}
				?>
				<div class="content">
					<?php the_content(); ?>
				</div>
			</div>
			<?php 
			}
			?>
		
		</div>
		<?php 
			$previous = get_previous_post(false, '5');
			$next = get_next_post(false, '5');
		?>
		<?php if( !$previous || !$next){ ?>
			<div class="next-prev content-align-center">
		<?php }else{?>
		 	<div class="next-prev">
		<?php } ?>
		
			<?php if( get_next_post() ){ 
			$nxtIm = wp_get_attachment_image_src( get_post_thumbnail_id($next->ID), 'full' );
			if( !empty( $nxtIm ) ){
				$imageN = '<img src="'.$nxtIm[0].'">';
			}
			?>
			<a href="<?php echo get_the_permalink($next->ID); ?>">
				<div class="button prev">
					<?php echo $imageN; ?>
					<div class="info">
						<p>Previous Post</p>
						<h3><?php echo get_the_title($next->ID); ?></h3>
						<i class="arrow-icons"></i>
					</div>
				</div>
			</a>
			<?php } ?>
			<?php if( get_previous_post() ){ 
			$prvsIm = wp_get_attachment_image_src( get_post_thumbnail_id($previous->ID), 'full' );
			if( !empty( $prvsIm ) ){
				$imageP = '<img src="'.$prvsIm[0].'">';
			}
			?>
			<a href="<?php echo get_the_permalink($previous->ID); ?>">
				<div class="button next">
					<?php echo $imageP; ?>
					<div class="info">
						<p>Next Post</p>
						<h3><?php echo get_the_title($previous->ID); ?></h3>
						<i class="arrow-icons"></i>
					</div>
				</div>
			</a>
			<?php } ?>
			
		</div>
<!-- 		<div class="comment-box" id="commentBox">
			<div class="container"> -->
				<?php //comments_template(); ?>
<!-- 			</div>
		</div> -->
	</div>
	<?php endwhile; ?>
	<?php endif; ?>

<?php get_footer(); ?>

