<?php
/**
 * The header for the theme
 *
 *
 * @package WordPress
 * @subpackage OpenContractr
 * @since 1.0
 * @version 1.0
 */

?>
<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
	<head>
		<title><?php echo get_bloginfo( 'name' ) ?></title>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		
		<noscript><link rel="stylesheet" href="<?php echo get_theme_file_uri( 'assets/css/noscript.css' ) ?>" /></noscript>
		<?php wp_head(); ?>
	</head>
	<body class="is-loading" <?php body_class(); ?>>
	
		<!-- Wrapper -->
			<div id="wrapper" class="fade-in">
				
				<?php if (is_home()) { ?>
				<!-- Intro -->
					<div id="intro">
						<h1>OpenContracti<span style="color:#008751">ng</span></h1>
						<p>An OCDS learning and publishing tool for organisations and government agencies</p>
						<ul class="actions">
							<li><a href="#title_header" class="button icon solo fa-arrow-down scrolly">Continue</a></li>
						</ul>
					</div>
				<?php } ?>

				<!-- Header -->
					<header id="header" <?php if (!is_home()) { ?>style="height: auto"<?php } ?>>
						<div class="logo subheader" id="title_header" <?php if (!is_home()) { ?>style="padding: 2rem"<?php } ?>>
							<span class="false-header">OpenContract<span style="color:#008751">r</span></span>
							<ul class="icons">
								<li class="title"><span>OpenContract<span style="color:#008751">r</span></li>
								<!--<li><a href="#" class="icon fa-home scrolly"><span class="label">Home</span></a></li>
								<li><span class="label icon fa-twitter"></span></li>
								<li><span class="label icon fa-github"></span></li>-->
								<li class="subtitle"><h4></h4></li>
							</ul>
						</div>
					</header>

				
		
