<?php 
/* 
Template Name: Single Service Page Template
*/
get_header(); 
?>

	<div class="single-service copyright-page">
		<?php
		$feauredIm = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		?>
		<div class="campaigns-list" data-image="<?php echo $feauredIm[0]; ?>">	
			<div class="container">
				<div class="heading">
					<h5 class="animatebefore animateblock"><?php echo get_field('header_section')['specification']; ?></h5>
					<h2><span class="animatebefore animateblock"><?php echo get_field('header_section')['heading']; ?></span></h2>
				</div>
				<div class="grid">
					<?php foreach( get_field('header_section')['services'] as $service ){ ?>
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
		<?php if( !empty( get_field('specification_section') ) ){ ?>
			<?php foreach( get_field('specification_section') as $specification ){ ?>
				<div id="<?php echo $specification['set_id']; ?>" class="copyright-grid auto">
					<div class="img-box" style="background-image:url('<?php echo $specification['image']; ?>')">
						<!--img src="<?php echo $specification['image']; ?>"-->
					</div>
					<div class="content-box">
						<div class="animateblock" data-animate-class="fadeInUpSmall">
							<h3 class="animatebefore animateblock"><?php echo $specification['title']; ?></h3>
							<div class="descptn">
								<?php echo $specification['description']; ?>
							</div>
							<?php if( !empty( $specification['button_link']['url'] ) ){ ?>
							<a class="btn" href="<?php echo $specification['button_link']['url']; ?>"><?php echo $specification['button_link']['title']; ?></a>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php } ?>
		<?php } ?>


		<?php if( !empty( get_field('play_and_plug_section')['images'] ) && !empty( get_field('play_and_plug_section')['description'] ) ){ ?>
			<div class="play-plug-section">
				<div class="inner-left-section">
					<div class="images-section">
						<?php foreach( get_field('play_and_plug_section')['images'] as $singleImage ){ ?>
							<img src="<?php echo $singleImage['image']; ?>"/>
						<?php } ?>
					</div>
				</div>
				<div class="inner-right-section">
					<h2><?php echo get_field('play_and_plug_section')['description']; ?></h2>
				</div>
			</div>
		<?php } ?>
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
		<?php if( !empty( get_field('good_fit_section')['images'] ) && !empty( get_field('good_fit_section')['heading'] ) && !empty( get_field('good_fit_section')['description'] ) ){ ?>
		<div class="good-fit-section">
			<div class="inner-content-section">
				<h2><?php echo get_field('good_fit_section')['heading']; ?></h2>
				<?php echo get_field('good_fit_section')['description']; ?>
			</div>
			<div class="images-section">
				<?php foreach( get_field('good_fit_section')['images'] as $singleImage ){ ?>
					<img src="<?php echo $singleImage['image']; ?>"/>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
		<div class="brand-logos">
			<?php if( !empty( get_field('logo_section') ) ){ ?>
			<?php if( count(get_field('logo_section')) > 6 ){ ?>
				<div id="brndLogoOwl" class="owl-carousel owl-theme">	
				<?php }else{ ?>
					<div id="brndLogoNoOwl" class="owl-carousel owl-theme">
				<?php }	?>
				
					<?php foreach( get_field('logo_section') as $logo ){ ?>
						<div class="item">
							<img src="<?php echo $logo['logo']; ?>">
						</div>
					<?php } ?>
					</div>
			<?php } ?>
			<?php if(get_field('footer_section')){ ?>
				<div class="ftr-section">
					<p class="animateblock" data-animate-class="fadeInUpSmall"><?php echo get_field('footer_section'); ?></p>
				</div>
			<?php } ?>
		</div>
		
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
