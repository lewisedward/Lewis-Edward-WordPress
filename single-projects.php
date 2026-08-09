<?php get_header(); ?>


	<div class="single-project">
		<?php if (have_posts()): while (have_posts()) : the_post(); ?>
		<div class="container">
			<div class="head">
				<div class="col">
					<!--a class="backicon" href="<?php echo site_url('projects'); ?>">
						<img src="https://lewisedward.com/wp-content/uploads/2020/05/grid-view.jpg">
					</a-->
				</div>
				<div class="col">
					<h2><?php the_title(); ?></h2>
				</div>
				<div class="col">
					<div class="buttons">
						<?php
						$previous = get_previous_post();
						$next = get_next_post();
						if( !empty( $previous ) && !empty( $next ) ){
							if( get_previous_post() ){ 
							?>
								<div class="preved">
									<a href="<?php echo get_the_permalink($next->ID); ?>">previous</a>
								</div>
							<?php } ?>
								<a class="backicon" href="<?php echo site_url('projects'); ?>">
									<img src="https://lewisedward.com/wp-content/uploads/2020/05/grid-view.jpg">
								</a>
							<?php if( get_next_post() ){  ?>
								<div class="nexted">
									<a href="<?php echo get_the_permalink($previous->ID); ?>">next</a>
								</div>
							<?php } ?>
							<?php }elseif( empty( $previous ) ){ ?>
								<div class="preved">
									<a href="<?php echo get_the_permalink($next->ID); ?>">previous</a>
								</div>
								<a class="backicon" href="<?php echo site_url('projects'); ?>">
									<img src="https://lewisedward.com/wp-content/uploads/2020/05/grid-view.jpg">
								</a>
							<?php }else{?>
								<a class="backicon" href="<?php echo site_url('projects'); ?>">
									<img src="https://lewisedward.com/wp-content/uploads/2020/05/grid-view.jpg">
								</a>
								<div class="nexted">
									<a href="<?php echo get_the_permalink($previous->ID); ?>">next</a>
								</div>
							<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<div class="content vcv">
			<div class="container">
				<?php the_content(); ?>
			</div>
		</div>
		<?php if( !empty( get_field('quote_section')['description'] ) ){ ?>
		<div class="info quote-sectn">
			<div class="container">
				<div class="item">
					<div class="text disc">
						<h3><?php echo get_field('quote_section')['title']; ?></h3>
						<?php echo get_field('quote_section')['description']; ?>
						<strong><?php echo get_field('quote_section')['testimonial_name']; ?></strong>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		<div class="info">
			<div class="">
				<?php foreach( get_field('footer_section') as $key => $singleSectn ){ ?>
					<div class="item <?php if( $key%2 == 0 ){ echo 'greyBackground'; }else{ echo 'whiteBackground'; } ?>">
					<div class="container">
						<?php if( !empty( $singleSectn['description'] ) ){ ?>
							<div class="text">
								<?php echo $singleSectn['description']; ?>
							</div>
						<?php } ?>
						<?php if( !empty( $singleSectn['images'] ) ){ ?>
							<div class="image-sectn">
								<?php foreach( $singleSectn['images'] as $singleImage ){ ?>
									<img src="<?php echo $singleImage['image']; ?>">
								<?php } ?>
							</div>
						<?php } ?>
					</div>	
					</div>
				<?php } ?>
			</div>
		</div>
		<?php endwhile; ?>
		<?php endif; ?>
	</div>


<?php get_footer(); ?>
