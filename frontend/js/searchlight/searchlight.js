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

//(function($){
jQuery(document).ready(function($) {

	/********* Tab navigation ***********/

	var filtered = [];

	$('.links li.tab').click(function() {
		if ( !$(this).hasClass('action') ){
			$('.links li.tab').removeClass('active')
			$(this).addClass('active');
			id = $(this).find('a').data('page');
			currpage = $('article#'+id);
			if (currpage.length) {
				$('#main article').hide(); // hide all pages
				currpage.show().fadeIn(); // display current page
			}
		}
		if ($(this).hasClass('charts')) {
			// prepare chart
			var amountscheme = {
				'planning-amount': 'planning/budget/value/amount',
				'awards-amount': 'awards/value/amount',
				'contracts-amount': 'contracts/value/amount'
			}
			var amountfields = getfields(amountscheme, filtered);
			var amountkeys = getKeyArray(amountfields);
			
			var datescheme = {
				'tender-start': 'tender/tenderPeriod/startDate',
				'tender-end': 'tender/tenderPeriod/endDate',
				'awards-date': 'awards/date',
				'contract-start': 'contracts/period/startDate',
				'contract-end': 'contracts/period/endDate',
				'contract-signed': 'contracts/dateSigned'
			}
			var datefields = getfields(datescheme, filtered);
			var datekeys = getKeyArray(datefields);
			
			// Create a list of day and monthnames.
			var weekdays = [
					"Sunday", "Monday", "Tuesday",
					"Wednesday", "Thursday", "Friday",
					"Saturday"
				],
				months = [
					"January", "February", "March",
					"April", "May", "June", "July",
					"August", "September", "October",
					"November", "December"
				];
			
			var supplierscheme = {
				'supplier-name': 'awards/suppliers/name'
			}
			var supplierfields = getfields(supplierscheme, filtered);
			var supplierkeys = getKeyArray(supplierfields);

			var singlefields = {
				'tender-title':'tender/title',
				'tender-status': 'tender/status'
			}
			
			chartdata = getfieldvalues(amountscheme, filtered, singlefields);
			//console.log(JSON.stringify(flattened));
			
			$('#chartarea div').each(function(index, item) {
				// run function from string
				fn = window["drawChart_"+$(item).attr('id')];
				if(typeof fn === 'function') {
					fn(chartdata, $(item).attr('id'));
				}
			})
		}
	});
	
	/********* Assign the flattened json to JsonQuery ***********/
	
    //var releases = contracts['releases'];
	var extraCols;
	
	$('#searchbtn').click(function() {
		flattened = [];
        var searchitem = $('#searchbox').val();
		var searchkey = $('#searchby select').val();
		baseurl = location.href.replace(location.search,'').replace('#','')+'?action=search&post_type=open_contract&';
		if (searchkey == 'all') {
            querystrings = $.map($('#searchby > select > option'), function(a) { return a.value+'='+searchitem; }).join('&');
        } else {
			querystrings = searchkey+'='+encodeURIComponent(searchitem);
		}
		searchurl = baseurl + querystrings;
		//console.log(searchurl)
		
		$('.loading').find('span').html('Searching '+(searchitem ? 'for "'+searchitem+'"...' : '...'));
		$('.loading').css('visibility','visible');

        /*$.ajax({
            url: searchurl,
            type: 'POST',
            success: function(response) {
				$('.loading').css('visibility','hidden');
				contracts = JSON.parse(response);
                $.each(contracts['releases'], function(index, item) {
					flattened.push(JSON.flatten(item))
				});
				//updateChart2(flattened);
				//console.log(flattened)
				/*$('#contractslist2').dynatable({
					dataset: {
					  records: flattened
					},
					features: {
						pushState: false
					},
					writers: { _rowWriter: function(index, record, columns, cellWriter) {
						row = '<tr>';
						var count = 0;
						title = getKeyVal('title', record);
						description = getKeyVal('description', record);
						contractor = getKeyVal('contractor', record);
						procuringEntity = getKeyVal('procuringentity', record);
						for(j=0; j<columns.length; j++) {
							//if (title.length > 0) { // show only valid results
								switch (j) {
									case 0:
										value = '<a href="'+record['post-url']+'" class="title">'+(title[1] ? title[1] : '[Untitled Project]')+'</a>';
										value += '<br><small><span class="label">Description</span>: '+(description[1] ? description[1] : '[None provided]')+'</small>';
										value += '<br><small><span class="label">Procuring Entity</span>: '+(procuringEntity[1] ? procuringEntity[1] : '[None provided]')+'</small>';
										value += '<br><small><span class="label">Contractor</span>: '+(contractor[1] ? contractor[1] : '[None provided]')+'</small>';
										editcontract = isloggedin ? '|<a href="?id='+record['post-id']+'" target="_blank">Edit this contract</a>' : '';
										value += '<br><input type="hidden" name="ocid" id="ocid" value="'+record['ocid']+'"><span class="action">Download: <a href="'+record['post-url']+'?action=download&type=csv">CSV</a>|<a href="'+record['post-url']+'?action=download&type=json">JSON</a>'+editcontract+'</span>'
										row += '<td class="main-title">'+value+'</td>';
									break;
									default:
										colname = columns[j].id;
										value = '<span>'+displayVal(colname, record)+'</span>';
										row += '<td class="extra '+colname+'" style="'+(($selectCol.val() == colname) ? '' : 'display:none')+'">'+value+'</td>';
								}
							//} else {
								//count--;
							//}
						}
						row += '</tr>'
						return row;
						}
					}
				});
				
				if($.isPlainObject(result)){
				  count = Object.keys(flattened).length; 
				}else{
				  count = flattened.length;
				}
				
				var dynatable = $('#contractslist2').data('dynatable');
				if (!extraCols) {
					extraCols = {'amount':['amount','Project Cost'],'status':['status','Project Status'],'contractdate':['contractdate','Contract Year'], 'awarddate':['awarddate','Award Year']};
					$selectCol = $('<select id="colselect" class="colselect"></select>');
					$selectCol.insertBefore($('#contractslist2'));
					$.each(extraCols, function(index, item) {
						$selectCol.append('<option value="'+item[0]+'">'+item[1]+'</option>')
						dynatable.domColumns.add($('<th class="extra '+item[0]+'">'+item[0]+'</th>'), 1);
					});
                }
				dynatable.records.updateFromJson({records: flattened});
				dynatable.records.init();
				dynatable.process();
				
				$('#resultwrap').on('change', '#colselect', function(){
					var col = $(this).val();
					$('#contractslist').find('th.extra,td.extra').hide();
					$('#contractslist').find('th.extra.'+col+',td.extra.'+col).show();
					$('#contractslist').find('th').hide();
				});
				$('#colselect').trigger('change');
				
				var formated_json = JSON.stringify(flattened, undefined, 2);
				updateResult("Found a total of " + count +  ' contracts ', formated_json);
				
				$('#scrollbtn').click();
				$('#resulthead, #resultwrap, #visualwrap').show();


				// Playground
				var amountscheme = {
			        'Planning': 'planning/budget/value/amount',
			        'Awards': 'awards/value/amount',
			        'Contracts': 'contracts/value/amount'
			    }
			    var amountfields = getfields(amountscheme);
				var amountkeys = getKeyArray(amountfields);
			    normaliseRecord(amountkeys, flattened);
			    
			    var datescheme = {
			        'Tender-start': 'tender/tenderPeriod/startDate',
			        'Tender-end': 'tender/tenderPeriod/endDate',
			        'Awards': 'awards/date',
			        'Contract-start': 'contracts/period/startDate',
			        'Contract-end': 'contracts/period/endDate',
			        'Contract-signed': 'contracts/dateSigned'
			    }
			    var datefields = getfields(datescheme);
			    var datekeys = getKeyArray(datefields);
			    normaliseRecord(datekeys, flattened);
			    //console.log(datekeys)
			    // Create a list of day and monthnames.
			    var weekdays = [
			            "Sunday", "Monday", "Tuesday",
			            "Wednesday", "Thursday", "Friday",
			            "Saturday"
			        ],
			        months = [
			            "January", "February", "March",
			            "April", "May", "June", "July",
			            "August", "September", "October",
			            "November", "December"
			        ];
			    
			    var supplierscheme = {
			        'Supplier': 'awards/suppliers/name'
			    }
			    var supplierfields = getfields(supplierscheme);
			    var supplierkeys = getKeyArray(supplierfields);
			    normaliseRecord(supplierkeys, flattened);
			    
			    function getfields(scheme) {
			        var fields = [];
			        for (var key in scheme) {
			            fields[key] = getQueryFields(scheme[key], flattened);
			        }
					//console.log(fields)
			        return fields;
			    }

				
				//showCharts();
			    
			    function showCharts() {
					var data = flattened;
			        // X axis slider
			        $('.top').show();
			        drawChart(flattened);
			    }
			    
			    var chart, chartData;
			    
			    function prepareData(data, fields) {
			        var datum = [];
			        for (var name in fields) {
			            if (fields.hasOwnProperty(name)) {
			                values = [];
			                key = {};
			                key['key'] = name;
			                data.forEach(function(item, index) {
			                    // sum the list of possible keys in each object i.e. for example, contracts[0] + contracts[1] etc.
			                    var arrsum = fields[name].reduce(function(a, b) {return a+item[b]}, 0);
			                    values.push({
			                        "x": item['tender/title'],
			                        "y": arrsum,
			                        "z": supplierkeys.map(function(skey) {return item[skey]})
			                    });
			                })
			                key['values'] = values;
			                datum.push(key);

			            }
			        }
			        //console.log(datum)
			        
			        return datum;
			    }
			    
			    function updateChart(data) {
			        var datum = prepareData(data, amountfields);
			        if (chartData) {
			            chartData.datum(datum).transition().duration(500).call(chart);
			            nv.utils.windowResize(chart.update);
			        }
			    }
			    
			    function drawChart(data) {
			        datum = prepareData(data, amountfields)
			        //console.log(JSON.stringify(datum))
			        
			        nv.addGraph(function() {
			            chart = nv.models.multiBarChart()
			                .barColor(d3.scale.category20().range())
			                .duration(300)
			                .margin({bottom: 100, left: 100})
			                .rotateLabels(45)
			                .groupSpacing(0.1)
			                .showXAxis(false)
			            ;
			            
			            chart.tooltip.contentGenerator(function(d) {
			                return 'Contract: '+d.value+'<br> Stage: '+d.series[0].key+'<br> Cost: '+d.series[0].value+'<br> Supplier(s): '+d.data.z;
			            });
			    
			            chart.reduceXTicks(false).staggerLabels(true);
			    
			            chart.xAxis
			                .axisLabel("Projects")
			                .axisLabelDistance(35)
			                .showMaxMin(false)
			                //.tickFormat(d3.format(',.6f'))
			                .tickFormat(function(d) { return d; })
			            ;
			    
			            chart.yAxis
			                .axisLabel("")
			                .axisLabelDistance(-5)
			                .tickFormat(d3.format(',.01f'))
			            ;
			    
			            chart.dispatch.on('renderEnd', function(){
			                nv.log('Render Complete');
							$('.links li a.active').click(); // hide it after rendering
			            });
			    
			            chartData = d3.select('#chartarea svg').datum(datum).call(chart);
			            chartData.transition().duration(500).call(chart);
			    
			            nv.utils.windowResize(chart.update);
			    
			            chart.dispatch.on('stateChange', function(e) {
			                nv.log('New State:', JSON.stringify(e));
			            });
			            chart.state.dispatch.on('change', function(state){
			                nv.log('state', JSON.stringify(state));
			            });
			    
			            return chart;
			        });
			    }
				
				
				$('a.selectchart').each(function(i, elem) {
                    $(elem).click(function(evt) {
                        $('#chartframe').prop('src', $(evt.target).prop('href'));
                        evt.stopPropagation();
                        evt.preventDefault();
                        //$('li').removeClass('selected');
                        //$(evt.target).parents('li').addClass('selected');
                        return false;
                    });
                });
			
			}
		});*/
		var table;
		$.ajax({
            url: searchurl,
            type: 'POST',
            success: function(response) {
				$('.loading').css('visibility','hidden');
				contracts = JSON.parse(response);
                $.each(contracts['releases'], function(index, item) {
					flattened.push(JSON.flatten(item))
				});
				function isNullOrWhiteSpace(str) {
					return (!str || str.length === 0 || /^\s*$/.test(str))
				}
				table = $('#contractslist').DataTable({
					data: flattened,
					columns: [{
						data: 'main',
						render: function ( data, type, record ) {
							title = getKeyVal('title', record);
							description = getKeyVal('description', record);
							contractor = getKeyVal('contractor', record);
							procuringEntity = getKeyVal('procuringentity', record);
							value = '<a href="'+record['post-url']+'" class="title">'+(!isNullOrWhiteSpace(title[1]) ? title[1] : 'n/a')+'</a>';
							value += '<table class="insetdata"><tr><td class="main">';
							value += '<small><span class="label">Description</span>: '+(!isNullOrWhiteSpace(description[1]) ? description[1] : 'n/a')+'</small>';
							value += '<br><small><span class="label">Procuring Entity</span>: '+(!isNullOrWhiteSpace(procuringEntity[1]) ? procuringEntity[1] : 'n/a')+'</small>';
							value += '<br><small><span class="label">Contractor</span>: '+(!isNullOrWhiteSpace(contractor[1]) ? contractor[1] : 'n/a')+'</small>';
							value += '</td><td class="sub">';
							value += '<small><span class="label">Cost</span>: '+displayVal('amount', record)+'</small>';
							value += '<br><small><span class="label">Contract Date</span>: '+displayVal('contractdate', record)+'</small>';
							value += '<br><small><span class="label">Award Date</span>: '+displayVal('awarddate', record)+'</small>';
							value += '<br><small><span class="label">Status</span>: '+displayVal('status', record)+'</small>';
							value += '</td></tr></table>';
							editcontract = isloggedin ? '|<a href="?id='+record['post-id']+'" target="_blank">Edit this contract</a>' : '';
							value += '<input type="hidden" name="ocid" id="ocid" value="'+record['ocid']+'"><span class="action">Download: <a href="'+record['post-url']+'?action=download&type=csv">CSV</a>|<a href="'+record['post-url']+'?action=download&type=json">JSON</a>'+editcontract+'</span>';
							return value;
						}
					}]
					
				}); // end datatable

				$(table.columns(0).header()).html(''); //remove column title
				
				table.on('search.dt', function() {
					//number of filtered rows
					filteredcount = table.rows( {filter : 'applied'} ).nodes().length;
					//filtered rows data as arrays
					allfiltered = table.rows( {filter : 'applied'} ).data();
					filtered = [];
					for (i=1; i<filteredcount; i++) {
						filtered.push(allfiltered[i])
					}                 
				});

				var formated_json = JSON.stringify(flattened, undefined, 2);
				updateResult("Found a total of " + flattened.length +  ' contracts ', formated_json);
				
				$('#scrollbtn').click();
				$('#resulthead, #resultwrap, #visualwrap').show(); 
			}
			
		}); // end ajax

	}); // end searchbtn click
	
	
	//Pie chart example data. Note how there is only a single array of key-value pairs.
	
	function getKeyVal(filteritem, record) {
		var allkeys = [], keyval = [], keyExists = false;
		// first, get all available keys from the record
        $.each(filterfields[filteritem], function(index, item) {
			keys = getQueryFields(item, [record]); // second argument should be an array
			allkeys = allkeys.concat(keys)
			//console.log(keys)
		});
		// then return the first key with a valid value
		for(i=0; i<allkeys.length; i++) {
			if (record[allkeys[i]] && record[allkeys[i]] != '') {
                keyval = [allkeys[i], record[allkeys[i]]];
            }
			if (keyval.length > 0) break;
		}
		return keyval
    }
	
	function displayVal(item, record) {
		keyval = getKeyVal(item, record);
        switch (item) {
            case 'amount':
				if (keyval[0]) {
                    keyroot = keyval[0].split('/');
					keyroot.pop();
					keyroot.push('currency');
					return record[keyroot.join('/')]+' '+keyval[1];
                } else {
					return 'n/a';
				}
			break;
			case 'status':
				if (keyval[1]) {
                    return keyval[1]+' ('+keyval[0].split('/')[0].replace(/\[.*?\]/g,'')+')';
                } else {
					return 'n/a';
				}
			break;
			case 'contractdate':
			case 'awarddate':
				d = new Date(keyval[1]);
				if ( !isNaN(d) ) {
                    return d.getFullYear();
                } else {
					return 'n/a';
				}
			break;
			default:
				return keyval[1];
        }
	}
	
	function getfields(scheme, data) {
		var fields = [];
		for (var key in scheme) {
			fields[key] = getQueryFields(scheme[key], data);
		}
		//console.log(fields)
		return fields;
	}

	function getfieldvalues(scheme, data, others) {
		var fields = getfields(scheme, data);
		// loop through values of array
		var rows = []
		data.forEach(function(datum, index) {
			row = {};
			for (item in fields) {
				values = [];
				for (i=0; i<fields[item].length; i++) {
					// get the value from the full dataset
					values.push(datum[fields[item][i]]);
				}
				sum = values.reduce((partial_sum, a) => !isNaN(a) ? partial_sum + a : partial_sum, 0);
				row[item] = sum;
				for (other in others) {
					row[other] = datum[others[other]] ? datum[others[other]] : '[none]';
				}
			}
			rows.push(row);
		});
		return rows;
	}

	function prepareData(data, fields) {
		var datum = [];
		for (var name in fields) {
			if (fields.hasOwnProperty(name)) {
				values = [];
				key = {};
				key['key'] = name;
				data.forEach(function(item, index) {
					// sum the list of possible keys in each object i.e. for example, contracts[0] + contracts[1] etc.
					var arrsum = fields[name].reduce(function(a, b) {return a+item[b]}, 0);
					values.push({
						"x": item['tender/title'],
						"y": arrsum
						//"z": supplierkeys.map(function(skey) {return item[skey]})
					});
				})
				key['values'] = values;
				datum.push(key);

			}
		}
		//console.log(datum)
		
		return datum;
	}
	
	/********* Utility functions ***********/
    
	/* To display ocds fields, given a stage
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
    }*/
    
	///// Get the list of valid fields from the flattened json
    function getQueryFields(queryField, flattened_data) {
		//console.log(flattened_data) // flattened_data should be an array of objects
		var searchfields = [];
		if (queryField) {
			var qfields = queryField.split('/');
			for (item in flattened_data) {
				for (key in flattened_data[item]) {
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
	
	////// Normalise the records i.e. Update insert each item with search fields (setting the value to empty)
	function normaliseRecord(fields, record) {
		var newrecord = [], newitem;
        $.each(record, function(index, item) {
			for (i=0; i<fields.length; i++) {
				if (!item.hasOwnProperty(fields[i])) {
					newitem = item
					newitem[fields[i]] = null;
				}
			}
			newrecord.push(newitem)
		});
		return newrecord;
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
	
	
	function updateResult(title, result, error) {
		var $ele = $("#result");
	  
		if(!error){
		  $ele.find('#result-title').text(title);
		  //$ele.find('pre').text(result);
		}else{
		  $ele.find('#result-title').text('');
		  $ele.find('pre').html("<div class='alert alert-danger'> ERROR:" + error.message + "</div>");
		}
	  
		$ele.fadeOut().fadeIn();
	}
    
    function removeCommas(num) {
        num=num.replace(/\,/g,''); // 1125, but a string, so convert it to number
        return parseInt(num);
    }
    
    // get the complete list of possible keys from the amountscheme
    // uses 'fields' object defined in global
    function getKeyArray(fields) {
        var keyarray = [];
        for (var key in fields) {
            for (i=0; i<fields[key].length; i++) {
                keyarray.push(fields[key][i]);
            }
        }
        return keyarray;
    }
    
    // returns [min, max] values
    // uses 'amountkeys' array defined in global
    function getMinMax(data, keys, src) {
        var array = [];
        data.forEach(function(item, index) {
            for (i=0; i<keys.length; i++) {
                if (src == 'amount') {
                    if (!isNaN(item[keys[i]])) {
                        array.push(item[keys[i]])
                    }
                } else if (src == 'date') {
                    if (timestamp(item[keys[i]])) {
                        array.push(timestamp(item[keys[i]]))
                    }
                }
            }
        });
        //console.log(array)
        return [
                array.reduce(function(a,b){return Math.min(a,b)}),
                array.reduce(function(a,b){return Math.max(a,b)})
               ]
    }
    
    function findMinMax(arr, key) {
        var min = arr[0][key],
            max = arr[0][key];
        //console.log(min, max)
        for (i=1; i<arr.length; i++) {
            var v = arr[i][key];
            min = (v < min) ? v : min;
            max = (v > max) ? v : max;
        }
        return [min, max];
    }
    
    
    // DATE HELPER FUNCTIONS
    // Create a new date from a string, return as a timestamp.
    function timestamp(str){
        return new Date(str).getTime();   
    }
    
    // Append a suffix to dates.
    // Example: 23 => 23rd, 1 => 1st.
    function nth (d) {
      if(d>3 && d<21) return 'th';
      switch (d % 10) {
            case 1:  return "st";
            case 2:  return "nd";
            case 3:  return "rd";
            default: return "th";
        }
    }
    
    // Create a string representation of the date.
    function formatDate ( date ) {
        /*weekdays[date.getDay()] + ", " +*/
        return date.getDate() + nth(date.getDate()) + " " +
            months[date.getMonth()] + " " +
            date.getFullYear();
    }
    
    function convertDate(timestamp) {
        //datestr = formatDate(new Date(parseInt(timestamp)));
        return new Date(parseInt(timestamp)).toISOString().split('.')[0]+'Z';
    }
	
	
	function editOCDS(el) {
        alert($(el).val())
	}
	
	
});
			