////// SIMPLE/ADVANCED OCDS SEARCH


/********* Flatten the OCDS json ***********/

JSON.flatten = function (data) {
	var result = {};

	function recurse(cur, prop) {
		if (Object(cur) !== cur) {
			result[prop] = cur;
		} else if (Array.isArray(cur)) {
			for (var i = 0, l = cur.length; i < l; i++)
			recurse(cur[i], prop + "[" + i + "]");
			if (l == 0) result[prop] = [];
		} else {
			var isEmpty = true;
			for (var p in cur) {
				isEmpty = false;
				recurse(cur[p], prop ? prop + "/" + p : p);
			}
			if (isEmpty && prop) result[prop] = {};
		}
	}
	recurse(data, "");
	return result;
};

JSON.unflatten = function (data) {
	"use strict";
	if (Object(data) !== data || Array.isArray(data)) return data;
	var regex = /\.?([^.\[\]]+)|\[(\d+)\]/g,
		resultholder = {};
	for (var p in data) {
		var cur = resultholder,
			prop = "",
			m;
		while (m == regex.exec(p)) {
			cur = cur[prop] || (cur[prop] = (m[2] ? [] : {}));
			prop = m[2] || m[1];
		}
		cur[prop] = data[p];
	}
	return resultholder[""] || resultholder;
};


$(document).ready(function(){
	
	/********* Assign the flattened json to JsonQuery ***********/
	
    var releases = contracts['releases'];
    var flattened = [];
    $.each(releases, function(index, item) {
      flattened.push(JSON.flatten(item))
    })
    //console.log(flattened)
    window.Releases = JsonQuery(flattened);
    queryHelper(Releases);
	
	$('#contractslist').dynatable({
        dataset: {
          records: flattened
        },
		features: {
			pushState: false
		},
		writers: { _rowWriter: function(index, record, columns, cellWriter) {
			row = '<tr>'
			$.each(record, function(key, value) {
				if (key=='planning/budget/description') {
					value = '<a href="post.php?post='+record['post-id']+'&action=edit">'+value+'</a>';
					row += '<td class="column-primary">'+value+'</td>'
				}
			})
			row += '</tr>'
			return row;
			}
		}
    });
  
    //window.Service = JsonQuery(services);


    /********* Create main filter form ***********/
	
    //var objData = JSON.parse(filterfields);
    var stageselect = $('<select class="stages"></select>');
    stageselect.append('<option value="" selected="selected">Select a stage</option>');
    var fieldselect = $('<select class="fields"></select>');
    var defaultfield = $('<option value="" selected="selected">Select a field</option>')
    fieldselect.append(defaultfield);
    var fieldlabels = Object.keys(filterfields);
    // populate stage dropdown
    for (item in filterfields) {
        stageselect.append('<option value="'+item+'">'+item+'</option>');
    }
    updatefields(stageselect, fieldselect, filterfields);
    var operators = {
      '$eq':'equal to',
      '$ne':'not equal to',
      '$gt':'greater than',
      '$ge':'greater than/equal to',
      '$lt':'less than',
      '$le':'less than/equal to',
      '$li':'contains'
    };
    var operator = $('<select class="operator"></select>');
    operator.append('<option value="" selected="selected">----</option>')
    $.each(operators, function(k,v) {
      operator.append('<option value="'+k+'">'+v+'</option>');
    });
    var text = $('<input type="text" class="filtervalue">');
    $('#filters').append('<table id="filteritems"></table>')
	var col1 = $('<td></td>').append(stageselect);
	var col2 = $('<td></td>').append(fieldselect);
	var col3 = $('<td></td>').append(operator);
	var col4 = $('<td></td>').append(text);
    var group = $('<tr class="filteritem"></tr>').append(col1).append(col2).append(col3).append(col4);
    $('#filteritems').append(group)
    var addbutton = $('<input type="button" value="Add new filter" class="button addfilterbtn" id="addfilter">').insertAfter($('#filteritems'));
    var loadbutton = $('<input type="button" value="Filter Records" class="button button-primary" id="dofilter">').insertAfter($('#addfilter'));
    addbutton.click(function() {
        var removebtn = $('<input type="button" value="Remove" class="remove-filter button">');
        var newfilter = group.clone();
		var col5 = $('<td></td>').append(removebtn);
        newfilter.append(col5).appendTo("#filteritems");
        newfilter.find('.fields').empty().append(defaultfield.clone());
        newfilter.find('.filtervalue').val('');
        updatefields(newfilter.find('.stages'), newfilter.find('.fields'), filterfields);
        removebtn.click(function() {
            $(this).closest('.filteritem').remove();
        });
    });
	
    
	/********* Visual search ***********/
	
	///// first, prepare the visual search output
	var vs_query = $('<span class="vs_query"></span>')
								.append('<input type="hidden" class="fields">')
								.append(operator.clone())
								.append(text.clone())
								.hide();
	var vs_wrapper = $('<span class="vs_query_wrapper">')
	vs_wrapper.append('<span id="field"></span>&nbsp;').append(vs_query)
	var advfilterbtn = $('<input type="button" value="Add to filter" class="button addfilterbtn" id="addrow">');
	var querycontainer = $('#ocds_search_query').append(vs_wrapper).append(advfilterbtn)
  
	///// second, generate the visual search bar and its functionality
    window.visualSearch = VS.init({
        container  : $('#ocds_search_container'),
        query      : '',
        showFacets : true,
        callbacks  : {
			search : function(query, searchCollection) {
			  var count  = searchCollection.size();
			  var fields = [];
			  if (searchCollection['models'][count-1]) {
				var stage = searchCollection['models'][count-1]['attributes']['category'];
				//console.log(searchCollection, stage, query)
				searchCollection['models'].forEach(function(item,i) {
				  fields.push(item['attributes']['category'])
				});
				if (stage) {
				  //console.log(fields, query)
				  this.facetMatches = function(callback) { callback(this.getFieldChildren(fields)) }
				  this.valueMatches = function(facet, searchTerm, callback) { this.checkFacet(stage, fields, callback); }
				}
			  } else {
				  // back to basic list
				  this.facetMatches = function(callback) { callback(this.stageFacet(), {preserveOrder: true}); }
			  }
			  var $query = $('#ocds_search_query');
			  var $querybox = $query.find('.vs_query_wrapper');
			  
			  curQuery = query.replace(/\s/g,'').replace(/:/g,'')
			  $query.find('#field').html(curQuery); // remove spaces and colons
			  
			  nextfield = this.getFieldChildren(fields);
			  if (!nextfield || nextfield.length == 0) {
				  curQuery = curQuery.replace(/\/$/, '');
				  $query.find('#field').html(curQuery + ':'); // display generated field
				  $querybox.find('.fields').val(curQuery); // assign field to input element
				  $querybox.find('.vs_query').show(); // display controls
				  $this = this;
				  if ($query) $('#addrow').off().on('click', function() { $this.addFilter($querybox) });
			  } else {
				  $querybox.find('.vs_query').hide();
				  $querybox.find('.filtervalue').val('')
			  }
			},
			facetMatches : function(callback) {
			  callback(this.stageFacet(), {preserveOrder: true});
			},
			valueMatches : function(facet, searchTerm, callback) {
			  callback(['/']);
			},
			stageFacet: function() {
			  return [
				'planning', 'tender', 'awards', 'contracts',
				{ label: 'ocid',       category: 'metadata' },
				{ label: 'id',    category: 'metadata' },
				{ label: 'language', category: 'metadata' },
			  ]
			},
			getFieldValue: function(fields) {
			  var obj = ocds;
			  fields.forEach(function(item,i) {
				if (obj[item]) {
					if (Array.isArray(obj[item])) {
						obj = obj[item][0];
					} else {
						obj = obj[item];
					}
				}
			  });
			  return obj;
			},
			getFieldChildren: function(fields) {
			  return this.scanFields(this.getFieldValue(fields));
			},
			checkFacet: function(stage, fields, callback) {
			  // check if field has object as its value
			  obj = this.getFieldValue(fields);
			  if (obj instanceof Object || Array.isArray(obj)) {
				console.log(stage, fields, obj)
				return callback(['/']);
			  } else {
				return callback(['']);
			  }
			},
			scanFields: function(obj) {
			  var k;
			  var items = []
			  if (obj instanceof Object) {
				  for (k in obj) {
					item = {}
					if (obj.hasOwnProperty(k)) {
					  items.push(k);
					}
				  }
			  }
			  return items;
			},
			clearSearch: function(fn) {
			  fn(); this.valueMatches = function(facet, searchTerm, callback) { callback(['/']) }
			},
			addFilter: function($querybox) {
				//// add the visual search output to the filter form
				var removebtn = $('<input type="button" value="Remove" class="remove-filter button">');
				var operator_val = $querybox.find('.operator').find(':selected').val();
				var newfilter = $querybox.clone();
				newfilter.append(removebtn);
				var col1 = $('<td colspan="2" class="fieldname"></td>')
								.html( newfilter.find('.fields').val() )
								.append( newfilter.find('.fields') );
				newfilter.find('.operator').val(operator_val); // set the value of the cloned select element
				var col3 = $('<td></td>').append( newfilter.find('.operator') );
				var col4 = $('<td></td>').append( newfilter.find('.filtervalue') );
				var col5 = $('<td></td>').append( removebtn );
				var querygroup = $('<tr class="filteritem"></tr>').append(col1).append(col3).append(col4).append(col5);
				//var querygroup = $('<tr class="filteritem"></tr>').append(newfilter)
				querygroup.appendTo("#filteritems");
				removebtn.click(function() {
					$(this).closest('.filteritem').remove();
					$('#dofilter').click();
				});
			}
        }
    });
	
	
	/********* Perform filter ***********/
    
    $('#dofilter').on('click', $('#filters'), function() {
        var queryitems = {},
			allqueries = [],
			querystring = '';
        $.each($('.filteritem'), function(index, item) {
            field = $(item).find('.fields').val();
			value = $(item).find('.filtervalue').val();
			operator = $(item).find('.operator').val();
			//console.log($(item), field)
			fields = getQueryFields(field);
			//console.log(fields)
			queries = [];
			for (i=0; i<fields.length; i++) {
				queryitem = {}; 
				queryitem[fields[i]+'.'+operator] = value
				queries.push(queryitem);
			}
			if (queries.length > 0) allqueries.push(queries)
		});
		
		var queryfields = (allqueries.length > 0) ? cartesian(allqueries) : [];
		//console.log(allqueries, JSON.stringify(queryfields))
		
		for (i=0; i<queryfields.length; i++) {
			qitem = queryfields[i].reduce(function(newObj, curObj) {
				for (var key in curObj) {
					if (curObj.hasOwnProperty(key)) {
                        newObj[key] = curObj[key];
                    }
				}
				return newObj;	
			}, {});
			if (i == 0) {
                querystring = 'Releases.where('+JSON.stringify(qitem)+')';
            } else if (i > 0) {
				querystring += '.or('+JSON.stringify(qitem)+')';
			}
		}
		querystring = (querystring) ? querystring+'.exec();' : '';
        $('#query').val(querystring);
		if (querystring) {
			$('#query-form').submit();
		} else {
			alert('No query to execute!');
		}
    })
	
	
	/********* Utility functions ***********/
    
	///// To display ocds fields, given a stage
    function updatefields(stageselect, fieldselect, filterfields) {
        stageselect.on('change', $('#filters'), function() {
            // populate fields dropdown on stage change
            fieldselect.empty();
            var curstage = $(this).val()
            fieldselect.append('<option value="" selected="selected">Select a field from '+curstage+'</option>');
            for (item in filterfields) {
                if (item == curstage) {
                    for (label in filterfields[item]) {
                        fieldselect.append('<option value="'+filterfields[item][label]+'">'+label+'</option>');
                    }
                }
            }
        });
    }
    
	///// Get the list of valid fields from the flattened json
    function getQueryFields(queryField) {
		var searchfields = [];
		if (queryField) {
			var qfields = queryField.split('/');
			for (item in flattened) {
				for (key in flattened[item]) {
					var exitkey = false
					var dfields = key.split('/');
					if (qfields.length == dfields.length) {
						for (i=0; i<qfields.length; i++) {
							//console.log(i, qfields[i], dfields[i], (dfields[i].indexOf(qfields[i])))
							if (dfields[i].indexOf(qfields[i]) == -1) {
								exitkey = true; break;
							}
						}
						if (exitkey) {
							continue;
						} else {
							// add only if field doesn't already exist
							if (searchfields.indexOf(key) == -1) {
								searchfields.push(key);
							}
						}
					}
				}
			}
		}
        return searchfields;
    }
	
	///// Generate all the possible permutation of arrays from two or more arrays
	///// (https://stackoverflow.com/a/15310051/5757040)
	function cartesian(arg) {
		var r = [], max = arg.length-1;
		function helper(arr, i) {
			for (var j=0, l=arg[i].length; j<l; j++) {
				var a = arr.slice(0); // clone arr
				a.push(arg[i][j]);
				if (i==max)
					r.push(a);
				else
					helper(a, i+1);
			}
		}
		helper([], 0);
		return r;
	}
	
});
			