<?php 
/* Template Name: Newsletter Page Template */
get_header(); 
global $post;
$imageN = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
?>

	<div class="newsletter-page">
		<div class="image-box">
			<img src="<?php echo $imageN[0]; ?>">
		</div>
		<div class="content-box">
			<h2 class="animatebefore animateblock">Get exclusive news & discounts</h2>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<?php the_content(); ?>
			<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>


<?php get_footer(); ?>
