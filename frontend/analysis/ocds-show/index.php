<?php
 /*Template Name: OpenContractr Viewer
 */
//print_r(stripslashes(json_encode(get_current_record($post))));
//get_header(); ?>

<?php
$ocid = get_post_meta($post->ID, 'ocid', true);
$filespath = $datapath .$ocid;
//print_r(get_data_files($filespath));
$data = stripslashes(json_encode(get_data_files($filespath)));
//echo $data;
$templatespath = plugin_dir_url(__FILE__) . 'templates';
?>

<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title> </title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) ?>css/normalize.min.css">
        <link href="<?php echo plugin_dir_url( __FILE__ ) ?>css/main.css" rel="stylesheet">

        <link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) ?>css/custom.css">

    </head>
    <body>
      <nav class="navbar navbar-inverse">
        <div class="container">
          <a class="navbar-brand" href="#"><?php the_title(); ?></a>
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
      </div>
      <div class="container">

        <div id="input-json-container" class="hide">
          <h3> Input a valid OCDS record/release 
          <button id="hide-input-button" class="pull-right btn btn-primary btn-sm">Hide Input</button>
          </h3>
          <textarea id="input-json" name="input-json"></textarea>
        </div>

        <div id="container">
        </div>
      </div>
    </body>
    
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>js/nunjucks.min.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>js/merge.js"></script>
  <script src="<?php echo plugin_dir_url( __FILE__ ) ?>js/filereader.js"></script>
  <script src="<?php echo plugin_dir_url( __FILE__ ) ?>templates.js"></script>
    <script>

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
       var env = nunjucks.configure()
       // this needs replacing with something readable
       env.addFilter('currency', function(number) {
          //return number.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,')
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

       jsonInput.val(JSON.stringify(<?php echo $data ?>))
       render_json({"newData": true});

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




    </script>
</html>
