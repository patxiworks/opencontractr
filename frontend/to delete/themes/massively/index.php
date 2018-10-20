<?php
 /*Template Name: OpenContractr Viewer
 */
//get_header();

$ocid = get_post_meta($post->ID, 'ocid', true);
$filespath = $datapath .$ocid;
//print_r(get_data_files($filespath));
$data = stripslashes(json_encode(get_data_files($filespath)));
//echo $data;

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>OpenContractr: The OCDS Publisher</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel='stylesheet' href='<?php echo get_plugin_url('frontend/css/') ?>bootstrap2.min.css' type='text/css' media='all' />
		<link rel='stylesheet' href='<?php echo get_plugin_url('frontend/css/') ?>custom.css' type='text/css' media='all' />
		<link rel='stylesheet' href='<?php echo get_plugin_url('frontend/css/') ?>main.css' type='text/css' media='all' />
		<link rel='stylesheet' href='<?php echo get_plugin_url('frontend/css/') ?>normalize.css' type='text/css' media='all' />
		<link rel='stylesheet' href='<?php echo get_plugin_url('frontend/css/') ?>normalize.min.css' type='text/css' media='all' />
		
		<link rel="stylesheet" href="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/css/main.css" />
		<noscript><link rel="stylesheet" href="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/css/noscript.css" /></noscript>
	</head>
	<body class="is-loading">

		<!-- Wrapper -->
			<div id="wrapper" class="fade-in">

				<!-- Intro -->
					<div id="intro">
						<h1>OpenContractr</h1>
						<p>An OCDS learning and publishing tool for organisations and government agencies in Africa</p>
						<ul class="actions">
							<li><a href="#title_header" class="button icon solo fa-arrow-down scrolly">Continue</a></li>
						</ul>
					</div>

				<!-- Header -->
					<header id="header">
						<span href="#" class="logo" id="title_header">About OpenContractr</span>
					</header>

				<!-- Nav -->
					<nav id="nav">
						<div id="stickynav">
							<ul class="links">
								<li class="active"><a href="#title_header" id="first" class="scrolly">Learn OCDS the right way</a></li>
								<li><a href="#title_header" id="second" class="scrolly">Validate and view your Data</a></li>
								<li><a href="#title_header" id="third" class="scrolly">Publish and Push your data</a></li>
								<li><a href="#title_header" id="fourth" class="scrolly">Get in Touch with us</a></li>
								<li class="icons"><a href="#header" class="icon fa-twitter"><span class="label">Twitter</span></a></li>
							</ul>
							<!--
							<ul class="icons">
								<li><a href="#" class="icon fa-twitter"><span class="label">Twitter</span></a></li>
								<li><a href="#" class="icon fa-facebook"><span class="label">Facebook</span></a></li>
								<li><a href="#" class="icon fa-instagram"><span class="label">Instagram</span></a></li>
								<li><a href="#" class="icon fa-github"><span class="label">GitHub</span></a></li>
							</ul>
							-->
						</div>
					</nav>

				<!-- Main -->
					<div id="main">

						<!-- Featured Post -->
							<article class="post featured page" id="first">
								<header class="major">

									<nav class="navbar navbar-inverse">
										<div class="container">
										  <a class="navbar-brand" href="#">OCDS Show</a>
										  <form class="navbar-form navbar-right" role="search">
											<div class="form-group">
												  <label class="btn btn-danger btn-file ">
													Upload File <input id="upload" type="file" class="form-control" style="display:none"/>
												  </label>
												  <button id="text-input" class="btn btn-danger">Text Input</button>
											</div>
										  </form>
										</div>
									</nav>
								  <div class="container">
							
									<div id="input-json-container" class="hide">
									  <h3> Input a valid OCDS record/release 
									  <button id="hide-input-button" class="pull-right btn btn-primary btn-sm">Hide Input</button>
									  </h3>
									  <textarea id="input-json" name="input-json"></textarea>
									</div>
							
									<div id="container"></div>
								  </div>
    
    

									
								</header>
									
							</article>
							
							<article class="post featured page" id="second">
								<header class="major">
									<h2>A tool to validate your OCDS Data</h2>
								</header>
								<p>Donec eget ex magna. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque venenatis dolor imperdiet dolor mattis sagittis magna etiam.</p>
								<ul class="actions">
									<li><a href="#" class="button">Full Story</a></li>
								</ul>
							</article>
							
							<article class="post featured page" id="third">
								<header class="major">
									<span class="date">April 24, 2017</span>
									<h2><a href="#">Page 3</h2>
								</header>
								<a href="#new" class="image fit scrolly"><img src="images/pic02.jpg" alt="" /></a>
								<p>Donec eget ex magna. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque venenatis dolor imperdiet dolor mattis sagittis magna etiam.</p>
								<ul class="actions">
									<li><a href="#" class="button">Full Story</a></li>
								</ul>
							</article>
							
							<article class="post featured page" id="fourth">
								<header class="major">
									<span class="date">April 24, 2017</span>
									<h2><a href="#">Page 4</h2>
								</header>
								<a href="#new" class="image fit scrolly"><img src="images/pic02.jpg" alt="" /></a>
								<p>Donec eget ex magna. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque venenatis dolor imperdiet dolor mattis sagittis magna etiam.</p>
								<ul class="actions">
									<li><a href="#" class="button">Full Story</a></li>
								</ul>
							</article>

					</div>

				<!-- Footer -->
					

				<!-- Copyright -->
					<div id="copyright">
						<ul><li>&copy; Untitled</li><li>Design: <a href="https://html5up.net">HTML5 UP</a></li></ul>
					</div>

			</div>
			
			<!-- Scripts -->
			<script src="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/js/jquery.min.js"></script>
			<script src="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/js/jquery.scrollex.min.js"></script>
			<script src="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/js/jquery.scrolly.min.js"></script>
			<script src="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/js/skel.min.js"></script>
			<script src="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/js/util.js"></script>
			<script src="<?php echo get_plugin_url('frontend/themes/massively/') ?>assets/js/main.js"></script>
			
			<script src="<?php echo get_plugin_url('frontend/js/') ?>nunjucks.min.js"></script>
			<script src="<?php echo get_plugin_url('frontend/js/') ?>filereader.js"></script>
			<script src="<?php echo get_plugin_url('frontend/js/') ?>bootstrap.min.js"></script>
			<script src="<?php echo get_plugin_url('frontend/js/') ?>merge.js"></script>
			<script>
    $(document).ready(function() {
		
		$('.links li').click(function() {
			$('.links li').removeClass('active')
			$(this).addClass('active');
			$('article.page').hide();
			id = $(this).find('a').attr('href').substring(1)
			id = $(this).find('a').attr('id')
			$('article.page#'+id).show().fadeIn();
		});
		$('.links li.active').click();
		
		stickyTop = $('#stickynav').offset().top - $('#stickynav').height();
		navWidth = $('#nav').width();
		$(window).on('scroll', function() {
			if ($(window).scrollTop() >= stickyTop) {
                $('#stickynav').css({position:"fixed", top:"0px", width:"72rem"});
            } else {
				$('#stickynav').css({position:"relative", top:"0px"});
			}
		})
       var jsonInput = $('#input-json')
       var container = $('#container')
       var jsonInputView = function() {
         return !$("#input-json-container").hasClass("hide")
       }


       FileReaderJS.setupInput(document.getElementById('upload'), {
         readAsDefault: 'Text',
         on: {
           load: function (event, file) {
             jsonInput.val(event.target.result);
             render_json({"newData": true});
           }
         }
       });

       
       var gettext = function(text) {
          return text
       }
       var env = nunjucks.configure('<?php echo get_plugin_url('frontend/templates/') ?>', {autoescape: true})
       // this needs replacing with something readable
       env.addFilter('currency', function(number) {
          //return number.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,')   # something wrong with this
          return number;
       })

       var render_json = function (context) {
         context = context || {}
         var currentValue = jsonInput.val()
         if (!currentValue & jsonInputView()) {
           container.html('')
           return
         }
         if (!currentValue) {
           container.html('<h1> Welcome to OCDS Show. </h1> <h4>Please supply either an OCDS release or OCDS record. Use upload file or text input buttons above.</h4>')
           return
         }

         try {
           var input = JSON.parse(jsonInput.val())
         } catch (e) {
           container.html('<h2> Invalid JSON data </h2>')
           return
         }

         input['gettext'] = gettext
        
         if (input.hasOwnProperty("records")) {
           input.ocids = input.records.map(function (value) {
             return value.ocid
           })
           if (context.newData) {
             id = input.ocids[0]
           } else {
             id = $('#id-select').val() 
           }
           input['ocid'] = id

           var current_record;
            
           input.records.some(function (value) {
             if (value.ocid === id) {
               current_record = value
               return true
             }
           })

           var releaseNumber = context["releaseNumber"] || 0
           input['releaseNumber'] = releaseNumber
           input['releases'] = current_record.releases
           var prev_release = merge(input.releases.slice(0, releaseNumber))
           var current_release =  merge(input.releases.slice(0, releaseNumber + 1))

           var changes = get_changes(flatten_all(prev_release), flatten_all(current_release))

           input['release'] = augment_path(current_release)

           //console.log(input['release'])

           //console.log(changes)

           function get_change(obj, field) {
             if (!obj) {return}
             var path = obj.__path;
             if (!path) {return}
             var path_list = JSON.parse(path)
             if (field) {
               path_list.push(field)
             }
             var full_path = JSON.stringify(path_list)
             return changes[full_path]
           }
           input['get_change'] = get_change


           container.empty()
           var content = env.render('record_select.html', input);
           container.append(content)
           var content = env.render('record_release.html', input);
           //console.log(input)
           container.append(content)
         } else {
           input.release_ids = input.releases.map(function (value) {
             return value.id
           })
           if (context.newData) {
             id = input.release_ids[0]
           } else {
             id = $('#id-select').val() 
           }
           var current_release;

           input.releases.some(function (value) {
             if (value.id === id) {
               current_release = value
               return true
             }
           })
           container.empty()
           input['release'] = current_release
           input['release_id'] = id
           var content = env.render('release_select.html', input);
           container.append(content)
           var content = env.render('release.html', input);
           container.append(content)
         }
       }
       
       jsonInput.val(JSON.stringify(<?php echo $data ?>));
       render_json({"newData": true});

       /*jsonInput.val("")
       render_json({"newData": true});*/

       $('#input-json').on("input", function(e) {
         render_json({"newData": true});
       })

       $('#container').on("click", ".release-button", function(e) {
         render_json({"releaseNumber": $(this).data()["releaseNumber"]})
       })

       $('#hide-input-button').on("click", function(e) {
         e.preventDefault()
         $("#input-json-container").addClass("hide")
       })

       $('#text-input').on("click", function(e) {
         e.preventDefault()
         if (jsonInputView()) {
           $("#input-json-container").addClass("hide")
         } else {
           $("#input-json-container").removeClass("hide")
         }
         render_json({"newData": true});
       })

       $('#container').on("click", ".nav a", function(e) {
         e.preventDefault()
         if (!$(this).parent().hasClass("disabled")) {
           $(this).tab('show');

         }
       })

       $('#container').on("change", "#id-select", function(e) {
         e.preventDefault()
         render_json()
       })



    });
    </script>
			<?php get_footer(); ?>
	</body>
</html>