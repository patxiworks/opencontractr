function runEditor() {


//var schema = JSON.parse(JSON.stringify($('#schema').val()));
var codelists = JSON.parse($('#codelists').html());
var release_tabs = ['planning','tender','buyer','awards','contracts']
var metafields = ['ocid','id','date','tag','initiationType', 'language', 'location']
mandatoryfields = [
		'buyer-name',
		'buyer-address-streetAddress',
		'buyer-address-locality',
		'buyer-address-region',
		'buyer-address-postalCode',
		'buyer-address-countryName',
		'buyer-contactPoint-name',
		'planning-rationale',
		'planning-budget-amount-amount',
		'planning-budget-amount-currency',
		'planning-documents-title',
		'planning-documents-datePublished',
		'tender-id',
		'tender-title',
		'tender-description',
		'tender-status',
		'tender-procurementMethod',
		'tender-procurementMethodRationale',
		'tender-awardCriteria',
		'tender-submissionMethod',
		'tender-tenderPeriod-startDate',
		'tender-tenderPeriod-endDate',
		'tender-enquiryPeriod-startDate',
		'tender-enquiryPeriod-endDate',
		'tender-items-id',
		'tender-items-description',
		'tender-items-quantity',
		'tender-tenderers-identifier-legalName',
		'tender-documents-id',
		'tender-documents-url',
		'tender-documents-datePublished',
		'awards-id',
		'awards-title',
		'awards-status',
		'awards-date',
		'awards-value-amount',
		'awards-value-currency',
		'awards-suppliers-name',
		'awards-suppliers-address-streetAddress',
		'awards-suppliers-address-locality',
		'awards-suppliers-address-region',
		'awards-suppliers-address-postalCode',
		'awards-suppliers-address-countryName',
		'awards-items-description',
		'awards-items-quantity',
		'contracts-id',
		'contracts-awardID',
		'contracts-title',
		'contracts-status',
		'contracts-period-startDate',
		'contracts-period-endDate',
		'contracts-dateSigned',
		'contracts-value',
		'contracts-value-amount',
		'contracts-value-currency',
		'contracts-items-description',
		'contracts-items-quantity',
		'contracts-items-unit',
		'contracts-documents-title',
		'contracts-documents-url',
		'contracts-documents-datePublished',
		'contracts-documents-dateModified',
		'contracts-implementation-transactions-id',
		'contracts-implementation-transactions-date',
		'contracts-implementation-transactions-amount-amount',
		'contracts-implementation-transactions-amount-currency',
		'contracts-implementation-documents'
	]

function renderSchema(schema) {
    var BrutusinForms = brutusin["json-forms"];
    var bf = BrutusinForms.create(schema);
        
    var container = document.getElementById('container');
    var input = document.getElementById('input').value ? JSON.parse(document.getElementById('input').value) : '';
    //bf.render(container);
	
	$('#jsonthis').click(function() {
        output = bf.getData();
        section = $('#release-tag').val();
        $('#saveamendment').prop( "disabled", true ).css("opacity", 0);
        if (checkMandatoryFields($(container), '#'+section)) {
            amended = input['amended']
            delete input['amended']
            output['amended'] = amended;
            //set date in ISO – the original OCDS format
            d = new Date();
            n = d.toISOString();
            output['date'] = n
            //console.log(input)
            //console.log(output)
            if(!_.isEqual(output, input)) { // _.isEqual function is from lodash.min.js
                strOutput = JSON.stringify(output)
                strData = JSON.stringify(input)
                $('#saveamendment').prop( "disabled", false ).css("opacity", 1);
            } else {
                $('#saveamendment').prop( "disabled", true ).css("opacity", 0);
            }
    
            $('#saveamendment').click(function() {
                updateFile( $('#ocid').val(), strOutput, strData, $('#meta').val(), $('#currentmda').val(), 'update' )
            });
        } else {
            alert("Validation failed. Please fill all the mandatory fields.");
            $('html, body').animate({ scrollTop: 0 }, 'slow');
            hashTagActive = "";
            hashTag = '#'+section
            if(hashTagActive != hashTag) { //this will prevent if the user click several times the same link to freeze the scroll.
                event.preventDefault();
                //calculate destination place
                var dest = 0;
                if ($(hashTag).offset().top > $(document).height() - $(window).height()) {
                    dest = $(document).height() - $(window).height();
                } else {
                    dest = $(hashTag).offset().top;
                }
                //go to destination
                $('html,body').animate({scrollTop: dest}, 'slow');
                hashTagActive = hashTag;
            }
        }
	});
}

function updateFile( releaseid, updatedjson, originaljson, ocdsmeta, mda, action ) {
    append_mda = (mda=='') ? '' : mda+"/"
    urlpath = "/"+action+"/"+releaseid+"/"+append_mda;
    //console.log(urlpath)
    $.ajax({
        type: "POST",
        //contentType: "application/json",
        url: urlpath,
        data: {'updatedjson': updatedjson, 'originaljson': originaljson, 'meta': ocdsmeta },
        //dataType: "json",
        success : function(result) {
            response = JSON.parse(result)
            $('#fileupdate').html(response['message']).fadeIn(1500).delay(2000).fadeOut(1200, function() {
                $(this).html('Returning to OCID list...').fadeIn(200).delay(3000, function() {
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

function filedownload(url, data){
    var iframe = $('<iframe></iframe>').attr('width',0).attr('height',0).attr('name','dummy').appendTo('body')
    var form = $('<form></form>').attr('action', url).attr('method', 'post').attr('target', 'dummy');
    Object.keys(data).forEach(function(key){
        var value = data[key];
        if(value instanceof Array) {
            value.forEach(function (v) {
                form.append($("<input></input>").attr('type', 'hidden').attr('name', key).attr('value', v));
            });
        } else {
            form.append($("<input></input>").attr('type', 'hidden').attr('name', key).attr('value', value));
        }
    });    
    //send request
    form.appendTo('body').submit().remove();
}

function formSetUp(container) {
	// add hastab and hasitems classes to divs with fieldset (object/array) children
	container.find(".kvp .front .prop-value").each(function() {
		var kvp_div = $(this).parent().parent();
		if ($(this).children("fieldset.array").length) {
			kvp_div.addClass("hastab")
		}
		if ($(this).children("fieldset.object").length || $(this).children("fieldset.array").length) {
			kvp_div.addClass("hasitems")
		}
	})
	
	// set data-paths and label ids
	container.find('.kvp').each(function() {
		kvp_parent = $(this).parent().closest('.kvp');
		itemid = $(this).attr('id');
		if (kvp_parent.length) {
			datapath = kvp_parent.attr('data-path') ? kvp_parent.attr('data-path') : kvp_parent.attr('id');
			path = datapath + '-' + itemid;
		} else {
			path = itemid;
		}
		//add data-path attribute
		$(this).attr('data-path', path);
		//add class to the label
		$(this).find('> .front > .prop-name > label.key').addClass(itemid);
	})
	
	//when additem button is clicked 
	$('#container').on('click', 'button.additem', function() {
		container = $(this).closest('.prop-value');
		//formSetUp(container);
		container.find('.front > .prop-value > fieldset').coolfieldset();
        setNonCamelCase(container);
		transformElements(container);
		setMandatoryFields(container, mandatoryfields);
        setTooltip(container)
	});
	
	// hide stage titles within the form
	$("#container > .form > .object > .kvp").each(function() {
		if ( $.inArray( $(this).attr("id"), metafields ) > -1 ) {
			$(this).hide();
		}
	});
	
	//hide tab labels inside tabs
	for	(i = 0; i < release_tabs.length; i++) {
		//$('.'+ release_tabs[i]).hide();
	}
	//hide metafields
	for	(i = 0; i < metafields.length; i++) {
        //make sure you point to the required metafields
		$('.brutusin-form > .object > .front > .kvp#'+ metafields[i]).hide();
	}
    
    //hide fieldsets without tabs
    inputjson = $('#input').val() ? JSON.parse($('#input').val()) : '';
    if (!_.isEmpty(inputjson)) {
        for (var i = 0; i < release_tabs.length; i++) {
            if (!inputjson[release_tabs[i]]) {
                //$('.kvp#'+release_tabs[i]).hide()
            }
        }
    }
    
}

function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

/****** transform input elements *******/
function transformElements(container) {
	container.find('input.value').each(function() {
		if (!$(this).siblings('.clone').length) {
			var input = $(this);
			var value = $(this).val();
			var item = $(this).closest('.kvp');
			var key = item.attr('id');
			var alias = item.attr('data-path');
            // for party name/id references
            if (!alias) {
                /* TODO: Does not work when all entries have been deleted, since in that case, there'll be no parent item to reference */
                // if this is a clone, get data-path value from its progenitor
                sibling = item.closest('tbody').prev().find('#'+key);
                item.attr('data-path', sibling.attr('data-path'));
                alias = item.attr('data-path');
            }
			keyObj = ''
			$.each(codelists, function(formkey, formitems) {
				
				if (key == formkey) {
					if( Object.prototype.toString.call( codelists[formkey] ) === '[object Array]' ) {
						for (arrayitem in codelists[formkey]) {
							alias_array = codelists[formkey][arrayitem].alias
							//alert(formkey + ' ' +alias_array + ' ' + alias)
							if (alias_array) {
								if (alias_array.indexOf(alias) > -1) {
									keyObj = codelists[formkey][arrayitem]
								}
							} else {
								keyObj = codelists[formkey][arrayitem]
							}
						}
					} else {
						alias_array = codelists[formkey].alias
							if (alias_array) {
								if (alias_array.indexOf(alias) > -1) {
									keyObj = codelists[formkey]
								}
							} else {
								keyObj = codelists[formkey]
							}
					}
					if (keyObj.tag == 'textarea') {
						var textarea = $('<textarea></textarea>')
						textarea
							.text(value)
							.addClass('value clone')
							//.attr('id', input.attr('id'))
							.attr('autocorrect', input.attr('autocorrect'))
							.attr('title', input.attr('title'))
						//input.replaceWith(textarea);
						input.css('display', 'none')
						textarea.insertAfter(input);
						textarea.change(function() {
							input.val( $(this).val() );
							input.change();
						})
                    } else if (keyObj.tag == 'input') {
                        var special = keyObj.special;
						var inputclone = $('<input>');
						inputclone
							.val(value)
							.addClass('value clone')
							//.attr('id', input.attr('id'))
							.attr('autocorrect', input.attr('autocorrect'))
							.attr('title', input.attr('title'));
						//input.replaceWith(textarea);
						input.css('display', 'none');
                        inputclone.insertAfter(input);
                        if (special == 'mask') {
                            inputclone.inputmask({'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true});
                        }
						inputclone.change(function() {
                            if (special == 'mask') {
                                unmasked = $(this).inputmask('unmaskedvalue');
                                input.val( parseFloat(unmasked) );
                            } else {
                                input.val( $(this).val() );
                            }
							input.change();
						})
					} else if (keyObj.tag == "select") {
						var options = keyObj.values
						select = '<select>';
						select += '<option value="">Choose an option</option>';
						$.each(keyObj.values, function(k, v) {
						  select += '<option value="'+k+'"'+((k==value)?'selected':'')+'>'+v+'</option>';
						});
						select += '</select>';
						select = $(select)
									.addClass('value clone')
									//.attr('id', input.attr('id'))
									.attr('autocorrect', input.attr('autocorrect'))
									.attr('title', input.attr('title'))
						//input.replaceWith(output);
						input.css('display', 'none')
						select.insertAfter(input);
						select.change(function() {
							input.val( $(this).val() );
							input.change();
						});
                        //console.log(keyObj)
                        //editbutton.insertAfter(select);
					} else if (keyObj.tag == "button") { // this is specifically for the references to parties/organisations
                        button = '<button onclick="get_parties_list(this)" data-jq-dropdown="#party-items">' + keyObj.values + '</button>';
                        button = $(button)
									.addClass('value clone')
									.attr('autocorrect', input.attr('autocorrect'))
									.attr('title', input.attr('title'));
                        input.css('display', 'none');
                        button.insertAfter(input);
						button.change(function() {
							input.val( $(this).val() );
							input.change();
						});
                        spanid = $('<span class="itemid"></span>');
                        spanid.text(value);
                        spanid.insertAfter(button);
                        // disable sibling above the button
                        buttonkvp = button.closest('.kvp')
                        //buttonkvp.prev().find('input.value').attr('disabled', 'disabled');
                        // hide all siblings after the button
                        buttonkvp.nextAll().css('display','none');
                    }
				//}
				}			
			});
            // check for date element
            if (input.hasClass('datetime-local')) {
                var datecomp = $('<input type="text">')
                datecomp
                    .addClass('value clone')
                    .attr('placeholder', input.attr('placeholder'))
                    .attr('autocorrect', input.attr('autocorrect'))
                    .attr('title', input.attr('title'));
                input.css('display', 'none')
                datecomp.insertAfter(input);
                try {
                    datecomp.datetimepicker({defaultDate: input.val()}).on('dp.change', function(e) {
                        d = new Date($(this).data('date'));
                        input.val( d.toISOString().split('.')[0]+'Z' );
                        input.change();
                    });
                } catch (err) {
                    console.log(err);
                }
            };
		}
        
	});
    //if mode is preview
    if ($('#preview').length) {
        container.find('input, select, textarea').each(function() {
            if (!$(this).hasClass('clone')) {
                var tempEl = $('<div></div>')
                tempEl.addClass('preview').html($(this).val())
                $(this).parent().prepend(tempEl)
            }
            $(this).hide();
        });
        container.find('.additem').hide();
        container.find('.remove').hide();
    }
    importDocuments(container, 'documents');
    importParties(container, 'parties', $('#default_organisation').html());
}

function get_parties_list(button) {
    var parties = bf.getData()['parties'];
    //console.log(parties)
    var buttonkvp = $(button).closest('.kvp');
    var namekvp = buttonkvp.prev('.kvp');
    var curpartyid = $(button).next('.itemid').text();
    var importedItems = '';
    if (parties) {
        parties.forEach(function(party, n) {
            if (party['id']) {
                var partyname = party['name'] ? party['name'] : '';
                importedItems += '<li><span id="'+party['id']+'" data-party="' + partyname + '" class="party-item '+((curpartyid==party['id']) ? "active-list-item" : "") +'">' + ((partyname) ? partyname : '[No name given]') + ' | ' + party['id'] + '</span></li>';
            }
        });
    } else {
        importedItems = '<span id="0" class="party-item disabled-list-item">No party available</span>';
    }
    $('#party-items ul').html(importedItems);
    
    $('.party-item').click(function() {
        partyid = $(this).attr('id');
        // update input value for party id (so it can be seen by brutusin)
        buttonkvp.find('input.value').val(partyid).change();
        // ditto for party name
        partyrefname = $('#'+partyid).attr('data-party');
        namekvp.find('input.value').val( partyrefname ? partyrefname : '[No name given]' ).change();
        // change current party id value
        $(button).next('.itemid').text(partyid);
    });
}

// APPLIES TO THE PARTIES FIELD (to import default values from Wordpress settings)
function importParties(container, field, data) {
    var importedData = JSON.parse(data);
    var $partyitem = container.find('.kvp.hasitems#'+field);
    $partyitem = $partyitem.length ? $partyitem : container;
    if ($partyitem.length) {
        // create insert button
        //console.log(i, $(el), $(el).siblings('.item-index').text());
        // add only the corresponding index
        var insertbtn = $('<button type="button" class="insert-party">Add default</button>');
        $partyitem.find('.item .item-action:last').append(insertbtn);
        insertbtn.click(function() {
            //retrieve importscheme
            try {
                var importscheme = JSON.parse($('#importscheme').html());
            } catch(e) {
                alert('Invalid import schema');
            }
            //console.log(partydata)
            var $kvp = $(this).parent().siblings('.item-value').find('.kvp');
            $kvp.each(function() {
                var item = $(this).attr('data-path');
                var fielditem = importscheme[field][item];
                var fieldvalue = importedData[fielditem];
                $(this).find('input.value').val(fieldvalue).change(); // .change is needed for brutusin form to pick up the value
            })
        });
        
    }
}


// APPLIES PARTICULARLY TO THE DOCUMENTS FIELD
// create elements (button, textarea) for managing documents
function importDocuments(container, field) {
    // create import button
    container.find('div.kvp#'+field).each(function(i, el) {
        $container = $(el);
        var importbtn = $('<button type="button" class="import">Import Data</button>');
        importbtn.insertAfter($container.find('button.additem'));
        $container.on('click', '.import', function() {
            $('#importdata').attr('data-path', $container.attr('data-path'));
            $('.thickbox.document').click();
        });
    });
    
    // set imported data as parsed json
    var importedData = function() {
        var data = $('#importdata').html();
        if (data != '') {
            try {
                return JSON.parse(data);
            } catch(e) {
                alert('Error reading data');
                return;
            }
        }
    }
    
    // create insert button
    if (container.closest('.kvp').attr('id') == field) {
        var insertbtn = $('<button type="button" class="insert" data-jq-dropdown="#imported-items">Insert Data</button>');
        var itemid = container.find('td.item-index:last').html();
        insertbtn.attr('data-index', itemid);
        insertbtn.click(function() {
            var index = $(this).attr('data-index');
            //console.log(importedData()[index-1]);
            $('#imported-items ul').attr('data-current-index', index);
            $('#imported-items ul li').each(function() {
                if (index == $(this).data('index')) {
                    $(this).css({'font-weight':'bold'});
                } else {
                    $(this).css({'font-weight':'normal'});
                }
            })
        });
        // add data-index attribute to created items
        container.find('td.item-value:last').attr('data-index', itemid)
        container.find('td.item-action:last').attr('data-index', itemid).append(insertbtn);
    }
    
    // set list of imported items
    if (importedData()) {
        var listul = $('#imported-items ul');
        var importedItems = '';
        $.each(importedData(), function(index, item) {
            importedItems += '<li data-index="'+(index+1)+'"><span id="item-'+(item.id)+'" class="import-item">'+item.title+'</span></li>'
        });
        $('#imported-items ul').html(importedItems);
    }
    
    // click action for imported item
    $('.import-item').click(function() {
        updateFields( $(this), field,  importedData() );
    })
}

function updateFields($el, field, importedData) {
    try {
        var importscheme = JSON.parse($('#importscheme').html());
    } catch(e) {
        alert('Invalid import schema');
    }
    var dataIndex = $el.parent().attr('data-index');
    var targetIndex = $el.closest('ul').attr('data-current-index') ? $el.closest('ul').attr('data-current-index') : $el.attr('data-index');
    var $kvp = $('.kvp#'+field+' td.item-value[data-index='+targetIndex+']').find('.kvp');
    $kvp.each(function() {
        var item = $(this).attr('id');
        var fielditem = importscheme[field][item];
        if (fielditem) {
            var fieldvalue = importedData[dataIndex-1][fielditem];
            $(this).find('input.value').val(fieldvalue).change(); // .change is needed for brutusin form to pick up the value
        }
        // to do: for select and other elements
        // toggle css
        $el.closest('ul').find('li').css({'font-weight':'normal'});
        $el.parent('li').css({'font-weight':'bold'});
    });
}

// function to trigger import
function triggerImport(data, field) {
    var curdatapath = $('#importdata').attr('data-path');
    var button = $('.kvp[data-path='+curdatapath+']').find('.import');
    if (data) {
        $('#importdata').html(data); // store the data for subsequent use
        try {
            objData = JSON.parse(data);
        } catch(e) {
            alert('Error reading data');
        }
        $.each(objData, function(index, item) {
            button.siblings('.additem').click();
            // preload data in fields
            insertbtn = $('.kvp[data-path='+curdatapath+']').find('table tbody:last').find('button.insert')
            updateFields( $(insertbtn), field, objData );
        });
    } else {
        alert('Could not import data');
    }
    
}

function setMandatoryFields(container, fields) {
    container.find('.kvp').each(function() {
		datapath = $(this).attr('data-path');
		if ($.inArray(datapath, fields) !== -1) {
			$(this).find('label.key').addClass('mandatory');
		}
	})
}

function checkMandatoryFields(container, section) {
    error=0;
    container.find(section).find('.mandatory').each(function() {
        inputfield = $(this).parent().siblings('.prop-value').find('input.value, textarea.value, select.value');
        inputfield.each(function() {
          if (!$(this).val()) {
            $(this).addClass('validation-error')
            error++;
            //console.log($(this).parent().html())
          }
        })
        inputfield.bind('keyup change', function() {
            $(this).removeClass('validation-error')
        })
    });
    return (error) ? false : true;
}

function checkInvalidField(container, datapath) {
    if (datapath) {
        inputdiv = $(container).find("div[data-path='"+datapath+"']");
        //console.log(inputdiv.length, datapath, $(container))
        if (inputdiv.length) {
            $('html,body').animate({ scrollTop: inputdiv.offset().top }, 'fast');
            inputdiv.effect('highlight', {}, 2000)
            inputfield = inputdiv.find('.prop-value').find('input.value, textarea.value, select.value');
            inputfield.addClass('validation-error');
            inputfield.bind('keyup change', function() {
                $(this).removeClass('validation-error');
            });
        }
    }
}

function setNonCamelCase(container) {
	container.find('.kvp').each(function() {
		kvp_id = $(this).attr('id');
		noncamelkey = kvp_id.replace(/([A-Z])/g, ' $1').replace(/^./, function(str){ return str.toUpperCase(); })
		$(this).find('label.key').text(noncamelkey)
	})
}

/*
function generateDescriptions(container) {
    descriptionSchema = {};
    container.find('.kvp').each(function() {
		datapath = $(this).attr('data-path');
		current = $(this).find('label.key').attr('title');
		descriptionSchema[datapath] = {};
		descriptionSchema[datapath]['original'] = current
		descriptionSchema[datapath]['custom'] = " "
		descriptionSchema[datapath]['required'] = false
	})
	console.log(JSON.stringify(descriptionSchema, null, '\t'))
}
*/

function setTooltip(container) {
    $(container).find('label.key').each(function() {
      desc = $(this).attr('title');
      $('<span class="helptip"></span>').appendTo($(this));
      $description = $('<span class="description"></span>');
      $description
        .appendTo($(this))
        .html(desc)
        .css('display', 'none');
      $(this).attr('title', '');
      
      //get local descriptions
      if ($('textarea#descriptions').length) {
        datapath = $(this).closest('.kvp').attr('data-path');
        localdesc = JSON.parse($('textarea#descriptions').val());
        if (localdesc[datapath]) {
          $description.html(localdesc[datapath]['description'])
        }
      }
    })
	
	//activate tooltipster
	$(container).find('.helptip').tooltipster({
		theme: 'tooltipster-noir',
		maxWidth: 350,
		iconDesktop: true,
		icon: '(?)',
		position: 'top',
        interactive: true,
		content: 'Loading...',
        contentAsHTML: true,
        animation: 'fade',
        updateAnimation: 'fade',
		functionBefore: function(origin, continueTooltip) {
			// we'll make this function asynchronous and allow the tooltip to go ahead and show the loading notification while fetching our data
			continueTooltip();
			_this = $(this);
            var messagebox = $('#descModal .message');
                messagebox.html('');
			function setTip() {
                //userstatus = ($('#userstatus').val() != "True") // not necessary in Wordpress context
				title = _this.parent().contents().get(0).nodeValue
				path = _this.closest('.kvp').attr('data-path')
                data = _this.siblings('.description').html();
				data = data ? data : 'Not found';
				//editlink = userstatus ? ' | <a href="#edit" class="editdesc" data-toggle="modal" data-target="#descModal">Edit</a>' : '';
                editlink = ' | <a href="#edit" class="editdesc" data-toggle="modal" data-target="#descModal">Edit</a>';
				htmldata = markdown.toHTML(data).replace(/^(?:<p>)?(.*?)(?:<\/p>)?$/, "$1") //remove enclosing <p>
				origin.tooltipster('content', htmldata + editlink);
            }
            
			setTip();
			
			$('.tooltipster-content').on('click', '.editdesc', function() {
				$('#descModal').find('.lbl_title').html(title);
				$('#descModal').find('.lbl_path').html(path.replace(/\-/g, '/'));
				$('#descModal').find('.edit_title').val(title);
				$('#descModal').find('.edit_path').val(path);
				$('#descModal').find('.edit_description').val(data);
			});
			
            var n=0; //to prevent multiple clicking actions; not sure why this happens...
			$(".updatebtn").click(function() {
              n++;
              if (n==1) {     
                newtitle = $('#descModal').find('.edit_title').val();
                newpath = $('#descModal').find('.edit_path').val();
                newdescription = $('#descModal').find('.edit_description').val();
                $.ajax({
                    data: JSON.parse('{"path":"' + newpath + '", "title":"' + newtitle + '","description":"' + newdescription + '"}'),
                    url: '/descriptions/edit/',
                    type: 'POST',
                    success: function(response) {
                        var jsondesc = JSON.parse(response);
                        if (jsondesc[path]['description'] == newdescription) {
                            newdata = $('.edit_description').val();
                            _this.siblings('.description').html(newdata);
                            setTip();
                            messagebox.html("Update successful!");
                        } else {
                            messagebox.html("Update successful!");
                        }
                    },
                    error : function(xhr, status) {
                        //alert('Could not retrieve data.');
                        console.log(xhr.responseText)
                    }
                });
              }
			});
		}
	});
}

function setHash(tag) {
    if (tag.val()) {
      window.location.hash = '#'+tag.val();
      $(window).on('hashchange', function() {
          setHash(tag); //reset hash to value in tag if changed by user
      });
      if ($(window).scrollTop() != 0) {
          $('html, body').animate({ scrollTop: 0 }, 'fast');
      }
    }
}
	
$(document).ready(function() {
    
    setHash( $('#release-tag') );
	
	//renderSchema(schema);

	formSetUp( $('#container') )
    
    setNonCamelCase( $('#container') );
	
	transformElements( $('#container') );
    
    checkInvalidField($('#container'), $('#invalidfield').val());
	
	setMandatoryFields( $('#container'), mandatoryfields );
    
    setTooltip( $('#container') );
	
	//$('#tab-container').easytabs();
	
	$('.front > .prop-value > fieldset').coolfieldset();
	
	//add initial block manager for documents picker and create for all field clones
	$('div#documents > div.front > fieldset.array > fieldset.object').prepend('<div class="ssi-documents dialog-picker document" data-field="document"></div>');
	
    /*
    fieldManager('documents')
    $('#searchbox').coolautosuggest({
        url:'/raw/' + $('#currentmda').val() + '/',
        showThumbnail:false,
        showDescription:true,
        idField:$("#searchId"),
        minChars:2,
        onSelected:function(result){
            window.location = '/edit/' + $('#currentmda').val() + '/' + $("#searchId").val();
        }
    });
    */
    
    $('.publishall').click(function() {
        download = $('input.downloadall').is(':checked') ? 'download' : '';
        //$('.publishall span i').addClass('fa-spin fa-1g fa-fw');
        url = '/packed/' + $('#currentmda').val() + '/'
        filedownload(url, {'download':download})
        //$('.publishall span i').removeClass('fa-spin fa-1g fa-fw');
    });
    
    $('.tab-container').on('click', '.vis', function() {
        var activeReleaseId = $('.tab.active').find('input').val()
        var activeStage = $('.tab.active').find('a').text()
        $('#previewcontrols a.edit')
            .attr('href', '/editor/' + $('#currentmda').val() + '/' + activeReleaseId)
            .find('span').html(activeStage)
    })
    $('.tab-container .vis.active').click();
	
	//add initial field manager picker and create pickers for all subsequent field clones
	if ($('.contactPoint').length) {  // use contactPoint as identifier of ocds organisation blocks
		var fieldObjCount = 0
        $('.contactPoint').each(function(i,el) {
			//get parent elements
			$fieldset = $(this).closest('fieldset.object');
			$kvp = $fieldset.closest('.kvp');
			$namefield = $fieldset.find('.kvp#name > .front > .prop-value');
			//console.log(datapath)
			//console.log(kvp.attr('data-path'))
			
			//set action button
			$actions = $fieldset.siblings('.actions');
			fieldObjCount = (datapath == $kvp.attr('data-path')) ? $actions.parent().find($fieldset).length + fieldObjCount : 0
			$actions.find('.another').attr('data-count', fieldObjCount);
			
			//set button variables
			fieldcount = (datapath == $kvp.attr('data-path')) ? Number(fieldcount)+1 : 0;
			datapath = $kvp.attr('data-path');
			field = $kvp.attr('id') + '-' + fieldcount;
			fieldname = $namefield.find('input.value').val();
			
			//create button elements
			$pickerwrap = $('<div class="pickerwrap"></div>');
			$newpicker = $('<div class="ssi-'+field+' dialog-picker organisation" data-index="'+fieldcount+'" data-field="organisation" title="Import data to this block"></div>');
			$newsaver = $('<div class="saver" data-index="'+fieldcount+'" data-path="'+datapath+'" title="Save the data in this block"></div>');
			$savestatus = $('<div class="savestatus"></div>');
			$newpickerName = $('<input type="hidden" class="pickername" value="'+fieldname+'">');
			
			setCSS($newpicker, $newsaver);
			
			$pickerwrap.prepend($savestatus);
			$pickerwrap.prepend($newsaver);
			$pickerwrap.prepend($newpicker);
			$pickerwrap.prepend($newpickerName);
			$fieldset.prepend($pickerwrap)
			
            //not used anymore (see click event below)
			$fieldset.find('.kvp#name').on('keyup', 'input.value', function() {
				$(this).closest('fieldset.object').find('.pickername').val($(this).val());
			});
			//fieldManager(field, datapath);
		});
	}
	
	$('body').on('click', '.saver', function() {
        saver = $(this)
        mydatapath = saver.attr('data-path');
		myfieldcount = saver.attr('data-index');
        ssi_modal.show({
              content: '<input type="text" class="itemName">',
              title: 'What title would you like to save this entity as?',
              sizeClass: 'small',
              buttons: [{
                  className: 'btn btn-primary',
                  label: 'Save',
                  closeAfter: false,
                  method: function (e, modal) {
                      item_name = $('.itemName').val();
                      saveItem(saver, mydatapath, myfieldcount, item_name);
                      modal.close();
                  }
              }, {
                  className: 'btn btn-danger',
                  label: 'Cancel',
                  closeAfter: true,
                  method: function (e, modal) {
                      modal.close();
                  }
              }]
        });
	});

});

function saveItem(item, datapath, fieldcount, fieldname) {
	if (!datapath || !fieldcount) {
        item.siblings('.savestatus').html('Sorry, there was a problem saving! Contact the admin')
		return false;
    }
	if (!fieldname) {
        item.siblings('.savestatus').html('Please make sure you have assigned a title to the entity')
		return false;
    }
	releaseid = $('#ocid').val() //get release id from hidden element
	data = {
		Id: releaseid + '/' + datapath + '/' + fieldcount,
		Title: fieldname,
		Description: releaseid
	}
	//console.log(data)
	$.ajax({
		data: JSON.stringify(data),
		stringifyData: true,
		contentType: "application/json",
		url: itemsUrl,
		type: 'POST',
		success: function(data) {
			//console.log(data)
			item.siblings('.savestatus').html('Field saved')
		},
		error : function(xhr, status) {
			//alert('Could not retrieve data.');
			console.log(xhr.responseText)
		},
	});
}

function setCSS($picker, $saver) {
	$picker.css('background-image', 'url(/static/app/images/import.png)')
		.css('background-repeat', 'no-repeat')
		.css('background-position', 'center center')
		.css('background-size', '100%');
			
	$saver.css('background-image', 'url(/static/app/images/save.png)')
		.css('background-repeat', 'no-repeat')
		.css('background-position', 'center center')
		.css('background-size', '75%');
}


}
