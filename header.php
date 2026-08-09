<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<title><?php wp_title(''); ?></title>
		
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="<?php bloginfo('description'); ?>">

		<style>
			.loader{
				height: 100%;
				width: 100%;
				position: fixed;
				z-index: 99999999;
			}
			.loader div{
				position: absolute;
				height: 100%;
				width: 100%;
				background: #a69f82;
			}
		</style>
		<link href="//www.google-analytics.com" rel="dns-prefetch">
        <link href="<?php echo get_template_directory_uri(); ?>/img/icons/favicon.ico" rel="shortcut icon">
        <link href="<?php echo get_template_directory_uri(); ?>/img/icons/touch.png" rel="apple-touch-icon-precomposed">
		

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

		<?php wp_head(); ?>
		<script>
			// conditionizr.com
			// configure environment tests
			conditionizr.config({
				assets: '<?php echo get_template_directory_uri(); ?>',
				tests: {}
			});
        </script>
		<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-27528959-47"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-27528959-47');
</script>
		<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9593419636473511"
     crossorigin="anonymous"></script>
	</head>
	
	<body <?php body_class('test'); ?>>
			<div class="loader"><div></div></div>
			<div class="lines">
				<span></span>
				<span></span>
				<span></span>
				<span></span>
			</div>
			<?php if( get_field('toggle_button') == 'On' ){ ?>

			<div class="floating-button-section">
				<a href="<?php echo get_field('floating_button')['url']; ?>"><?php echo get_field('floating_button')['title']; ?></a>
			</div>
			<?php } ?>
			<!-- header -->
			<header class="header">
				<div class="logo">
					<?php // echo get_field('logo','option'); ?>
					<a href="<?php echo site_url(); ?>"><img src="" data-src="https://lewisedward.com/wp-content/themes/ldavis/img/top-bar-logo.gif"></a>
				</div>
				<div class="fl-right">
					<div class="menu-bar">
						<?php ldavis_nav(); ?>
						
							<div class="side-dr">
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</div>	
						<!-- <div class="social"> 
							<?php foreach( get_field('header_social_link','option') as $social ){ ?>
								<a href="<?php echo $social['link']; ?>"><i class="fa <?php echo $social['icon_class']; ?>"></i></a
							<?php } ?>-->
						</div>
						
					</div>
						
						<span class="tagline">made with love</span>
				</div>
					<div class="toggle">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</div>
				</div>
			</header>
			<div class="sidenaver">
				 <?php if( have_rows('hamburger_menu','option') ): ?> 
			    <?php while( have_rows('hamburger_menu','option') ): the_row(); 
			        $content = get_sub_field('content'); ?> 
			        <div class="inner-bar">
							<?php echo $content; ?>
						  </div>
			    <?php endwhile; ?> 
				<?php endif; ?>
			</div> 
			<a id="cstm-button"></a>
			<!-- /header -->
			<!-- Content Section -->
			<div class="main-content">
