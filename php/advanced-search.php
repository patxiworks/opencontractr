<?php

add_action( 'admin_menu', 'register_advanced_search' );

function register_advanced_search() {
    $advsearch_page = add_submenu_page( 
        'edit.php?post_type=open_contract',
        'OpenContractr – Advanced Search',
        'Advanced Search',
        'edit_posts',
        'advancedsearch',
        'advanced_search_page'
    );
	// load the JS conditionally
    add_action( 'load-'.$advsearch_page, 'load_advsearch_files');
}

// this function is only called when our plugin's page loads!
function load_advsearch_files() {
    add_action( 'admin_enqueue_scripts', 'load_advsearch_js' );
}

function load_advsearch_js() {
	$advsearch_js_path = 'js/advanced-search/';
	$jsfiles = get_dir_files($advsearch_js_path); $i=0;
	// styles
	wp_enqueue_style( 'visualsearch', plugin_dir_url(__DIR__) . 'css/visualsearch-datauri.css');
	wp_enqueue_style( 'dynatable', plugin_dir_url(__DIR__) . 'css/jquery.dynatable.css');
    // scripts
    foreach ($jsfiles as $file) {
		$thefile = plugin_dir_path(__DIR__) . $advsearch_js_path . $file;
		if(is_file($thefile)) {
			wp_enqueue_script( 'advsearch-'.$file, plugin_dir_url(__DIR__) . $advsearch_js_path . $file, '', '', true); $i++;
		} else {
			$vsfiles = get_dir_files($advsearch_js_path . $file);
			foreach ($vsfiles as $vsfile)
				wp_enqueue_script( 'advsearch-'.$vsfile, plugin_dir_url(__DIR__) . $advsearch_js_path . $file . '/' . $vsfile, '', '', true); $i++;
		}
    }
}

function advanced_search_page() {
	// get all releases
	$args = array(
		'numberposts' => -1,
		'post_type'   => 'open_contract'
	);
	   
	$posts = get_posts($args);
	$releaselist = array();
	$allreleases = array();
	foreach($posts as $post) {
		$compiledrelease = get_compiled_release($post);
		$compiledrelease['post-id'] = $post->ID;
		$compiledrelease['post-title'] = $post->post_title;
		array_push($releaselist, $compiledrelease);
	}
	$allreleases['releases'] = $releaselist;
	?>
	<script>
		var contracts = <?php echo json_encode($allreleases, true); ?>;
		//console.log(contracts)
	</script>
	<div class="wrap">
		<h1>Advanced OCDS Search</h1>
		<p><?php echo __('These is an advanced tool to search for contracts using their OCDS values','opencontractr'); ?></p>
		
        <div id="ocds_search_container"></div>
        <div id="ocds_search_query"></div>
        <div id="filters"></div>
        <div class="help"></div>
		<div id="result" class='col-md-12 result'>
			<h3 id="result-title"></h3>
			<!--<pre> Result ...  </pre>-->
		</div>
		<table id="contractslist" class="wp-list-table widefat striped contractslist">
          <thead>
            <th>planning/budget/description</th>
          </thead>
          <tbody>
          </tbody>
		</table>
		<form action="#" role="form" id="query-form">
          <div class="form-group">
            <input type="hidden" class="form-control typeahead" id="query" value="">
          </div>
          <button type="button" class="button button-primary pull-right" id="all-records">Download all contracts</button>
		  <button type="button" class="button button-primary pull-right" id="all-records">Download search results only</button>
        </form>
    </div>
	
	<?php
	
}

?>