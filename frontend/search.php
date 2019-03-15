<?php
/**
 * Template Name: OpenContractr Search
 *
 *
 * @package WordPress
 * @subpackage OpenContractr
 * @since 1.0
 * @version 1.0
 */
//global $wp_styles; var_dump($wp_styles);
?>

<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>
	<head>
		<title><?php echo get_bloginfo( 'name' ) ?></title>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		
		<noscript><link rel="stylesheet" href="<?php echo get_theme_file_uri( 'assets/css/noscript.css' ) ?>" /></noscript>
		<?php wp_head(); ?>
		<script>
			var isloggedin = <?php echo (is_user_logged_in() ? is_user_logged_in() : 0) ?>;
		</script>
	</head>
	<body class="is-loading" <?php body_class(); ?>>

		<!-- Wrapper -->
			<div id="wrapper" class="fade-in searchlight">
				
				<?php //if (!is_home()) { ?>
				<!-- Intro -->
					<div id="intro">
						<span><?php echo $publisher_options['publisher_name'] ?></span>
						<h1>OpenContract<span style="color:#008751">r</span></h1>
						<div id="searcharea">
							<input type="text" id="searchbox" placeholder="Search for a contract">
							<span id="searchbtn" class="button icon solo fa-arrow-right">Search</span>
							<a href="#title_header" id="scrollbtn" class="scrolly"></a>
							<div id="searchby">
								<span>Search contracts by:</span>
								<select>
									<option value="all">Anything</option>
									<option value="projecttitle">Title</option>
									<option value="description">Description</option>
									<option value="contractor">Contractor</option>
									<option value="procuringentity">Procuring entity</option>
									<option value="awarddate">Award year</option>
									<option value="contractdate">Contract year</option>
								</select>
							</div>
						</div>
						<div class="loading" style="visibility: hidden"><i class="fa fa-cog fa-spin fa-3x fa-fw"></i><br><span>Loading...</span></div>
						<!--<form action="#" role="form" id="query-form">
						  <div class="form-group">
							<input type="hidden" class="form-control typeahead" id="query" value="">
						  </div>
						</form>-->
						<ul class="actions">
							<li></li>
						</ul>
					</div>
				<?php //} ?>

				<!-- Header -->
					<header id="header">
						<div class="logo subheader" id="title_header">
							<div id="results" class='result'>
								<i class="fa fa-book"></i><span id="result-titles">List of contracts</span>
							</div>
						</div>
					</header>
					
					<!-- Nav -->
					<header id="resulthead">
							<nav id="nav">
								<ul class="links">
									<li class="tab active"><a href="#title_header" data-page="resultwrap" class="active scrolly">Search results</a></li>
									<li class="tab charts"><a href="#title_header" data-page="charts" class="scrolly">Charts</a></li>
									<li>
										<div id="result" class='result'>
											<span id="result-title"></span><span id="result-sub-title"></span>
										</div>
									</li>
									<li>
										<div class="right-tab"><a href="#intro" class="scrolly">Back to search</a></div>
									</li>
								</ul>
							</nav>
					</header>
					
					<div id="main">
						<article id="resultwrap" class="post featured page">
							
							<table id="contractslist" class="contractslist">
							  <thead>
								<tr>
								<th>main</th>
								</tr>
							  </thead>
							</table>

							<table id="contractslist2" class="contractslist2">
							  <thead>
								<th></th>
							  </thead>
							  <tbody>
							  </tbody>
							</table>
							
						</article>
						<article id="charts" class="post featured page">
							<div id="chartarea">
								<div id="chart1">
								<?php blazing_charts_insert(array("charttype" => "d3","source" => "c3-bar-chart", "options" => "c3", "target"=>"chart1")) ?>
								</div>
								<div id="chart2">
								<?php blazing_charts_insert(array("charttype" => "d3","source" => "c3-pie-chart", "options" => "c3", "target"=>"chart2")) ?>
								</div>
								<div id="chart3">
								<?php blazing_charts_insert(array("charttype" => "d3","source" => "c3-pie-chart", "options" => "c3", "target"=>"chart3")) ?>
								</div>
								<div id="chart4">
								<?php blazing_charts_insert(array("charttype" => "d3","source" => "c3-bar-chart", "options" => "c3", "target"=>"chart4")) ?>
								</div>
							</div>
						</article>
					</div>

					<div id="copyright">
						<ul><li>&copy; Centre for Open Data Research</li></ul>
					</div>

			</div><!-- #wrapper -->
			
		<?php wp_footer(); ?>

	</body>
</html>
		
