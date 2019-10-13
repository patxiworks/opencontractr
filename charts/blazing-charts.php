<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Define the URL path to the plugin...
define( 'BC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
require_once( BC_PLUGIN_PATH . 'inc/options-page.php' );

if ( ! class_exists( 'Blazing_Charts' ) ) {

class Blazing_Charts {
	private $add_library = array (
		'highcharts' => false,
		'morris'     => false,
		'zingchart'  => false,
		'chartjs'    => false,
		'google'     => false,
		'd3'         => false,
		'chartist'   => false,
		'smoothie'   => false,
		'flot'       => false
//		'amcharts'   => false
	);
	private $options = array();
	private $settings_name = 'blazing_charts_settings'; // The settings string name for this plugin in options table
	private $bc_settings_ver = '1.0';
	private $library_options = "";  // it contains the options from shortcode

	public function __construct() {
		//read plugin settings from database
		$this->get_options();

		$this->register_post_type();
		// register the shortcode
		add_shortcode('BlazingChart', array($this, 'BlazingChart_handler'));
		// Add shortcode support for widgets
		add_filter('widget_text', 'do_shortcode');

		add_action('wp_footer', array($this , 'register_scripts'));
		// make it possible to get the instansce of this class
		add_filter( 'get_blazing_charts_instance', [ $this, 'get_instance' ] );
	}

	// add a method to provide the class instance
	public function get_instance() {
		return $this; // return the object
	}

	public function get_options() {
		// Set class property
		if (!($this->options = get_option( $this->settings_name )) ) {
			$this->options = array(
				'morris_cdn' => '0',
				'chartjs_cdn' => '0',
				'd3_cdn' => '0',
				'chartist_cdn' => '0',
				'flot_cdn' => '0',
				'version' => $this->bc_settings_ver	// see on top class
			);
			update_option( $this->settings_name, $this->options);
		}
	}

	public function register_post_type()
	{
		$args = array(
			'labels' => array(
				'name' => 'Chart Snippets',
				'singular_name' => 'Chart Snippet',
				'add_new ' => 'Add New Chart Snippet',
				'add_new_item' => 'Add New Chart Snippet',

				'menu_name'          => _x( 'Chart Snippets', 'admin menu', 'blazing-charts' ),
				'name_admin_bar'     => _x( 'Chart Snippet', 'add new on admin bar', 'blazing-charts' ),
				'new_item'           => __( 'New Chart Snippet', 'blazing-charts' ),
				'edit_item'          => __( 'Edit Chart Snippet', 'blazing-charts' ),
				'view_item'          => __( 'View Chart Snippet', 'blazing-charts' ),
				'all_items'          => __( 'Charts Manager', 'blazing-charts' ),
				'search_items'       => __( 'Search Chart Snippets', 'blazing-charts' ),
				'parent_item_colon'  => __( 'Parent Chart Snippets:', 'blazing-charts' ),
				'not_found'          => __( 'No Chart Snippet found.', 'blazing-charts' ),
				'not_found_in_trash' => __( 'No Chart Snippet found in Trash.', 'blazing-charts' )
				),
			'query_var' => 'chartsnippets',
			'rewrite' => array(
				'slug' => 'chart-snippets/',
				),
			'public' => true,
			'menu_position' => 25,
			'menu_icon' => BC_PLUGIN_URL . 'images/blazing-graph-icon.png',
			'show_in_menu' => 'edit.php?post_type=open_contract',
			'supports' => array(
				'title',
				'editor'
				)
		);
		register_post_type('chart_snippet', $args);
	}

	public function BlazingChart_handler($incomingfrompost) {
		$BLAZING = "";
		$this->add_library['highcharts'] = false;
		$this->add_library['morris'] = false;
		$this->add_library['zingchart'] = false;
		$this->add_library['chartjs'] = false;
		$this->add_library['google'] = false;
		$this->add_library['d3'] = false;
		$this->add_library['chartist'] = false;
		$this->add_library['smoothie'] = false;
		$this->add_library['flot'] = false;

		// actual shortcode handling here
		$patts=shortcode_atts(array(
			"charttype" => "",
			"source"    => "",
			"function"  => "",
			"options"   => "",
			"target"	=> ""
			), $incomingfrompost);
		$this->library_options = strtolower($patts["options"]) ;
		$patts["charttype"] = strtolower($patts["charttype"]);
		switch ($patts["charttype"]) {
			case "highcharts":
				$this->add_library['highcharts'] = true;
				break;
			case "morris":
				$this->add_library['morris'] = true;
				break;
			case "zingchart":
				$this->add_library['zingchart'] = true;
				break;
			case "chartjs":
				$this->add_library['chartjs'] = true;
				break;
			case "google":
				$this->add_library['google'] = true;
				break;
			case "d3":
				$this->add_library['d3'] = true;
				break;
			case "chartist":
				$this->add_library['chartist'] = true;
				break;
			case "smoothie":
				$this->add_library['smoothie'] = true;
				break;
			case "flot":
				$this->add_library['flot'] = true;
				break;
//			case "amcharts":
//				$this->add_library['amcharts'] = true;
//				break;
		}

		// check if a function is supposed to define the graph
		$func_name = $patts["function"];
		$string = "";
		$callable_name = "";
		// $func_name can be simply a function name or somthing like "array($anObject, 'someMethod')"
		if ( is_callable($func_name, false, $callable_name)) {
			if ( $string = call_user_func($callable_name) ) {
				return $string;
			}
		}

		// If "source" tag is given
		$postslug = $patts["source"];
		$target = $patts["target"];
		if ( $postslug !== "") {
			$start = 'function drawChart_'.$target.'(chartdata, target) {'."\r\n";
			$string = $this->get_post_content($postslug);
			$end = '}';
			$BLAZING .= "<script>\r\n".$start.$string.$end."\r\n</script>\r\n";
		}
		return $BLAZING;
	}

	public function get_post_content($post_slug) {
		$page = get_page_by_path($post_slug, OBJECT, 'chart_snippet');
		if ($page) {
			$queried_post = get_post($page->ID);
			//$content = apply_filters('the_content', $content);
			$content = str_replace(array('// <![CDATA[','// ]]>','// ]]&gt;'), '', $queried_post->post_content);
			return $content;
		}
		return null;
	}

	public function register_highcharts_scripts() {
			$lib_url = "//code.highcharts.com/highcharts.js";
		//}
		wp_register_script("highcharts", $lib_url ,array("jquery"), '1.0', true);
		wp_enqueue_script('highcharts');
		// check if there is any options to take care of
		if ( $this->library_options === "") {
			return;
		}
		// now take care of optional libraries
		$arr = explode(",", $this->library_options);
		foreach ($arr as $value) {
			switch ($value) {
				case "more":
					$lib_url = "//code.highcharts.com/highcharts-more.js";
					wp_register_script("highcharts-more", $lib_url, array("jquery", "highcharts"), '1.0', true);
					wp_enqueue_script('highcharts-more');
					break;
				case "3d":
					$lib_url = "//code.highcharts.com/highcharts-3d.js";
					wp_register_script("highcharts-3d", $lib_url, array("jquery", "highcharts"), '1.0', true);
					wp_enqueue_script('highcharts-3d');
					break;
				case "exporting":
					$lib_url = "//code.highcharts.com/modules/exporting.js";
					wp_register_script("highcharts-exporting", $lib_url, array("jquery", "highcharts"), '1.0', true);
					wp_enqueue_script('highcharts-exporting');
					break;
			}
		}
	}

	public function register_morris_scripts() {
		$lib_path = BC_PLUGIN_PATH .  "js/morris/morris.css";
		$lib_url = BC_PLUGIN_URL .  "js/morris/morris.css";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['morris_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css";
		}
		wp_register_style("morris_css", $lib_url ,null);
		wp_enqueue_style('morris_css');

		$lib_path = BC_PLUGIN_PATH .  "js/morris/raphael-min.js";
		$lib_url = BC_PLUGIN_URL .  "js/morris/raphael-min.js";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['morris_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js";
		}
		wp_register_script("raphael", $lib_url ,array("jquery"), '1.0', true);
		wp_enqueue_script('raphael');

		$lib_path = BC_PLUGIN_PATH .  "js/morris/morris.min.js";
		$lib_url = BC_PLUGIN_URL .  "js/morris/morris.min.js";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['morris_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js";
		}
		wp_register_script("morris", $lib_url ,array("jquery"), '1.0', true);
		wp_enqueue_script('morris');
	}

	public function register_chartjs_scripts() {
		$lib_path = BC_PLUGIN_PATH .  "js/chartjs/Chart.min.js";
		$lib_url = BC_PLUGIN_URL .  "js/chartjs/Chart.min.js";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['chartjs_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.2/Chart.min.js";
		}
		// now take care of optional libraries
		$arr = explode(",", $this->library_options);
		foreach ($arr as $value) {
			switch ($value) {
				case "bundle":
					$lib_path = BC_PLUGIN_PATH .  "js/chartjs/Chart.bundle.min.js";
					$lib_url = BC_PLUGIN_URL .  "js/chartjs/Chart.bundle.min.js";
					if ($this->options['chartjs_cdn'] === '1' || !file_exists($lib_path) ) {
						$lib_url = "//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.2.2/Chart.bundle.min.js";
					}
					break;
			}
		}
		wp_register_script("chartjs", $lib_url, array("jquery"), '1.0', true);
		wp_enqueue_script('chartjs');
	}

	public function register_d3_scripts() {
		$lib_path = BC_PLUGIN_PATH .  "js/d3js/d3.v5.7.0.min.js";
		$lib_url = BC_PLUGIN_URL .  "js/d3js/d3.v5.7.0.min.js";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['d3_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdnjs.cloudflare.com/ajax/libs/d3/5.7.0/d3.js";
		}
		wp_register_script("d3js", $lib_url ,null, '1.0', true);
		wp_enqueue_script('d3js');
		// check if there is any options to take care of
		if ( $this->library_options === "") {
			return;
		}
		// now take care of optional libraries
		$arr = explode(",", $this->library_options);
		foreach ($arr as $value) {
			switch ($value) {
				case "pie":
					$lib_url = BC_PLUGIN_URL .  "js/d3js/d3pie.min.js";
					wp_register_script("d3pie", $lib_url, array("d3js"), '1.0', true);
					wp_enqueue_script('d3pie');
					break;
				case "nvd3":
					$lib_path = BC_PLUGIN_PATH .  "js/d3js/nv.d3.min.js";
					$lib_url = BC_PLUGIN_URL .  "js/d3js/nv.d3.min.js";
					if ($this->options['d3_cdn'] === '1' || !file_exists($lib_path) ) {
						$lib_url = "//cdn.rawgit.com/novus/nvd3/v1.8.1/build/nv.d3.min.js";
					}
					wp_register_script("nvd3", $lib_url, array("d3js"), '1.0', true);
					wp_enqueue_script('nvd3');

					$lib_path = BC_PLUGIN_PATH .  "js/d3js/nv.d3.css";
					$lib_url = BC_PLUGIN_URL .  "js/d3js/nv.d3.css";
					//$lib_path = str_replace('\\', '/', $lib_path);
					if ($this->options['d3_cdn'] === '1' || !file_exists($lib_path) ) {
						$lib_url = "//cdn.rawgit.com/novus/nvd3/v1.8.1/build/nv.d3.css";
					}
					wp_register_style("nvd3_css", $lib_url ,null);
					wp_enqueue_style('nvd3_css');
					break;
				case "c3":
					$lib_path = BC_PLUGIN_PATH .  "js/d3js/c3.min.js";
					$lib_url = BC_PLUGIN_URL .  "js/d3js/c3.min.js";
					if ($this->options['d3_cdn'] === '1' || !file_exists($lib_path) ) {
						$lib_url = "//cdnjs.cloudflare.com/ajax/libs/c3/0.6.12/c3.min.js";
					}
					wp_register_script("c3", $lib_url, array("d3js"), '1.0', true);
					wp_enqueue_script('c3');

					$lib_path = BC_PLUGIN_PATH .  "js/d3js/c3.min.css";
					$lib_url = BC_PLUGIN_URL .  "js/d3js/c3.min.css";
					//$lib_path = str_replace('\\', '/', $lib_path);
					if ($this->options['d3_cdn'] === '1' || !file_exists($lib_path) ) {
						$lib_url = "//cdnjs.cloudflare.com/ajax/libs/c3/0.6.12/c3.min.css";
					}
					wp_register_style("c3_css", $lib_url ,null);
					wp_enqueue_style('c3_css');
					break;
			}
		}
	}

	public function register_chartist_scripts() {
		$lib_path = BC_PLUGIN_PATH .  "js/chartist/chartist.min.css";
		$lib_url = BC_PLUGIN_URL .  "js/chartist/chartist.min.css";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['chartist_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css";
		}
		wp_register_style("chartist_css", $lib_url , null);
		wp_enqueue_style('chartist_css');

		$lib_path = BC_PLUGIN_PATH .  "js/chartist/chartist.min.js";
		$lib_url = BC_PLUGIN_URL .  "js/chartist/chartist.min.js";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['chartist_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js";
		}
		wp_register_script("chartist", $lib_url , null, '1.0', true);
		wp_enqueue_script('chartist');
	}

	public function register_flot_scripts() {
		$lib_path = BC_PLUGIN_PATH .  "js/flot/jquery.flot.min.js";
		$lib_url = BC_PLUGIN_URL .  "js/flot/jquery.flot.min.js";
		//$lib_path = str_replace('\\', '/', $lib_path);
		if ($this->options['flot_cdn'] === '1' || !file_exists($lib_path) ) {
			$lib_url = "//cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js";
		}
		wp_register_script("flot", $lib_url ,array("jquery"), '1.0', true);
		wp_enqueue_script('flot');
		// check if there is any options to take care of
		if ( $this->library_options !== "") {
			// now take care of optional libraries
			$arr = explode(",", $this->library_options);
			foreach ($arr as $value) {
				$name = $value;
				if ( $value !== "colorhelpers") {
					$name = "flot." . $value;
				}
				$lib_path = BC_PLUGIN_PATH .  "js/flot/jquery." . $name . ".min.js";
				$lib_url = BC_PLUGIN_URL .  "js/flot/jquery." . $name . ".min.js";
				if ($this->options['flot_cdn'] === '1' || !file_exists($lib_path) ) {
					$lib_url = "//cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot." . $name . ".min.js";
				}
				wp_register_script("flot-" . $value, $lib_url, array("jquery", "flot"), '1.0', true);
				wp_enqueue_script("flot-" . $value);
			}
		}
	}

	public function register_scripts() {
		//wp_register_script('jquery');
		if ( $this->add_library['highcharts'] ) {
			$this->register_highcharts_scripts();
		}
		if ( $this->add_library['morris'] ) {
			$this->register_morris_scripts();
		}
		if ( $this->add_library['zingchart'] ) {
			$lib_url = "//cdn.zingchart.com/zingchart.min.js";
			wp_register_script("zingchart", $lib_url ,null, '1.0', true);
			wp_enqueue_script('zingchart');
		}
		if ( $this->add_library['chartjs'] ) {
			$this->register_chartjs_scripts();
		}
		if ( $this->add_library['d3'] ) {
			$this->register_d3_scripts();
		}
		if ( $this->add_library['chartist'] ) {
			$this->register_chartist_scripts();
		}
		if ( $this->add_library['smoothie'] ) {
			wp_register_script("smoothie", BC_PLUGIN_URL  . "js/smoothie/smoothie.js", null, null, true);
			wp_enqueue_script('smoothie');
		}
		if ( $this->add_library['flot'] ) {
			$this->register_flot_scripts();
		}
	}
}
	add_action('init', function(){
			new Blazing_Charts();
	});
}

add_filter('user_can_richedit', 'disable_wyswyg_for_custom_post_type');
function disable_wyswyg_for_custom_post_type( $default ){
  global $post;
  if( $post->post_type === 'chart_snippet') {
  	return false;
  }
  return $default;
}

/**
 * [blazing_charts_insert: a function to invoke the chart anywhere in your template
 * @param  [array] $atts [array(
			"charttype" => "",
			"source"    => "",
			"function"  => "",
			"options"   => ""
			);
 * @return [type]       [nothing, it just echoes the output]
 */
function blazing_charts_insert($atts) {
	// to get the object instance
	$shortcode_handler = apply_filters( 'get_blazing_charts_instance', NULL );
	if ( is_a( $shortcode_handler, 'Blazing_Charts' ) ) {
		echo $shortcode_handler->BlazingChart_handler($atts);
	}
}

