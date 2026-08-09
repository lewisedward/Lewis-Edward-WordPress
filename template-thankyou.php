<?php 
/* Template Name: Thank You Page Template */
get_header(); 
global $post;
$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
$url = $src[0];
?>

	<div class="thankyou">
		<?php if( !empty( $src ) ){ echo '<img src="'.$url.'" alt"">'; } ?>
		<div class="container">
			<h3><?php the_title(); ?></h3>

			<?php if (have_posts()): while (have_posts()) : the_post(); ?>

					<?php the_content(); ?>

			<?php endwhile; ?>

			<?php else: ?>


			<?php endif; ?>
		</div>

	</div>

<?php get_footer(); ?>
