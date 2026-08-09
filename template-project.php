<?php 
/* Template Name: Project Page Template */
get_header(); 
?>
	<?php /* ?>
	<div class="project-page">
	<?php 
	$projects = new WP_Query(
        array(
            'post_type' => 'projects',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        )
    );
	if($projects->have_posts() ) : while ( $projects->have_posts() ) : $projects->the_post();
	?>
		
		<div class="item animateblock" data-animate-class="fadeIn">
			<a href="<?php the_permalink(); ?>">
				<div class="img-box">
					<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); ?>
					<img src="<?php echo $image[0]; ?>">
				</div>
				<div class="title">
					<h2><?php the_title(); ?></h2>
				</div>
			</a>
		</div>
		
	
	<?php endwhile; else: ?>
	<?php endif; wp_reset_postdata(); ?>
	</div>
	<?php */ ?>
	<div class="project-page" id="project_page">
		<?php 
			$getProjects = get_field('project_sections');
			foreach( $getProjects as $singleProject ){ ?>
					<?php if( !empty( $singleProject['title'] ) ){ ?>
						<h2 class="project-title"><?php echo $singleProject['title']; ?></h2>
					<?php } ?>
					<?php foreach( $singleProject['select_project'] as $singleProjectSelection ){ ?>
					<div class="item animateblock" data-animate-class="fadeIn">
						<a href="<?php if( !empty( get_field('enable_link',$singleProjectSelection) ) ){ echo get_the_permalink($singleProjectSelection); }else{ echo 'javascript:void(0)'; } ?>">
							<div class="img-box">
								<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $singleProjectSelection ), 'full' ); ?>
								<img src="<?php echo $image[0]; ?>">
							</div>
							<div class="title">
								<h2><?php echo get_the_title($singleProjectSelection); ?></h2>
							</div>
						</a>
					</div>
					<?php } ?>
				
	<?php   }
		?>
	</div>

<?php get_footer(); ?>


