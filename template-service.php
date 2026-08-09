<?php 
/* 
Template Name: Service Page Template
*/
get_header(); 
?>

	<div class="services-page">
		<div class="main-services">
			<?php foreach( get_field('header_section')as $service ){ ?>
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
		<div class="descption">
			<div class="container">
				<?php echo get_field('description'); ?>
				<?php if( !empty( get_field('say_hello_section')['on_hover_image'] ) ){ ?>
				<div class="serviceHoverImage customHelloHvr">
					<img src="<?php echo get_field('say_hello_section')['on_hover_image']; ?>">
				</div>
				<?php } ?>
				<?php if( !empty( get_field('request_quote_section')['on_hover_image'] ) ){ ?>
				<div class="serviceHoverImage customQuoteHvr">
					<img src="<?php echo get_field('request_quote_section')['on_hover_image']; ?>">
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="gray-box">
			<div class="container">
				<div class="services-list">
					<?php $lcount =1; foreach( get_field('services_section')['services'] as $service ){ ?>
						<div class="item animateblock" data-animate-class="fadeIn"> <?php //$lcount ?>
							<?php if( !empty($service['service_icon']) ){ ?>
								<div class="srv-icn">
									<?php echo $service['service_icon']; ?>
								</div>
							<?php } ?>
							<p><?php echo $service['service_name']; ?></p>
						</div>
					<?php $lcount++; } ?>
				</div>
				<div class="my-exp">
					<div class="info animateblock" data-animate-class="fadeInUpSmall">
						<?php echo get_field('services_section')['left_section']; ?>
					</div>
					<div class="list">
						<?php foreach( get_field('services_section')['right_section'] as $commnt ){ ?>
							<div class="item">
								<p>
									“<?php echo $commnt['author_description']; ?>”
									
								</p>
								<h5> - <?php echo $commnt['author_name']; ?></h5>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<div class="testimonial-slider">
			<?php echo do_shortcode(get_field('testimonial_section_shortcode')); ?>
		</div>
		<div class="projects">
			<div class="container">
				<?php echo do_shortcode(get_field('project_section')['projects_shortcode']); ?>
				<a class="btn" href="<?php echo get_field('project_section')['project_link']['url']; ?>"><?php echo get_field('project_section')['project_link']['title']; ?></a>
			</div>
		</div>
	</div>
<?php get_footer(); ?>
