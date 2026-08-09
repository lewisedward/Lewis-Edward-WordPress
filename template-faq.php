<?php 
/* Template Name: FAQ Page Template */
get_header(); 
?>
	<div class="faq-page">
		<?php foreach( get_field('faq_section') as $faq ){ ?>
			<div class="faq-section">
				<h2><?php echo $faq['heading']; ?></h2>
				<?php foreach( $faq['question_answer'] as $faqSec ){ ?>
				<div class="item">
					<h3 class="cstm-qustn"><span class="icon"></span><?php echo $faqSec['question']; ?></h3>
					<div class="cstm-answr">
						<p><?php echo $faqSec['answer']; ?></p>
					</div>
				</div>
				<?php } ?>
			</div>
		<?php } ?>
		<div class="still-qust" style="background-image:url(<?php echo get_field('footer_section')['background_image']; ?>)">
			<h2><?php echo get_field('footer_section')['heading']; ?></h2>
			<p><?php echo get_field('footer_section')['description']; ?></p>
			<a class="btn" href="<?php echo get_field('footer_section')['button_link']['url']; ?>"><?php echo get_field('footer_section')['button_link']['title']; ?></a>
		</div>
	</div>


<?php get_footer(); ?>
