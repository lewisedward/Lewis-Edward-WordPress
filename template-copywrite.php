<?php 
/* Template Name: Copywrite Page Template */
get_header(); 
global $post;
$imageN = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
?>

	<div class="copyright-page">
		<div class="sub-header" style="background-image:url(<?php echo $imageN[0]; ?>)">
			<div class="container">			
				<h1><?php the_title(); ?></h1>
				<?php if (have_posts()): while (have_posts()) : the_post(); ?>
				<?php the_content(); ?>
				<?php endwhile; ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="copyright-grid left">
			<div class="img-box">
				<img src="<?php echo get_field('marketing_section')['image']; ?>">
			</div>
			<div class="content-box">
				<div class="animateblock" data-animate-class="fadeInUpSmall">
					<h2><?php echo get_field('marketing_section')['title']; ?></h2>
					<div class="descptn">
						<?php echo get_field('marketing_section')['description']; ?>
					</div>
					<a class="btn" href="<?php echo get_field('marketing_section')['link_button']['url']; ?>"><?php echo get_field('marketing_section')['link_button']['title']; ?></a>
				</div>
			</div>
		</div>
		<div class="campaigns-list" style="background-image:url(<?php echo get_field('marketing_services')['image']; ?>)">
			<div class="container">
				<div class="heading">
					<h5><?php echo get_field('marketing_services')['heading']; ?></h5>
					<?php echo get_field('marketing_services')['description']; ?>
				</div>
				<div class="grid">
					<?php foreach( get_field('marketing_services')['services'] as $service ){ ?>
						<div class="item">
							<?php if( !empty( $service['image'] ) ){ ?>
								<img src="<?php echo $service['image']; ?>">
							<?php } ?>
							<div class="info">
								<h2><?php echo $service['title']; ?></h2>
								<p><?php echo $service['description']; ?></p>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="copyright-grid right">
			<div class="img-box">
				<img src="<?php echo get_field('storytelling_section')['image']; ?>">
			</div>
			<div class="content-box">
				<div class="animateblock" data-animate-class="fadeInUpSmall">
					<h2><?php echo get_field('storytelling_section')['title']; ?></h2>
					<div class="descptn">
						<?php echo get_field('storytelling_section')['description']; ?>
					</div>
					<a class="btn" href="<?php echo get_field('storytelling_section')['link_button']['url']; ?>"><?php echo get_field('storytelling_section')['link_button']['title']; ?></a>					
				</div>
			</div>
		</div>
	</div>
	
<?php get_footer(); ?>
