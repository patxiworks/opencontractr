<?php

////// CONTRACTS LISTING /////////

add_filter('manage_open_contract_posts_columns', 'create_contracts_columns');
function create_contracts_columns( $columns ) {
    global $search_fields;
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Contract Title' ),
		'stage' => __( 'Current Stage' ),
		'date' => __( 'Date' ),
		'downloads' => __( 'Downloads' )
	);
	if ( is_admin() && isset($_GET['searchfields']) && $_GET['searchfields'] != '' ) {
		$newfield = array();
		$field = $_GET['searchfields'];
        $newfield[ $field ] = $search_fields[$field][0];
		$newcolumns = array_merge(
								  array_slice($columns, 0, 3, true),
								  $newfield,
								  array_slice($columns, 3, count($columns) - 1, true)
								);
		$columns = $newcolumns;
    }
    return $columns;
}

add_action( 'manage_open_contract_posts_custom_column', 'populate_contracts_columns', 10, 2 );
function populate_contracts_columns( $column_name, $post_id ) {
	global $ocds_stages, $search_fields;
	//$contractor = get_field_data('awards/suppliers/name', $post_id);
    if( $column_name == 'stage' ) {
        $current_stage = get_post_meta( $post_id, 'currentstage', true );
        if($current_stage) {
            echo $ocds_stages[$current_stage][0];
        } else {
			echo 'None specified';
		}
    }
	// display values of extra fields
	if ( is_admin() && isset($_GET['searchfields']) && $_GET['searchfields'] != '' ) {
		$field = $_GET['searchfields'];
		if( $column_name == $field ) {
			echo get_field_data($search_fields[$field][1], $post_id, $column_name);
		}
	}
	if ( $column_name == 'downloads') {
		$postinfo = get_post($post_id);
		echo '<form></form><form method="POST" action="'.get_permalink($post_id).'?action=download" id="downloadsform">';
        printf( '<input type="button" class="button contractbtn" data-type="json" onclick="download(this)" value="%s" />&nbsp;', esc_attr( __( 'JSON' ) ) );
		printf( '<input type="button" class="button contractbtn" data-type="csv" onclick="download(this)" value="%s" />&nbsp;', esc_attr( __( 'CSV' ) ) );
		printf( '<input type="button" class="button contractbtn" data-type="raw" onclick="download(this)" value="%s" />', esc_attr( __( 'Raw' ) ) );
		echo '</form>';
	}
	?>
	<script>
	function download(btn) {
		var type = $(btn).data('type');
		var btnform = $(btn).parent();
		var url = UpdateQueryString('type', type, btnform.attr('action'));
		if (type == 'raw') {
			url = UpdateQueryString('type', 'json', btnform.attr('action'));
            url = UpdateQueryString('display', 'raw', url);
        }
		btnform.attr('action', url);
		btnform.submit();
	}
	
	function UpdateQueryString(key, value, url) {
		if (!url) url = window.location.href;
		var re = new RegExp("([?&])" + key + "=.*?(&|#|$)(.*)", "gi"),
			hash;
	
		if (re.test(url)) {
			if (typeof value !== 'undefined' && value !== null)
				return url.replace(re, '$1' + key + "=" + value + '$2$3');
			else {
				hash = url.split('#');
				url = hash[0].replace(re, '$1$3').replace(/(&|\?)$/, '');
				if (typeof hash[1] !== 'undefined' && hash[1] !== null) 
					url += '#' + hash[1];
				return url;
			}
		}
		else {
			if (typeof value !== 'undefined' && value !== null) {
				var separator = url.indexOf('?') !== -1 ? '&' : '?';
				hash = url.split('#');
				url = hash[0] + separator + key + '=' + value;
				if (typeof hash[1] !== 'undefined' && hash[1] !== null) 
					url += '#' + hash[1];
				return url;
			}
			else
				return url;
		}
	}
	</script>
	<?php
}

function get_field_data($path, $post_id, $column_name) {
	$fields = explode("/", $path);
	//return $fields[0];
	// get the path from $search_fields without the stage
	$data = json_decode(get_post_meta( $post_id, $fields[0], true ), true);
	unset($fields[0]);
	$searchq = implode('/', $fields);
	// run the search
	$column_value = rtrim(get_field_values($data, '/'.$searchq), ', ');
	// store as post meta so that it can be filtered/searched
	set_field_data($column_name, $column_value, $post_id);
	return $column_value;
}

function do_field_value_edit($column_name, $column_value) {
	//if ($column_name == )
}

function set_field_data($metakey, $metavalue, $post_id) {
	if (! add_post_meta( $post_id, $metakey, $metavalue, true )) {
		update_post_meta( $post_id, $metakey, $metavalue );
	}
}

function get_field_values($array, $field, $path="") {
	//print_r($array);
    $output = array();
	if (is_array($array) || is_object($array)) {
		foreach($array as $key => $value) {
			if( is_array($value) ) {
				if (!empty($path)) {
					if (!is_numeric($key)) {
						$newpath = $path.$key."/";
					} else {
						$newpath = $path;
					}
				} else {
					if (!is_numeric($key)) {
						$newpath = $key."/";
					} else {
						$newpath = "/";
					}
				}
				$result .= get_field_values($value, $field, $newpath);
			} else {
				//$path = $path ? $path : "/";
				if ( ltrim($path.$key, '/') != ltrim($field, '/') ) {
					$output[$path.$key] = $value;
				} else {
					$result .= $value.', ';
				}
			}
		}
	}
	return $result;
}

function isAssoc(array $arr) {
	if (array() === $arr) return false;
	return array_keys($arr) !== range(0, count($arr) - 1);
}

add_filter("manage_edit-open_contract_sortable_columns", "columns_register_sortable" );
function columns_register_sortable( $columns ) {
	global $search_fields;
	$columns['stage']  = 'currentstage';
	if ( is_admin() && isset($_GET['searchfields']) && $_GET['searchfields'] != '' ) {
		$field = $_GET['searchfields'];
		if ($search_fields[$field][2]) $columns[$field] = $field;
	}
    return $columns;
}

add_action( 'pre_get_posts', 'contracts_orderby' );
function contracts_orderby( $query ) {
    if( ! is_admin() )
        return;
 
    $orderby = $query->get( 'orderby');
	
	if( 'currentstage' == $orderby ) {
		$query->set('meta_key','currentstage');
		$query->set('orderby','meta_value');
	}
 
	if ( is_admin() && isset($_GET['searchfields']) && $_GET['searchfields'] != '' ) {
		$field = $_GET['searchfields'];
		if( $field == $orderby ) {
			$query->set('meta_key',$field);
			$query->set('orderby','meta_value');
		}
	}
}

// Not needed anymore, since the fields have been ordered in the $columns definition
add_filter('manage_posts_columns', 'manage_contracts_columns');
function manage_contracts_columns( $columns ) {  
	
	$fieldlist = array('stage');
    $new_columns = array();
	$fields = array();
	for ($i=0; $i < count($fieldlist); $i++) {
		$fields[$i] = $columns[$fieldlist[$i]];  // save the stage column
		unset($columns[$fieldlist[$i]]);   // remove it from the columns list
	}

    foreach($columns as $key=>$value) {
        if($key=='date') {  // when we find the date column
			for ($i=0; $i < count($fields); $i++) {
				$new_columns[$fieldlist[$i]] = $fields[$i];  // put the stage column before it
			}
        }    
        $new_columns[$key]=$value;
    }  

    return $new_columns;  
}


// from https://pastebin.com/tabUfh3Y (https://wordpress.stackexchange.com/a/45447)
add_action( 'restrict_manage_posts', 'acs_admin_posts_filter_restrict_manage_posts' );
/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 * 
 * @author Ohad Raz
 * 
 * @return void
 */
function acs_admin_posts_filter_restrict_manage_posts(){
	global $ocds_stages, $search_fields;
    $type = 'open_contract';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    //only add filter to post type you want
    if ('open_contract' == $type){
        //change this to the list of values you want to show
        //in 'label' => 'value' format
        $stages = $ocds_stages;
        ?>
        <select name="contractstage">
        <option value=""><?php _e('Filter By Stage', 'opencontractr'); ?></option>
        <?php
            $current_v = isset($_GET['contractstage'])? $_GET['contractstage']:'';
            foreach ($stages as $label => $stage) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $label,
                        $label == $current_v? ' selected="selected"':'',
                        $stage[0]
                    );
                }
        ?>
        </select>
        <?php
		$fields = $search_fields;
        ?>
        <select name="searchfields">
        <option value=""><?php _e('Add a field', 'opencontractr'); ?></option>
        <?php
            $current_v = isset($_GET['searchfields'])? $_GET['searchfields']:'';
            foreach ($fields as $label => $field) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $label,
                        $label == $current_v? ' selected="selected"':'',
                        $field[0]
                    );
                }
        ?>
        </select>
		<?php
    }
}


add_filter( 'parse_query', 'acs_posts_filter' );
/**
 * if submitted filter by post meta
 * 
 * @author Ohad Raz
 * @param  (wp_query object) $query
 * 
 * @return Void
 */
function acs_posts_filter( $query ){
    global $pagenow;
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }
    if ( 'open_contract' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['contractstage']) && $_GET['contractstage'] != '') {
        $query->query_vars['meta_key'] = 'currentstage';
        $query->query_vars['meta_value'] = $_GET['contractstage'];
    }
}


// search filter
add_filter('posts_join', 'contracts_search_join' );
function contracts_search_join ($join){
    global $pagenow, $wpdb;
    // filter only when performing a search on edit page of Custom Post Type named "open_contract"
    if ( is_admin() && $pagenow=='edit.php' && $_GET['post_type']=='open_contract' && $_GET['s'] != '') {    
        $join .='LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    return $join;
}

add_filter( 'posts_where', 'contracts_search_where' );
function contracts_search_where( $where ){
    global $pagenow, $wpdb;
    // filter only when performing a search on edit page of Custom Post Type named "open_contract"
    if ( is_admin() && $pagenow=='edit.php' && $_GET['post_type']=='open_contract' && $_GET['s'] != '') {
        $where = preg_replace(
       "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
       "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }
    return $where;
}

add_filter( 'posts_distinct', 'contracts_search_distinct' );
function contracts_search_distinct( $where ){
    global $pagenow, $wpdb;
    if ( is_admin() && $pagenow=='edit.php' && $_GET['post_type']=='open_contract' && $_GET['s'] != '') {
		return "DISTINCT";
    }
    return $where;
}

/*
function custom_search_query( $query ) {
    $custom_fields = array(
        // put all the meta fields you want to search for here
        "cost",
        "contractor",
		"title"
    );
    $searchterm = $query->query_vars['s'];

    // we have to remove the "s" parameter from the query, because it will prevent the posts from being found
    $query->query_vars['s'] = "";

    if ($searchterm != "") {
        $meta_query = array('relation' => 'OR');
        foreach($custom_fields as $cf) {
            array_push($meta_query, array(
                'key' => $cf,
                'value' => $searchterm,
                'compare' => 'LIKE'
            ));
        }
        $query->set("meta_query", $meta_query);
    }
	print_r($query);
}
add_filter( "pre_get_posts", "custom_search_query");
*/

add_filter( 'bulk_actions-edit-open_contract', 'register_download_bulk_actions' );

function register_download_bulk_actions($bulk_actions) {
	$bulk_actions['download_bulk_action_json'] = __( 'Download as JSON', 'open_contractr');
	$bulk_actions['download_bulk_action_csv'] = __( 'Download as CSV', 'open_contractr');
	return $bulk_actions;
}

add_filter( 'handle_bulk_actions-edit-open_contract', 'download_bulk_action_handler', 10, 3 );
 
function download_bulk_action_handler( $redirect_to, $action_name, $post_ids ) {
	$postids = array();
	foreach ( $post_ids as $post_id ) { 
		array_push($postids, get_post($post_id)); 
	}
	$json = get_releases($postids);
	$releaseCount = count( $post_ids );
	$downloadtime = time();
	
	if ( 'download_bulk_action_json' === $action_name ) {
		$data = stripslashes(json_encode($json, JSON_PRETTY_PRINT));
		header('Content-Disposition: attachment; filename="releases_'.$releaseCount.'_'.$downloadtime.'.json"');
		header('Content-type: application/json');
		echo $data;exit;
		$redirect_to = add_query_arg( 'bulk_json', $postids, $redirect_to );
		
		return $redirect_to;
		
	} 

	elseif ( 'download_bulk_action_csv' === $action_name ) { 
		$flatjson = multi_nested_values($json['releases']);
		$data = multi_putcsv($flatjson);
		header('Content-Disposition: attachment; filename="releases_'.count( $post_ids ).'_'.$downloadtime.'.csv"');
		header('Content-type: text/csv');
		echo $data;exit;
		$redirect_to = add_query_arg( 'bulk_csv', count( $post_ids ), $redirect_to );
		return $redirect_to; 
	}
  
	else 
		return $redirect_to;
}


?>