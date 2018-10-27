<?php
//print_r($post);
//print_r(get_current_record($post));
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
		<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) ?>nouislider/nouislider.min.css">
        <link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) ?>css/custom.css">
		<link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) ?>libs/nvd3/nv.d3.min.css">
		<style>
			.bar rect {
				shape-rendering: crispEdges;
			}
		  
			.bar text {
				fill: #999999;
			}
		  
			.axis path, .axis line {
				fill: none;
				stroke: #000;
				shape-rendering: crispEdges;
			}
		</style>
    </head>
    <body>
      <nav class="navbar navbar-inverse">
        <div class="container">
          <a class="navbar-brand" href="#">OCDS Visualise</a>
          <form class="navbar-form navbar-right" role="search">
            <div class="form-group">
				<textarea id="imported" style="display: none"></textarea>
				<select id="projects" class="btn btn-danger btn-file" onchange="location=this.options[this.selectedIndex].value;">
					<option value="">Select a project</option>
					<?php
					//if (!$_POST) {
						$args = array('post_type' => 'open_contract', 'posts_per_page'=>-1);
						$loop = new WP_Query($args);
						if ( $loop->have_posts() ) : while ($loop->have_posts() ) : $loop->the_post();
							echo '<option value="'.get_permalink($post->ID).'">'.$post->post_title.'</option>';
						endwhile; endif;
					//}
					?>
				</select>
            </div>
          </form>
        </div>
      </nav>
	  <nav class="subnavbar">
		<div class="container">
			<form id="altsource" class="navbar-form navbar-right">
			  <div class="form-group">
				  <input type="file" value="Select an alternative data source" class="altsource" style="float:left">
				  <input type="button" value="Load" class="loadsource">
			  </div>
			</form>
		  </div>
		</div>
	  </nav>
      <div class="container">

        <div id="input-json-container" class="hide">
          <h3> Input a valid OCDS record/release 
          <button id="hide-input-button" class="pull-right btn btn-primary btn-sm">Hide Input</button>
          </h3>
          <textarea id="input-json" name="input-json"></textarea>
        </div>
		
		<div id="records"><?php
			if ( $loop->have_posts() ) : while ($loop->have_posts() ) : $loop->the_post();
				echo "<input type='hidden' class='record' value='".stripslashes(json_encode(get_current_record($post)['records'][0]['compiledRelease']))."'>";
			endwhile; endif;
			?>
		</div>

        <div id="container">
			<div style="border:1px solid">
				<div class="top">
					<!--<input type="range" min="10" max="1000" step="10" value="1000" data-orientation="horizontal">-->
					<div id="slider-barchart"></div>
					<svg></svg>
				</div>
				<div class="middle">
					<button class="refresh">Refresh</button>
					<div id="slider-histogram"></div>
					<svg></svg>
				</div>
				<div class="left-top" style="float:left; width:48%"></div>
				<div class="right-top" style="float:right; width:48%"></div>
				<br style="clear:both">
			</div>
			<div class="right-top" style="float:left; border:0px solid; clear:left"></div>
		</div>
      </div>
    </body>
    
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>flatten.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>/libs/d3.v3.min.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>dimple.min.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>nouislider/nouislider.min.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>libs/nvd3/nv.d3.min.js"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) ?>jstat.min.js"></script>
	<script>
		
		$('.top').hide();
		var records = document.querySelectorAll('.record')
		var result = [];
		records.forEach(function(item, index) {
			var json = item.value;
			try {
				result.push(JSON.flatten(JSON.parse(json), null, "\t"));
			} catch(e) {
				console.log(e)
			}
		});
		//console.log(result)
		
		prepareCount(result);
		
		// get alternative source
		$('.loadsource').click(function() {
			//console.log(result)
			var filedata = $('.altsource').prop('files')[0]
			var formdata = new FormData();
			formdata.append('file', filedata);
			$.ajax({
				url: '<?php echo plugin_dir_url(__FILE__) ?>../import.php',
				type: 'POST',
				data: formdata,
				processData: false,
				contentType: false,
				success: function(response) {
					records = JSON.parse(response);
					result = [];
					//alert(result)
					records['releases'].forEach(function(item, index) {
						try {
							result.push(JSON.flatten(item, null, "\t"));
						} catch(e) {
							console.log(e)
						}
					});
					showChart();
				}
			});
		});
		
		showCharts();
		
		function showCharts() {
            var sliderBarchart = document.getElementById('slider-barchart');
			var sliderHistogram = document.getElementById('slider-histogram');
			if (sliderBarchart.noUiSlider) slider.noUiSlider.destroy();
			noUiSlider.create(sliderBarchart, {
				start: [1, result.length],
				connect: true,
				tooltips: true,
				range: {
					'min': 1,
					'max': result.length
				}
			});
			$('.top').show();
			drawChart(result);
			//drawHistogram(result, '');
			sliderBarchart.noUiSlider.on('update', function( values, handle ) {
				pos = sliderBarchart.noUiSlider.get();
				data = result.slice(pos[0], pos[1]);
				//console.log(pos)
				updateChart(data);
			});
        }
		

		var chart, chartData;
		
		function prepareCount(arr) {
			// adapted from https://stackoverflow.com/questions/37251765/lodash-count-values-from-array-of-objects
            var mydata = {};
			arr.forEach(function(v){
				var key = v['tender.status'], Q = this[key], found = false;
				//console.log(v['tender.title'])
				if (Q) {
					var len = Q.length;
					while (len--) {
						if (Q[len]['x'] === v['awards[0].suppliers[0].name']) {
							Q[len]['y']++;
							found = true;
						}
					}
					if (!found) Q.push({'x': v['awards[0].suppliers[0].name'], 'y' : 1});
				} else {
					this[key] = [];
					this[key].push({'x': v['awards[0].suppliers[0].name'], 'y' : 1});
				}
			}, mydata);
			
			//console.log(JSON.stringify(mydata, 0, 4));
        }
		
		function prepareData(data) {
			datum = [];
			var fields = {
				'Planning': 'planning.budget.value.amount',
				'Awards': 'awards[0].value.amount',
				'Contracts': 'contracts[0].value.amount'
			}
			for (var name in fields) {
				if (fields.hasOwnProperty(name)) {
					values = [];
					key = {};
					key['key'] = name;
					data.forEach(function(item, index) {
						values.push({
							"x": item['tender.title'],
							"y": item[fields[name]]
						});
					})
					key['values'] = values;
					datum.push(key);
				}
			}
			console.log(JSON.stringify(datum))
			
			return datum;
        }
		
		function updateChart(data) {
			var datum = prepareData(data);
			if (chartData) {
				chartData.datum(datum).transition().duration(500).call(chart);
				nv.utils.windowResize(chart.update);
            }
        }
		
		function drawChart(data) {
			datum = prepareData(data)
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
				});
		
				chartData = d3.select('.top svg').datum(datum).call(chart);
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
		
		
		function prepareHistogramData(data) {
			var hisdata = [];
			data.forEach(function(item, index) {
				hisdata.push(item['awards[0].value.amount'])
			});
			//console.log(hisdata);
			return hisdata;
		}
		
		function drawHistogram2(data) {
			//define margins for chart, histogram bin size, and the x scale for the bins
			var m = {top: 30, right: 60, bottom: 50, left: 70}
			  , h = 400 - m.top - m.bottom
			  , w = 400 - m.left - m.right
			  , numBins = 10;
			var x = d3.scale.linear().domain([0, 10]).range([0, w]);
			console.log(w)
			
			var dataset = prepareHistogramData(data);
			//in case we want to deal with the unaltered data
			//generate the histogram bin'd dataset using d3 histogram methods (which should use x scale defined above?)
			//and generate the CDF values using jStat - https://github.com/jstat/jstat
			var jstat = this.jStat(dataset);
			binData = d3.layout.histogram().bins(x.ticks(numBins))(dataset);
			var convertedData = [];
			var countObj = {'key': 'Count', 'bar': true, 'color': '#ccf', 'values': []};
			var cdfObj = {'key': 'CDF', 'color': '#333', 'values': []};
			for(var i = 0; i < binData.length; i++){
			  countObj.values.push([binData[i].x,binData[i].y]);
			  cdfObj.values.push([binData[i].x,jstat.normal(jstat.mean(), jstat.stdev()).cdf(binData[i].x)]);
			}
			convertedData.push(countObj);
			convertedData.push(cdfObj);
			data = convertedData;
			//console.log(data)
			finalDataset = data;
		  
			nv.addGraph(function() {
				var chart = nv.models.linePlusBarChart()
					.margin({top: m.top, right: m.right, bottom: m.bottom, left: m.left})
					//.width(500)
					//We can set x data accessor to use index. Reason? So the bars all appear evenly spaced.
					.x(function(d,i) { return i })
					.y(function(d,i) {return d[1] })
					.focusEnable(false)
					;
		  
				chart.xAxis.tickFormat(function(d) {
				  return d
				});
				chart.xAxis.ticks(data[0].values.length)
		  
				chart.y1Axis
					.tickFormat(d3.format(',f'));
		  
				chart.y2Axis
					.tickFormat(function(d) { return d3.format('%')(d) });
		  
				chart.bars.forceY([0]);
		  
				d3.select('.middle svg')
				  .datum(data)
				  .transition()
				  .duration(0)
				  .call(chart);
		  
				nv.utils.windowResize(chart.update);
		  
				return chart;
			});
  
		}
		
		$('.refresh').click(function() {
			refreshHistogram(result)
		})
		
		
		//function drawHistogram(hisdata, action) {
            var color = "steelblue";
			
			// Generate a 1000 data points using normal distribution with mean=20, deviation=5
			//var values = d3.range(1000).map(d3.random.normal(20, 5));
			//console.log(JSON.stringify(values))
			var values = prepareHistogramData(result);
			//var values = data.map(d3.random.normal(20, 5));
			values = values.sort().slice(1,200);
			
			// A formatter for counts.
			var formatCount = d3.format(",.0f");
			
			var margin = {top: 20, right: 30, bottom: 30, left: 30},
				width = 960 - margin.left - margin.right,
				height = 500 - margin.top - margin.bottom;
			
			var max = d3.max(values);
			var min = d3.min(values);
			var x = d3.scale.linear()
				  .domain([min, max])
				  .range([0, width]);
			console.log(max, width)
			
			// Generate a histogram using twenty uniformly-spaced bins.
			var data = d3.layout.histogram()
				.bins(x.ticks(20))
				(values);
			
			var yMax = d3.max(data, function(d){return d.length});
			var yMin = d3.min(data, function(d){return d.length});
			var colorScale = d3.scale.linear()
						.domain([yMin, yMax])
						.range([d3.rgb(color).brighter(), d3.rgb(color).darker()]);
			
			var y = d3.scale.linear()
				.domain([0, yMax])
				.range([height, 0]);
			
			var xAxis = d3.svg.axis()
				.scale(x)
				.orient("bottom");
				
			var svg = d3.select(".middle svg")
				.attr("width", '100%')
				.attr("height", height + margin.top + margin.bottom)
			  .append("g")
				.attr("transform", "translate(" + margin.left + "," + margin.top + ")");
				
			var bar = svg.selectAll(".bar")
				.data(data)
			  .enter().append("g")
				.attr("class", "bar")
				.attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });
				
		/*if (action == 'refresh') {
            //code
			var values = [2.4059769174850905,
2.7600000000000002,
3.8217080187144488,
2.3899284588203313,
3.7264403738739054,
7.63,
3.16,
3.1600000000000006,
3.160000000000001,
2.06,
1.9728802107932477,
1.7180599494369857,
1.747203022782844,
2.39,
2.06,
2.06]
        
			values = values.sort().slice(1, 10);
			console.log(values)
			var data = d3.layout.histogram()
			  .bins(x.ticks(20))
			  (values);
			console.log(data)
		  
			// Reset y domain using new data
			var yMax = d3.max(data, function(d){return d.length});
			var yMin = d3.min(data, function(d){return d.length});
			y.domain([0, yMax]);
			var colorScale = d3.scale.linear()
						.domain([yMin, yMax])
						.range([d3.rgb(color).brighter(), d3.rgb(color).darker()]);
		  
			var bar = svg.selectAll(".bar").data(data);
		  
			// Remove object with data
			bar.exit().remove();
		  
			bar.transition()
			  .duration(1000)
			  .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });
		  
			bar.select("rect")
				.transition()
				.duration(1000)
				.attr("height", function(d) { return height - y(d.y); })
				.attr("fill", function(d) { return colorScale(d.y) });
		  
			bar.select("text")
				.transition()
				.duration(1000)
				.text(function(d) { return formatCount(d.y); });
			
		} else {*/
			
			bar.append("rect")
				.attr("x", 1)
				.attr("width", (x(data[0].dx) - x(0)) - 1)
				.attr("height", function(d) { return height - y(d.y); })
				.attr("fill", function(d) { return colorScale(d.y) });
			
			bar.append("text")
				.attr("dy", ".75em")
				.attr("y", -12)
				.attr("x", (x(data[0].dx) - x(0)) / 2)
				.attr("text-anchor", "middle")
				.text(function(d) { return formatCount(d.y); });
			
			svg.append("g")
				.attr("class", "x axis")
				.attr("transform", "translate(0," + height + ")")
				.call(xAxis);
				
		//}
		
		
		/*
		* Adding refresh method to reload new data
		*/
		function refreshHistogram(result){
			// var values = d3.range(1000).map(d3.random.normal(20, 5));
			values = prepareHistogramData(result);
			values = values.sort().slice(1,200);
			//console.log(values)
			
			var max = d3.max(values);
			var min = d3.min(values);
			var x = d3.scale.linear()
				  .domain([min, max])
				  .range([0, width]);
			console.log(min, max, width)
			
			var xAxis = d3.svg.axis()
				.scale(x)
				.orient("top");
				  
			var data = d3.layout.histogram()
			  .bins(x.ticks(5))
			  (values);
		  
			// Reset y domain using new data
			var yMax = d3.max(data, function(d){return d.length});
			var yMin = d3.min(data, function(d){return d.length});
			y.domain([0, yMax]);
			var colorScale = d3.scale.linear()
						.domain([yMin, yMax])
						.range([d3.rgb(color).brighter(), d3.rgb(color).darker()]);
		  
			var bar = svg.selectAll(".bar").data(data);
		  
			// Remove object with data
			bar.exit().remove();
		  
			bar.transition()
			  .duration(1000)
			  .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });
		  
			bar.select("rect")
				.transition()
				.duration(1000)
				.attr("height", function(d) { return height - y(d.y); })
				.attr("fill", function(d) { return colorScale(d.y) });
		  
			bar.select("text")
				.transition()
				.duration(1000)
				.text(function(d) { return formatCount(d.y); });
		  
		  }
		
		
		function loadchart(target, data) {
            var svg = dimple.newSvg(target, "100%", "60%");
			var Chart1 = new dimple.chart(svg, data);
			//Chart1.setBounds(75, 30, 480, 330)
			Chart1.addMeasureAxis("y", "awards[0].value.amount");
			var x = Chart1.addCategoryAxis("x", "awards[0].title");
			//y.addOrderRule("Date");
			Chart1.addSeries(null, dimple.plot.bar);
			//Chart1.draw();
			return Chart1;
			
			// Add a method to draw the chart on resize of the window
			window.onresize = function () {
				// As of 1.1.0 the second parameter here allows you to draw
				// without reprocessing data.  This saves a lot on performance
				// when you know the data won't have changed.
				//Chart1.draw(0, true);
				//Chart2.draw(0, true);
				//Chart3.draw(0, true);
			};
        }
		
		//loadchart('.left-top', result);
		//loadchart('.right-top', result)
		//loadchart('.left-top', result)
		
		/*
		var svg = dimple.newSvg(".right-top", "100%", "60%");
		var Chart2 = new dimple.chart(svg, result);
		//Chart2.setBounds(75, 30, 480, 330)
		Chart2.addMeasureAxis("y", "planning.budget.amount.amount");
		var x = Chart2.addCategoryAxis("x", "awards[0].documents[0].title");
		//y.addOrderRule("Date");
		Chart2.addSeries(null, dimple.plot.bar);
		Chart2.draw();
		
		
		var svg = dimple.newSvg(".left-top", "100%", "100%");
		// Set up a standard chart
		//var myChart;
		var Chart3 = new dimple.chart(svg, result);
		// Fix the margins
		//myChart.setMargins("60px", "30px", "110px", "70px");
		// Continue to set up a standard chart
		Chart3.addMeasureAxis("y", "awards[0].value.amount");
		var x = Chart3.addCategoryAxis("x", "awards[0].documents[0].title");
		Chart3.addSeries(null, dimple.plot.bar);
		// Set the legend using negative values to set the co-ordinate from the right
		//Chart3.addLegend("-100px", "30px", "100px", "-70px");
		Chart3.draw();
		*/
		
	</script>
</html>
