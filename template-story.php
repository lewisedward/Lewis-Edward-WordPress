<?php 
/* Template Name: Story Page Template */
get_header(); 
global $post;
$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
$imageUrl = $src[0];
$sliderImage = get_field('image_slider');
?>

	<div class="story-page">
		<div class="image-box">
			<?php if( !empty( $sliderImage ) ){ ?>
			<div class="slideshow-block">
				<ul class="slides">
				<?php foreach( $sliderImage as $singleImage ){ ?>
					<li><img src="<?php echo $singleImage['image']; ?>"></li>
				<?php } ?>
				</ul>
			</div>
			<?php }else{ ?>
				<img src="<?php echo $imageUrl; ?>"></img>
			<?php } ?>
			
		</div>
		<div class="content-box animateblock" data-animate-class="fadeInRightSmall">
			<h1 class="animatebefore animateblock"><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<?php the_content(); ?>
			<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>
	<?php if( !empty( get_field('ambitious_businesses_section')['images'] ) && !empty( get_field('ambitious_businesses_section')['heading'] ) && !empty( get_field('ambitious_businesses_section')['description'] ) ){ ?>
		<div class="ambitious-businesses-section">
			<div class="inner-left-section">
				<div class="images-section">
					<?php foreach( get_field('ambitious_businesses_section')['images'] as $singleImage ){ ?>
						<img src="<?php echo $singleImage['image']; ?>"/>
					<?php } ?>
				</div>
			</div>
			<div class="inner-right-section">
				<h2><?php echo get_field('ambitious_businesses_section')['heading']; ?></h2>
				<p><?php echo get_field('ambitious_businesses_section')['description']; ?></p>
			</div>
		</div>	
	<?php } ?>
	<?php if( !empty( get_field('service_section') ) ){ ?>
		<div class="services-page service-footer" <?php if( !empty( get_field('service_background_color') ) ){ echo 'style=background-color:'.get_field("service_background_color").''; } ?>>
			<div class="serv-footer-container">
				<div class="main-services">
					<?php foreach( get_field('service_section')as $service ){ ?>
					<div class="item">
						<a href="<?php echo $service['link_button']['url']; ?>">
							<div class="image-box">
								<img src="<?php echo $service['image']['sizes']['large']; ?>" alt="">
							</div>
							<div class="content">
								<h4><?php echo $service['first_heading']; ?></h4>
								<h3><?php echo $service['second_heading']; ?></h3>
								<span class="link"><?php echo $service['link_button']['title']; ?><span class="icon"></span></span>
							</div>
						</a>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>


<?php get_footer(); ?>
