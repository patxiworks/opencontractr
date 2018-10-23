<?php

/*
 * Plugin Name: OpenContractr
 * Plugin URI: 
 * Description: Plugin to manage OCDS records
 * Version: 0.1.0
 * Author: Patrick Enaholo
 * Author URI: http://opendata.smc.edu.ng
 * License: GPL v2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: opencontractr
 * Domain Path: 
 */

 define('OPENCONTRACTR_ABS_PATH', WP_PLUGIN_DIR.'/opencontractr/opencontractr/');
 define('OPENCONTRACTR_ABS_URL', WP_PLUGIN_URL.'/opencontractr/opencontractr/');
 define('OPENCONTRACTR_FRONTEND_PATH', OPENCONTRACTR_ABS_PATH.'frontend/');
 define('OPENCONTRACTR_FRONTEND_URL', OPENCONTRACTR_ABS_URL.'frontend/');
 define('OPENCONTRACTR_SCHEMA_PATH', OPENCONTRACTR_ABS_PATH.'schema/');
 define('OPENCONTRACTR_SCHEMA_URL', OPENCONTRACTR_ABS_URL.'schema/');
 define('OPENCONTRACTR_REL_PATH', '/opencontractr/opencontractr/');

require_once('php/simple-search.php');
require_once('php/advanced-search.php');
require_once('php/settings.php');
require_once('php/tools.php');
require_once('php/schema.php');
require_once('frontend/v2/functions.php');

 // Exit if accessed directly.
if( !defined( 'ABSPATH' ) ) exit;

/*******************
  GLOBAL VARIABLES
 *******************/

	$general_options = get_option( 'general_options' );
	$publisher_options = get_option( 'publisher_options' );
	$organisation_options = get_option( 'organisation_options' );
	
    $prefix = $general_options['ocds_prefix'];
    $publisher = $publisher_options['publisher_scheme'];
    $datapath = '/wp-content/data/';
	
	$ocds_sections = array(
        "parties"=> array("Parties", __("Information about the organisations and other participants involved in this project","opencontractr")),
        "buyer"=> array("Buyer", __("The procuring entity for this project","opencontractr")),
        "planning"=> array("Planning", __("Information about goals, budgets and projects related to this project","opencontractr")),
        "tender"=> array("Tender", __("Information about how the tender taking place for this contracting process","opencontractr")),
        "awards"=> array("Awards", __("Details on awards made as part of this contracting process","opencontractr")),
        "contracts"=> array("Contracts", __("Details on contracts signed as part of this contracting process","opencontractr"))
    );
	
	$ocds_stages = $ocds_sections;
	unset($ocds_stages['parties'], $ocds_stages['buyer']);
	//$ocds_stages['implementation'] = array("Implementation", __("Details on contract implementation","opencontractr"));

    $ocds_metadata = array(
        "ocid",
        "id",
        "tag",
        "initiationType",
        "date",
        "language"
    );
	
	$ocds_extensions = array();
	
	$ocds_metadata_desc = '<span class="desc"> – Contextual information about each release of data</span>';
	
	// TODO: ideally, these variables should be stored in and captured from a JSON file
	$search_fields = array(
		// Column title, ocds path, sortable, description
        "contractor"=> array("Contractor", 'awards/suppliers/name', false, __("The suppliers for this contract","opencontractr")),
        "cost"=> array("Amount Awarded", 'awards/value/amount', false, __("The amount awarded for this contract","opencontractr")),
		"contractstatus"=> array("Contract Status", 'contracts/status', false, __("The status of this contract","opencontractr")),
		"awarddate"=> array("Contract Award Date", 'awards/date', false, __("The date this contract was awarded","opencontractr")),
		"tendermethod"=> array("Type of Tendering", 'tender/procurementMethod', false, __("The type of tendering or the procurement method","opencontractr")),
		"contracttimeleft"=> array("Time left for Completion", 'contracts/period/endDate', false, __("The time left for the completion of the contract","opencontractr"))
    );
	
	
	
add_action( 'admin_enqueue_scripts', 'load_renderjson' );
function load_renderjson() {
    wp_enqueue_script( 'jquery-js', plugin_dir_url(__FILE__) . 'js/common/jquery.min.js', '', '', false);
    wp_enqueue_script( 'render-json-js', plugin_dir_url(__FILE__) . 'js/common/renderjson.js', '', '', false);
    wp_enqueue_script( 'brutusin-json-js', plugin_dir_url(__FILE__) . 'js/common/brutusin-json-forms.js', '', '', false);
	wp_enqueue_script( 'lodash-js', plugin_dir_url(__FILE__) . 'js/common/lodash.min.js', '', '', false);
	wp_enqueue_script( 'convert-js', plugin_dir_url(__FILE__) . 'js/common/ocds_convert.js', '', '', false);
	wp_enqueue_script( 'qualitycheck', plugin_dir_url(__FILE__) . 'js/common/qualitycheck.js', '', '', false);
}
	

/**
 * Register Contract post type.
 *  
 * 
 */
function oc_register_post_type() {

    $labels = array(
        'name'                  => _x( 'Contracts', 'Post type general name', 'opencontractr' ),
        'singular_name'         => _x( 'Contract', 'Post type singular name', 'opencontractr' ),
        'menu_name'             => _x( 'Contracts', 'Admin Menu text', 'opencontractr' ),
        'name_admin_bar'        => _x( 'Contracts', 'Add New on Toolbar', 'opencontractr' ),
        'parent_item_colon'     => _x( 'Parent Contract', 'opencontractr' ),
		'all_items'             => _x( 'All Contracts', 'opencontractr' ),
		'view_item'             => _x( 'View Contract', 'opencontractr' ),
		'add_new_item'          => _x( 'Add New Contract', 'opencontractr' ),
		'add_new'               => _x( 'Add New Contract', 'opencontractr' ),
		'edit_item'             => _x( 'Edit Contract', 'opencontractr' ),
		'update_item'           => _x( 'Update Contract', 'opencontractr' ),
		'search_items'          => _x( 'Search Contract', 'opencontractr' ),
		'not_found'             => _x( 'Not Found', 'opencontractr' ),
		'not_found_in_trash'    => _x( 'Not found in Trash', 'opencontractr' ),
	);

    $args = array(
        'label'              => __( 'contracts', 'opencontractr' ),
        'description'        => __( 'Available contracts', 'opencontractr' ),
	    'labels'             => $labels,
	    'public'             => true,
	    'publicly_queryable' => true,
	    'show_ui'            => true,
	    'show_in_menu'       => true,
        'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'can_export'         => true,
	    'query_var'          => true,
	    'rewrite'            => array( 'slug' => 'contract' ),
	    'capability_type'    => 'post',
	    'has_archive'        => true,
	    'hierarchical'       => false,
	    'menu_position'      => null,
	    'supports'           => array( 'title', 'editor' ),
	    'menu_icon'			 => 'dashicons-screenoptions', //plugins_url( 'images/image.png', __FILE__ ),
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
        'taxonomies'          => array( '' ),
		'supports'            => array( 'title', 'custom-fields' ),
		//'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),

    );

	register_post_type( 'open_contract', $args );

}
add_action( 'init', 'oc_register_post_type' );

/**
 * Register Item Type taxonomy.
 * 
 */
function oc_create_taxonomy() {

    $labels = array(
        'name'              => _x( 'Contract Types', 'taxonomy general name', 'opencontractr' ),
        'singular_name'     => _x( 'Contract Type', 'taxonomy singular name', 'opencontractr' ),
    );
 
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'contract-type' ),
    );
    
    // not yet...
	//register_taxonomy( 'oc_item_type', 'oc_contract', $args );

}
// not yet...
//add_action( 'init', 'pp_create_taxonomy' );

add_filter( 'default_hidden_meta_boxes', 'hide_customfield_metabox', 10, 2 );
function hide_customfield_metabox( $hidden, $screen )
{
    $hidden = array( 'postcustom' );
    return $hidden;
}



/*******************
  PLUGIN ACTIVATION
 *******************/

//global $opencontractr;
//$opencontractr = new OpenContractr_Activation();

class OpenContractr_Activation {
    public function plugin_activated(){
		global $datapath;
		//  When the plugin is activated...
        // ...create the data directory
		//wp_mkdir_p(rtrim(ABSPATH, '/') . $datapath);
		// ...create a new open_contract post-type
		if ( empty( get_posts('post_type=open_contract') ) ) {
			$newpost = array(
				'post_title' => 'Sample contract',
				'post_type' => 'open_contract',
				'post_status' => 'publish'
			);
			$post_id = wp_insert_post( $newpost );
		}
    }

    public function plugin_deactivated(){
         // This will run when the plugin is deactivated, e.g. use to delete from the database
    }
}

register_activation_hook( __FILE__, array('OpenContractr_Activation', 'plugin_activated' ));
    

/*******************
  GENERATE META-DATA
 *******************/

add_action('wp_insert_post', 'save_new_opencontract');

function save_new_opencontract($postid, $newocid='') {
	$post = get_post($postid);
	if ($post->post_type == 'open_contract') {
		// TODO: get prefix, publisher, datapath from plugin settings
		global $prefix, $publisher, $datapath;
		$fulldatapath = ABSPATH . $datapath;
		$newocid = generate_ocid($prefix, $postid, 8, $publisher);
		// create ocid post_meta and then create directory
		//$post = get_post($postid);
		if (add_post_meta( $postid, 'ocid', $newocid, true )) {
			create_ocid_dir($fulldatapath, $newocid);
		}
	}
}

function create_release_file($ocid, $releaseid, $filecontent) {
    global $datapath;
	if ( function_exists('get_home_path') ) {
		$fulldatapath = get_home_path() . $datapath . $ocid . '/' . $releaseid . '.json';
	} else {
		$fulldatapath = ABSPATH . $datapath . $ocid . '/' . $releaseid . '.json';
	}
    $fh = fopen($fulldatapath, "w");
    fwrite($fh, $filecontent);
}

function create_ocid_dir($fulldatapath, $ocid) {
    wp_mkdir_p($fulldatapath . $ocid);
}

function generate_ocid($prefix, $postid, $length, $publisher) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $random = '';
    for ($i = 0; $i < $length; $i++) {
        $random .= $characters[rand(0, strlen($characters) - 1)];
    }
    return 'ocds-' . $prefix . '-' . $random . '-' . $postid . '-' . $publisher;
}



/*******************
  META BOXES
 *******************/

add_action( 'admin_enqueue_scripts', 'load_common_files' );

function load_common_files() {
    // main custom style
	wp_enqueue_style( 'main-custom', plugin_dir_url(__FILE__) . 'css/custom.css');
	wp_enqueue_style( 'font-awesome', plugin_dir_url(__FILE__) . 'css/font-awesome.min.css');
}

add_filter( 'get_user_option_meta-box-order_open_contract', 'metabox_order' );
function metabox_order( $order ) {
    return array(
        'normal' => join( 
            ",", 
            array(  // Arrange here as desired
                'stage_meta_box',
				'sections_meta_box',
                'release_tags_meta_box',
                'extensions_meta_box',
				'downloads_meta_box',
            )
        ),
    );
}

add_action( 'add_meta_boxes', 'ocds_meta_boxes' );

function ocds_meta_boxes() {
    $screen = get_current_screen();
    global $ocds_sections, $ocds_metadata_desc, $ocds_extensions_desc;
    //$arrlength = count($ocds_sections);
    if('add' != $screen->action ) {
		add_meta_box( 'stage_meta_box', __('Stages','opencontractr'), 'display_stage_meta_box', 'open_contract', 'normal', 'high');
		add_meta_box( 'metadata_meta_box', __('Meta-data '.$ocds_metadata_desc,'opencontractr'), 'display_metadata_meta_box', 'open_contract', 'normal', 'high');
		add_meta_box( 'sections_meta_box', __('Sections','opencontractr'), 'display_sections_meta_box', 'open_contract', 'normal', 'high');
		foreach ($ocds_sections as $key=>$section) {
            add_meta_box( $section[0].'_meta_box', __($section[0] . ' <span class="desc">– ' . $section[1] . '</span>','opencontractr'), 'display_ocds_section_meta_box', 'open_contract', 'normal', 'low', array(strtolower($section[0])));
			// add classes to the meta box
			add_filter( 'postbox_classes_open_contract_'.$section[0].'_meta_box', 'add_section_classes' );
        }
		add_meta_box( 'extensions_meta_box', __('Extensions','opencontractr'), 'display_extensions_meta_box', 'open_contract', 'normal', 'high');
        add_meta_box( 'release_tags_meta_box', __('Releases','opencontractr'), 'display_release_tags_meta_box', 'open_contract', 'side', 'high');
		add_meta_box( 'downloads_meta_box', __('Downloads','opencontractr'), 'display_downloads_meta_box', 'open_contract', 'normal', 'low');
		// side boxes – for extra services
		add_meta_box( 'validate_meta_box', __('Validate','opencontractr'), 'display_validate_meta_box', 'open_contract', 'side', 'low');
		add_meta_box( 'import_meta_box', __('Import','opencontractr'), 'display_import_meta_box', 'open_contract', 'side', 'low');
    }
}

function display_stage_meta_box( $post ) {
	//print_r($_POST);
	global $ocds_stages;
	if ($_POST && $_POST['currentstage']) {
		$currentstage = $_POST['currentstage'];
		// update current stage
		if (! add_post_meta( $post->ID, 'currentstage', $currentstage, true )) {
            update_post_meta( $post->ID, 'currentstage', $currentstage );
        }
	} else {
		$currentstage = get_post_meta( $post->ID, 'currentstage', true );
	}
	$appendstage = ($currentstage) ? '<span class="stageflag">(Current stage is <strong>'.$ocds_stages[$currentstage][0].'</strong>)<span>' : '';
	echo '<p><span class="step">1</span>What stage is this contract?&nbsp;';
    echo '<select id="selectstage" name="selectstage">';
    echo '<option value="">Choose a contract stage...</option>';
    foreach($ocds_stages as $key=>$stage) {
        echo "<option value='".$key."'>".$stage[0]."</option>";
    }
    echo '</select>' . $appendstage . '</p>';
	?>
	<script>
	$(document).ready(function() {
		$('#selectstage').on('change', function() {
			$this = $(this);
			var stageval = $this.val();
			$.each($('.checkstage'), function(item, el) {
				if (stageval) {
					if (stageval == $(el).val()) {
						$(el).prop('checked', true).change(); // the change event here is to enable the releasetag select element
						$(el).prop('disabled', true);
					} else {
						$(el).prop('checked', false).change(); // ... to disable the releasetag select element
						$(el).prop('disabled', false);
					}
				} else {
					$(el).prop('checked', false).change();
					$(el).prop('disabled', true);
				}
			});
		});
	});
	</script>
	<?php
}

function display_sections_meta_box() {
	global $ocds_stages;
	echo '<p><span class="step">2</span>Choose the sections you would like to add to this stage (optional)</p>';
	?>
	<script>
	// create grouped meta-boxes
	$(document).ready(function() {
		//$("#normal-sortables").prepend('<div id="sections_div" class="postbox meta-box-sortables ui-sortable"><div class="handlediv" title="Click to toggle."><br></div><h3 class="hndle"><span>OCDS Sections</span></h3><div id="sections_inside" class="inside"></div></div>');
		$("#metadata_meta_box").appendTo("#sections_meta_box > .inside");
		$(".sectionbox").each(function(i, el) {
			var box = $(el).attr('id');
			$('#'+box).appendTo("#sections_meta_box > .inside");
		});
	});
	</script>
	<?php
}

function add_section_classes( $classes=array() ) {
    /* In order to ensure we don't duplicate classes, we should
        check to make sure it's not already in the array */
    if( !in_array( 'sectionbox', $classes ) )
        $classes[] = 'sectionbox';

    return $classes;
}

function display_metadata_meta_box( $post ) {
    //print_r($_POST);
    global $ocds_metadata;
	// Ensure that $_POST['targetimport'] is not set to create a new post – in cases when data is imported
	// New posts are dealt with elsewhere
    if (isset($_POST['savedjson']) && !$_POST['targetimport']) {
        $savedjson_raw = stripslashes($_POST['savedjson']);
        // convert savedjson to php array
        $savedjson = json_decode($savedjson_raw, TRUE);
        // save to file
        create_release_file($savedjson['ocid'], $savedjson['id'], $savedjson_raw);
        // get all metadata fields
        $metadata = array(); $metadataid = array();
        $existingmeta = json_decode(get_post_meta( $post->ID, 'metadata', true ), TRUE);
        $releaseids = get_post_meta( $post->ID, 'releaseids', true );
		// loop through ocds_metadata array to extract release tag and id for the project's metadata
        for ($i=0; $i<count($ocds_metadata); $i++) {
            if ($ocds_metadata[$i] == 'tag' && $existingmeta && is_array($existingmeta['tag']) && !empty($savedjson['tag'])) {
				// append tag to metadata tag field
				if (!in_array($savedjson['tag'][0], $existingmeta['tag'])) {
					$metadata[$ocds_metadata[$i]] = array_merge($existingmeta['tag'], $savedjson['tag']);
				} else {
					$metadata[$ocds_metadata[$i]] = $existingmeta['tag'];
				}
            } elseif ($ocds_metadata[$i] == 'id') {
				// replace metadata id with id from saved json
				$metadata['id'] = $savedjson['id'];
                if ($releaseids) {
                    $metadataid = $releaseids.','.$savedjson['id'];
                } else {
                    $metadataid = $savedjson['id'];
                }
                //print_r($metadataid);
            } else {
                $metadata[$ocds_metadata[$i]] = $savedjson[$ocds_metadata[$i]];
            }
        }
        $metadatajson = json_encode($metadata);
        //echo $releaseids;
        // add post meta for metadata
        if (! add_post_meta( $post->ID, 'metadata', $metadatajson, true )) {
            update_post_meta( $post->ID, 'metadata', $metadatajson );
        }
        // add post meta for release ids
        if (! add_post_meta( $post->ID, 'releaseids', $metadataid, true )) {
            update_post_meta( $post->ID, 'releaseids', $metadataid );
        }
    }
    ?>
    <table>
        <tr>
            <td id="rendered-metadata"></td>
        </tr>
    </table>
    <script>
    <?php if (get_post_meta( $post->ID, 'metadata', true )) { ?>
    document.getElementById("rendered-metadata").appendChild(
    renderjson.set_show_to_level(0)
              .set_max_string_length(100)
                (<?php echo get_post_meta( $post->ID, 'metadata', true ); ?>));
    <?php } ?>
    </script>
    <?php
}

function display_ocds_section_meta_box( $post, $callback_args ) {
    //print_r($_POST);
    wp_nonce_field( basename( __FILE__ ), 'opencontractr_nonce' );
    $ocid = get_post_meta( $post->ID, 'ocid', true );
    $section = $callback_args['args'][0];
    ?>
    <table>
        <tr>
            <td id="rendered-<?php echo $section ?>"></td>
        </tr>
        <tr>
            <td><form></form> <!-- a dummy form to ensure that the DOM does not remove subsequent form tags -->
                <form method="POST" action="admin.php?page=<?php echo $section; ?>&post=<?php echo $post->ID; ?>&ocid=<?php echo $ocid; ?>&tab=<?php echo $section; ?>" id="sectionform">
                    <textarea name="editjson" id="editjson" style="display: none;"><?php echo get_post_meta( $post->ID, $section, true ); ?></textarea>
                    <input type="hidden" name="editsection" value="<?php echo $section; ?>">
					<?php if ($section != 'parties') { ?>
						<input type="checkbox" class="sectioncheck checkstage" value="<?php echo $section; ?>" disabled="disabled"> Select <?php echo $section; ?> section
					<?php } else { ?>
						<input type="checkbox" class="sectioncheck" name="editsections" value="<?php echo $section; ?>" checked="checked" style="display: none">
						<!--<input type="button" name="editsubmit" id="edit_<?php echo $section; ?>_button" class="button" value="Edit <?php echo $section; ?> section">-->
					<?php } ?>
                </form>
        </tr>
        
    </table>
    <script>
	<?php
	$postmeta = get_post_meta( $post->ID, $section, true );
	if ($postmeta) {
	?>
	document.getElementById("rendered-<?php echo $section ?>").appendChild(
		renderjson.set_show_to_level(0)
			.set_max_string_length(100)
				(<?php echo $postmeta; ?>));
		
    <?php } ?>
	// submit routine for the parties section
	$('#edit_parties_button').click(function() {
		partiesjson = JSON.parse($('#editjson').html());
		filteredjson = {};
		filteredjson['parties'] = partiesjson;
		$('form#sectionform #editjson').val(JSON.stringify(filteredjson));
		$('#sectionform').submit();
	})
    </script>
    <?php
    // set post_meta
    if (!get_post_meta( $post->ID, $section, true )) {
        add_post_meta( $post->ID, $section, '', true );
    }
	
}

function display_release_tags_meta_box( $post ) {
    //print_r($_POST);
    global $release_tags, $ocds_sections;
	// Ensure that $_POST['targetimport'] is not set to 'new post' (which is specifically for cases when data is imported)
	// New posts are dealt with elsewhere (see below)
    if (isset($_POST['savedsections']) && $_POST['targetimport'] != 'newpost') {
        $savedsections = $_POST['savedsections'];
        $savedjson = stripslashes($_POST['savedjson']);
		$currentstage = $_POST['currentstage'];
        // convert savedjson to php array
        $savedjson = json_decode($savedjson, TRUE);
        // update individual ocds sections with data from the created release
        $savedsections = explode(',', $savedsections);
        for ($i=0; $i<count($savedsections); $i++) {
            $sectionjson = json_encode($savedjson[$savedsections[$i]]);
			if ($sectionjson != 'null') {
				if (! add_post_meta( $post->ID, $savedsections[$i], $sectionjson, true )) {
					update_post_meta( $post->ID, $savedsections[$i], $sectionjson );
				}
			}
        }
		// create/update other post meta for searchability
		create_search_post_meta($post->ID);
		$saved_post = array(
			'ID' => $post->ID,
			'post_title' => get_post_meta( $post->ID, 'projecttitle', true ),
			'post_content' => get_post_meta( $post->ID, 'description', true )
		);
		wp_update_post( $saved_post );
    }
    $ocid = get_post_meta( $post->ID, 'ocid', true );
    $mergedsections = array();
    //for ($i=0; $i<count($ocds_sections); $i++) {
	foreach ($ocds_sections as $key=>$section) {
        $sectionkey = strtolower($section[0]);
		$sectionvalue = get_post_meta( $post->ID, $sectionkey, true );
		if ($sectionvalue) {
			$mergedsections[$sectionkey] = json_decode($sectionvalue, TRUE);
		}
    }
    echo '<p><span class="step">3</span>Choose a release tag and click on the button to edit the contract</p>';
    echo '<textarea id="fulljson" name="fulljson" style="display:none">'.json_encode($mergedsections).'</textarea>';
    wp_nonce_field( basename( __FILE__ ), 'opencontractr_nonce' );
    echo '<form method="POST" id="releasetagform" name="releasetagform" action="">';
	echo '<table class="releasetaglist">';
	foreach($release_tags as $tag => $value) {
		echo '<tr class="'.$value[1].' releasetagrow">';
		echo '<td><input type="radio" class="releasetag" name="releasetag" value="'.$tag.'"></td>';
		echo '<td>'.$value[0].'</td>';
		echo '</tr>';
	}
	echo '</table>';
    echo '<input type="hidden" name="ocid" id="ocid" value="'.$ocid.'">';
	echo '<input type="hidden" name="currentstage" id="currentstage" value="">';
    echo '<input type="hidden" name="editsections" id="editsections" value="">';
    echo '<textarea id="editjson" name="editjson" style="display:none"></textarea>';
	echo '<input type="hidden" name="validationerrorpath" id="validationerrorpath" value="">';
    echo '<input type="button" id="submit_release_tag" value="Create new release" class="button button-primary">';
	echo '&nbsp;<span id="submiterror" class="errormessage"></span>';
    echo '</form>';
	
	echo '<div id="record-view-box" style="display:none"><div id="recordjson"></div></div>';
	echo '<a href="#TB_inline?width=600&height=550&inlineId=record-view-box" class="thickbox record" title="Current Record"></a>';
	add_thickbox();
    ?>
    <script>
		$(document).ready(function() {
			$('.releasetagrow').hide();
			$('.sectioncheck').change(function() {
				if (this.checked) {
					// enable release tag in dropdown list
					//$('select#releasetag option[data-stage="'+this.value+'"]').removeAttr('disabled');
					$('tr.'+this.value).show()
				} else {
					// disable release tag
					//$('select#releasetag option[data-stage="'+this.value+'"]').attr('disabled', 'disabled');
					$('tr.'+this.value).find('input.releasetag').prop('checked', false)
					$('tr.'+this.value).hide()
				}
			})
			$('#submit_release_tag').click(function() {
				//if ($('select#releasetag').val()) {
				if ($('input.releasetag:checked').is(':checked')) {
					$('#submiterror').html('');
					var selectedtags = $('input.releasetag:checked').val();
					var selectedsections = $('.sectioncheck:checkbox:checked');
					var currentstage = $('#selectstage').val();
					var sectionlist = [];
					for (i=0; i<selectedsections.length; i++) {
						sectionlist.push($(selectedsections[i]).val())
					}
					// get the post_meta for the section
					fulljson = JSON.parse($('#fulljson').html());
					filteredjson = {};
					for (i=0; i<sectionlist.length; i++) {
						filteredjson[sectionlist[i]] = fulljson[sectionlist[i]];
					}
					if ($(selectedsections[0]).val()) {
						$('form#releasetagform #editsections').val(sectionlist);
						$('form#releasetagform #editjson').val(JSON.stringify(filteredjson));
						$('form#releasetagform #currentstage').val(currentstage);
						$('form#releasetagform').attr('action', 'admin.php?page='+$(selectedsections[1]).val()+'&post=<?php echo $post->ID; ?>&ocid=<?php echo $ocid; ?>&tab='+$(selectedsections[1]).val());
						$('form#releasetagform').submit();
					} 
				} else {
					$('#submiterror').html('Please select at least one stage and a release tag');
				}
			});
			
			$('#recordview').click(function() {
				$('#recordjson').html(renderjson.set_show_to_level(0)
				  .set_max_string_length(100)(<?php //print_r(get_current_record($post)); ?>));
				$('#recordjson').html('<h1>Feature coming soon</h1>');
				$('.thickbox.record').click();
			});
			
		})
    </script>
    <?php
}

function display_extensions_meta_box( $post ) {
	?>
	<table>
        <tr>
            <td>Extensions are sections, building blocks or fields <a href="http://standard.open-contracting.org/latest/en/extensions/" target="_blank">which can be published as part of the OCDS</a>.</td>
        </tr>
	</table>
	<?php
}

function display_downloads_meta_box( $post ) {
	// List of releases
	$releaseids = explode(',', get_post_meta( $post->ID, 'releaseids', true ));
	if ($releaseids != '') {
		echo '<p><em>View an existing release</em></p>';
		echo '<select id="releaseids">';
		echo '<option value="">Choose a release ID</option>';
		for ($i=0; $i<count($releaseids); $i++) {
			echo '<option value="' . $releaseids[$i] . '">' . $releaseids[$i] . '</option>';
		}
		echo '</select>';
		echo '<button id="releaseview" class="button">Preview</button>';
	}
	
	if ($releaseids != '') {
		echo '<hr class="divider">';
		echo '<input type="button" id="recordview" class="button" value="Preview Current Record">';
	}
}

function display_validate_meta_box( $post ) {
	?>
	<div>Validate against the OCDS v1.1 schema</div>
	<form></form><form method="POST" action="<?php echo get_permalink($post->id).'?action=validate'?>" id="validateform">
	<div class="extra_services">
		<span id="validatemessage">
			<span class="icon"></span>
			<a href="#TB_inline?width=800&height=550&inlineId=validatebox" class="thickbox validate"></a>
		</span>
		<input type="button" id="validatebtn" value="Validate" class="button button-primary button-large">
	</div>
	<div id="validatebox" style="display: none">
			<table class="validation-error-table" border="1">
				<thead> 
				  <tr> 
					<th><?php echo __('Error Description', 'opencontractr') ?></th> 
					<th><?php echo __('Error Count', 'opencontractr') ?></th> 
					<th><?php echo __('First 3 Examples', 'opencontractr') ?></th> 
					<th><?php echo __('Location of first 3 errors', 'opencontractr') ?></th> 
				  </tr> 
				</thead>
			</table>
	</div>
	
	<script>
		$(document).ready(function() {
			$('#validatebtn').click(function() {
				//$('#validatemessage .icon').html('<img src="<?php echo plugin_dir_url(__FILE__) ?>images/loader.gif">');
				$('#validatemessage .icon').html('<i class="fa fa-cog fa-spin fa-2x fa-fw"></i>');
				$('a.thickbox.validate').empty();
				$.ajax({
					url : '<?php echo get_permalink($post->id).'?action=validate'?>',
					type : 'GET',
					dataType:'json',
					success : function(data) {
						if (data) {
							if (data.validation_errors_count > 0) {
								data.validation_errors.forEach(function(error, n) {
									errors = JSON.parse(error[0]);
									var $tr = $('<tr>');
									var $td1 = $('<td>').html(errors[1]);
									var $td2 = $('<td>').html(error[1].length);
									var $td3 = $('<td>');
									var $td4 = $('<td>');
									if (error[1].length > 1) {
										$ul3 = $('<ul>');
										$ul4 = $('<ul>');
										error[1].forEach(function(values, i) {
											//console.log(values)
											//$ul3.append('<li>'+values[i].value+'</li>');
											$ul4.append('<li>'+getPath(values.path)+'</li>');
										});
										$td3.append($ul3);
										$td4.append($ul4);
									} else {
										//$td3.html(error[1][0].value);
										$td4.html(getPath(error[1][0].path));
									}
									$tr.append($td1).append($td2).append($td3).append($td4);
									$('table.validation-error-table').append($tr);
								});
								$('#validatemessage .icon').html('<img src="<?php echo plugin_dir_url(__FILE__) ?>images/false.png">');
								$('a.thickbox.validate').html('<span class="viewerrors">See the errors</span>');
							} else if (data.validation_errors_count == 0) {
								$('#validatemessage .icon').html('<img src="<?php echo plugin_dir_url(__FILE__) ?>images/true.png">');
								$('a.thickbox.validate').empty();
							}
						} else {
							$('#validatemessage .icon').html('Error reading the output!');
							$('a.thickbox.validate').empty();
						}
						$('.viewerrors').click(function() { $('.thickbox.validate').click() })
					},
					error : function(request,error)
					{
						$('#validatemessage .icon').html('Connection error!');
						console.log("Request: "+JSON.stringify(request)+" "+error);
					}
				});
			});
			
			$('.validation-error-table').on('click', '.error-path', function() {
				goToValidationError('#selectstage', $(this).data('stage'), $(this).data('path'));
			})
			
			function getPath(path) {
				var strpath = '';
                var realpath = path.split('records/0/compiledRelease/')[1];
				if (realpath) {
                    var datapath = realpath.split('/');
					datapath.forEach(function(item, n) {
						if (!$.isNumeric(item)) {
							strpath += item + '/';
						}
					});
					if (strpath.charAt(strpath.length - 1) == '/') {
						errorpath = strpath.substr(0, strpath.length - 1);
					}
					errorpath = errorpath.replace(/\//g, '-');
					strpath = '<a href="#" class="error-path" data-stage="'+datapath[0]+'" data-path="'+errorpath+'">'+strpath+'</a>';
                } else {
					strpath = path;
				}
				return strpath;
            }
			
			function goToValidationError(selector, stage, path) {
				$(selector).find('option').each(function() {
					if ($(this).val() == stage) {
						// select the stage from the dropdown
						$(selector).val($(this).val());
						$(selector).change();
						// choose the release tag (stage+'Update')
						if (stage.charAt(stage.length - 1) == 's') {
							tag = stage.substr(0, stage.length - 1) + 'Update';
						} else {
							tag = stage + 'Update';
						}
						$('.releasetaglist input[value="'+tag+'"]').prop('checked', true);
						// set validation error path
						$('#validationerrorpath').val(path)
						// trigger the release tag button
						$('#submit_release_tag').click();
					}
				});
			}
		});
	</script>
	<?php
	//add_thickbox();
}

function display_import_meta_box( $post ) {
	//print_r($_POST);
	if ($_POST) {
		if ($_POST['targetimport'] == 'newpost') {
			import_to_new_post();
		} elseif ($_POST['targetimport'] == 'existingpost') {
			print_r(import_to_existing_post());
		}
	}
	?>
	<div>Import OCDS Record Packages from other sources. Data to be imported could be OCDS v1.0 or v1.1</div>
	<div class="extra_services">
		<input type="button" id="importview" value="Import" class="button button-primary button-large">
	</div>
	<div id="import-form" style="display: none">
		<form></form>
		<form action="" method="post" name="importform" id="importform" enctype="multipart/form-data">
			<div class="target">
				<input type="radio" name="targetimport" class="targetimport" value="existingpost" checked="checked">Existing contract
				<input type="radio" name="targetimport" class="targetimport" value="newpost">New contracts
			</div>
			<input type="file" name="importfile" id="importfile">
			<input type="hidden" name="savedtag" id="importedtag">
			<textarea id="importedjson" name="savedjson" style="display:none"></textarea>
			<input type="hidden" name="savedsections" id="importedsections">
			<br><input type="button" id="importbtn" value="Import this" class="button button-primary">
			<br><span class="uploaderror"></span>
		</form>
	</div>
	<a href="#TB_inline?width=600&height=550&inlineId=import-form" class="thickbox import"></a>
	<script>
		$('#importbtn').click(function() {
			var filedata = $('#importfile').prop('files')[0]
			var formdata = new FormData();
			formdata.append('file', filedata);
			$.ajax({
				url: '<?php echo plugin_dir_url(__FILE__) ?>src/import.php',
				type: 'POST',
				data: formdata,
				processData: false,
				contentType: false,
				success: function(response) {
					data = response.split('|||');
					if (data[0] == 'error') {
                        $('.uploaderror').html(data[1]);
                    } else {
						$('.uploaderror').empty();
						if ($('.targetimport:checked').val() == 'newpost') {
							try {
								//console.log(response)
								var ocds = JSON.parse(data[1]);
								if (ocds['releases'].length <= 1) {
									try {
										// if there is only one release to import
										var importedjson = convert(ocds['releases'][0])
										var savedsections = [];
										// get the sections to save (based on what is present in the release)
										$.each(ocds_sections, function(i, item) {
											if (item in importedjson) {
												savedsections.push(item);
												importedtag = item;
											}
										})
										$('#importedsections').val(savedsections.join());
										$('#importedtag').val(importedtag);
										// update the ocid, id and date for the imported release
										importedjson['ocid'] = '<?php echo get_post_meta( $post->ID, 'ocid', true ); ?>';
										timestamp = Date.now() / 1000 | 0
										importedjson['id'] = importedjson['ocid'] + '-' + importedtag + '-' + timestamp;
										importedjson['date'] = new Date().toISOString().split('.')[0]+'Z';
										$('#importedjson').html(JSON.stringify(importedjson))
										//console.log(importedjson)
										$('#importform').submit();
									} catch(e) {
										$('.uploaderror').html('Error reading data: 002');
										console.log(e)
									}
									
								} else {
									
									// if there are multiple releases to import
									new_ocds = {}; new_ocds['releases'] = [];
									//console.log(ocds['releases'])
									$.each(ocds['releases'], function(i, release) {
										try {
											var importedjson = convert(release);
											// update the ocid, id and date for the imported release
											//importedjson['ocid'] = '<?php echo get_post_meta( $post->ID, 'ocid', true ); ?>';
											//timestamp = Date.now() / 1000 | 0
											//importedjson['id'] = importedjson['ocid'] + '-' + importedtag + '-' + timestamp;
											//importedjson['date'] = new Date().toISOString().split('.')[0]+'Z';
											new_ocds['releases'].push(importedjson);
										} catch(e) {
											console.log(e);
										}
									});
									//console.log(new_ocds)
									$('#importedjson').html(JSON.stringify(new_ocds))
									$('#importform').submit();
								}
							} catch(e) {
								$('.uploaderror').html('Error reading data: 001');
								console.log(e)
							}
						
						} else if ($('.targetimport:checked').val() == 'existingpost') {
							try {
								console.log(response);
								var ocds = JSON.parse(data[1]);
								// might want to do something here later...
								$('#importedjson').html(JSON.stringify(ocds))
								$('#importform').submit();
							} catch(e) {
								$('.uploaderror').html('Error reading data: 002');
								console.log(e)
							}
                            
                        }
					}
				}
			});
		});
		
		$('#importview').click(function() {
			$('.thickbox.import').click();
		});
	</script>
	<?php
	add_thickbox();
}

function import_to_new_post() {
	$ocds = json_decode(stripslashes($_POST['savedjson']), TRUE);
	if ($ocds['releases']) {
		// for multiple releases + new post
		$ocds = $ocds['releases'];
		for ($i=0; $i<count($ocds); $i++) {
			set_new_contract($ocds[$i]);
		}
	} else {
		// for single release + new post
		set_new_contract($ocds);
	}
}

function set_new_contract($release) {
	global $ocds_sections, $ocds_metadata, $ocds_stages;
	$title = ($release['tender']['title'] != '') ? $release['tender']['title'] : $release['contracts'][0]['title'];
	//$description = ($release['tender']['description'] != '') ? $release['tender']['description'] : $release['planning']['budget']['description'];
	
	// create new posts
	$args = array(
		'post_type' 	=> 'open_contract',
		'post_title'	=> $title,
		'post_content'	=> '',
		//'post_status'	=> 'publish'
	);
	
	$post_id = wp_insert_post($args);
	save_new_opencontract($post_id);
	
	update_meta_values($post_id, $release);
}

function import_to_existing_post() {
	$ocds = json_decode(stripslashes($_POST['savedjson']), TRUE);
	if ($ocds['releases']) {
		// for multiple releases + existing post
		$ocds = $ocds['releases'];
		for ($i=0; $i<count($ocds); $i++) {
			update_contract($ocds[$i]);
		}
	} else {
		// decide what to do...
		return 'None';
	}
}


function update_contract($release) {
	$ocid = $release['ocid'];
	// create new posts
	$args = array(
		'meta_key' => 'ocid',
		'meta_value' => $ocid,
		'post_type' => 'open_contract',
		'posts_per_page' => 1
	);
	$posts = get_posts($args);
	$post_id = $posts[0]->ID;
	
	if ($post_id) {
		update_meta_values($post_id, $release);
	}
}


function update_meta_values($post_id, $release) {
	global $ocds_sections, $ocds_metadata, $ocds_stages;
	// update individual ocds sections with data from the created release
	foreach ($ocds_sections as $section=>$value) {
		if (array_key_exists($section, $release)) {
			if ($release != NULL || $release != '') {
				$sectionjson = json_encode($release[$section]);
				if (! add_post_meta( $post_id, $section, $sectionjson, true )) {
					update_post_meta( $post_id, $section, $sectionjson );
				}
				// update current stage
				if (! add_post_meta( $post_id, 'currentstage', $section, true )) {
					update_post_meta( $post_id, 'currentstage', $section );
				}
				//}
			}
			$releasetag = $value[0];
		}
	}
	
	// save to file
	$ocid = get_post_meta( $post_id, 'ocid', true );
	$raw_release = json_encode($release);
	$date = strtotime(date("Y-m-d H:i:s"));
	$release_id = $ocid . '-' . $releasetag . '-' . $date;
	create_release_file($ocid, $release_id, $raw_release);
	// get all metadata fields
	$metadata = array(); $metadataid = array();
	//$existingmeta = json_decode(get_post_meta( $post->ID, 'metadata', true ), TRUE);
	$releaseids = get_post_meta( $post_id, 'releaseids', true );
	// loop through ocds_metadata array to update tag and id for the new project's metadata
	for ($j=0; $j<count($ocds_metadata); $j++) {
		// set ocid
		if ($ocds_metadata[$j] == 'ocid') {
			$metadata[$ocds_metadata[$j]] = $ocid;
		} elseif ($ocds_metadata[$j] == 'tag') {
			// append tag to metadata tag field
			$metadata[$ocds_metadata[$j]] = array($releasetag);
		} elseif ($ocds_metadata[$j] == 'id') {
			// set id to generated value for the new contract
			$metadata['id'] = $release_id;
			if ($releaseids) {
				$metadataid = $releaseids.','.$release_id;
			} else {
				$metadataid = $release_id;
			}
		} elseif ($ocds_metadata[$j] == 'date') {
			// set date as current date (ISO format)
			$metadata[$ocds_metadata[$j]] = date('Y-m-d\TH:i:s\Z', $date);
		} else {
			$metadata[$ocds_metadata[$j]] = $release[$ocds_metadata[$j]];
		}
	}
	$metadatajson = json_encode($metadata);
	//echo $releaseids;
	// add post meta for metadata
	if (! add_post_meta( $post_id, 'metadata', $metadatajson, true )) {
		update_post_meta( $post_id, 'metadata', $metadatajson );
	}
	// add post meta for release ids
	if (! add_post_meta( $post_id, 'releaseids', $metadataid, true )) {
		update_post_meta( $post_id, 'releaseids', $metadataid );
	}
	// create other post meta for searchability
	create_search_post_meta($post_id);
	// update post title and description
	$project_title = get_post_meta( $post_id, 'projecttitle', true );
	$current_post = get_post($post_id);
	$this_post = array(
		'ID' => $post_id,
		'post_title' => $project_title,
		'post_content' => get_post_meta( $post_id, 'description', true )
	);
	wp_update_post( $this_post );
}


/////// OCDS EDIT FORM /////////

add_action( 'admin_menu', 'register_section_editor' );

function register_section_editor() {
    global $ocds_sections;
    //$arrlength = count($ocds_sections);
    //for ($i=0; $i<$arrlength; $i++) {
	foreach ($ocds_sections as $key=>$section) {
        $ocid = get_post_meta( $post->ID, 'ocid', true );
        $item = strtolower($section[0]);
        $edit_page = add_submenu_page( 
                        null,
                        'OpenContractr – Edit '.$section[0].' Section',
                        'OpenContractr – Edit '.$section[0].' Section',
                        'edit_posts',
                        $item,
                        'data_entry_form'
                    );
        // load the JS conditionally
        add_action( 'load-'.$edit_page, 'load_admin_files');
    }
}
// this function is only called when our plugin's page loads!
function load_admin_files() {
    add_action( 'admin_enqueue_scripts', 'load_data_entry_files' );
}

function load_data_entry_files() {
    // styles
    $cssfiles = get_dir_files('css'); $i=0;
    foreach($cssfiles as $file) {    
        wp_enqueue_style( 'data_entry_form-css-'.$i, plugin_dir_url(__FILE__) . 'css/' . $file); $i++;
    }
    
    // scripts
    $jspath = 'js/form/';
    $jsfiles = get_dir_files($jspath); $i=0;
    foreach($jsfiles as $file) {
		$thefile = plugin_dir_path(__FILE__) . $jspath . $file;
		if(is_file($thefile)) {
			wp_enqueue_script( 'data_entry_form-js-'.$i, plugin_dir_url(__FILE__) . $jspath . $file, '', '', true); $i++;
		}
    }
	$jsdtpath = 'js/form/datetimepicker/';
    $jsdtfiles = get_dir_files($jsdtpath); $i=0;
    foreach($jsdtfiles as $jsdtfile) {
		wp_enqueue_script( 'data_entry_dtp-js-'.$i, plugin_dir_url(__FILE__) . $jsdtpath . $jsdtfile, '', '', true); $i++;
    }
}

function get_dir_files($dirname) {
    $path = plugin_dir_path(__FILE__).$dirname;
    $files = array_diff(scandir($path), array('.', '..', '.DS_Store'));
    return $files;
}


function data_entry_form() {
    //print_r($_POST);
    global $ocds_sections;
    if ($_POST['editsections'] && $_GET['page']) {
        $mode = 'edit';
    } else {
        $mode = 'view';
    }
	if (isset($_GET['post'])) {
		// for backend
		$post_title = get_post($_GET['post'], ARRAY_A)['post_title'];
	} else {
		// for frontend
		$post_title = get_post($_GET['id'], ARRAY_A)['post_title'];
	}
    ?>
	<div class="wrap">
		<?php if (isset($_GET['post'])) { ?>
		<h2><span style="font-size:0.5em">Contract Name: <a href="post.php?post=<?php echo $_GET['post'] ?>&action=edit"><?php echo $post_title ?></a></span><br>Current Data at <?php echo ucfirst($_GET['tab']) ?> Stage <small>[<?php echo $mode ?> mode]</small></h2>
        <?php } else { ?>
		<h2 style="display:none"><span>Contract Name: <?php echo $post_title ? $post_title : '[No title given]' ?></span></h2>
		<?php } ?>
		<h2 class="nav-tab-wrapper">
            <?php
            if( isset( $_GET[ 'page' ] ) ) {
                $active_tab = $_GET[ 'page' ];
            }
            if ($_POST['editsections']) {
                $sectionarray = explode(',',$_POST['editsections']);
                for ($i=0; $i<count($sectionarray); $i++) {
                ?>
                    <a href="#" id="<?php echo strtolower($sectionarray[$i]) ?>" class="nav-tab <?php echo $active_tab == strtolower($sectionarray[$i]) ? 'nav-tab-active' : ''; ?>"><?php echo ucfirst($sectionarray[$i]); ?></a>
                <?php
                }
            } else {
                ?>
                    <div>There are no sections selected to view or edit.</div>
                <?php
            }
            ?>
        </h2>
        <div class="bootstrap-iso">
			<div id="container" class="release"></div>
		</div>
        <textarea id="partyroles" style="display: none"><?php echo get_post_meta( $_GET['id'], 'roles', true ); ?></textarea>
        <textarea id="codelists" style="display: none"><?php echo get_option('_opencontractr_codelists'); ?></textarea>
		<textarea id="importscheme" style="display: none"><?php echo get_import_scheme() ?></textarea>
		<textarea id="default_organisation" style="display: none"><?php echo json_encode(get_option('organisation_options')) ?></textarea>
		<textarea id="importdata" style="display: none"></textarea>
		<input type="hidden" name="invalidfield" id="invalidfield" value="<?php echo $_POST['validationerrorpath'] ?>">
		<?php if ($mode == 'edit') { ?>
            <input type="button" id="getData" class="button" value="Submit">
        <?php } ?>
        <form name="jsonform" id="jsonform" method="POST" action="">
			<input type="hidden" name="postid" id="postid" value="<?php echo $_REQUEST['id']; ?>">
			<input type="hidden" name="posttitle" id="posttitle" value="">
            <input type="hidden" name="currentstage" id="currentstage" value="<?php echo $_POST['currentstage']; ?>">
			<input type="hidden" name="ocid" id="ocid" value="<?php echo get_post_meta( $_REQUEST['id'], 'ocid', true ); ?>">
			<input type="hidden" name="savedtag" id="savedtag" value="<?php echo $_POST['releasetag']; ?>">
			<input type="hidden" name="partydata" id="partydata" value="">
            <textarea id="savedsections" name="savedsections" style="display:none"><?php echo $_POST['editsections']; ?></textarea>
            <textarea id="savedjson" name="savedjson" style="display:none"></textarea>
        </form>
		<?php add_thickbox(); ?>
		<div id="organisation-selection-box" style="display:none">
			<div>Select a list of organisations based on items in the parties section.
			<p>To add or remove from this list, simply add or remove them from the parties section
			and then return to list to select the organisation you want.</p></div>
			<hr>
			<p class="partylist"></p>
			<input type="button" value="Choose Organisation" id="update-party" name="update-party" class="button" />
		</div>
		<a href="#TB_inline?width=600&height=550&inlineId=organisation-selection-box" class="thickbox parties" title="Select an Organisation"></a>
		<a href="<?php echo plugin_dir_url(__FILE__) ?>lib/filemanager/filemanager.php#TB_iframe?width=990&height=650&inlineId=document-management-box" class="thickbox document" title="Upload/Select a Document"></a>
	</div>
	<div id="imported-items" class="jq-dropdown jq-dropdown-relative"> <!-- jq-dropdown-tip can also be added to classes -->
		<ul class="jq-dropdown-menu"></ul>
	</div>
	<div id="party-items" class="jq-dropdown jq-dropdown-relative"> <!-- jq-dropdown-tip can also be added to classes -->
		<ul class="jq-dropdown-menu"></ul>
	</div>
    <script>
		jQuery(document).ready(function($) {
			codelists = JSON.parse( $('#codelists').text() );
			var codelist_ids = codelists['id'][0]['alias'];
			$.getJSON( "<?php echo plugin_dir_url(__FILE__) . 'php/schema.php?ocds_data=schema' ?>", function( schema ) {
				
				var BrutusinForms = brutusin["json-forms"];
				var bf = BrutusinForms.create(schema);
				
				var container = document.getElementById('container');
				var data = document.getElementById('jsoninput').value ? document.getElementById('jsoninput').value: '{}';
				//console.log(data)
				var ocid = $('#ocid').val()
				bf.render(container, JSON.parse(data));
				runEditor();
				
				$('#getData').click(function() {
					// insert meta values
					$('#savedsection').val('<?php echo $_GET['tab']; ?>');
					output = bf.getData();
					//console.log(output)
					
					// set metadata fields
					output['ocid'] = ocid;
					output['language'] = 'en';
					output['initiationType'] = 'tender' // default; already exists
					d = new Date();
					output['date'] = d.toISOString();
					//output['tag'] = ['<?php echo $_POST['releasetag'] ?>'];
					output['tag'] = [$('#releasetaglist').val()];
					timestamp = Date.now() / 1000 | 0
					output['id'] = output['ocid'] + '-' + $('#releasetaglist').val() + '-' + timestamp;
					console.log(output)
					
					$('#posttitle').val($('#contract-title').val());
					
					if ( $('#partydata').length ) {
						var partypaths = codelist_ids;
						var partyids = [];
						// search for organisation ids
						for (i=0; i<partypaths.length; i++) {
							$('.kvp[data-path='+partypaths[i]+']').each(function() {
								id = $(this).find('input.value').val();
								role = $(this).prev().find('select.role').val();
								partyid = [id, role, partypaths[i]]
								partyids.push(partyid);
							})
						}
						$('#partydata').val( JSON.stringify(partyids) );
					}
					//console.log(partyids)
					
					$('#savedjson').html(JSON.stringify(output));
					$('#jsonform').submit();
				});
				
				
				// set initial tab
				show_hide_tabs($('.nav-tab-active').attr('id'))
				
				// set tab when clicked
				$('.nav-tab').click(function() {
					// unset active tab
					$('.nav-tab').removeClass('nav-tab-active');
					// set active tab
					$(this).addClass('nav-tab-active');
					// show/hide tabs
					show_hide_tabs($(this).attr('id'));
				})
				
				function show_hide_tabs(elid) {
					// hide all sections
					$('#container > .brutusin-form > .object > .front > .kvp').hide();
					// show only relevant section
					$('#container > .brutusin-form > .object > .front > .kvp#'+elid).show();
				}
			
			});
		});
    </script>
    
	<?php
}



/*******************
  CODELIST MANAGER
 *******************/

add_action( 'admin_menu', 'register_codelist_editor' );
function register_codelist_editor() {
    $codelist_page = add_submenu_page( 
        'edit.php?post_type=open_contract',
        'OpenContractr – Edit Codelists',
        'Codelists',
        'edit_posts',
        'codelists',
        'codelist_settings_page'
    );
	// load the JS conditionally
    add_action( 'load-'.$codelist_page, 'load_codelist_files');
}

// this function is only called when our plugin's page loads!
function load_codelist_files() {
    add_action( 'admin_enqueue_scripts', 'load_codelist_js' );
}

function load_codelist_js() {
	$codelist_js_path = 'js/codelists/';
	$jsfiles = get_dir_files($codelist_js_path); $i=0;
	// styles
	wp_enqueue_style( 'reformed-css', plugin_dir_url(__FILE__) . 'css/reformed.css');
    // scripts
    foreach($jsfiles as $file) {
        wp_enqueue_script( 'codelist-js-'.$i, plugin_dir_url(__FILE__) . $codelist_js_path . $file, '', '', true); $i++;
    }
}

function codelist_settings_page() {
	if ($_POST['reset-codelists']) {
		// user has asked to reset the codelist data
		reset_codelists('update');
	} elseif ($_POST['save-codelists']) {
		// store the updated codelist in the database
		update_option('_opencontractr_codelists', stripslashes($_POST['codelist-data']));
	}
	$codelist = json_decode(get_option('_opencontractr_codelists'), TRUE);
	$key = array_search('Organization Identifier Scheme', array_column($codelist['scheme'], 'name'));
	?>
	<div class="wrap">
		<h1>OCDS Codelists</h1>
		<?php if ($_POST['save-codelists']) { ?>
		<div class="updated notice is-dismissable">
			<p><?php _e( 'The codelist has been saved, excellent!', 'opencontractr' ); ?></p>
		</div>
		<?php } ?>
		<p style="float:left"><?php echo __('These are the codelists according to specifications of the OCDS','opencontractr'); ?></p>
		<div style="float:right">
			<form method="POST" id="reset" action="">
				<input type="submit" id="reset-codelists" name="reset-codelists" class="button" value="Reset all codelists">
			</form>
		</div>
        <h2 class="nav-tab-wrapper clear" style="margin-bottom: 10px">
			<?php
			if( isset( $_GET[ 'type' ] ) ) {
                $active_tab = $_GET[ 'type' ];
            }
			?>
			<a href="#open" id="open-codelist" class="nav-tab <?php echo $active_tab == 'open' ? 'nav-tab-active' : ''; ?>">Open Codelist</a>
			<a href="#closed" id="closed-codelist" class="nav-tab <?php echo $active_tab == 'closed' ? 'nav-tab-active' : ''; ?>">Closed Codelist</a>
		</h2>
		<table class="wp-list-table widefat codelists">
			<thead>
				<tr>
					<th id="name">Name</th>
					<th id="description">Description</th>
					<th></th>
				</tr>
				<?php
					foreach ($codelist as $key1=>$value1) {
						if ($codelist[$key1]['type']) {
							write_codelist_row($codelist[$key1], $key1);
						}
						foreach ($codelist[$key1] as $key2=>$value2) {
							if(is_array($codelist[$key1][$key2]) && $codelist[$key1][$key2]['type']) {
								write_codelist_row($codelist[$key1][$key2], $key1, $key2);
							}
						}
					}
				?>
			</thead>
		</table>
		<?php add_thickbox(); ?>
		<div id="codelist-editor" style="display:none">
			<div id="reformed" class="codelists"></div>
			<form method="POST" action="" id="codelistform">
				<textarea id="input" name="codelist-data" style="display: none"><?php echo json_encode($codelist) ?></textarea>
				<input type="submit" value="Save" id="savecodelist" name="save-codelists" class="button" />
			</form>
		</div>
		<a href="#TB_inline?width=600&height=550&inlineId=codelist-editor" class="thickbox"></a>
	</div>
	<script>
		$(document).ready(function() {
			
			var codelist = JSON.parse($('#input').html());
			$('.codelists').on('click', '.editcodelist', function() {
				// add data-codelist-path attribute for the save button to pick up
				$('#savecodelist').attr('data-codelist-path', $(this).attr('data-codelist-path'))
				// get name of codelist
				var codelistname = $(this).next('.codelistname').val()
				// get values of codelist
				var codelistpath = $(this).attr('data-codelist-path').split(',');
				codelistpath.push('values');
				var values = getValue(codelist, codelistpath);
				console.log(values)
				stringvalues = JSON.stringify(values);
				$('#reformed').reform(stringvalues, {'editor':'edit'});
				$('.thickbox').attr('title', 'Edit Codelist: '+codelistname).click();
				
				$('#savecodelist').click(function() {
					var codelistpath = $(this).attr('data-codelist-path').split(',');
					codelistpath.push('values');
					var updated = JSON.parse($.rejson('#reformed'));
					var newcodelist = setValue(codelist, codelistpath, updated);
					$('#input').html(JSON.stringify(newcodelist));
					$('#codelistform').submit();
				});
				
			});
			
			// get and set values from json object based on array path
			// from: https://stackoverflow.com/questions/40405940/update-values-in-a-json-object-based-on-dynamic-keys-available-as-array
			// first: function to get the specific codelist values
			function getValue(object, path) {
				return path.reduce(function(o,k) {
					return (o || {})[k];
				}, object);
			}
			
			// second: function to set and update the specific values in the codelist json
			function setValue(object, path, value) {
				var target = path.slice(0, -1).reduce(function(obj, key) {
					return (obj || {})[key];
				}, object);
				target[path[path.length-1]] = value;
				return object;
			}
	
			$('.nav-tab').click(function() {
				// unset active tab
				$('.nav-tab').removeClass('nav-tab-active');
				// set active tab
				$(this).addClass('nav-tab-active');
				if ($(this).attr('id') == 'open-codelist') {
					$('tr.open').show();
					$('tr.closed').hide();
				} else {
					$('tr.closed').show();
					$('tr.open').hide();
				}
			})
			$('#open-codelist').click();
		});
	</script>
	<?php
}

function write_codelist_row($codelist, $key1='', $key2='') {
	$codelist_path = (strlen($key1) != 0 ? $key1.(strlen($key2) != 0 ? ','.$key2 : ''): '');
	?>
	<tr class="<?php echo $codelist['type'] ?>">
		<td class="codelist-title column-primary"><?php echo $codelist['name'] ?></th>
		<td class="column-description desc"><?php echo $codelist['description'] ?></th>
		<td class="action">
			<?php if ($codelist['type'] == 'open') { ?>
			<input type="button" value="Edit" class="editcodelist" data-codelist-path="<?php echo $codelist_path ?>" />
			<input type="hidden" value="<?php echo $codelist['name'] ?>" class="codelistname">
			<?php } ?>
		</td>
	</tr>
	<?php
}

function reset_codelists($action) {
	$filepath = plugin_dir_path(__FILE__) . 'schema/codelists.json';
    $fh = fopen($filepath, "r");
	$content = fread($fh, filesize($filepath));
	if (!$action) {
		add_option('_opencontractr_codelists', $content);
	} elseif ($action=='update') {
		update_option('_opencontractr_codelists', $content);
	}
}



/*******************
  FIELD MANAGER
 *******************/

add_action( 'admin_menu', 'register_field_manager' );
function register_field_manager() {
    $fieldlist_page = add_submenu_page( 
        'edit.php?post_type=open_contract',
        'OpenContractr – Edit OCDS Fields',
        'Fields',
        'edit_posts',
        'fields',
        'fields_settings_page'
    );
}

function fields_settings_page() {
	global $ocds_sections;
	
	if (isset($_POST['action'])) {
		if ($_POST['action'] == 'save-fields') {
			update_option('_opencontractr_user_selected_fields', $_POST['selected-fields']);
			$saved = true;
		}
		// get field scheme from [internet] source and reload page	
	}
	$fieldschemepath = plugin_dir_url(__FILE__) . 'schema/fieldscheme.json';
	$selectedfieldspath = '?ocds_data=selected-fields';
	$fh = fopen($filepath, "r");
	$content = fread($fh, filesize($filepath));
	//add_option('_opencontractr_fields', $content);
	$fields = json_decode(get_option('_opencontractr_fields'), TRUE);
	$field_keys = array(
						array('label','Label'),
						array('description','Description'),
						array('mandatory','Mandatory?')
				);
	?>
	<div class="wrap">
		<h1>OCDS Fields</h1>
		<?php if ($saved) { ?>
			<div class="updated notice is-dismissable">
				<p><?php _e( 'Your selected fields has been saved!', 'opencontractr' ); ?></p>
			</div>
		<?php } ?>
		<p style="float:left"><?php echo __('These are the fields according to OCDS specification','opencontractr'); ?></p>
		<div style="float:right">
			<form method="POST" name="savefields" id="savefields" action="">
				<!--<input type="button" id="reload-fields" name="reload-fields" class="button" value="Reload Fields">-->
				<input type="button" id="save-fields" name="save-fields" class="button-primary" value="Save Selected Fields">
				<input type="hidden" id="action" name="action">
				<textarea id="selected-fields" name="selected-fields" style="display: none"></textarea>
			</form>
		</div>
		
        <h2 class="nav-tab-wrapper clear" style="margin-bottom: 10px">
			<?php
			if( isset( $_GET[ 'type' ] ) ) {
                $active_tab = $_GET[ 'type' ];
            }
			echo '<a href="#metadata" id="metadata" class="nav-tab '.( $active_tab == 'metadata' ? 'nav-tab-active' : '' ).'">Metadata</a>';
			foreach ($ocds_sections as $key => $value) {
				echo '<a href="#'.$key.'" id="'.$key.'-stage" class="stage nav-tab '.( $active_tab == 'planning' ? 'nav-tab-active' : '' ).'">'.$value[0].'</a>';
			}
			echo '<div style="float:right">Field score: <span id="fieldscore"></span>%</div>';
			?>
		</h2>
		<table class="wp-list-table widefat fields" style="display: none">
			<thead>
					<th id="field">Field</th>
					<?php
					for ($i=0; $i<count($field_keys); $i++) {
						echo '<th id="'.$field_keys[$i][0].'">'.$field_keys[$i][1].'</th>';
					}
					?>
			</thead>
			<?php
			foreach ($fields as $key=>$value) {
				$keyparts = explode("-",$key);
				echo '<tr class="'.$keyparts[0].'">';
				echo '<td><strong><a href="options-general.php?page=opencontractr_settings&tab='.$key.'_field_options">'.join( "/", $keyparts ).'</a></strong></td>';
				for ($i=0; $i<count($value); $i++) {
					$option = get_option('field_options')[$key.'_'.$field_keys[$i][0]];
					echo '<td>';
					if ( $option ) {
						if ($field_keys[$i][0] == 'mandatory') {
							echo ($option == '1') ? 'Yes' : 'No';
						} else {
							echo $option;
						}
					} else {
						echo $value[$i];
					}
					echo '</td>';
				}
				echo '</tr>';
			}
			?>
		</table>
		
		<table class="wp-list-table widefat" id="fieldlist" border=1>
			<tr class="title">
				<td><input type="checkbox" id="checkall"></td>
				<th>Field</th>
				<th>Basic</th>
				<th>Intermediate</th>
				<th>Advanced</th>
				<th>Description</th>
				<th>Type</th>
			</tr>			
		</table>
	</div>
	<script>
		$(document).ready(function() {
			$.getJSON( '<?php echo $fieldschemepath ?>', function( scheme, status, xhr ) {
				qualitycheck.writeoutput( $('#fieldlist'), scheme );
				$('.field').each(function(i,el) {
					fieldtab = $(this).html().split('/').join('-');
					$(this).html('<strong><a href="options-general.php?page=opencontractr_settings&tab='+fieldtab+'_field_options">'+$(this).html()+'</a></strong>');
				});
				$.getJSON( "<?php echo $selectedfieldspath ?>", function( selectedfields ) {
					defaultfields = ['ocid','id','language','date','tag','initiationType','parties/id','buyer/id','tender/id','awards/id','contracts/id']
					for (dfield in defaultfields) {
						if ( !selectedfields.hasOwnProperty(defaultfields[dfield]) ) {	
							selectedfields[defaultfields[dfield]] = '';
						}
					}
					$('.fieldcheck').each(function(i,el) {
						var field = $(this).data('field');
						if ( selectedfields.hasOwnProperty(field) ) {
                            $(this).prop('checked', true);
                        }
						if ( defaultfields.indexOf(field) > -1 ) {
							$(this).prop('disabled', true);
						}
					});
					$('#checkall').change(function() {
						stage = $('.nav-tab-active').attr('id').split('-')[0];
						$('.fieldcheck').each(function(i,el) {
							if ($(el).data('field').split('/')[0] == stage && defaultfields.indexOf($(el).data('field')) < 0) {
                                $(el).prop('checked', $('#checkall').prop('checked'));
                            }
						})
					})
					
					output = qualitycheck.calculatescores(scheme, selectedfields);
					console.log(output);
					
					$('#fieldscore').text( output['fieldscore'].toFixed(2) )
				});
				
				// tab navigation
				$('.nav-tab').click(function() {
					// unset active tab
					$('.nav-tab').removeClass('nav-tab-active');
					// set active tab
					$(this).addClass('nav-tab-active');
					stage = $(this).attr('id').split('-')[0];
					$('tr').not('.title').hide();
					$('tr.'+stage).show();
					if (stage == 'metadata') {
                        $('tr').not('.parties,.buyer,.planning,.tender,.awards,.contracts').show();
						$('.relatedProcesses').hide();
                    }
				})
				$('.nav-tab#metadata').click();
				
				// save selected fields
				$('#save-fields').click(function() {
					var selectedfields = {};
					$('.fieldcheck').each(function() {
						if ($(this).is(':checked')) {
							field = $(this).data('field');
                            selectedfields[field] = scheme[field]
							delete selectedfields[field]['Description'];
							delete selectedfields[field]['Type'];
                        }
					});
					$('#selected-fields').html(JSON.stringify(selectedfields));
					$('#savefields #action').val('save-fields')
					$('#savefields').submit();
				})
			});
		});
	</script>
	<?php
}


/***** Lists *****/

$release_tags = array(
        "planning" => array("Planning information created", "planning"),
        "planningUpdate" => array("Planning information updated", "planning"),
        "tender" => array("Tender information created", "tender"),
        "tenderAmendment" => array("Tender information amended", "tender"),
        "tenderUpdate" => array("Tender information updated", "tender"),
        "tenderCancellation" => array("Tender cancelled", "tender"),
        "award" => array("New award created", "awards"),
        "awardUpdate" => array("Award information updated", "awards"),
        "awardCancellation" => array("Award cancelled", "awards"),
        "contract" => array("New contract created", "contracts"),
        "contractUpdate" => array("Contract information updated", "contracts"),
        "contractAmendment" => array("Contract information amended", "contracts"),
        "implementation" => array("Implementation information created", "contracts"),
        "implementationUpdate" => array("Implementation information updated", "contracts"),
        "contractTermination" => array("Contract terminated", "contracts")
    );



/*******************
  FRONTEND TEMPLATES
 *******************/

add_filter( 'template_include', 'include_template_function', 1 );

function include_template_function( $template_path ) {
    if ( get_post_type() == 'open_contract' ) {
        if ( is_single() ) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( 'single-open_contract.php' ) ) ) {
                $template_path = $theme_file;
            } else {
				if ($_REQUEST['action'] == 'download' || $_REQUEST['action'] == 'validate') {
					$template_path = OPENCONTRACTR_ABS_PATH . 'src/actions.php';
				} /*else {
					$template_path = plugin_dir_path( __FILE__ ) . 'frontend/analysis/ocds-show/index2.php';
				}*/
            }
        } elseif (is_archive() ) {
            if ( $theme_file = locate_template( array ( 'archive-open_contract.php' ) ) ) {
                $template_path = $theme_file;
            } else {
				if ($_REQUEST['action'] == 'downloadall' || $_REQUEST['action'] == 'search') {
					$template_path = OPENCONTRACTR_ABS_PATH . 'src/actions.php';
				} /*else {
					$template_path = plugin_dir_path( __FILE__ ) . 'frontend/analysis/ocds-visualise/index2.php';
				}*/
            }
		}
		if ( isset($_REQUEST['id']) || isset($_REQUEST['data']) ) {
			$template_path = OPENCONTRACTR_FRONTEND_PATH . 'v2/edit.php';
		} elseif ($_REQUEST['do'] == 'create') {
			$template_path = OPENCONTRACTR_FRONTEND_PATH . 'v2/create.php';
		} elseif ($_REQUEST['do'] == 'search' ) {
			$template_path = OPENCONTRACTR_FRONTEND_PATH . 'v2/search.php';
		}
    }
	
    return $template_path;
}

function get_plugin_url($dir) {
	return plugin_dir_url(__FILE__) . $dir;
}

add_action( 'wp_enqueue_scripts', 'enqueue_frontend_files' );

function enqueue_frontend_files() {
	if ( is_single() ) {
		$frontend_js_path = 'frontend/js/';
		$frontend_css_path = 'frontend/css/';
		$jsfiles = get_dir_files($frontend_js_path); $i=0;
		$cssfiles = get_dir_files($frontend_css_path); $j=0;
		// ocds-show styles
		foreach($cssfiles as $cssfile) {
			wp_enqueue_style( 'frontend-css-'.$i, plugin_dir_url(__FILE__) . $frontend_css_path . $cssfile); $i++;
		}
		// ocds-show scripts
		foreach($jsfiles as $jsfile) {
			//wp_enqueue_script( 'frontend-js-'.$j, plugin_dir_url(__FILE__) . $frontend_js_path . $jsfile, array('jquery'), '', true); $j++;
		}
		//wp_enqueue_script( 'frontend-js-'.$j, plugin_dir_url(__FILE__) . 'frontend/templates.js', '', '', true);
		
		// theme scripts
		$theme_js_path = 'frontend/themes/massively/assets/js/';
		$theme_css_path = 'frontend/css/';
		$themejsfiles = get_dir_files($theme_js_path); $i=0;
		$themecssfiles = get_dir_files($theme_css_path); $j=0;
		foreach($themecssfiles as $cssfile) {
			//wp_enqueue_style( 'theme-css-'.$i, plugin_dir_url(__FILE__) . $theme_css_path . $cssfile); $i++;
		}
		foreach($themejsfiles as $jsfile) {
			//wp_enqueue_script( 'theme'.$jsfile, plugin_dir_url(__FILE__) . $theme_js_path . $jsfile, $dep, '', true); $j++;
		}
		/*wp_enqueue_script( 'scrollex-js', plugin_dir_url(__FILE__) . $theme_js_path . 'jquery.scrollex.min.js' , array( 'jquery' ), '', true);
		wp_enqueue_script( 'scrolly-js', plugin_dir_url(__FILE__) . $theme_js_path . 'jquery.scrolly.min.js', array( 'jquery' ), '', true);
		wp_enqueue_script( 'skel-js', plugin_dir_url(__FILE__) . $theme_js_path . 'skel.min.js', array( 'jquery' ), '', true);
		wp_enqueue_script( 'util-js', plugin_dir_url(__FILE__) . $theme_js_path . 'util.js', array( 'jquery' ), '', true);
		wp_enqueue_script( 'main-js', plugin_dir_url(__FILE__) . $theme_js_path . 'main.js', array( 'jquery' ), '', true);*/
	}
}


function get_data_files($dirname) {
    global $ocid;
    // get filenames
    $path = ABSPATH.$dirname;
    $files = array_diff(scandir($path), array('.', '..', '.DS_Store'));
    
    // get file contents
    $releases = array();
    foreach($files as $file) {
        $filepath = $path.'/'.$file;
        $fh = fopen($filepath, "r");
        $content = fread($fh, filesize($filepath));
        $filecontent = json_decode($content, true);
        array_push($releases, $filecontent);
    }
    $fullreleases = array(
        "ocid" => $ocid,
        "releases" => $releases
    );
    $records = array($fullreleases);
    $fullrecords = array(
        "records" => $records
    );
    return $fullrecords;
}


function get_compiled_release($post, $tag='') {
	global $ocds_sections;
	$compiled = array();
	$metadata = json_decode(get_post_meta( $post->ID, 'metadata', true ), true);
	if (is_array($metadata) || is_object($metadata)) {
		foreach ($metadata as $key=>$value) {
			$compiled[$key] = $value;
		}
	}
	if (!$tag) $compiled['tag'] = array('compiled');
	foreach( $ocds_sections as $key => $value ) {
		$sectionVal = get_post_meta( $post->ID, $key, true );
		if ($sectionVal && $sectionVal != "" && $sectionVal != null) {
			$compiled[$key] = json_decode(get_post_meta( $post->ID, $key, true ), true);
		}
	}
	return $compiled;
}


function get_current_record($post) {
	global $ocds_sections, $datapath;
	$recordpackage = array();
	// compiledrelease
	$compiled = get_compiled_release($post);
	// ocid
	$ocid = get_post_meta( $post->ID, 'ocid', true );
	// releases
	$releases = array(); $packages = array();
	$path = rtrim(ABSPATH, '/').$datapath.$ocid;
	if ( file_exists($path) ) {
		$files = array_diff(scandir($path), array('.', '..', '.DS_Store'));
	}
	foreach($files as $file) {
		$release = array();
        $filepath = $path.'/'.$file;
		if ( file_exists($filepath) ) {
			$fh = fopen($filepath, "r");
			$content = fread($fh, filesize($filepath));
			$filecontent = json_decode($content, true);
			$release['date'] = $filecontent['date'];
			$release['tag'] = $filecontent['tag'];
			$release['url'] = get_site_url().$datapath.$ocid.'/'.$filecontent['id'].'.json';
			array_push($releases, $release);
			array_push($packages, $release['url']);
		}
    }
	// record item
	$recorditem = array();
	$recorditem['compiledRelease'] = $compiled;
	$recorditem['ocid'] = $ocid;
	$recorditem['releases'] = $releases;
	// records
	$records = array();
	array_push($records, $recorditem);
	// extensions
	$extensions = array();
	// packages
	$packages = $packages;
	// publisher
	$publisher = get_publisher_scheme();
	// publishDate
	$publishedDate = date('Y-m-d\TH:i:s\Z', time());
	// uri
	$uri = get_site_url($path='/archive/');
	// version
	$version = '1.1';
	
	$record = array();
	$record['extensions'] = $extensions;
	$record['packages'] = $packages;
	$record['publishedDate'] = $publishedDate;
	$record['publisher'] = $publisher;
	$record['records'] = $records;
	$record['uri'] = $uri;
	$record['version'] = $version;
	
	return $record;
}

function get_releases($posts) {
	$releases = array();
	$releases['uri'] = get_site_url();
	$releases['version'] = '1.1';
	$releases['extensions'] = array();
	$releases['publicationPolicy'] = '';
	$releases['publisher'] = get_publisher_scheme();
	$releases['publishedDate'] = date('Y-m-d\TH:i:s\Z', time());;
	$releases['license'] = '';
	
	$releaselist = array();
	foreach($posts as $post) {
		$compiledrelease = get_compiled_release($post, 'release');
		array_push($releaselist, $compiledrelease);
	}
	$releases['releases'] = $releaselist;
	
	return $releases;
}

function get_publisher_scheme() {
	global $publisher_options;
	$publisher = array();
	foreach ($publisher_options as $key=>$value) {
		$ocds_publisher_key = explode('_', $key)[1];
		$publisher[$ocds_publisher_key] = $value;
	}
	return $publisher;
}

function get_import_scheme() {
	$filepath = plugin_dir_path(__FILE__) . 'schema/importscheme.json';
    $fh = fopen($filepath, "r");
	return fread($fh, filesize($filepath));
}


/**
 * Creates a set of post_meta for a given post
 *
 * This is to for posts to be searched by these post_meta values
 *
 */
$meta_search_fields = array(
	// Search title, ocds path, sortable, description
	"projecttitle"=> array("Project Title", array('planning/budget/project','tender/title','awards/title','contracts/title'), __("The buyer for this contract","opencontractr")),
	"description"=> array("Project Description", array('planning/budget/description','tender/description','awards/description','contracts/description'), __("The buyer for this contract","opencontractr")),
	"procuringentity"=> array("Procuring Entity", array('buyer/name'), __("The buyer for this contract","opencontractr")),
	"contractor"=> array("Contractor", array('awards/suppliers/name'), __("The suppliers for this contract","opencontractr")),
	"amount"=> array("Amount Awarded", array('awards/value/amount'), __("The amount awarded for this contract","opencontractr")),
	"awarddate"=> array("Contract Award Date", array('awards/date'), __("The date this contract was awarded","opencontractr")),
	"contractdate"=> array("Contract Award Date", array('contracts/period/startDate','contracts/dateSigned'), __("The date this contract was signed","opencontractr"))
);
function create_search_post_meta($post_id) {
	global $meta_search_fields;
	foreach ($meta_search_fields as $metakey=>$value) {
		$paths = $value[1];
		foreach ($paths as $path) {
			$fields = explode("/", $path);
			
			// get the path from $search_fields without the stage
			$data = json_decode(get_post_meta( $post_id, $fields[0], true ), true);
			unset($fields[0]);
			$searchq = implode('/', $fields);
			
			// run the search
			$metavalue = rtrim(get_field_values($data, '/'.$searchq), ', ');
			
			if ($metavalue) {
				// store as post meta so that it can be filtered/searched
				if (! add_post_meta( $post_id, $metakey, $metavalue, true )) {
					update_post_meta( $post_id, $metakey, $metavalue );
				}
				break; // break out of the current loop only
			}
		}
	}
	
}


function save_ocds_form($postid) {
	global $ocds_stages, $ocds_metadata, $release_tags, $ocds_sections;
	if ($postid) {
		
		// get the post that holds the contract
		$post = get_post($postid);
		
		// save current stage of the contract
		if ($_POST && $_POST['currentstage']) {
			$currentstage = $_POST['currentstage'];
			// update current stage
			if (! add_post_meta( $post->ID, 'currentstage', $currentstage, true )) {
				update_post_meta( $post->ID, 'currentstage', $currentstage );
			}
		} else {
			$currentstage = get_post_meta( $post->ID, 'currentstage', true );
		}
		
		//echo $_POST['savedjson'];exit;
		// save metadata
		if (isset($_POST['savedjson']) && isset($_POST['ocid']) && $_POST['ocid'] != '') {
			$savedjson_raw = stripslashes($_POST['savedjson']);
			// convert savedjson to php array
			$savedjson = json_decode($savedjson_raw, TRUE);
			// save to file
			create_release_file($savedjson['ocid'], $savedjson['id'], $savedjson_raw);
			// get all metadata fields
			$metadata = array(); $metadataid = array();
			$existingmeta = json_decode(get_post_meta( $post->ID, 'metadata', true ), TRUE);
			$releaseids = get_post_meta( $post->ID, 'releaseids', true );
			// loop through ocds_metadata array to extract release tag and id for the project's metadata
			for ($i=0; $i<count($ocds_metadata); $i++) {
				if ($ocds_metadata[$i] == 'tag' && $existingmeta && is_array($existingmeta['tag'])) {
					// append tag to metadata tag field
					if (!in_array($savedjson['tag'][0], $existingmeta['tag'])) {
						$metadata[$ocds_metadata[$i]] = array_merge($existingmeta['tag'], $savedjson['tag']);
					} else {
						$metadata[$ocds_metadata[$i]] = $existingmeta['tag'];
					}
				} elseif ($ocds_metadata[$i] == 'id') {
					// replace metadata id with id from saved json
					$metadata['id'] = $savedjson['id'];
					if ($releaseids) {
						$metadataid = $releaseids.','.$savedjson['id'];
					} else {
						$metadataid = $savedjson['id'];
					}
					//print_r($metadataid);
				} else {
					$metadata[$ocds_metadata[$i]] = $savedjson[$ocds_metadata[$i]];
				}
			}
			$metadatajson = json_encode($metadata);
			
			//echo $releaseids;
			// add post meta for metadata
			if (! add_post_meta( $post->ID, 'metadata', $metadatajson, true )) {
				update_post_meta( $post->ID, 'metadata', $metadatajson );
			}
			// add post meta for release ids
			if (! add_post_meta( $post->ID, 'releaseids', $metadataid, true )) {
				update_post_meta( $post->ID, 'releaseids', $metadataid );
			}
		
		
			// retrieve parties
			if (! add_post_meta( $post->ID, 'roles', $_POST['partydata'], true )) {
				update_post_meta( $post->ID, 'roles', $_POST['partydata'] );
			}
			$party_ids = json_decode(stripslashes($_POST['partydata']));
			$party_info = array(); $j=0;
			$id_list = array(); $roles = array();
			for ($i=0; $i<count($party_ids); $i++) {
				$id_list[$party_ids[$i][0]] = array();
			}
			for ($i=0; $i<count($party_ids); $i++) {
				array_push($id_list[$party_ids[$i][0]], $party_ids[$i][1]);
			}
			//print_r($id_list);exit;
			foreach ($id_list as $id=>$role) {
				$idarray = explode("-", $id);
				$realid = end($idarray);
				$party_post = get_post($realid);
				//print_r($id_list); print_r($idarray); print_r($party_post); exit;
				if ($party_post) {
					$party_info[$j] = get_post_meta( $realid, 'organisation_fields', true );
					$party_info[$j]['party_name'] = $party_post->post_title;
					$party_info[$j]['party_id'] = $id;
					$party_info[$j]['roles'] = $role;
					$j++;
				} 
			}
			//print_r($party_info);exit;
			// get parties json structure from file and populate it
			$partyarray = json_decode(file_get_contents(WP_CONTENT_DIR . '/plugins/opencontractr/schema/parties.json'));
			$new_parties = array();
			for ($i=0; $i<count($party_info); $i++) {
				$new_party = array();
				foreach ($partyarray as $key=>$value) {
					if (is_object($value)) {
						foreach ($value as $item=>$val) {
							if ($key == 'additionalIdentifiers') {
								if ($party_info[$i]['additionalScheme'] != '') {
									$new_parties[$i][$key]['scheme'] = $party_info[$i]['additionalScheme'];
								}
								if ($party_info[$i]['additionalId'] != '') {
									$new_parties[$i][$key]['id'] = $party_info[$i]['additionalId'];
								}
								if ($party_info[$i]['additionalLegalName'] != '') {
									$new_parties[$i][$key]['legalName'] = $party_info[$i]['additionalLegalName'];
								}
								if ($party_info[$i]['additionalUri'] != '') {
									$new_parties[$i][$key]['uri'] = $party_info[$i]['additionalUri'];
								}
							} else {
								if ($party_info[$i][$item] != '') {
									$new_parties[$i][$key][$item] = $party_info[$i][$item];
								}
							}
						}
					} elseif (is_array($value)) {  // for the roles array
						if ( !empty($party_info[$i][$key]) ) {
							$new_parties[$i][$key] = $party_info[$i][$key];
						}
					} else {
						if ($party_info[$i]['party_id'] != '') {
							$new_parties[$i]['id'] = $party_info[$i]['party_id'];
						}
						if ($party_info[$i]['party_name'] != '') {
							$new_parties[$i]['name'] = $party_info[$i]['party_name'];
						}
					}
				}
			}
			//print_r($new_parties);exit;
			$savedjson['parties'] = $new_parties;
			
			
			// retrieve buyers
			// ----
			
			// save sections
			$sectionslist = '';
			foreach ($ocds_sections as $key=>$section) {
				$sectionslist .= $key.',';
			}
			$savedsections = $_POST['savedsections'] ? $_POST['savedsections'] : rtrim($sectionslist, ',');
			// update individual ocds sections with data from the created release
			$savedsections = explode(',', $savedsections);
			//print_r($savedsections);
			for ($i=0; $i<count($savedsections); $i++) {
				$sectionjson = json_encode($savedjson[$savedsections[$i]]);
				if ($sectionjson != 'null') {
					if (! add_post_meta( $post->ID, $savedsections[$i], $sectionjson, true )) {
						update_post_meta( $post->ID, $savedsections[$i], $sectionjson );
					}
				}
				//echo $sectionjson;
			}
			
			// create/update other post meta for searchability
			create_search_post_meta($post->ID);
		}
	}
}


###### ORGANISATIONS #######

add_action( 'init', 'org_register_post_type' );

function org_register_post_type() {
	register_post_type( 'organisations',
		array(
			'labels' => array(
					//'name' => __( 'Organisations' ),
					//'singular_name' => __( 'Organisation' ),
					'name'                  => _x( 'Organisations', 'Post type general name', 'opencontractr' ),
					'singular_name'         => _x( 'Organisation', 'Post type singular name', 'opencontractr' ),
					'menu_name'             => _x( 'Organisations', 'Admin Menu text', 'opencontractr' ),
					'name_admin_bar'        => _x( 'Organisations', 'Add New on Toolbar', 'opencontractr' ),
					'parent_item_colon'     => _x( 'Parent Organisation', 'opencontractr' ),
					'all_items'             => _x( 'Organisations', 'opencontractr' ),
					'view_item'             => _x( 'View Organisation', 'opencontractr' ),
					'add_new_item'          => _x( 'Add Organisation', 'opencontractr' ),
					'add_new'               => _x( 'Add Organisation', 'opencontractr' ),
					'edit_item'             => _x( 'Edit Organisation', 'opencontractr' ),
					'update_item'           => _x( 'Update Organisation', 'opencontractr' ),
					'search_items'          => _x( 'Search Organisation', 'opencontractr' ),
					'not_found'             => _x( 'Not Found', 'opencontractr' ),
					'not_found_in_trash'    => _x( 'Not found in Trash', 'opencontractr' ),
			),
			'public' => true,
			'has_archive' => true,
			'show_in_menu' => 'edit.php?post_type=open_contract',
			'supports'  => array( 'title' ),
		)
	);
}

add_action( 'add_meta_boxes', 'organisation_meta_boxes' );

function organisation_meta_boxes() {
    $screen = get_current_screen();
    if('add' != $screen->action ) {
		add_meta_box( 'identifier_meta_box', __('Identifier','opencontractr'), 'display_organisation_identifier_meta_box', 'organisations', 'normal', 'high');
		add_meta_box( 'address_meta_box', __('Address','opencontractr'), 'display_organisation_address_meta_box', 'organisations', 'normal', 'high');
		add_meta_box( 'contactpoint_meta_box', __('Contact Point','opencontractr'), 'display_organisation_contactpoint_meta_box', 'organisations', 'normal', 'high');
		add_meta_box( 'additionalidentifier_meta_box', __('Additional Identifier','opencontractr'), 'display_organisation_additionalidentifier_meta_box', 'organisations', 'normal', 'high');
    }
}

function display_organisation_identifier_meta_box() {
	global $post;  
		$meta = get_post_meta( $post->ID, 'organisation_fields', true ); ?>

	<input type="hidden" name="organisation_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

    <!-- All fields will go here -->
	<table>
		<tr>
			<td><label for="organisation_fields[scheme]">Scheme: </label></td>
			<td><input type="text" name="organisation_fields[scheme]" id="organisation_fields[scheme]" class="regular-text" value="<?php echo $meta['scheme']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[id]">ID: </label></td>
			<td><input type="text" name="organisation_fields[id]" id="organisation_fields[id]" class="regular-text" value="<?php echo $meta['id']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[legalName]">Legal Name: </label></td>
			<td><input type="text" name="organisation_fields[legalName]" id="organisation_fields[legalName]" class="regular-text" value="<?php echo $meta['legalName']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[uri]">URI: </label></td>
			<td><input type="text" name="organisation_fields[uri]" id="organisation_fields[uri]" class="regular-text" value="<?php echo $meta['uri']; ?>"></td>
		</tr>
	</table>

	<?php }
	
	
function display_organisation_address_meta_box() {
	global $post;  
		$meta = get_post_meta( $post->ID, 'organisation_fields', true ); ?>

	<input type="hidden" name="organisation_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

    <!-- All fields will go here -->
	<table>
		<tr>
			<td><label for="organisation_fields[streetAddress]">Street Address: </label></td>
			<td><input type="text" name="organisation_fields[streetAddress]" id="organisation_fields[streetAddress]" class="regular-text" value="<?php echo $meta['streetAddress']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[locality]">Locality: </label></td>
			<td><input type="text" name="organisation_fields[locality]" id="organisation_fields[locality]" class="regular-text" value="<?php echo $meta['locality']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[region]">Region: </label></td>
			<td><input type="text" name="organisation_fields[region]" id="organisation_fields[region]" class="regular-text" value="<?php echo $meta['region']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[postalCode]">Postal Code: </label></td>
			<td><input type="text" name="organisation_fields[postalCode]" id="organisation_fields[postalCode]" class="regular-text" value="<?php echo $meta['postalCode']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[countryName]">Country Name: </label></td>
			<td><input type="text" name="organisation_fields[countryName]" id="organisation_fields[countryName]" class="regular-text" value="<?php echo $meta['countryName']; ?>"></td>
		</tr>
	</table>

	<?php }
	
	
function display_organisation_contactpoint_meta_box() {
	global $post;  
		$meta = get_post_meta( $post->ID, 'organisation_fields', true ); ?>

	<input type="hidden" name="organisation_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

    <!-- All fields will go here -->
	<table>
		<tr>
			<td><label for="organisation_fields[name]">Name: </label></td>
			<td><input type="text" name="organisation_fields[name]" id="organisation_fields[name]" class="regular-text" value="<?php echo $meta['name']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[email]">Email: </label></td>
			<td><input type="text" name="organisation_fields[email]" id="organisation_fields[email]" class="regular-text" value="<?php echo $meta['email']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[telephone]">Telephone: </label></td>
			<td><input type="text" name="organisation_fields[telephone]" id="organisation_fields[telephone]" class="regular-text" value="<?php echo $meta['telephone']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[faxNumber]">Fax Number: </label></td>
			<td><input type="text" name="organisation_fields[faxNumber]" id="organisation_fields[faxNumber]" class="regular-text" value="<?php echo $meta['faxNumber']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[url]">URL: </label></td>
			<td><input type="text" name="organisation_fields[url]" id="organisation_fields[url]" class="regular-text" value="<?php echo $meta['url']; ?>"></td>
		</tr>
	</table>

	<?php }
	
	
function display_organisation_additionalidentifier_meta_box() {
	global $post;  
		$meta = get_post_meta( $post->ID, 'organisation_fields', true ); ?>

	<input type="hidden" name="organisation_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">

    <!-- All fields will go here -->
	<table>
		<tr>
			<td><label for="organisation_fields[additionalScheme]">Scheme: </label></td>
			<td><input type="text" name="organisation_fields[additionalScheme]" id="organisation_fields[additionalScheme]" class="regular-text" value="<?php echo $meta['additionalScheme']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[additionalId]">ID: </label></td>
			<td><input type="text" name="organisation_fields[additionalId]" id="organisation_fields[additionalId]" class="regular-text" value="<?php echo $meta['additionalId']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[additionalLegalName]">Legal Name: </label></td>
			<td><input type="text" name="organisation_fields[additionalLegalName]" id="organisation_fields[additionalLegalName]" class="regular-text" value="<?php echo $meta['additionalLegalName']; ?>"></td>
		</tr>
		<tr>
			<td><label for="organisation_fields[additionalUri]">URI: </label></td>
			<td><input type="text" name="organisation_fields[additionalUri]" id="organisation_fields[additionalUri]" class="regular-text" value="<?php echo $meta['additionalUri']; ?>"></td>
		</tr>
	</table>

	<?php }


function save_organisation_meta( $post_id ) {   
	// verify nonce
	if ( !wp_verify_nonce( $_POST['organisation_meta_box_nonce'], basename(__FILE__) ) ) {
		return $post_id; 
	}
	// check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// check permissions
	if ( 'organisations' === $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}  
	}
	
	$old = get_post_meta( $post_id, 'organisation_fields', true );
	$new = $_POST['organisation_fields'];

	if ( $new && $new !== $old ) {
		update_post_meta( $post_id, 'organisation_fields', $new );
	} elseif ( '' === $new && $old ) {
		delete_post_meta( $post_id, 'organisation_fields', $old );
	}
}
add_action( 'save_post', 'save_organisation_meta' );

function add_organisation_form() {
	?>
	<div id="organisation-box" style="display:none">
			<form action="" id="organisationform" method="POST">
				<?php wp_nonce_field( 'post_nonce', 'post_nonce_field' ); ?>
				<input type="hidden" id="targetInput" value="">
				<label for="postTitle">Organisation Name</label>
				<input type="text" name="postTitle" id="postTitle" class="organisation-title" />
				<table class="organisation-info">
					<tr><td colspan=2 class="organisation-info-title">Identifier</td></tr>
					<tr>
						<td class="label"><label for="organisation_fields[scheme]">Scheme: </label></td>
						<td><input type="text" name="organisation_fields[scheme]" id="organisation_fields[scheme]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[id]">ID: </label></td>
						<td><input type="text" name="organisation_fields[id]" id="organisation_fields[id]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[legalName]">Legal Name: </label></td>
						<td><input type="text" name="organisation_fields[legalName]" id="organisation_fields[legalName]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[uri]">URI: </label></td>
						<td><input type="text" name="organisation_fields[uri]" id="organisation_fields[uri]" class="regular-text" value=""></td>
					</tr>
				</table>
				
				<table class="organisation-info">
					<tr><td colspan=2 class="organisation-info-title">Address</td></tr>
					<tr>
						<td class="label"><label for="organisation_fields[streetAddress]">Street Address: </label></td>
						<td><input type="text" name="organisation_fields[streetAddress]" id="organisation_fields[streetAddress]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[locality]">Locality: </label></td>
						<td><input type="text" name="organisation_fields[locality]" id="organisation_fields[locality]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[region]">Region: </label></td>
						<td><input type="text" name="organisation_fields[region]" id="organisation_fields[region]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[postalCode]">Postal Code: </label></td>
						<td><input type="text" name="organisation_fields[postalCode]" id="organisation_fields[postalCode]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[countryName]">Country Name: </label></td>
						<td><input type="text" name="organisation_fields[countryName]" id="organisation_fields[countryName]" class="regular-text" value=""></td>
					</tr>
				</table>
				
				<table class="organisation-info">
					<tr><td colspan=2 class="organisation-info-title">Contact Point</td></tr>
					<tr>
						<td class="label"><label for="organisation_fields[name]">Name: </label></td>
						<td><input type="text" name="organisation_fields[name]" id="organisation_fields[name]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[email]">Email: </label></td>
						<td><input type="text" name="organisation_fields[email]" id="organisation_fields[email]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[telephone]">Telephone: </label></td>
						<td><input type="text" name="organisation_fields[telephone]" id="organisation_fields[telephone]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[faxNumber]">Fax Number: </label></td>
						<td><input type="text" name="organisation_fields[faxNumber]" id="organisation_fields[faxNumber]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[url]">URL: </label></td>
						<td><input type="text" name="organisation_fields[url]" id="organisation_fields[url]" class="regular-text" value=""></td>
					</tr>
				</table>
				
				<table class="organisation-info">
					<tr><td colspan=2 class="organisation-info-title">Additional Identifier</td></tr>
					<tr>
						<td class="label"><label for="organisation_fields[additionalScheme]">Scheme: </label></td>
						<td><input type="text" name="organisation_fields[additionalScheme]" id="organisation_fields[additionalScheme]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[additionalId]">ID: </label></td>
						<td><input type="text" name="organisation_fields[additionalId]" id="organisation_fields[additionalId]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[additionalLegalName]">Legal Name: </label></td>
						<td><input type="text" name="organisation_fields[additionalLegalName]" id="organisation_fields[additionalLegalName]" class="regular-text" value=""></td>
					</tr>
					<tr>
						<td><label for="organisation_fields[additionalUri]">URI: </label></td>
						<td><input type="text" name="organisation_fields[additionalUri]" id="organisation_fields[additionalUri]" class="regular-text" value=""></td>
					</tr>
				</table>
				<input type="hidden" name="submit-org" id="submit-org" value="true" />
			</form>
			<button id="submit-organisation">Add Organisation</button><span class="org-error"></span>
		</div>
	<?php
}
