<?php get_header(); ?>

	<div class="projct-arch services-page">
		<div class="projects">
			<div class="container">
				<h2>Copywriting</h2>
				<div class="projects-grid">				
					<?php if (have_posts()): while (have_posts()) : the_post(); ?>					
					<div class="item">
						<a href="<?php echo get_the_permalink(); ?>">
							<?php $projectImage = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' ); ?>
							<div class="img-box">
								<img src="<?php echo $projectImage[0]; ?>">	
							</div>
							<h3><?php echo get_the_title(); ?></h3>
						</a>
					</div>
									
					<?php endwhile; ?>
					<?php 
					wp_reset_postdata();
					endif; 
					?>
				</div>
			</div>
		</div>
	</div>
	

<?php get_footer(); ?>

