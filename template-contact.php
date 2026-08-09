<?php 
/* Template Name: Contact Page Template */
get_header(); 
global $post;
$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
$imageUrl = $src[0];
?>

	<div class="contact-page" data-image="<?php echo $imageUrl; ?>">
		<div class="container">
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
				<div class="contct-frm">
					<?php the_content(); ?>
				</div>
				<div class="footer-section">
					<div class="logo-section">
						<?php foreach( get_field('logos') as $singleLogo ){ ?>
							<?php if( !empty( $singleLogo['single_logo'] ) ){ ?>
							<a href="<?php if( !empty( $singleLogo['link'] ) ){ echo $singleLogo['link']['url']; }else{ echo 'javascript:void(0)'; } ?>"><img src="<?php echo $singleLogo['single_logo']; ?>"></a>
							<?php } ?>
						<?php } ?>
					</div>
					<h2><?php echo get_field('title'); ?></h2>
					<p><?php echo get_field('description'); ?></p>
				</div>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>

<?php get_footer(); ?>

