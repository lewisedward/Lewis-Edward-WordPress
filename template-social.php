<?php 
/* Template Name: Social Media Page Template */
get_header(); 
global $post;
$imageH = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
?>

	<div class="social-page copyright-page">
		<div class="campaigns-list" style="background-image:url(<?php echo $imageH[0]; ?>)">
			<div class="container">
				<div class="heading">
					<h5><?php echo get_the_title($post->ID); ?></h5>
					<?php echo get_field('header_section')['title']; ?>
				</div>
				<div class="grid">
					<?php foreach( get_field('header_section')['social_media_section'] as $service ){ ?>
						<div class="item">
							<?php if( !empty( $service['image'] ) ){ ?>
								<img src="<?php echo $service['image']; ?>">
							<?php } ?>
							<div class="info">
								<h2><?php echo $service['name']; ?></h2>
								<p><?php echo $service['description']; ?></p>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>		
		<?php foreach( get_field('media_section') as $media ){ ?>
			<div class="copyright-grid auto">
				<div class="img-box">
					<img src="<?php echo $media['image']; ?>">
				</div>
				<div class="content-box">
					<div class="animateblock" data-animate-class="fadeInUpSmall">
						<h2><?php echo $media['title']; ?></h2>
						<div class="descptn">
							<?php echo $media['description']; ?>
						</div>
						<a class="btn" href="<?php echo $media['link']['url']; ?>"><?php echo $media['link']['title']; ?></a>
					</div>
				</div>
			</div>
		<?php } ?>		
		<div class="brand-logos">
			<div id="brndLogoOwl" class="owl-carousel owl-theme">
				<?php foreach( get_field('brand_section') as $logo ){ ?>
					<div class="item">
						<img src="<?php echo $logo['brand_logo']; ?>">
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php get_footer(); ?>
