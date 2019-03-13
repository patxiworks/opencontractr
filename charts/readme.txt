=== Blazing Charts ===
Contributors: massoudshakeri
Tags: charts, graph, maps, highcharts, morris.js, zingchart, chart.js, google charts, d3.js, chartist.js, smoothie charts, flot, javascript charts, javascript maps
Requires at least: 3.0.1
Tested up to: 4.9.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to easily allow you to add interactive charts and maps by using a collection of Charting libraries.  

== Description ==
We are not going to re-invent the wheel, and this plugin is not going to give you another charting library. This purpose of this plugin is to let you get the most out of charting libraries that already exist and are awesome. There are many of them out there, and most of them are free. Many of the other commercial libraries are free for a personal or non-profit project, or have a free branded version, which may have a small link to their website.

Here is a list of libraries currently added to the collection. If you know another charting library, compatible with GPL license, please inform us to add it.

*   HighCharts.js: The library used (disabled by default) is a free version with a small link to their website, and can be used if the user's site opts in to link. This plugin does NOT substitute HighCharts terms of use. HighCharts use is free for a personal or non-profit project under the Creative Commons Attribution-NonCommercial 3.0 License.<br>Please refer to HighCharts license page http://shop.highsoft.com/highcharts.html to check the HighCharts precise license conditions.
*   Morris.js
*   ZingChart: The library used (disabled by default) is a free branded version, and can be used if the user's site opts in to link. This plugin does NOT substitute ZingChart terms of use.<br>Please refer to ZingChart license page https://www.zingchart.com/buy/details/branded-license/ to check the ZingChart precise license conditions.
*   Chart.js
*   Google Charts: by loading Google's JSAPI library loader remotely (as a service), and then using it to load the visualization library and other packages
*   D3.js
*   Chartist.js
*   Smoothie Charts
*   Flot Charts

Please refer to the license page of a library you want to use to check the precise license conditions.

= What does this plugin do? =
This plugin allows you to create chart code snippets, which can be inserted into the posts or pages by using the shortcode of "BlazingChart".

Another scenario is you have a function, defined in another plugin or in your theme, which extracts data from database and produces the scripts for the chart. Then you can give the name of that function as a parameter to the shortcode, so the function is called and the output of that function will be inserted in the page.
The "BlazingChart" shortcode can have up to 4 parameters:

1. "charttype":
The first parameter of that shortcode tells which Charting library you want to use. At the moment the value of this parameter can be one of these:

*   highcharts: for HighCharts.js
*   morris: for Morris.js
*   zingchart: for ZingChart
*   chartjs: for Chart.js
*   google: for Google Charts
*   d3: for D3.js
*   chartist: for Chartist.js
*   smoothie: Smoothie Charts
*   flot: for Flot library

Other than Google Charts which does not have a local version of library, all the other libraries are included in this plugin. Some of them are hosted on a CDN as well. So by changing the settings, you can easily switch between remote or local chart/map libraries.

2. "source":
The second parameter specifies the user-friendly slug of the chart snippet as the source of script and data for the chart.

3. "function":
If a function, defined in another plugin or in the theme, is going to produce the data and script for the chart, the name of that function can be given as the value of this parameter. This value can be not only  a function name, but also other types of callbacks, like "Foo::method", "array('Foo', 'method')", or "array($obj, 'method')".

4. "options":
Some of the charting libraries are just a single file. But others, like Flot, are divided to a main library and several auxiliary libraries, to make it lighter and more efficient. By using this parameter, you can give a comma separated list of auxiliary libraries of the main library to load. Here is the list of options for each library:

*   HighCharts.js: more, 3d, exporting
*   D3.js : pie, nvd3
*   Chart.js : bundle
*   Flot Charts : colorhelpers, canvas, categories, crosshair, errorbars, fillbetween, image, navigate, pie, resize, selection, stack, symbol, threshold, time


= How Charting libraries are used? =
To include JavaScript files efficiently, none of these libraries are enqueued, until the shortcode of "BlazingChart" appears in a post or a widget. The plugin loads only one of these libraries, specified by "charttype" parameter. Moreover, to load the page faster, that library is loaded in the footer.

You can also use a built-in PHP function to invoke the chart anywhere in your template:

&lt;?php<br>
// define the parameters<br>
$patts= array(<br>
	"charttype" => "name of one of the aforementioned charting libraries",<br>
	"source"    => "slug of the chart snippet",<br>
	"options"   => "auxiliary libraries to load"<br>
	);<br>
// call the function to invoke the shortcode handler<br>
blazing_charts_insert($patts);<br>
?&gt;

As always, there are exceptions:

To reduce the size of the libraries loaded for Google Charts, that library decides which portions of the library to be included, depending to the type of the chart. So the link to the CDN library must be included at the top of the script, saved in the chart snippet.

= How To use this plugin: =

1. Although theoretically you can have various charts in a single page, drawn by more than one charting library, it is advisable not to do that. These libraries may conflict with each other, and may not get any of the charts drawn properly. Just make sure in every page, only one of those libraries is included.

2. Every chart has two major parts: first a container, specified by a &lt;div&gt; or &lt;canvas&gt; tag, with a certain id or class; and second a script which tells how that chart should be drawn.

3. If you have more than one chart in a single page, make sure each chart, saved as a Chart Snippet or produced by a function, has a unique id or class for its container. Otherwise there will be a conflict between those charts.

4. Shortcodes are case-sensitive. So if you want to use the shortcode of [BlazingChart], please make sure to type it correctly.

= Examples =
I tried to bring some examples about each of the libraries, in this plugin's URI:

http://blazingspider.com/wp-demo

= Documentation =
The detailed and updated version of documentation can be found in this link:

http://blazingspider.com/plugins/blazing-charts

== Installation ==

1. If you choose to install in manually, make sure all the files from the downloaded archive to be uploaded to the '/wp-content/plugins/blazing-charts/' directory.

2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==
1. Where to find plugins' Settings page.
2. Chart Snippets Post Type, which conatain HTML, javascripts, and styles for each chart or map.
3. A D3.js Example, drawn using this plugin.
4. A Line chart drawn using Flot Charts library

== Frequently Asked Questions ==
= What if I want to use a different version of those libraries locally? =
In that case, please upload it to the related foler of "js" folder of this plugin, and keep the same naming convention for the library.

== Changelog ==

= 1.0.6 =
Removed all the commented lines which references to local highcharts libraries, to prevent Wordpress plugin alarms triggered

= 1.0.5 =
Changed Flot CDN to Cloudflare, the original CDN is not there any more. Changed HighCharts CDN url to protocol-relative.

= 1.0.4 =
* Updated Readme.txt and Settings page

= 1.0.3 =
* Updated chart.js charting library to version 2.2.2, and added a CDN source for it.
* Also added "bundle" as an option for Chart.js, so it can be used like this:
*   [BlazingChart charttype="chartjs" options="bundle" ...]


= 1.0.2 =
* Added NVD3.js as an option for D3.js, so it can be used like this:
   [BlazingChart charttype="d3" options="nvd3" ...]

= 1.0.1 =
* Updated Chartist.js local js & css libraries

= 1.0 =
* First Release.

== Upgrade Notice ==

= 1.0 =
* First Release.

= 1.0.3 =
Updated chart.js charting library to the latest version. Be prepared to rewrite all your scripts using that library.

= 1.0.4 =
Updated Settings page to highlight that HighCharts and ZingChart are not enabled by default.

= 1.0.5 =
Changed Flot CDN to Cloudflare. Changed HighCharts CDN url to protocol-relative.

= 1.0.6 =
Removed all the commented lines which references to local highcharts libraries, to prevent Wordpress plugin alarms triggered
