<?php get_header(); ?>
		<?php if( !empty( get_field('header_section_slider') ) ){ ?>
			<div class="slide-area">
				<div class="sliderOwl owl-carousel owl-theme">
					<?php $owlDot = ''; ?>
					<?php foreach( get_field('header_section_slider') as $key => $slider ){ ?>
						<div class="item cstmInfo" data-image="<?php echo $slider['image']; ?>">
							<div class="loadbar"></div>
							<div class="info">
								<a href="<?php echo $slider['image_link']; ?>"><h2><?php echo $slider['title']; ?></h2></a>
								<p class="dscrptn"><?php echo $slider['description']; ?></p>
								<?php if( !empty( $slider['on_hover_image'] ) ){ ?>
								<div class="hoverImage customHvr<?php echo $key; ?>">
									<img src="<?php echo $slider['on_hover_image']; ?>">
								</div>
								<?php } ?>
								<input type="hidden" class="hiddenElement" value="<?php echo $slider['image_link']; ?>"> 
							</div>
							<a href="#<?php echo $slider['service_id']; ?>" class="scroll-btn">Start Scrolling <span></span></a>
						</div>
						<?php /* $owlDot .= "<li class='owl-dot'>".++$key." <span></span> ".$slider['service_name']."</li>";*/ ?>
						<?php $owlDot .= "<li class='owl-dot'><a>".$slider['service_name']."</a></li>"; ?>
					<?php } ?>
				</div>
				<ul id='carousel-custom-dots' class='owl-dots'> 
					<?php echo $owlDot; ?>
				</ul>
			</div>
		<?php } ?>
		<?php
			if( !empty(get_field('project_section_shortcode')) ){
				$shrtcd = get_field('project_section_shortcode');
				echo do_shortcode($shrtcd); 
			}
		?>
		
		<?php if( !empty(get_field('service_section')) ){ ?>
		<div class="home-services">
			<?php foreach( get_field('service_section') as $key => $singleSer ){ ?>
				<div id="<?php echo $singleSer['service_id']; ?>" class="item <?php if( ($key % 2) == 0 ){echo 'lftSec';}else{echo 'rghtSec';} ?>" data-image="<?php echo $singleSer['service_background_image']; ?>">
					<div class="container">
						<div class="content animateblock" data-animate-class="fadeInLeftSmall">
							<h3 class="animateblock animatebefore"><?php echo $singleSer['title']; ?></h3>
							<p><?php echo $singleSer['description']; ?></p>
							<a class="btn" href="<?php echo $singleSer['link']['url']; ?>"><?php echo $singleSer['link']['title']; ?></a>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
		
		<?php } ?>
		<?php if( !empty(get_field('community_section')['title']) && !empty(get_field('community_section')['description']) ){ ?>
		<div class="community-section">
			<div class="item" data-image="<?php echo get_field('community_section')['background_image']; ?>">
				<div class="container">
					<div class="image-box animateblock" data-animate-class="zoomIn">
						<a class="image-popup-no-margins" href="<?php echo get_field('community_section')['community_image']; ?>">
							<img src="<?php echo get_field('community_section')['community_image']; ?>">
						</a>
					</div>				
					<div class="content animateblock" data-animate-class="fadeInRightSmall">
						<h3 class="animateblock animatebefore" ><?php echo get_field('community_section')['title']; ?></h3>
						<p><?php echo get_field('community_section')['description']; ?></p>
						<a class="btn" href="<?php echo get_field('community_section')['link']['url']; ?>"><?php echo get_field('community_section')['link']['title']; ?></a>
					</div>			
				</div>
			</div>
		</div>
		<?php } ?>


<?php get_footer(); ?>
