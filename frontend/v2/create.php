<?php


if ( !is_user_logged_in() ){
	
	wp_redirect( wp_login_url().'?redirect_to='.$_SERVER['REQUEST_URI'] );
	
} else {
	
	if ( isset($_POST['savedjson']) ) {
		
		$contract_title = ($_POST['posttitle'] ? $_POST['posttitle'] : 'Contract – '.time());
		// create new post
		$args = array(
			'post_type' 	=> 'open_contract',
			'post_title'	=> $contract_title,
			'post_status'	=> 'publish'
		);
		
		// inserts the post and runs save_new_opencontract() [see opencontractr.php line ~221]
		$post_id = wp_insert_post($args);
		// update $_POST['savedjson'] with ocid generated during post insertion
		//echo $_POST['savedjson'] . '<p>';
		$savedjson = json_decode(stripslashes($_POST['savedjson']), true);
		$_POST['ocid'] = $savedjson['ocid'] = get_post_meta($post_id, 'ocid', true);
		$savedjson['tag'] = ['planning'];
		$savedjson['id'] = $savedjson['ocid'] . '-planning-' . time();
		$_POST['savedjson'] = json_encode($savedjson);
		//print_r($_POST);exit;
		save_ocds_form($post_id);
		wp_redirect( '?id='.$post_id );
		exit;
	}
		
	$ocid = generate_ocid();
	$fieldsjson = file_get_contents(WP_CONTENT_DIR . '/plugins/opencontractr/schema/fieldsmap.json');
	$fields = json_decode($fieldsjson, true);
		
	//get_header();
	require_once('header.php');
?>
	<div id="subheader"><h4></h4></div>
	<div id="fieldsource">
	
		<div id="main" class="site-main" role="main">
			<textarea id="jsoninput" name="jsoninput" style="display:none"><?php echo json_encode($mergedsections); ?></textarea>
			<?php data_entry_form(); ?>
			<?php add_organisation_form(); ?>
		</div><!-- .content-area -->

	</div>
	
	<nav id="title"><h2><input type="text" id="contract-title" placeholder="Enter the contract title here..."></h2></nav>
	<nav id="nav">
		<div id="navigation" style="">
			<ul class="links">
				<?php
				foreach ($fields as $key=>$value) {
					$selected = ($i==0) ? 'selected' : '';
					$i++;
				?>
				<li class="<?php echo $selected ?>">
					<a href="#"><?php echo $fields[$key]['tab'] ?></a>
				</li>
				<?php } ?>
				<li class="buttons">
					<input type="hidden" id="newform" name="newform" value="true">
					<input type="button" id="getData" style="display: none">
					<input type="button" id="saveData" class="button save" value="Save">
				</li>
			</ul>
		</div>
	</nav>
	<div id="main" class="site-main" role="main">
		<div id="steps">
			<form id="formElem" name="formElem" action="" method="post">
				<?php foreach ($fields as $key=>$value) { ?>
				
				<fieldset class="step" id="<?php echo $key ?>">
					<legend>
						<span class="icon <?php echo $fields[$key]['icon'] ?>"></span>
						<span class="title"><?php echo $fields[$key]['title'] ?></span>
					</legend>
					<p class="description"><?php echo $fields[$key]['description'] ?></p>
					
				</fieldset>
					
				<?php } ?>
				
			</form>
        </div>
	</div><!-- .content-area -->
	<textarea type="hidden" id="fieldsjson" style="display: none"><?php echo $fieldsjson ?></textarea>
	
	<div id="copyright">
		<ul><li>&copy; Centre for Open Data Research</li></ul>
	</div>

	
<?php
	
} // end if !user_logged_in

wp_footer();
?>