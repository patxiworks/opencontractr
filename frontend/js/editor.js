
function runEditor() {
	
	jQuery(document).ready(function($) {
		
		var data = JSON.parse($('#jsoninput').html());
		var actions = $('li#actions')
		var jumplinks_el = actions.find('select');
		var hideid_el = actions.find('input#showid');
		var jump_el = $('a#jump')
		var selectfield_el = $('select#fieldpaths');
		var codelists_el = $('#codelists');
		codelists_json = JSON.parse( codelists_el.text() );
		var codelists_ids = codelists_json['id'][0]['alias'];
		
		// convert select elements to select2 object
		if ($('#fieldpaths').length) $('#fieldpaths').select2({width:'350px', dropdownCssClass: 'fieldpaths'});
		
		$('#subnav').on('click', 'span.close', function() {
			$('#subnav').css('z-index',2);
            $('#subnav').css('margin-top','-60px').hide();
			$(this).hide();
			$('#nav span.open').show()
        });
		
		$('#nav').on('click', 'span.open', function() {
			$('#subnav').css('z-index',3);
            $('#subnav').css('margin-top','0px').show();
			$(this).hide();
			$('#subnav span.close').show()
        });
		
		$('.links').on('click', 'li', function() {
			if ( !$(this).hasClass('action') ){
				$('.links li').removeClass('active')
				$(this).addClass('active');
				id = $(this).find('a').data('id');
				currpage = $('.kvp#'+id);
				if (currpage.length) {
					$('#main .edit-intro').hide(); // hide intro message
					$('.brutusin-form > .object > .front > .kvp').hide(); // hide all pages
					currpage.show().fadeIn(); // display current page
					populateDropdown(getFieldlist(id));
					$('.links li.action').show();
				}
            }
		});
		
		$('.edit-intro').on('click', 'a.edit', function() {
			$('.links li:first-child').click();
		})
		
		function getFieldlist(tab) {
			tabfields = [];
			$parentelement = tab ? $('.kvp#'+tab).find('.kvp') : $('.kvp');
            $parentelement.each(function(i,el) {
				tabfield = $(el).attr('data-path');
				if ($(el).data('count')) tabfield = tabfield+'_'+$(el).data('count');
				tabfields.push(tabfield);
			});
			return tabfields;
        }
		
		function getIdFields(list, exclude) {
			var idfields = {};
            for (item in list) {
				mainItem = list[item].split('_');
				splitItem = mainItem[0].split('-');
				if ( splitItem[splitItem.length-1] == 'id' ) {
					idfields[mainItem[0]] = mainItem[1] ? mainItem[1] : 0;
                }
			}
			if (exclude) {
				for (field in idfields) {
					if (exclude.indexOf(field) > -1) {
                        delete idfields[field]
                    }
				}
                /*idfields = idfields.filter( function(el) {
					return exclude.indexOf(el) < 0;
				})*/
            }
			return idfields;
        }
		
		function populateDropdown(list) {
			selectfield_el.empty();
			for (item in list) {
				fielditem = list[item].split('_');
				fieldcount = fielditem[1] ? fielditem[1] : '';
				if ( $('.kvp[data-path='+fielditem[0]+']').is(":visible") ) {
					var label = fielditem[0].split('-'); label.shift(); newlabel = [];
					for (text in label) {
						result = label[text].replace( /([A-Z])/g, " $1" );
						result = result.charAt(0).toUpperCase() + result.slice(1);
						newlabel.push(result)
					}
					prettylabel = newlabel.join(' / ');
					if ($('.kvp[data-path='+fielditem[0]+']').hasClass('hasitems')) {
						selectfield_el.append('<optgroup label="'+prettylabel+'">');
                    } else {
						prettycount = fieldcount.split('-').join('.');
						selectfield_el.append('<option value="'+fielditem[0]+'" data-count="'+fieldcount+'">'+prettylabel + ( (fieldcount) ? ' ('+prettycount+')' : '') + '</option>');
					}
				}
			}
        }
		
		jumplinks_el.on('change', selectfield_el, function() {
			selected = $(this).find(":selected").val();
			selectedcount = $(this).find(":selected").data('count');
			//console.log(selectedcount)
			$('.fieldfinder').attr('href', selected).attr('data-ref', selectedcount).fieldfinder();
			jump_el.click();
			countref = selectedcount ? '[data-count='+selectedcount+']' : '';
			target = $('.kvp[data-path='+selected+']'+countref);
			target.find('label').addClass('focus');
			targetel = target.find('input.value,select.value,textarea.value');
			//targetel.focus();
			targetel.addClass('shadow');
			targetel.parent().siblings().find('span.help').click();
		})
		
		function updateIdFields(status) {
			extra_fields = ['id','buyer-identifier-id','parties-id','parties-identifier-id']
			list = getIdFields(getFieldlist(), codelists_ids.concat(extra_fields));
            for (item in list) {
				idvalue = item + '-' + $('#postid').val() + '-' + list[item];
				iditem = $('.kvp[data-path='+item+']');
				if (status) {
                    iditem.hide();
                } else {
					iditem.show();
				}
				iditem.find('input').val(idvalue).change();
			}
        }
		
		actions.on('click','#showid', function() {
            updateIdFields( $(this).is(':checked') )
		})
		
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
				//console.log(kvp_parent.length, kvp_parent.attr('data-path'), itemid)
				if (kvp_parent.length) {
					datapath = kvp_parent.attr('data-path') ? kvp_parent.attr('data-path') : kvp_parent.attr('id');
					path = datapath + '-' + itemid;
				} else {
					path = itemid;
				}
				// remove all placeholders
				$(this).find('input.value').removeAttr('placeholder');
				// add tooltip
				$thislabel = $(this).find('.prop-name label').first(); // select only the first and ignore other selections
				if (!$thislabel.find('span.tooltip').length && $thislabel.attr('title')) {
                    $thislabel.append('<span class="help tooltip icon fa-question-circle" title="'+$thislabel.attr('title')+'"></span>');
                }
                //$thislabel.removeAttr('title');
				
				var count = [], $this = $(this);
				$this.parents().each(function(i, el) {
					if ($(el).siblings().hasClass('item-index')) {
						count.push($(el).siblings('.item-index').text())
						$this.attr('data-count', count.join('-'));
                    }
					
				})
				//add data-path attribute
				$(this).attr('data-path', path);
				//add class to the label
				$(this).find('> .front > .prop-name > label.key').addClass(itemid);
				
			});
			setFieldOptions();
			
			// update ids with values
			updateIdFields( hideid_el.is(':checked') )
			
			
		}
		
		
		function setFieldOptions() {
            $.getJSON( '?data=fields', function( data, status, xhr ) {
				$('.kvp').each(function(i,el) {
					var datapath = $(this).attr('data-path');
					var label = $(this).find('label').first();
					// mandatory fields
					if ( data[datapath + '_mandatory'] ) {
                        label.addClass('mandatory');
                    }
					// labels
					if ( data[datapath + '_label'] ) {
                        label.contents()[0].data = data[datapath + '_label'];
                    }
					// descriptions
					if ( data[datapath + '_description'] ) {
                        label.attr('title', data[datapath + '_description']);
						label.find('span').attr('title',data[datapath + '_description'])
                    }
				})
			})
        }
		
		
		/****** transform input elements *******/
		function transformElements(container) {
			var codelists = JSON.parse(codelists_el.html());
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
									//console.log(formkey, alias_array, alias)
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
							//console.log(keyObj)
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
									.attr('type', 'text')
									.addClass('value clone')
									//.attr('id', input.attr('id'))
									.attr('autocorrect', input.attr('autocorrect'))
									.attr('title', input.attr('title'));
								//input.replaceWith(textarea);
								//console.log(input.siblings('.clone').length)
								if (!input.hasClass('clone') && !input.siblings('.clone').length) { // check if there is already an instance
                                    input.css('display', 'none');
									inputclone.insertAfter(input);
                                }
								if (special == 'mask') {
									inputclone.inputmask({'alias': 'decimal', 'groupSeparator': ',', 'autoGroup': true});
									inputclone.css('text-align','left');
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
								/*button = '<button onclick="get_parties_list(this)" data-jq-dropdown="#party-items">' + keyObj.values + '</button>';
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
								spanid.insertAfter(button);*/
								buttonkvp = input.closest('.kvp');
								roles = codelists['roles']['values'];
								select = $('<select class="role"></select>');
								select.append('<option value="">Select a role</option>');
								try {
									allroles = JSON.parse( $('#partyroles').text() );
								} catch(e) {
									allroles = {};
								}
								for (i=0; i<allroles.length; i++) {
									if (allroles[i][0] == input.val() && allroles[i][2] == buttonkvp.attr('data-path')) {
                                        var role = allroles[i][1];
                                    }
								}
								if (buttonkvp.attr('data-path') == 'buyer-id') {
                                    role = 'buyer';
									select.attr('disabled','disabled');
                                }
								$.each(roles, function(k, v) {
								  select.append('<option value="'+k+'"'+((k==role)?'selected':'')+'>Role: '+v+'</option>');
								});
								//console.log(input)
								// append to id
								if (!buttonkvp.parent().find('select.role').length) {
                                    roleselect = $('<span>').html(select)
									buttonkvp.prev().append(roleselect);
                                }
								//buttonkvp.prev().find('input.value').attr('disabled', 'disabled');
								// hide all siblings after the button
								buttonkvp.nextAll().css('display','none');
							}
						//}
						}			
					});
					/// check for date element
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
							datecomp.datetimepicker({
									defaultDate: input.val(),
									widgetPositioning: { horizontal: 'left', vertical: 'bottom'}
								}).on('dp.change', function(e) {
									try {
										d = new Date($(this).data('date'));
										input.val( d.toISOString().split('.')[0]+'Z' );
										input.change();
									} catch (e) {
										// do nothing
									}
								});
						} catch (err) {
							console.log(err);
						}
					};
				}
				
			});
			
			// autoselect organisations
			$.getJSON( '?data=organisations', function( data, status, xhr ) {
				$('.kvp#name').each(function() {
					//doAutocomplete(this, $(this).attr('data-path'));
					if ($(this).find('select.value').length) {
						var $el = $(this);
						if ( !$el.find('.add-new').length && $el.attr('data-path') != 'buyer-name' ) {
							$el.find('label').append('<a href="#TB_inline?width=650&height=550&inlineId=organisation-box" class="thickbox addorg" title="Add a new organisation"><span class="add-new label icon fa-plus"></span></a>');
						}
						// disable the id field
						orgid_el = $el.next().find('input');
						orgid_el.attr('disabled', 'disabled');
						$.each(data, function(i,item) {
							elid = orgid_el.val().split('-');
							realid = elid[elid.length-1];
                            $el.find('select.value').append('<option value="'+item.ID+'" '+( item.ID == realid ? 'selected' : '' )+'>'+item.label+'</option>');
                        });
						$el.find('select.value').change(function() {
							$(this).prev('input.value').val( $(this).find(':selected').text() ).change();
							organisationid = setOrganisationId($(this).val());
							$(this).closest('.kvp').next('#id').find('input.value').val( organisationid ).change();
						});
					}
					$(this).find('select.value').select2({width:'100%', dropdownCssClass: $(this).attr('data-path')});
				});
			});
			
			
			$('.changebuyer').click(function() {
				$('.kvp[data-path=buyer-name]').find('input').focus();
			});
			
			$('.item-action .remove').click(function() {
				if ( $('#fieldpaths').length ) {
					id = $('.links li.active').find('a').data('id');
					populateDropdown(getFieldlist(id));
				}
			});
		}
		
		function setOrganisationId(id) {
            return 'entity-'+id;
        }
		
		// deprecated for select2
		function doAutocomplete(el,newcls) {
			var nextElement = $(el).next()
			if ( nextElement.attr('id') == 'id') {
				// append 'add-new' button to the label
				if ( !$(el).find('.add-new').length && newcls != 'buyer-name' ) {
					$(el).find('label').append('<a href="#TB_inline?width=650&height=550&inlineId=organisation-box" class="thickbox addorg" title="Add a new organisation"><span class="add-new label icon fa-plus"></span></a>');
				}
				// disable the id field
				nextElement.find('input').attr('disabled', 'disabled')
				/* copied from jqueryui.com/autocomplete/#remote-with-cache */
				var cache = {};
				$(el).find('input').autocomplete({
					source: function( request, response ) {
						var term = request.term;
						if ( term in cache ) {
							response( cache[term] );
							return;
						}
						$.getJSON( '?data=organisations', request, function( data, status, xhr ) {
							cache[term] = data;
							response(data);
						});
					},
					minLength: 2,
					select: function(event, ui) {
						nextElement.find('input').val(ui.item['ID']);
						nextElement.find('input').change();
						if (newcls == 'buyer-name') {
                            $('.tb-close-icon').click();
                        }
					}
				}).autocomplete('widget').addClass(newcls);
				$(this).find('input').attr('autocomplete','on');
			}
		}

		
		function styleElements(container) {
			container.find('input.value, select, textarea,.select2-selection,.select2-selection__rendered').each(function(i, el) {
				label = $(el).parent().siblings().find('label');
				label.addClass('withInput');
				$('body').click(function(evt) {
					//if ( !$(evt.target).is('input[type=checkbox]') ) evt.preventDefault();
					if ( $(evt.target).parents('div.wrap').length ) evt.preventDefault();
					var parentlabel = $(el).parent().siblings().find('label');
					if ($(el).is(':focus') && !$(el).is('input[type=checkbox]')) {
						parentlabel.addClass('focus');
						$(el).addClass('shadow');
					} else {
						parentlabel.removeClass('focus');
						$(el).removeClass('shadow');
						$('.select2-selection').removeClass('shadow');
					}
				});
			});
			$('body').click(function(evt) {
				label = $(evt.target).closest('.kvp').find('label.withInput');
				element = $(evt.target).closest('.kvp').find('input,select,textarea,.select2-selection');
				if ($(evt.target).is('label, span.help,input.value, select, textarea,.select2-selection,.select2-selection__rendered')) {
					label.addClass('focus')
					element.addClass('shadow')
				} 
			})
			$('.brutusin-form').find('select.role').each(function(i, el) {
				$('body').click(function() {
					var parentinput = $(el).closest('.kvp').find('input')
					if ($(el).is(':focus')) {
						parentinput.addClass('sidefocus');
					} else {
						parentinput.removeClass('sidefocus');
					}
				});
			});
		}
		
		// run functions on first load
		build('#main', '.brutusin-form');
		
		$('.brutusin-form').on('click', '.array button', function() {
			build('#main', '.brutusin-form');
			if ( $('#fieldpaths').length ) {
				id = $('.links li.active').find('a').data('id');
                populateDropdown(getFieldlist(id));
            }
		});
		
		$('#steps').on('click', '.array button', function() {
			build('#steps', '#steps');
		});
		
		$('.brutusin-form').on('click', '.addorg', function() {
			$('#organisationform').find('#targetInput').val($(this).closest('.kvp').attr('data-path'))
		});
		
		$('.step').on('click', '.addorg', function() {
			$('#organisationform').find('#targetInput').val($(this).closest('.kvp').attr('data-path'))
		});
		
		
		// submit organisation thickbox form
		$("#submit-organisation").click(function() {
			formdata = new FormData($('#organisationform').get(0));
			$.ajax({
				data: formdata,
				url: location.href.replace(location.search,'')+'?id=0',
				type: 'POST',
				cache: false,
				processData: false,
				contentType: false,
				success: function(response) {
					var organisation = JSON.parse(response);
					console.log(response)
					if (organisation['success'] == true && organisation['id']) {
						var targetinput = $('#organisationform').find('#targetInput').val();
						$('.kvp[data-path='+targetinput+']').find('select.value').append('<option value="'+organisation['id']+'">'+organisation['name']+'</option>');
						$('.kvp[data-path='+targetinput+']').find('select.value').val(organisation['id']).trigger('change');
						organisationid = setOrganisationId(organisation['id']);
						$('.kvp[data-path='+targetinput+']').next().find('input.value').val( organisationid );
						$('.tb-close-icon').click();
					} else {
						$('.org-error').html(organisation['error'])
					}
				},
				error : function(xhr, status) {
					//alert('Could not retrieve data.');
					console.log(xhr.responseText)
				}
			});
		})
		
		var title = $('div.wrap h2 span').html();
		$('div.wrap h2').hide();
		if (!$('#steps').length) {
			//$('#subheader h4').html(title);
			$ocid = $('#jsonform').find('#ocid');
			$('li.subtitle h4').html("<span class='edit-info'>"+($ocid.val() ? 'Contract ID: '+$ocid.val() : '')+'</span><br><span class="edit-info score">Data Completeness Score: <span id="dcs"></span>%</span>');
		}
		if ($('#steps').length) {
			$('li.subtitle h4').html("<span class='create-info'></span>");
		}
		
		if ($('#stickynav').length) {
			stickyTop = $('#stickynav').offset().top - ($('#stickynav').height() - $('#nav').height());
			navWidth = $('#nav').width();
			$(window).on('scroll', function() {
				if ($(window).scrollTop() >= stickyTop) {
					$('#stickynav').removeClass('menu');
					$('#stickynav').addClass('menutop');
					$('#stickysubnav').addClass('submenutop');
				} else {
					$('#stickynav').removeClass('menutop');
					$('#stickynav').addClass('menu');
					$('#stickysubnav').removeClass('submenutop');
				}
			});
		}
		
		$('#main > .page p').each(function() {
			if ($(this).find('img').length) {
				parent = $(this).closest('article');
				img = $(this).find('img');
				imgitem = img.parent().is('a') ? img.parent() : img;
				imgitem.clone();
				newp = $('<p></p>').css({width:"100%", margin: "0"});
				$(this).wrap(newp);
				img.addClass('thumbright');
				imgitem.insertAfter($(this));
				parent.css({})
			}
		});
		
		$('#saveData').click(function() {
			if ( $('#releasetaglist').length ) {
				if ( $('#releasetaglist').val() ) {
					$('#jsonform').attr('action', window.location.href);
					$('#getData').click();
				} else {
					$('.releasetip').next('.scrolly').click();
					alert("Oops! Seems you've forgotten to describe your changes...");
					$('.releasetip').tooltipster('open');
				}
			} else {
				if ( $('#contract-title').val() ) {
					$('#jsonform').attr('action', window.location.href);
					$('#getData').click();
				} else {
					alert("You'll need to give the contract a title.");
					$('#contract-title').focus();
				}
			}
		});

		$('#importData').click(function() {
			$('a.thickbox.import').click();
		});
		
		if ($('#steps').length) mapFields($);
		
		function mapFields($) {
			var fields = JSON.parse( $('#fieldsjson').text() );
			$('#steps fieldset').each(function() {
				$this = $(this);
				thisfield = $this.attr('id');
				fieldlist = fields[thisfield]['fields'];
				$.each(fieldlist, function(i, item) {
					fieldparent = item.field.split('-')[0];
					var elements = $('.kvp[data-path='+item.field+']');
					$this.append(elements);
					var parent = $("<div data-path='"+fieldparent+"' class='kvp widget'></div>");
					elements.wrap(parent);
					//if (elements.hasClass('hasitems')) {
						elements.find('label:first').text(item.description);
					//}
				});
			})
		}
		
		$('.releasetip').tooltipster({
			side: ['top'],
			maxWidth: 350,
			trigger: 'click',
			distance: 1
		});
		
		function build( maincontainer, subcontainer ) {
			$maincontainer = $(maincontainer);
            formSetUp( $maincontainer );
			transformElements( $maincontainer );
			styleElements( $(subcontainer) );
			$('.help').tooltipster({
				side: ['bottom'],
				maxWidth: 350,
				trigger: 'click',
				distance: 10,
				debug: false
			});
        }
		
		// contract name select dropdown
		$.getJSON( '?data=contracts', function( data, status, xhr ) {
			contractId = location.search.split('=')[1];
			$.each(data, function(i,el) {
				$('select#contractname').append('<option value="'+el.ID+'" '+(el.ID==contractId ? 'selected' : '')+'>'+el.label+'</option>');
			})
		});
		$('#contractname').select2({width:'400px', dropdownCssClass: 'contract-dropdown'});
		//$('#subheader').on('click', '.select2', function() {
		$('body').click(function(evt) {
			if ( $(evt.target).closest('#subheader h4').length || $(evt.target).is('.contract-dropdown .select2-search__field') ) {
				$('#subheader .select2-selection').addClass('contract-selected');
			} else {
				$('#select2-contractname-container').parent().removeClass('contract-selected');
			}
		})
		$('#subheader').on('change', '#contractname', function() {
			location.href = '?id='+$(this).val();
		});
		
		// create.php: simultaneous data entry
		$('#contract-title').keyup(function() {
			val = $(this).val();
			$('.kvp[data-path=planning-budget-project]').find('input.value').val(val).change();
			$('.kvp[data-path=tender-title]').find('input.value').val(val).change();
		})
		
		// Data completeness
		json_download_url = $('#json_download_url').val() + "&format=releases&display=raw";
		$.getJSON( '?ocds_data=field-scheme', function( scheme, status, xhr ) {
			$.getJSON( '?ocds_data=selected-fields', function( selectedfields, status, xhr ) {
				$.getJSON( json_download_url, function( ocds, status, xhr ) {
					//console.log(ocds)
					output = qualitycheck.calculatescores(scheme, selectedfields, ocds);
					$('#dcs').text(output['completionscore'].toFixed(2))
				});
			});
		});
	
	})
	
}

function validate(url) {
	var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	if (pattern.test(url)) {
		return true;
	} 
		return false;
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
			label.innerHTML = fileName;
		else
			label.innerHTML = labelVal;
	});
});
