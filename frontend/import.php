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
	if ( isset($_POST['savedjson']) ) {
		$imported = import_to_new_post();
		if ($imported == 1) {
			$message = $imported.' dataset imported. Have a look.';
		} elseif ($imported > 1) {
			$message = $imported.' datasets imported. Have a look.';
		}
	}
	//get_header();
	require_once('header.php');
?>

	<div id="main" class="site-main" role="main">
		<div id="wrap">
			<form action="" method="post" name="importform" id="importform" enctype="multipart/form-data">
				<h2>Import OCDS data</h2>
				<p>This imports data from other sources and adds them to your list of contracts</p>
				<hr>
				<div>
					<input type="file" name="importfile" id="importfile" class="importfile">
					<label for="importfile">Select a file... <br><small>It could be a json, csv or zip file</small></label>
				</div>
				<span>–OR–</span>
				<div class="importurlbox">
					<input type="text" name="importurl" id="importurl" class="importurl" placeholder="Enter a URL with valid OCDS data (Hint: It should begin with http://)">
				</div>
				<input type="hidden" name="savedtag" id="importedtag">
				<textarea id="importedjson" name="savedjson" style="display:none"></textarea>
				<input type="hidden" name="savedsections" id="importedsections">
				<input type="button" name="importaction" id="importbtn" value="Import it" class="importbtn">
				<br><span class="uploaderror"></span>
				<br><span class="importmessage"><a href="<?php echo admin_url('edit.php?post_type=open_contract') ?>" target="blank"><?php echo $message ?></a></span>
			</form>
		</div>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('#importbtn').click(function() {
			if( $("#importfile").get(0).files.length == 0 && !$("#importurl").val()){
				$('.uploaderror').html('Oops! No data source provided.');
				return false;
			}
			if ($("#importurl").val()) {
				if (!validate($("#importurl").val())) {
					$('.uploaderror').html('Sorry, the URL is invalid.');
					return false;
				}
			}
			var filedata = $('#importfile').prop('files')[0]
			var formdata = new FormData();
			formdata.append('file', filedata);
			formdata.append('url', $("#importurl").val());
			$.ajax({
				url: '<?php echo plugin_dir_url(__FILE__) ?>../php/import.php',
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
						//if ($('.targetimport:checked').val() == 'newpost') {
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
										$('#importedjson').html(JSON.stringify(importedjson));
										//console.log(importedjson)
										$('#importform').submit();
									} catch(e) {
										$('.uploaderror').html('Sorry, the data could not be read :(');
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
								$('.uploaderror').html('Sorry, the data could not be read :(');
								console.log(e)
							}
						//} 
					}
				}
			});
		});
	});
	</script>
	<div id="copyright">
		<ul><li>&copy; Centre for Open Data Research</li></ul>
	</div>

	</div><!-- #wrapper -->
<?php
	
} // end if !user_logged_in

wp_footer();

?>

	</body>
</html>
