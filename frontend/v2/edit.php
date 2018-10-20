<?php
/**
 * Template Name: OpenContractr Editor
 *
 * This template is used to edit OCDS data at the front end.
 *
 * @package WordPress
 * @subpackage OpenContractr
 * @since 1.0
 * @version 1.0
 */

if ( !is_user_logged_in() ){
	
	//echo "Sign in to see the content!";
	//wp_login_form( array( 'echo' => true ) );
	wp_redirect( wp_login_url().'?redirect_to='.$_SERVER['REQUEST_URI'] );
	
} else {
	
	if ( isset($_REQUEST['data']) ) {
		
		header("Content-type: application/json; charset=utf-8");
		
		if ($_REQUEST['data'] == 'organisations') {
		
			$args = array(
				'post_type' => 'organisations',
				'post_status' => 'publish',
				'posts_per_page' => -1 // all posts
			);
			
			$posts = get_posts( $args );
			
			if( $posts ) :
				foreach( $posts as $k => $post ) {
					$source[$k]['ID'] = $post->ID;
					$source[$k]['label'] = $post->post_title; // The name of the post
					$source[$k]['permalink'] = get_permalink( $post->ID );
				}
			endif;
			echo json_encode($source);
			exit;
			
		} elseif ($_REQUEST['data'] == 'fields') {
			
			echo json_encode( get_option('field_options') );
			exit;
			
		} elseif ($_REQUEST['data'] == 'contracts') {
			
			$args = array(
				'post_type' => 'open_contract',
				'post_status' => 'publish',
				'posts_per_page' => -1 // all posts
			);
			
			$posts = get_posts( $args );
			
			if( $posts ) :
				foreach( $posts as $k => $post ) {
					$source[$k]['ID'] = $post->ID;
					$source[$k]['label'] = $post->post_title; // The name of the post
				}
			endif;
			echo json_encode($source);
			exit;
			
		}
	
	} else {
		
		$postTitleError = '';
	
		if ( isset( $_POST['submit-org'] ) && isset( $_POST['post_nonce_field'] ) && wp_verify_nonce( $_POST['post_nonce_field'], 'post_nonce' ) ) {
			
			if ( trim( $_POST['postTitle'] ) === '' ) {
				
				echo '{"error":"Please enter the name of the organisation"}';
				exit;
				
			} else {
		 
				$post_information = array(
					'post_title' => wp_strip_all_tags( $_POST['postTitle'] ),
					'post_type' => 'organisations',
					'post_status' => 'publish'
				);
			 
				$organisation_id = wp_insert_post( $post_information );
				$organisation_info = '';
				
				if ( 'organisations' === $_POST['post_type'] ) {
					if ( !current_user_can( 'edit_page', $post_id ) ) {
						return $organisation_id;
					} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
						return $organisation_id;
					}  
				}
				$new = $_POST['organisation_fields'];
				add_post_meta( $organisation_id, 'organisation_fields', $new );
				
				if ($organisation_id) {
					$organisation_info = json_encode(array(
											'success' => true,
											'name'=> wp_strip_all_tags( $_POST['postTitle'] ),
											'id'  => $organisation_id
										));
				}
				echo $organisation_info;
			}
			
			exit;
		}
		
		if ( isset($_POST['savedjson']) ) {
			save_ocds_form($_REQUEST['id']);
		}
		
		$contract_post_id = $_REQUEST['id'];
		$ocid = get_post_meta( $contract_post_id, 'ocid', true );
		if ( !$ocid && $ocid == '' ) {
			$invalid_contract = true;
		} else {
			$invalid_contract = false;
		}
		$mergedsections = array();
		
		add_action('init', 'init_theme_method');
		function init_theme_method() {
		   add_thickbox();
		}
		
		//get_header();
		require_once('header.php');
?>

	<?php if ($invalid_contract) { ?>

	<div id="main" class="site-main" role="main">
		<h2>Oops! There's no contract here.</h2>
		<span><a href="?do=search" target="_blank">Click here to search for the contract you're looking for.</a></span>
		<div id="wrap"></div>
	</div>

	<?php } else { ?>
	
	<div id="subheader">
		<h4>Contract: <select id="contractname"></select></h4>
		<span class="releasetags">
			<?php
			echo '<select id="releasetaglist">';
			echo '<option value="">Choose a description of changes made</option>';
			foreach($release_tags as $tag => $value) {
				echo '<option value="'.$tag.'">'.$value[0].'</option>';
			}
			echo '</select>';
			?>
		</span>
		<span class="releasetip tooltip icon fa-question-circle" title="Select an option that best describes the changes made to this contract"></span><a href="#title_header" class="scrolly"></a>
	</div>
		
	</div>
	<!-- Nav -->
	<nav id="nav">
		<div id="stickynav">
			<ul class="links">
				<?php
				foreach ($ocds_sections as $key=>$section) {
					$sectionkey = strtolower($section[0]);
					$sectionvalue = get_post_meta( $contract_post_id, $sectionkey, true );
					if ($sectionvalue) {
						$mergedsections[$sectionkey] = json_decode($sectionvalue, TRUE);
					}
					if ($key != 'parties' && $key != 'buyer') {
						echo '<li><a href="#" data-id="'.$key.'" class="scrolly">'.$key.'</a></li>';
					}
				}
				?>
				<li class="action">
					<input type="hidden" id="newform" name="newform" value="true">
					<input type="button" id="getData" style="display: none">
					<input type="button" id="saveData" class="button save" value="Save">
				</li>
				<li class="action"><span class="open icon fa-arrow-down"></span></li>
			</ul>
		</div>
	</nav>
	<nav id="subnav">
		<div id="stickysubnav">
			<ul>
				<li>
					<div>
						<span><a href="#TB_inline?width=650&height=550&inlineId=buyer" class="thickbox changebuyer" title="Change default buyer">Edit Buyer</a></span>
					</div>
				</li>
				<li id="actions">
					<span class="label">Jump to:</span>
					<select id="fieldpaths">
						<option value="">Jump to any field</option>
					</select>
					<span><a href="" id="jump" class="fieldfinder"></a></span>
					<span><input type="checkbox" name="showid" id="showid" value="true" checked>Hide IDs</span>
				</li>
			</ul>
			<span class="close icon fa-close"></span>
		</div>
	</nav>
	<div id="main" class="site-main" role="main">
		<div class="edit-intro">
			<?php
				$permalink = get_permalink( $_REQUEST['id'] );
				$json_download_url = $permalink . '?action=download&type=json';
				$csv_download_url = $permalink . '?action=download&type=csv';
				if ( parse_url($_SERVER['HTTP_REFERER'])['query'] == 'do=create' ) {
			?>
			<h2>Awesome! You've created a new contract.</h2>
			<p>Now you can go ahead to <a href="#" class="edit">edit it</a>, or just download it as <a href="<?php echo $csv_download_url ?>">CSV</a> or <a href="<?php echo $json_download_url ?>">JSON</a>.</p>
			<?php
				} elseif ( isset($_POST['savedjson']) ) {
			?>
			<h2>You successfully updated the contract!</h2>
			<p>Keep <a href="#" class="edit">editing it</a>, or download it as <a href="<?php echo $csv_download_url ?>">CSV</a> or <a href="<?php echo $json_download_url ?>">JSON</a>.</p>
			<?php
				} else {
			?>
			<h2>Start <a href="#" class="edit">editing this</a> contract...</h2>
			<p>Or you can just download it as <a href="<?php echo $csv_download_url ?>">CSV</a> or <a href="<?php echo $json_download_url ?>">JSON</a>.</p>
			<?php
				}
			?>
		</div>
		<input type="hidden" id="json_download_url" value="<?php echo $json_download_url ?>">
		<textarea id="jsoninput" name="jsoninput" style="display:none"><?php echo json_encode($mergedsections); ?></textarea>
		<?php data_entry_form(); ?>
		<?php add_organisation_form(); ?>
	</div><!-- .content-area -->
	
	<?php } // end if $invalid_contract ?>
	
	<div id="copyright">
		<ul><li>&copy; Centre for Open Data Research</li></ul>
	</div>

	
<?php

	} // end if $data
	
} // end if !user_logged_in

wp_footer();
?>
