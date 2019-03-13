<?php

/**
 * Title         : Options Page settings
 * Description   : All the code related to the Settings is here 
 * Version       : 1.0.6
 * Author        : Massoud Shakeri
 * Author URI    : http://www.blazingspider.com/
 * Documentation : https://github.com/BlazingSpider/blazing-charts
 * Plugin URI    : http://blazingspider.com/plugins/blazing-charts
 * License       : GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * IMPORTANT NOTE: The above statement does NOT substitute HighCharts terms of use. HighCharts use is free for a personal or non-profit project under the Creative Commons Attribution-NonCommercial 3.0 License. Anyway please refer to HighCharts license page http://shop.highsoft.com/highcharts.html to check the HighCharts precise license conditions.  
 * @class 		 Blazing_Charts_Options_Page
*/

if ( ! class_exists( 'Blazing_Charts_Options_Page' ) ) {

class Blazing_Charts_Options_Page {
    /**
     * Holds the values to be used in the fields callbacks
     */
    var $options = array();
	var $settings_name = 'blazing_charts_settings'; // The settings string name for this plugin in options table
	var $bc_settings_ver = '1.0';

    /**
     * Start up
     */
	public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings_and_Fields' ) );
	}

    /**
     * Add options page
     */
	public function admin_menu () {
		add_options_page('Bazing Charts Settings', 'Bazing Charts Settings', 'administrator', __FILE__, array( $this, 'settings_page' ));
	}

    /**
     * Options page callback
     */
	public function  settings_page () {
        // Set class property
		if (!($bc_settings = get_option( $this->settings_name )) ) {
			$bc_settings = array(
				'morris_cdn' => '0',
				'chartjs_cdn' => '0',
				'd3_cdn' => '0',
				'chartist_cdn' => '0',
				'flot_cdn' => '0',
				'version' => $this->bc_settings_ver	// see on top class
			);
			update_option( $this->settings_name, $bc_settings);
		}
		$this->options = $bc_settings;

		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields('blazing_charts_group');
				do_settings_sections(__FILE__);
				submit_button();
			?>
			</form>
		</div>
		<?php
	}
	
    /**
     * Register and add settings
     */
	public function register_settings_and_Fields() {
		register_setting(
			'blazing_charts_group', // Option group
			$this->settings_name, // Option name
            array( $this, 'sanitize' ) // Sanitize
			); // 3rd param = optional cb
		add_settings_section(
			'blazing_main_section', //ID
			'CDN Settings', //Title of section
			array($this, 'blazing_main_section_cb'), //cb
			__FILE__ //which page?
			);
		
		add_settings_field('morris_cdn', 'Morris.js', array($this, 'morris_cdn_set'), __FILE__, 'blazing_main_section');
		add_settings_field('chartjs_cdn', 'Chart.js', array($this, 'chartjs_cdn_set'), __FILE__, 'blazing_main_section');
		add_settings_field('d3_cdn', 'D3.js', array($this, 'd3_cdn_set'), __FILE__, 'blazing_main_section');
		add_settings_field('chartist_cdn', 'Chartist.js', array($this, 'chartist_cdn_set'), __FILE__, 'blazing_main_section');
		add_settings_field('flot_cdn', 'Flot Charts', array($this, 'flot_cdn_set'), __FILE__, 'blazing_main_section');
	}
	public function blazing_main_section_cb($arg) {
		echo "<h3>Choose which one of theses Chart Libraries to be loaded from CDN (Content Delivery Network)</h3>";
	}

    /** 
     * Get the settings option array and print one of its values
     */
	public function morris_cdn_set() {
		$checked = ( isset ($this->options['morris_cdn']) && $this->options['morris_cdn']) ? "checked='checked'" : "";
		echo "<input value='1' id='morris_cdn' name='blazing_charts_settings[morris_cdn]' type='checkbox' {$checked} />";
		echo "<label for='morris_cdn'>Check if you want to get the library from CDN, Uncheck for loading local copy</label>";
	}
	public function chartjs_cdn_set() {
		$checked = ( isset ($this->options['chartjs_cdn']) && $this->options['chartjs_cdn']) ? "checked='checked'" : "";
		echo "<input value='1' id='chartjs_cdn' name='blazing_charts_settings[chartjs_cdn]' type='checkbox' {$checked} />";
		echo "<label for='chartjs_cdn'>Check if you want to get the library from CDN, Uncheck for loading local copy</label>";
	}
	public function d3_cdn_set() {
		$checked = ( isset ($this->options['d3_cdn']) && $this->options['d3_cdn']) ? "checked='checked'" : "";
		echo "<input value='1' id='d3_cdn' name='blazing_charts_settings[d3_cdn]' type='checkbox' {$checked} />";
		echo "<label for='d3_cdn'>Check if you want to get the library from CDN, Uncheck for loading local copy</label>";
	}
	public function chartist_cdn_set() {
		$checked = ( isset ($this->options['chartist_cdn']) && $this->options['chartist_cdn']) ? "checked='checked'" : "";
		echo "<input value='1' id='chartist_cdn' name='blazing_charts_settings[chartist_cdn]' type='checkbox' {$checked} />";
		echo "<label for='chartist_cdn'>Check if you want to get the library from CDN, Uncheck for loading local copy</label>";
	}
	public function flot_cdn_set() {
		$checked = ( isset ($this->options['flot_cdn']) && $this->options['flot_cdn']) ? "checked='checked'" : "";
		echo "<input value='1' id='flot_cdn' name='blazing_charts_settings[flot_cdn]' type='checkbox' {$checked} />";
		echo "<label for='flot_cdn'>Check if you want to get this library and all its auxiliary libraries from CDN, Uncheck for loading local copy</label>";
	}

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array(
			'morris_cdn' => '0',
			'chartjs_cdn' => '0',
			'd3_cdn' => '0',
			'chartist_cdn' => '0',
			'flot_cdn' => '0',
		);
        if( isset( $input['morris_cdn'] ) ) {
            $new_input['morris_cdn'] = $input['morris_cdn'];
		}
       if( isset( $input['chartjs_cdn'] ) ) {
           $new_input['chartjs_cdn'] = $input['chartjs_cdn'];
		}
        if( isset( $input['d3_cdn'] ) ) {
            $new_input['d3_cdn'] = $input['d3_cdn'];
		}
        if( isset( $input['chartist_cdn'] ) ) {
            $new_input['chartist_cdn'] = $input['chartist_cdn'];
		}
        if( isset( $input['flot_cdn'] ) ) {
            $new_input['flot_cdn'] = $input['flot_cdn'];
		}
		$new_input['version'] = $this->bc_settings_ver ; // because not in input !

        return $new_input;
    }
}
}

if( is_admin() ) {
    $my_settings_page = new Blazing_Charts_Options_Page();
}
