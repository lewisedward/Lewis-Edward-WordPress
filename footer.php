			</div>
			<!-- /Content Section -->
			
			<!-- footer -->
			<footer class="footer">
				<div class="container">
					<div class="first-sectn">
						<div class="logo-sectn">
						<?php //echo get_field('footer_logo','option'); ?>
							<a href="/"><img src="" data-src="<?php echo site_url(); ?>/wp-content/themes/ldavis/img/footer-logo.gif"></a>
						</div>
						<div class="copy-right">
							<?php footer_menu(); ?>
						</div>
					</div>
					<div class="second-sectn">
						<div class="copyright-section">
							<p><?php echo get_field('copyright_text','option'); ?></p>
						</div>
						<?php if( !empty( get_field('footer_right_text','option') ) ){ ?>
							<div class="right-section">
								<p><?php echo get_field('footer_right_text','option'); ?></p>
							</div>
						<?php } ?>
					</div>
					<!--div class="social">
						<ul>
							<?php foreach( get_field('footer_social_link','option') as $social ){ ?>
								<li><a href="<?php echo $social['link']; ?>"><i class="fa <?php echo $social['icon_class']; ?>"></i></a></li>
							<?php } ?>
						</ul>
					</div-->
				</div>
			</footer>
			<!-- /footer -->

		<?php wp_footer(); ?>

		<!-- analytics -->


<style>
  /*.img-box.\.project-page.\.img-box {
    transform: translateY(4em) rotateZ(360deg);
    transition: transform 4s .25s cubic-bezier(0,1,.3,1), opacity .3s .25s ease-out;
    will-change: transform, opacity;
    box-shadow: 1em 1em 2em .25em rgba(0,0,0,.2);
}
.activeone, .activetwo, .activethree, .activefour {
    transform: rotateZ(0deg);
     transition: transform 4s .25s cubic-bezier(0,1,.3,1), opacity .3s .25s ease-out;
}
.activetwo {
    transform: rotateZ(-1deg);
     transition: transform 4s .25s cubic-bezier(0,1,.3,1), opacity .3s .25s ease-out;
}
.activethree {
    transform: rotateZ(0deg);
     transition: transform 4s .25s cubic-bezier(0,1,.3,1), opacity .3s .25s ease-out;
}
.activefour{
    transform: rotateZ(-1deg);
     transition: transform 4s .25s cubic-bezier(0,1,.3,1), opacity .3s .25s ease-out;
}*/
</style>
		<script>
		(function(f,i,r,e,s,h,l){i['GoogleAnalyticsObject']=s;f[s]=f[s]||function(){
		(f[s].q=f[s].q||[]).push(arguments)},f[s].l=1*new Date();h=i.createElement(r),
		l=i.getElementsByTagName(r)[0];h.async=1;h.src=e;l.parentNode.insertBefore(h,l)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('create', 'UA-XXXXXXXX-XX', 'yourdomain.com');
		ga('send', 'pageview');
		</script>
		<script>
	    jQuery(function() {
    //caches a jQuery object containing the header element
    var header = jQuery(".project-page .img-box");
    jQuery(window).scroll(function() {
        var scroll = jQuery(window).scrollTop();

        if (scroll >= 200) {
            header.removeClass('.project-page .img-box').addClass("scroll_img-box");
        } else {
            header.removeClass("scroll_img-box").addClass('.project-page .img-box');
        }
    });
});
</script>
<script>
	   jQuery(function() {
    //caches a jQuery object containing the header element
    var header = jQuery(".ambitious-businesses-section");
    jQuery(window).scroll(function() {
        var scroll = jQuery(window).scrollTop();

        if (scroll >= 200) {
            header.removeClass('.ambitious-businesses-section').addClass("scroll_img");
        } else {
            header.removeClass("scroll_img").addClass('.ambitious-businesses-section');
        }
    });
});
</script>
  <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
  <script>
  	/*Interactivity to determine when an animated element in in view. In view elements trigger our animation*/
jQuery(document).ready(function($) {

  //window and animation items
 var animation_elements = $.find('.inner-left-section');
  var web_window = $(window);

  //check to see if any animation containers are currently in view
  function check_if_in_view() {
    //get current window information
    var window_height = web_window.height();
    var window_top_position = web_window.scrollTop();
    var window_bottom_position = (window_top_position + window_height);

    //iterate through elements to see if its in view
    $.each(animation_elements, function() {

      //get the element sinformation
      var element = $(this);
      var element_height = $(element).outerHeight();
      var element_top_position = $(element).offset().top;
      var element_bottom_position = (element_top_position + element_height);

      //check to see if this current container is visible (its viewable if it exists between the viewable space of the viewport)
      if ((element_bottom_position >= window_top_position) && (element_top_position <= window_bottom_position)) {
        element.addClass('in-view');
      } else {
        element.removeClass('in-view');
      }
    });

  }

  //on or scroll, detect elements in view
  $(window).on('scroll resize', function() {
      check_if_in_view()
    })
    //trigger our scroll event on initial load
  $(window).trigger('scroll');

});
  </script>
  <script>
    $(window).scroll(function() {
  var $height = $(window).scrollTop();
  if($height <= 600) {
    $('#project_page .img-box').addClass('activeone');
    $('#project_page .img-box').removeClass('activetwo activethree activefour');
  }
    else if($height >= 600 && $height <= 1200) {
    $('#project_page .img-box').addClass('activetwo');
    $('#project_page .img-box').removeClass('activeone activethree activefour');
  }
    else if($height >= 1200 && $height <= 2400) {
    $('#project_page .img-box').addClass('activethree');
    $('#project_page .img-box').removeClass('activeone activetwo activefour');
  }
    else if($height >= 2400){
    $('#project_page .img-box').addClass('activefour');
    $('#project_page .img-box').removeClass('activeone activetwo activethree');
  }
  else {
    $('#project_page .img-box').addClass('activemain');
    $('#project_page .img-box').removeClass('activeone activetwo activethree activefour');
  }
});

  </script>
	</body>
</html>
