<?php 
/* Template Name: Demo Page Template */
get_header(); 
?>
<div class="request-demo">
	
		<div class="sub-header">
			<h1><?php the_title(); ?></h1>
		</div>
		<div class="container">

			<?php if (have_posts()): while (have_posts()) : the_post(); ?>

					<?php the_content(); ?>

			<?php endwhile; ?>

			<?php else: ?>


			<?php endif; ?>
		</div>

	</div>

<?php get_footer(); ?>
