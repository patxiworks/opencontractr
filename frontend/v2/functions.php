<?php
/**
 * OpenContractr functions and definitions
 *
 *
 * @package WordPress
 * @subpackage OpenContractr
 * @since 1.0
 * @version 1.0
 */

/**
 * Enqueue scripts and styles.
 */

function opencontractr_frontend_scripts() {
	global $wp_styles, $wp_scripts;
	
	if ( get_post_type() == 'open_contract' ) {
		///// remove theme styles
		foreach($wp_styles->queue as $handle) {
			//echo $handle . ' | ';
			if ( explode('-',$handle)[0] != 'opencontractr' ) {
				wp_dequeue_style( $handle );
			}
		}
		foreach($wp_scripts->queue as $handle) {
			if ( explode('-',$handle)[0] != 'opencontractr' ) {
				wp_dequeue_script( $handle );
			}
		}
		
		///// Add opencontractr styles and scripts
		///// common
		wp_enqueue_style( 'opencontractr-custom', plugin_dir_url() . 'opencontractr/frontend/v2/css/font-awesome.min.css' );
		wp_enqueue_style( 'opencontractr-main', plugin_dir_url() . 'opencontractr/frontend/v2/css/main.css' );
		
		wp_enqueue_script( 'opencontractr-scrollex', plugin_dir_url() . 'opencontractr/frontend/v2/js/jquery.scrollex.min.js', array( 'jquery' ), '', true);
		wp_enqueue_script( 'opencontractr-scrolly', plugin_dir_url() . 'opencontractr/frontend/v2/js/jquery.scrolly.min.js', array( 'jquery' ), '', true);
		wp_enqueue_script( 'opencontractr-skel', plugin_dir_url() . 'opencontractr/frontend/v2/js/skel.min.js', array( 'jquery' ), '', true);
		wp_enqueue_script( 'opencontractr-util', plugin_dir_url() . 'opencontractr/frontend/v2/js/util.js', array( 'jquery' ), '', true);
		wp_enqueue_script( 'opencontractr-main', plugin_dir_url() . 'opencontractr/frontend/v2/js/main.js', array( 'jquery' ), '', true);
		
		//if (is_page('searchlight')) {
		if ( $_REQUEST['do'] == 'search' ) {
			///// searchlight styles
			wp_enqueue_style( 'opencontractr-searchlight', plugin_dir_url() . 'opencontractr/frontend/v2/css/searchlight/searchlight.css' );
			wp_enqueue_style( 'opencontractr-dynatable', plugin_dir_url() . 'opencontractr/frontend/v2/css/searchlight/jquery.dynatable.css' );
			
			///// searchlight scripts
			//wp_enqueue_script( 'custom', get_theme_file_uri( 'assets/js/custom.js' ), array( 'jquery' ), '', true);
			wp_enqueue_script( 'json-query', plugin_dir_url() . 'opencontractr/js/common/json_query.js', array( 'jquery' ), '', true);
			
			wp_enqueue_script( 'opencontractr-ocdsdata', plugin_dir_url() . 'opencontractr/frontend/v2/js/searchlight/data/ocds.js', array( 'jquery' ), '', true);
			wp_enqueue_script( 'opencontractr-services', plugin_dir_url() . 'opencontractr/frontend/v2/js/searchlight/data/services.js', array( 'jquery' ), '', true);
			wp_enqueue_script( 'opencontractr-dynatable', plugin_dir_url() . 'opencontractr/frontend/v2/js/searchlight/jquery.dynatable.js', array( 'jquery' ), '', true);
			wp_enqueue_script( 'opencontractr-searchlight', plugin_dir_url() . 'opencontractr/frontend/v2/js/searchlight/searchlight.js', array( 'jquery' ), '', true);
		}
		
		//if (is_page('editor')) {
		if ( isset($_REQUEST['id']) || $_REQUEST['do'] == 'create' ) {
			///// editor styles
			wp_enqueue_style( 'opencontractr-editor', plugin_dir_url() . 'opencontractr/frontend/v2/css/editor.css' );
			wp_enqueue_style( 'opencontractr-datetime-picker-standalone', plugin_dir_url() . 'opencontractr/frontend/v2/css/bootstrap-datetimepicker-standalone.css');
			wp_enqueue_style( 'opencontractr-datetime-picker', plugin_dir_url() . 'opencontractr/frontend/v2/css/bootstrap-datetimepicker.css');
			wp_enqueue_style( 'opencontractr-select2', plugin_dir_url() . 'opencontractr/frontend/v2/css/select2.min.css');
			wp_enqueue_style( 'opencontractr-tooltipster', plugin_dir_url() . 'opencontractr/frontend/v2/css/tooltipster.min.css');
			
			///// editor scripts
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-autocomplete' ); // Enqueue jQuery UI and autocomplete
			wp_enqueue_script( 'opencontractr-fieldfinder', plugin_dir_url() . 'opencontractr/frontend/v2/js/jquery.fieldfinder.js', array( 'jquery' ), '', true);
			wp_enqueue_script( 'opencontractr-select2', plugin_dir_url() . 'opencontractr/frontend/v2/js/select2.full.min.js', array( 'jquery' ), '', true);
			wp_enqueue_script( 'opencontractr-editor', plugin_dir_url() . 'opencontractr/frontend/v2/js/editor.js', array( 'jquery' ), '', true);
			wp_enqueue_script( 'opencontractr-brutusin', plugin_dir_url() . 'opencontractr/js/common/brutusin-json-forms.js', '', '', false);
			wp_enqueue_script( 'opencontractr-moment', plugin_dir_url() . 'opencontractr/frontend/v2/js/moment-with-locales.min.js', '', '', false);
			wp_enqueue_script( 'opencontractr-datetime-picker', plugin_dir_url() . 'opencontractr/frontend/v2/js/bootstrap-datetimepicker.min.js', '', '', false);
			wp_enqueue_script( 'opencontractr-inputmask', plugin_dir_url() . 'opencontractr/frontend/v2/js/jquery.inputmask.bundle.js', '', '', false);
			wp_enqueue_script( 'opencontractr-tooltipster', plugin_dir_url() . 'opencontractr/frontend/v2/js/tooltipster.min.js', '', '', false);
			wp_enqueue_script( 'opencontractr-qualitycheck', plugin_dir_url() . 'opencontractr/js/common/qualitycheck.js', '', '', false);
		}
		
		if ($_REQUEST['do'] == 'create') {
			wp_enqueue_style( 'opencontractr-createCss', plugin_dir_url() . 'opencontractr/frontend/v2/css/style.css');
			
			wp_enqueue_script( 'opencontractr-slidingform', plugin_dir_url() . 'opencontractr/frontend/v2/js/sliding.form.js', array( 'jquery' ), '', true);
		}
	
	}
	
}
add_action( 'wp_enqueue_scripts', 'opencontractr_frontend_scripts', 100 );

// disable frontend admin toolbar
add_filter('show_admin_bar', '__return_false');

/**
 * Register custom query vars
 *
 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/query_vars
 */
function sm_register_query_vars( $vars ) {
	global $meta_search_fields;
	foreach ($meta_search_fields as $metakey=>$value) {
		$vars[] = $metakey;
	}
	return $vars;
} 
add_filter( 'query_vars', 'sm_register_query_vars' );

/**
 * Build a custom query based on several conditions
 * The pre_get_posts action gives developers access to the $query object by reference
 * any changes you make to $query are made directly to the original object - no return value is requested
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/pre_get_posts
 *
 */
function sm_pre_get_posts( $query ) {
	// check if the user is requesting an admin page 
	// or current query is not the main query
	if ( is_admin() || ! $query->is_main_query() ){
		return;
	}

	// edit the query only when post type is 'open_contract'
	// if it isn't, return
	if ( !is_post_type_archive( 'open_contract' ) ){
		return;
	}

	$meta_query = array();
	
	foreach ($meta_search_fields as $metakey=>$value) {
		// add meta_query elements
		if( !empty( get_query_var( $metakey ) ) ){
			$meta_query[] = array( 'key' => $metakey, 'value' => get_query_var( $metakey ), 'compare' => 'LIKE' );
		}
	}

	if( count( $meta_query ) > 1 ){
		$meta_query['relation'] = 'OR';
	}

	if( count( $meta_query ) > 0 ){
		$query->set( 'meta_query', $meta_query );
	}
	//print_r($query);
}
//add_action( 'pre_get_posts', 'sm_pre_get_posts', 1 );