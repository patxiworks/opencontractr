var release_tabs = ['planning','tender','buyer','awards','contracts']
var release_static = ['ocid','id','date','tag','initiationType', 'language', 'location']

$(document).ready(function () {
				// setting csrftoken for ajax calls
				function getCookie(name) {
					var cookieValue = null;
					if (document.cookie && document.cookie != '') {
						var cookies = document.cookie.split(';');
						for (var i = 0; i < cookies.length; i++) {
							var cookie = jQuery.trim(cookies[i]);
							// Does this cookie string begin with the name we want?
							if (cookie.substring(0, name.length + 1) == (name + '=')) {
								cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
								break;
							}
						}
					}
					return cookieValue;
				}
				var csrftoken = getCookie('csrftoken');
				
				function csrfSafeMethod(method) {
					// these HTTP methods do not require CSRF protection
					return (/^(GET|HEAD|OPTIONS|TRACE)$/.test(method));
				}
				$.ajaxSetup({
					beforeSend: function(xhr, settings) {
						if (!csrfSafeMethod(settings.type) && !this.crossDomain) {
							xhr.setRequestHeader("X-CSRFToken", csrftoken);
						}
					}
				});
				
				function setLayoutCookie(cname, cvalue, exdays) {
					var d = new Date();
					d.setTime(d.getTime() + (exdays*24*60*60*1000));
					var expires = "expires="+d.toUTCString();
					document.cookie = cname + "=" + cvalue + "; " + expires;
				}
				
				function getLayoutCookie(cname) {
					var name = cname + "=";
					var ca = document.cookie.split(';');
					for(var i=0; i<ca.length; i++) {
						var c = ca[i];
						while (c.charAt(0)==' ') c = c.substring(1);
						if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
					}
					return "";
				}
                
                if (!$('#input').html()) {
                      $('.panel-container').html('<span style="color:#fff">No data to display</span>')
                      $('input#jsonthis').hide()
                }
                
                $('#meta-info').hide();
                /*
                $('#meta-status').toggle(function(){
                                $('#meta-info table').slideDown();
                                $('#meta-info table').toggleClass("icon-circle-arrow-up icon-circle-arrow-down");
                 }, function(){
                                $('#meta-info table').slideUp();
                });
                */
				$('#mdalist').change(function() {
                      if ($(this).val()!=0) {
                          window.location.href = '/list/'+$(this).val()+'/';
                      }
                })
                
                
})

function capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
}

function updateFile( releaseid, updatedjson, originaljson, ocdsmeta, mda, action ) {
                append_mda = (mda=='') ? '' : mda+"/"
                $.ajax({
                           type: "POST",
                           //contentType: "application/json",
                           url: "/"+action+"/"+releaseid+"/"+append_mda,
                           data: {'updatedjson': updatedjson, 'originaljson': originaljson, 'meta': ocdsmeta },
                           //dataType: "json",
                           success : function(result) {
                                response = JSON.parse(result)
                                $('#fileupdate').html(response['message']).fadeIn(1500).delay(2000).fadeOut(1200, function() {
                                       if (!$('#importModal').length) {
                                             window.parent.$('#importModal').modal('hide');
                                             window.location.reload()
                                       }
                                       $('#fileupdate').html('Returning to OCID list...').fadeIn(200).delay(3000, function() {
                                                if (response['result'] == "OK") {
                                                        window.location.href = '/list/'+append_mda+'?updated='+releaseid
                                                }
                                       });
                                       
                               });
                               
                           },
                           error : function(xhr, status) {
                               alert('Sorry, there was a problem saving the data. Please try again a little later.');
                               $("#fileupdate").hide()
                               console.log(xhr.responseText)
                           },
                           complete : function(xhr, status) {
                           }
                 });
}



function importFile( mda, error ) {
                if (!error) {
                                append_mda = (mda=='') ? '' : mda+"/"
                                $.ajax({
                                           type: "POST",
                                           url: "/create-ocid/"+append_mda,
                                           success : function(result) {
                                                $('#output').val($.rejson('#reformed'));
                                                
                                                /*****************
                                                var BrutusinForms = brutusin["json-forms"];
                                                var bf = BrutusinForms.create(schema);
                                                    
                                                var container = document.getElementById('container');
                                                var input = JSON.parse($('#output').val($.rejson('#reformed')));
                                                bf.render(container, input);
                                                output = bf.getData();
                                                console.log(output)
                                                ******************/
                                                
                                                record = JSON.parse(result);
                                                ocidinfo = JSON.parse(record.Record);
                                                printStatus( 'Project with OCID: <strong>'+ocidinfo.ocid+'</strong> created', '<p>', '</p>' );
                                                printStatus( 'Creating sections...', '<em>', '</em>' );
                                                for (var i = 0; i < release_tabs.length; i++) {
                                                      createRelease( mda, ocidinfo.ocid, release_tabs[i], output );
                                                }
                                           },
                                           error : function(xhr, status) {
                                               alert('Sorry, there was a problem creating an ocid.');
                                               console.log(xhr.responseText)
                                           },
                                           complete : function(xhr, status) {
                                           }
                                 });
                } else {
                                printStatus( error, '<p>', '</p>' );  
                }
}


function createRelease( mda, ocid, stage, output ) {
                $.ajax({
                           type: "POST",
                           //contentType: "application/json",
                           url: "/create-record/release/"+mda+"/"+ocid+"/",
                           data: {'tag': stage },
                           success : function(result) {
                                record = JSON.parse(result)
                                stagejson = JSON.parse(record.Record)
                                printStatus( '-- '+stage+' stage (id: <strong>'+stagejson.id+'</strong>) created...', '<p>', '' );
                                //parse schema from output
                                schemajson = JSON.parse( $('#output').val() )
                                //schemajson = JSON.parse( output )
                                //add schema stage key|value (i.e. planning, tender etc.) to stage json
                                stagejson[stage] = schemajson[stage]
                                stagejson['tag'] = [stage] //add (missing) tag 
                                //insert new stage into textarea
                                stagestring = JSON.stringify( stagejson )
                                stageVal = $("<input type='hidden' id='"+stage+"'>").val(stagestring);
                                $("#release_outputs").append(stageVal)
                                updateImportFile( stagejson['id'], stagestring, mda, 'update' )
                           },
                           error : function(xhr, status) {
                               alert('Sorry, there was a problem creating releases');
                               console.log(xhr.responseText)
                           },
                           complete : function(xhr, status) {
                           }
                 });
}


function updateImportFile( releaseid, updatedjson, mda, action ) {
                append_mda = (mda=='') ? '' : mda+"/"
                $.ajax({
                           type: "POST",
                           //contentType: "application/json",
                           url: "/"+action+"/"+releaseid+"/"+append_mda,
                           data: {'updatedjson': updatedjson },
                           //dataType: "json",
                           success : function(result) {
                                printStatus( '-- '+ releaseid + ' updated in database', '<p>', '</p>' );
                               
                           },
                           error : function(xhr, status) {
                               alert('Sorry, there was a problem saving the data. Please try again a little later.');
                               console.log(xhr.responseText)
                           },
                           complete : function(xhr, status) {
                           }
                 });
}

function printStatus(message, starttag, endtag) {
     $('#import-status').show();
     $('#import-status').append(starttag + message + endtag)
     console.log(starttag + message + endtag)
}

var inputs = document.querySelectorAll( '.importfile' );
Array.prototype.forEach.call( inputs, function( input )
{
	var label	 = input.nextElementSibling,
		labelVal = label.innerHTML;

	input.addEventListener( 'change', function( e )
	{
		var fileName = '';
		if( this.files && this.files.length > 1 )
			fileName = ( this.getAttribute( 'data-multiple-caption' ) || '' ).replace( '{count}', this.files.length );
		else
			fileName = e.target.value.split( '\\' ).pop();

		if( fileName )
			label.querySelector( 'span' ).innerHTML = fileName;
		else
			label.innerHTML = labelVal;
	});
});