
(function ($, root, undefined) {
	$(window).on('load', function() {
		setTimeout(function(){ $('body .loader').remove(); }, 2000);
	});
	$(document).ready(function() {
		
			'use strict';
			
			
			var hoverMouse = function($el,dev) {
			  $el.each(function() {
				var $self = $(this);
				var hover = false;
				var offsetHoverMax = $self.attr("offset-hover-max") || dev;
				var offsetHoverMin = $self.attr("offset-hover-min") || dev;
				
				var attachEventsListener = function() {
				  $(window).on("mousemove", function(e) {
					//
					var hoverArea = hover ? offsetHoverMax : offsetHoverMin; 
					
					// cursor
					var cursor = {
					  x: e.clientX,
					  y: e.clientY + $(window).scrollTop()
					};
					
				
					// size
					var width = $self.outerWidth();
					var height = $self.outerHeight();

					// position
					var offset = $self.offset();
					var elPos = {
					  x: offset.left + width / 2,
					  y: offset.top + height / 2
					};

					// comparaison
					var x = cursor.x - elPos.x;
					var y = cursor.y - elPos.y;

					// dist
					var dist = Math.sqrt(x * x + y * y);
					
					// mutex hover
					var mutHover = false;

					// anim
					if (dist < width * hoverArea) {
					  mutHover = true;
					  if (!hover) {
						hover = true;
					  }
					  onHover(x, y);
					}

					// reset
					if (!mutHover && hover) {
					  onLeave();
					  hover = false;
					}
				  });
				};
 
				var onHover = function(x, y) {
				  gsap.to($self, {
					duration: 0.4,
					x: x * dev,
					y: y * dev,
				  });
				};
				var onLeave = function() {
				  gsap.to($self, {
					duration: 0.4,
					x: 0,
					y: 0,
				  });
				};

				attachEventsListener();
			  });
			};
		
			if($(".testimonial-slider").length){
				$(window).on('load', function() {
					hoverMouse($('.testimonial-slider .owl-nav button.owl-next'),0.6);
					hoverMouse($('.testimonial-slider .owl-nav button.owl-prev'),0.6);
				});
			}
			if($("#cstm-button").length){
				hoverMouse($('#cstm-button'),0.6);
			}
			
			
			if($(".single-project .content a").length){
				hoverMouse($('.single-project .content a'),0.3);
			}
			
			if($(".side-dr").length){
				hoverMouse($('.side-dr'),0.4);
			}
			
			if($(".copyright-page").length){
				function imagePosition(){
					var winwidth = $(window).outerWidth();
					if(winwidth>=992){
						$(".copyright-grid").each(function(){
							if ($(this).index() % 2 == 0) {
								//Even Number
								let position = (winwidth/4)+"px center";
								$(this).find(".img-box").css("background-position",position);
							}else{
								//Odd Number
								let position = -(winwidth/4)+"px center";
								$(this).find(".img-box").css("background-position",position);
							}
						});
					}
					else{
						$(".copyright-grid").each(function(){
							$(this).find(".img-box").css("background-position","center center");
						});
					}
				}
				imagePosition();
				$(window).on('resize', function() {
					imagePosition();
				});
				
			}
			
			var headerHt = $(".header").outerHeight();
			var winheight = $(window).outerHeight();
			var footheight = $(".footer").outerHeight();
			var foottop = $(".footer").offset().top;	
			
			$(window).bind('wheel', function(event) {
				if (event.originalEvent.deltaY < 0) {
					$(".header").css({
						top:"3px",
					});
					$(".floating-button-section").removeClass("show");
				}
				else {
					if($(window).scrollTop() >= headerHt*2){
						$(".header").css({
							top:-headerHt+"px",
						});	
					}
					if($(window).scrollTop() >= winheight){ 
						if($(window).scrollTop() <= (foottop+footheight)){
							$(".floating-button-section").addClass("show");	
						}
						else{
							$(".floating-button-section").removeClass("show");
						}
					}
				}
			});
			
			
			
			if(!(/iPhone|iPad|iPod|Opera Mini/i.test(navigator.userAgent))) {
				document.addEventListener('swiped', function(e) {
					if($(window).scrollTop()>100){
						if(e.detail.dir=="up"){
							$(".header").css({
								top:-headerHt+"px",
							});	
						}
					}
					if($(window).scrollTop()>=winheight){
						if(e.detail.dir=="up"){
							if($(window).scrollTop() <= (foottop+footheight)){
								$(".floating-button-section").addClass("show");	
							}
							else{
								$(".floating-button-section").removeClass("show");
							}
						}
					}
					if(e.detail.dir=="down"){
						$(".header").css({
							top:"3px",
						});
						$(".floating-button-section").removeClass("show");
					}			  
				});
			}
			
			
			
			
			
			$("#cstm-button").click(function(){
				$(".header").css({
					top:"3px",
				});
			});
			
			
			$(".header .toggle").click(function(){
				if(!$(this).hasClass("open")){
					$(this).addClass("open");
				}
				else{
					$(this).removeClass("open");
				}
				$(".header .menu-bar").stop(true,false).fadeToggle(function(){
					if($(this).is(":hidden")){
						$("body").css("overflow","visible");
					} else{
						$("body").css("overflow","hidden");
					}
				});
			});
			
					
			$(".header .side-dr").click(function(){
				if(!$(this).hasClass("open")){
					$(this).addClass("open");
				}
				else{
					$(this).removeClass("open");
				}  
				$(".sidenaver").toggleClass("active");
			});
			
			var logo = $(".logo a img").attr("data-src");
			var flogo = $(".footer .logo-sectn img").attr("data-src");
			
			$(".footer .logo-sectn img").mouseover(function(){
				$(this).attr("src",flogo);
			});
			
			$(window).load(function(){				
				$(".loader div").animate({
					width:0,
				},500,function(){
					$(".loader div").css("right","0");
					$(".loader").css("visibility","hidden");
					$(".logo a img").attr("src",logo);
					$(".footer .logo-sectn img").attr("src",flogo);
				});
			});
			$(window).on('beforeunload', function(){
				$(".loader").css("visibility","visible");
				$(".loader div").animate({
					width:'100%',
				},500);
			});
			
			
			$(window).on('load', function(){
				if($('.blog-grid').length){
					var $grid = $('.blog-grid').isotope({
						itemSelector: '.grid-item',
					});
					$grid.isotope();
				}
			});
				
			$('.faq-section .item .cstm-qustn').on('click', function() {
				if($(this).parent(".item").hasClass("visible")){
					$(this).parent(".item").removeClass("visible");
					$(this).next().slideUp();
					return;
				}
				else{
					$(".faq-section .item").removeClass("visible");
					$(this).parent(".item").addClass("visible");
					$(".faq-section .item .cstm-answr").slideUp();
					$(this).next().slideDown();	
				}				
			});
			
			function fullheight(elemnt){
				
				var headerHt = $(".header").outerHeight();
				var footerHt = $(".footer").outerHeight();
				var winwidth = $(window).outerWidth();
				
				if($(".slide-area").length){
					
					if(winwidth>575){
						$(".slide-area .sliderOwl .item").css("min-height","calc(100vh - "+ (headerHt+6) +"px)");
					}
					else{
						$(".slide-area .sliderOwl .item").css("min-height","calc(100vh - "+ (headerHt+130) +"px)");
					}
					
					
				}
				if($(".not-found").length){
					$(".not-found").css("height","calc(100vh - "+ (headerHt+footerHt+6) +"px)")
				}
			
				var height = 'calc(100vh - '+(footerHt+6)+'px)';
				
				$(elemnt).find(".main-content").css("padding-top",headerHt);
				$(elemnt).find(".main-content").css("min-height",height);
			}
			
			
			fullheight("body");
			$(window).on('resize', function() {
				fullheight("body");
			});
			
			
			$("body").cssAnimate();
			
			if($(".services-list").length){
				function iconAnimate(){
						var $elTop = $(".services-list").offset().top;
						var pos = $(window).scrollTop();
						var windowHeight = $(window).outerHeight();
						
						if($elTop < (pos+windowHeight-(windowHeight/4))){
							if(!$(".services-list").hasClass("animated")){
								$(".services-list .item .srv-icn").each(function(index, value){
									var id = "nectar-svg-animation-instance-"+index;
									new Vivus(id, {
										type: 'delayed',
										duration: 150,
									});
								});		
							}
							$(".services-list").addClass("animated");
						}
				}
				
				
				$(window).on('load', function(){
					setTimeout(function(){
						iconAnimate();
					}, 400);
					$(window).on('scroll', function(){
						iconAnimate();
					});
				});  
				
				$(".services-list .item").hover(function(){
					let eleindex = $(this).index();
					console.log(eleindex);
					var id = "nectar-svg-animation-instance-"+eleindex;
					new Vivus(id, { 
						type: 'delayed',
						duration: 150,
					});
				},function(){
					return;
				});
				
			}
				
			
			$(".home-services .item").bgParallax({
				speedFactor: 0.3,
			});
			$(".community-section .item").bgParallax({
				speedFactor: 0.3,
			});
			$(".contact-page").bgParallax({
				speedFactor: 0.3,
			});
			
			
			/* $(document).ready(function(){    
				$(".side-dr").click(function(){
					$(".sidenaver").attr("id","page_navigation1");
				});      
				$(".side-dr").click(function(){
					$(".sidenaver").removeAttr("id");
				});     
			}); */
	
			
			$(".slide-area .sliderOwl .item").bgParallax({
				speedFactor: 0.3,
				//blurEffect:true,
				//blurspeed:0.2,
			});
			
			//if(!($(".copyright-page").hasClass("single-service"))){
				$(".copyright-page .campaigns-list").bgParallax({
					speedFactor: 0.3,
				});
			//};
		
			
			$(".slide-area .item a.scroll-btn").click(function(event){
				event.preventDefault();
				var myid = $(this).attr("href");
				
				$('html, body').animate({
					scrollTop: $(myid).offset().top
				}, 500);
				
			});
			
			$(".meta-comment-count a").click(function(event){
				event.preventDefault();
				var myid = $(this).attr("href");
				
				$('html, body').animate({
					scrollTop: $(myid).offset().top
				}, 500);
				
			});
			
			
			  var owl = $('.mainOwlCrsl');
			  if(owl.length){
				 //  owl.owlCarousel({
					// margin: 10,
					// nav: true,
					// items: 1,
					// loop:true,
					// onInitialized  : counter, 
					// onTranslated : counter 
				 //  });
				  owl.slick({
					  infinite: false,
					  slidesToShow: 3,
					  slidesToScroll: 3,
					  responsive: [
						{
						  breakpoint: 767,
						  settings: {
							slidesToShow: 2,
							slidesToScroll: 2
						  }
						},
						{
						  breakpoint: 480,
						  settings: {
							slidesToShow: 1,
							slidesToScroll: 1
						  }
						}
						]
					});
  
					$('.recent-project .btns span.prev').click(function() {
						owl.trigger('prev.owl.carousel');
					});
					$('.recent-project .btns span.next').click(function() {
						owl.trigger('next.owl.carousel');
					});
			  }
			  
			 
			  function counter(event) {
				  if (!event.namespace) {
					return;
				  }
				  var slides = event.relatedTarget;
				  $('#counter').text(slides.relative(slides.current()) + 1 + '/' + slides.items().length);
			  }
		  
			  var owlT = $('#testimonialOwl');
			  owlT.owlCarousel({
				margin: 10,
				autoHeight:true,
				nav: true,
				items: 1,
				loop:true,
				animateOut: 'fadeOut',
				onInitialized  : testmCounter, 
				onTranslated : testmCounter 
				
			  });

			  function testmCounter(event) {  
				  if (!event.namespace) {
					return;
				  }
				  var slides = event.relatedTarget;
				  $('#testm-counter').text(slides.relative(slides.current()) + 1 + '/' + slides.items().length);
			  }
		  
		  
			var owlL = $('#brndLogoOwl');
              owlL.owlCarousel({
                items: 6,
                loop: true,
                margin: 30
              });
			  
			  var owlNL = $('#brndLogoNoOwl');
              owlNL.owlCarousel({
                items: 6,
                loop: false,
                margin: 30,
				responsive : {
					// breakpoint from 0 up
					0 : {
						items: 2,
						margin: 10,
					},
					576 : {
						items: 3,
						margin: 20,
					},
					// breakpoint from 480 up
					768 : {
						items: 4,
						margin: 30,
					},
					// breakpoint from 992 up
					992 : {
						items: 5,
					},
					// breakpoint from 1200 up
					1200 : {
						items: 6,
					}
				}
				
              });
		  
		  
		  
			var owlS = $('.sliderOwl');
			if(owlS.length){
				function sliderAnimate(){
					$(".slide-area .sliderOwl .item .loadbar").removeAttr("style");	
					$(".slide-area .sliderOwl .item .loadbar").stop(true,false).animate({
						width:"100%",
					},9000);
					
					$(".slide-area .sliderOwl .item .info").removeAttr("style");	
					$(".slide-area .sliderOwl .item .info").stop(true,false).delay(300).animate({
						top: 0,
						opacity: 1,	
					},600);	
					
					$(".slide-area .sliderOwl .item a.scroll-btn").removeAttr("style");	
					$(".slide-area .sliderOwl .item a.scroll-btn").stop(true,false).delay(1000).animate({
						bottom: 0,
						opacity: 1,	
					},200);		
				}
				owlS.on('change.owl.carousel', function(event) {
					sliderAnimate();	
				});
				
				
				owlS.owlCarousel({
					items: 1,
					loop: true,
					nav: true,
					margin: 10,
					autoHeight:true,
					autoplay: false,
					autoplayTimeout: 10000,
					transitionStyle : "fade",
					animateIn: 'fadeIn', // add this
					animateOut: 'fadeOut', // and this
					dotsContainer: '#carousel-custom-dots',
				});
				$('.owl-dot').click(function () {
					owlS.trigger('to.owl.carousel', [$(this).index(), 300]);
				});
			}
			  
			  
		  
		    $('.image-popup-no-margins').magnificPopup({
				type: 'image',
				closeOnContentClick: true,
				closeBtnInside: false,
				fixedContentPos: true,
				mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
				image: {
					verticalFit: true
				},
				zoom: {
					enabled: true,
					duration: 300 // don't foget to change the duration also in CSS
				}
			});
			
			$('.cstmSerBtn').on('click',function(e){
				//e.preventDefault();
			});
			
			$(".disable-owl-swipe").on("touchstart mousedown", function(e) {
				// Prevent carousel swipe
				e.stopPropagation();
			});
		
			
			
			$('.cstmHovrSectn').hover(function(){
				var topOffset = $(this).offset().top;  
				var leftOffset = $(this).offset().left;
				$(this).parent().parent().find('.hoverImage').css('left',leftOffset);
				$(this).parent().parent().find('.hoverImage').css('top',topOffset);
				$(this).parent().parent().find('.hoverImage').stop(true, false).slideDown(800);
			  },function(){
				$(this).parent().parent().find('.hoverImage').stop(true, false).slideUp(800);
			  }
			);
			
			$('.services-page .descption .container p .sayHelloSection').hover(function(){
				var topOffset = $(this).offset().top;  
				var leftOffset = $(this).offset().left;
				$(this).parents('.container').find('.customHelloHvr').css('left',leftOffset);
				$(this).parents('.container').find('.customHelloHvr').css('top',topOffset);
				$(this).parents('.container').find('.customHelloHvr').stop(true, false).slideDown(800);
			},function(){
				$(this).parents('.container').find('.customHelloHvr').stop(true, false).slideUp(800);
			  }
			);
			
			$('.services-page .descption .container p .requestQuoteSection').hover(function(){
				var topOffset = $(this).offset().top;  
				var leftOffset = $(this).offset().left;
				$(this).parents('.container').find('.customQuoteHvr').css('left',leftOffset);
				$(this).parents('.container').find('.customQuoteHvr').css('top',topOffset);
				$(this).parents('.container').find('.customQuoteHvr').stop(true, false).slideDown(800);
			},function(){
				$(this).parents('.container').find('.customQuoteHvr').stop(true, false).slideUp(800);
			  }
			);
			
			
			var btn = $('#cstm-button');

			$(window).scroll(function() {
			  // var bottomPos = $(window).scrollTop() + $(window).height();
			  var bottomPos = $(document).height() - $(window).height();
			  if ( ( $(window).scrollTop() > 300 ) && ( $(window).scrollTop()< bottomPos - $(".footer").outerHeight()-50 ) ) {
				btn.addClass('show');
			  }
			  else{
				btn.removeClass('show');
			  }
			  
			  
			});

			btn.on('click', function(e) {
			  e.preventDefault();
			  $('html, body').animate({scrollTop:0}, '300');
			});
			$('.sliderOwl .owl-item').each(function(e){
				var hiddenUrl = $(this).find('.info .hiddenElement').val();
				var spanHtml = $(this).find('.info .dscrptn .cstmHovrSectn').html();
				var newSpanHtml = '';
				newSpanHtml += '<a href="'+hiddenUrl+'">'+spanHtml+'</a>';
				$(this).find('.info .dscrptn .cstmHovrSectn').html('');
				$(this).find('.info .dscrptn .cstmHovrSectn').append(newSpanHtml);
			});
			
			
			
			$('.menu-bar ul li.menu-item').each(function(){
				
				if( $(this).hasClass('menu-item-has-children') == true ){
					$(this).find('a').first().after('<i class="fa fa-angle-down"></i>');
				}
			});
			var mouseEvent = 'click';
			var mouseMenuClass ='.menu-bar ul li.menu-item i';
			
			if($( window ).width() > 991){
				mouseEvent = 'mouseover';
				mouseMenuClass ='.menu-bar ul li.menu-item-has-children a';
			} 
			$(document).on(mouseEvent,mouseMenuClass,function(){
				// $('.menu-bar ul li.menu-item ul.sub-menu').stop(true, false).slideUp();
				if( $(this).parent().find('ul.sub-menu').is(':visible') ){
					$(this).parent().find('ul.sub-menu').stop(true, false).slideUp();
				}else{
					$(this).parent().find('ul.sub-menu').stop(true, false).slideDown();
				}
				
			});
			
			$('.slides').cycle({ 
				fx:     'none',
				speed:   1000,
				timeout: 5
			}).cycle("pause");

			// Pause &amp; play on hover
			$('.slideshow-block').hover(function(){
				$(this).find('.slides').addClass('active').cycle('resume');
			}, function(){
				$(this).find('.slides').removeClass('active').cycle('pause');
			});
			
			if(/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
				$(this).find('.slides').addClass('active').cycle('resume');
			}
			
		});
		
		var tagtext = jQuery('.meta-category a').text();   
		if(tagtext == "Services"){ 
			jQuery(".header .menu-bar ul li.services a").addClass("margin-check");
		}
		else if(jQuery('.meta-category').length > 0 && (jQuery('.meta-category:contains("News")') || jQuery('.meta-category:contains("Technology")') || jQuery('.meta-category:contains("Positive Vibes")') || jQuery('.meta-category:contains("Work")') || jQuery('.meta-category:contains("Offers")') || jQuery('.meta-category:contains("Videos")') ) ){ 
			jQuery(".header .menu-bar ul li.blog-cls a").addClass("margin-check"); 
		}

})(jQuery, this);


