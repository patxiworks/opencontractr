<?php
class OpenContractrSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
	private $general_options;
    private $publisher_options;
	private $organisation_options;
	private $field_options;
	private $organisation_props = Array(
				array("organisation.name","Organisation Name"),
				array("organisation.identifier.scheme","Scheme","text"),
				array("organisation.identifier.id","ID","text"),
				array("organisation.identifier.legalName","Legal Name","text"),
				array("organisation.identifier.uri","URI","text"),
				array("organisation.address.streetAddress","Street Address","text"),
				array("organisation.address.locality","Locality","text"),
				array("organisation.address.region","Region","text"),
				array("organisation.address.postalCode","Postal Code","text"),
				array("organisation.address.countryName","Country Name","text"),
				array("organisation.contactPoint.name","Name of Contact","text"),
				array("organisation.contactPoint.email","Email of Contact","text"),
				array("organisation.contactPoint.telephone","Telephone of Contact","text"),
				array("organisation.contactPoint.faxNumber","Contact Fax Number","text"),
				array("organisation.contactPoint.url","Contact Url","url")
			);
	private $fields = array(
				array("tender-id"),
				array("tender-description"),
				array("tender-procuringEntity-name")
			);
	private $new_fields;
	private $field_props = Array(
				array("mandatory","Mandatory?","checkbox"),
				array("label","Label","text"),
				array("description","Description","text")
			);

    /**
     * Start up
     */
    public function __construct()
    {
		//$this->new_fields = json_decode( get_option('_opencontractr_fields'), TRUE );
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
			'OpenContractr Settings',
			'OpenContractr',
			'manage_options',
			'opencontractr_settings',
			array( $this, 'create_admin_page' )
		);
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
		$this->general_options = get_option( 'general_options' );
        $this->publisher_options = get_option( 'publisher_options' );
		$this->organisation_options = get_option( 'organisation_options' );
		$this->field_options = get_option( 'field_options' );
		
        ?>
        <div class="wrap">
            <h1>OpenContractr Settings</h1>
			
			<?php
			// set default active tab
			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general_options';
			// set active tab when clicked
			if( isset( $_GET[ 'tab' ] ) ) {
				$active_tab = $_GET[ 'tab' ];
			}
			?>
			
			<h2 class="nav-tab-wrapper">
				<a href="?page=opencontractr_settings&tab=general_options" class="nav-tab <?php echo $active_tab == 'general_options' ? 'nav-tab-active' : ''; ?>">General Settings</a>
				<a href="?page=opencontractr_settings&tab=publisher_options" class="nav-tab <?php echo $active_tab == 'publisher_options' ? 'nav-tab-active' : ''; ?>">Publisher Settings</a>
				<a href="?page=opencontractr_settings&tab=organisation_options" class="nav-tab <?php echo $active_tab == 'organisation_options' ? 'nav-tab-active' : ''; ?>">Default Organisation Settings</a>
				<!--<a href="?page=opencontractr_settings&tab=field_options" class="nav-tab <?php echo $active_tab == 'field_options' ? 'nav-tab-active' : ''; ?>">Default Field Settings</a>-->
			</h2>
			
            <form method="post" action="options.php">
            <?php
				if ( $active_tab == 'general_options' ) {
					settings_fields( 'general_option_group' );
					do_settings_sections( 'opencontractr_general_options' );
				} elseif ( $active_tab == 'publisher_options' ) {
					settings_fields( 'publisher_option_group' );
					do_settings_sections( 'opencontractr_publisher_options' );
				} elseif ( $active_tab == 'organisation_options' ) {
					settings_fields( 'organisation_option_group' );
					do_settings_sections( 'opencontractr_organisation_options' );
				}
				
				for ($i=0; $i<count($this->fields); $i++) {
					if ( $active_tab == $this->fields[$i][0].'_field_options' ) {
						settings_fields( $this->fields[$i][0].'_field_option_group' );
						do_settings_sections( 'opencontractr_field_options_'.$this->fields[$i][0] );
					}
				}
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
		// If the options don't exist, create them.
		if( false == get_option( 'opencontractr_general_options' ) ) {   
			add_option( 'opencontractr_general_options' );
		}
		if( false == get_option( 'opencontractr_publisher_options' ) ) {   
			add_option( 'opencontractr_publisher_options' );
		}
		if( false == get_option( 'opencontractr_organisation_options' ) ) {   
			add_option( 'opencontractr_organisation_options' );
		}
		for ($i=0; $i<count($this->fields); $i++) {
			if( false == get_option( 'opencontractr_field_options_'.$this->fields[$i][0] ) ) {   
				add_option( 'opencontractr_field_options_'.$this->fields[$i][0] );
			} // end if
		}
		
		// GENERAL
		register_setting(
            'general_option_group', // Option group
            'general_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'general_section', // ID
            'General Settings', // Title
            function(){ print 'Enter general settings for OpenContractr below'; }, // Callback
            'opencontractr_general_options' // Page
        );
		
		add_settings_field(
            'ocds_prefix', 
            'OCDS Prefix', 
            array( $this, 'general_ocds_prefix_callback' ), 
            'opencontractr_general_options', 
            'general_section'
        );
		
		// PUBLISHER
        register_setting(
            'publisher_option_group', // Option group
            'publisher_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'publisher_section', // ID
            'Publisher Settings', // Title
            array( $this, 'print_publisher_info' ), // Callback
            'opencontractr_publisher_options' // Page
        );
		
		add_settings_field(
            'publisher_name', 
            'Publisher Name', 
            array( $this, 'publisher_name_callback' ), 
            'opencontractr_publisher_options', 
            'publisher_section'
        );

        add_settings_field(
            'publisher_scheme', // ID
            'Publisher Scheme', // Title 
            array( $this, 'publisher_scheme_callback' ), // Callback
            'opencontractr_publisher_options', // Page
            'publisher_section' // Section           
        );
		
		add_settings_field(
            'publisher_uid', // ID
            'Publisher UID', // Title 
            array( $this, 'publisher_uid_callback' ), // Callback
            'opencontractr_publisher_options', // Page
            'publisher_section' // Section           
        );
		
		add_settings_field(
            'publisher_uri', // ID
            'Publisher URI', // Title 
            array( $this, 'publisher_uri_callback' ), // Callback
            'opencontractr_publisher_options', // Page
            'publisher_section' // Section           
        );
		
		// ORGANISATION
		register_setting(
            'organisation_option_group', // Option group
            'organisation_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'organisation_section', // ID
            'Default Organisation Settings', // Title
            function(){ print 'Fill default organisation settings below. This will serve as the default information for the buyer of every contract.'; }, // Callback
            'opencontractr_organisation_options' // Page
        );
		
		for ($i=0; $i<count($this->organisation_props); $i++) {
			add_settings_field(
				$this->organisation_props[$i][0], // ID
				$this->organisation_props[$i][1], // Title
				array($this, 'organisation_callback'), // Callback
				'opencontractr_organisation_options', // Page
				'organisation_section', // Section
				array('organisation_info' => $this->organisation_props[$i][0])
			);
		}
		
		// FIELDS
		for ($j=0; $j<count($this->fields); $j++) {
			register_setting(
				$this->fields[$j][0].'_field_option_group', // Option group
				'field_options', // Option name
				array( $this, 'sanitize_fields' ) // Sanitize
			);
	
			add_settings_section(
				$this->fields[$j][0].'_field_section', // ID
				'Default Field Settings for '.$this->fields[$j][0], // Title
				function(){ print 'Fill default field settings below.'; }, // Callback
				'opencontractr_field_options_'.$this->fields[$j][0] // Page
			);
			
			for ($i=0; $i<count($this->field_props); $i++) {
				add_settings_field(
					$this->fields[$j][0] . '_' . $this->field_props[$i][0], // ID
					$this->field_props[$i][1], // Title
					array($this, 'field_'.$this->field_props[$i][0].'_callback'), // Callback
					'opencontractr_field_options_'.$this->fields[$j][0], // Page
					$this->fields[$j][0].'_field_section', // Section
					array('field' => $this->fields[$j])
				);
			}
		}
      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
		if( isset( $input['ocds_prefix'] ) )
            $new_input['ocds_prefix'] = sanitize_text_field( $input['ocds_prefix'] );
			
		if( isset( $input['publisher_name'] ) )
            $new_input['publisher_name'] = sanitize_text_field( $input['publisher_name'] );
			
        if( isset( $input['publisher_scheme'] ) )
            $new_input['publisher_scheme'] = sanitize_text_field( $input['publisher_scheme'] ); //absint( $input['publisher_scheme'] );
			
		if( isset( $input['publisher_uid'] ) )
            $new_input['publisher_uid'] = sanitize_text_field( $input['publisher_uid'] );
			
		if( isset( $input['publisher_uri'] ) )
            $new_input['publisher_uri'] = esc_url( $input['publisher_uri'] );
			
		for ($i=0; $i<count($this->organisation_props); $i++) {
			$organisation_prop = $this->organisation_props[$i][0];
			if( isset( $input[$organisation_prop] ) ) {
				switch ($this->organisation_props[$i][2]) {
					case 'text':
						$new_input[$organisation_prop] = sanitize_text_field( $input[$organisation_prop] );
					break;
					case 'url':
						$new_input[$organisation_prop] = esc_url( $input[$organisation_prop] );
					break;
					default:
						$new_input[$organisation_prop] = sanitize_text_field( $input[$organisation_prop] );
				}
			}		
		}

		//print_r($options);

        return $new_input;
    }
	
	
	/**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize_fields( $input ) {
		
		$options = get_option('field_options');
		for ($i=0; $i<count($this->fields); $i++) {
			for ($j=0; $j<count($this->field_props); $j++) {
				$fieldname = $this->fields[$i][0].'_'.$this->field_props[$j][0];
				if( isset( $input[$fieldname] ) ) {
					switch ($this->field_props[$j][2]) {
						case 'text':
							$options[$fieldname] = sanitize_text_field( $input[$fieldname] );
						break;
						default:
							$options[$fieldname] = $input[$fieldname];
					}
				} else {
					if ( $this->field_props[$j][2] == 'checkbox' )
						unset($options[$fieldname]);
				}
				$new_input = $options;
			}
		}
		return $new_input;
	
	}

    /** 
     * Print the Section text
     */
    public function print_publisher_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * GENERAL
     * Get the settings option array and print one of its values
     */
	public function general_ocds_prefix_callback()
    {
        printf(
            '<input type="text" id="ocds_prefix" name="general_options[ocds_prefix]" class="regular-text" value="%s" />',
            isset( $this->general_options['ocds_prefix'] ) ? esc_attr( $this->general_options['ocds_prefix']) : ''
        );
    }
	
	/** 
     * PUBLISHER
     * Get the settings option array and print one of its values
     */
    public function publisher_name_callback()
    {
        printf(
            '<input type="text" id="publisher_name" name="publisher_options[publisher_name]" class="regular-text" value="%s" />',
            isset( $this->publisher_options['publisher_name'] ) ? esc_attr( $this->publisher_options['publisher_name']) : ''
        );
    }

    public function publisher_scheme_callback()
    {
        printf(
            '<input type="text" id="publisher_scheme" name="publisher_options[publisher_scheme]" class="regular-text" value="%s" />',
            isset( $this->publisher_options['publisher_scheme'] ) ? esc_attr( $this->publisher_options['publisher_scheme']) : ''
        );
    }
	
    public function publisher_uid_callback()
    {
        printf(
            '<input type="text" id="publisher_uid" name="publisher_options[publisher_uid]" value="%s" />',
            isset( $this->publisher_options['publisher_uid'] ) ? esc_attr( $this->publisher_options['publisher_uid']) : ''
        );
    }
	
    public function publisher_uri_callback()
    {
        printf(
            '<input type="text" id="publisher_uri" name="publisher_options[publisher_uri]" class="regular-text" value="%s" />',
            isset( $this->publisher_options['publisher_uri'] ) ? esc_attr( $this->publisher_options['publisher_uri']) : ''
        );
    }
	
	/** 
     * ORGANISATION
     * Get the settings option array and print one of its values
     */
	public function organisation_callback($organisation) {
		printf(
			'<input type="text" id="'.$organisation['organisation_info'].'" name="organisation_options['.$organisation['organisation_info'].']" class="regular-text" value="%s" />',
			isset( $this->organisation_options[$organisation['organisation_info']] ) ? esc_attr( $this->organisation_options[$organisation['organisation_info']]) : ''
		);
	}
	
	
	/** 
     * FIELDS
     * Get the settings option array and print one of its values
     *
	public function field_callback($field) {
		printf(
			//'<input type="checkbox" id="'.$field['field_info'].'">'.
			'<input type="text" id="'.$field['field_info'].'" name="field_options['.$field['field_info'].']" class="regular-text" value="%s" />',
			isset( $this->field_options[$field['field_info']] ) ? esc_attr( $this->field_options[$field['field_info']]) : ''
		);
	}
	*/
	
	public function field_mandatory_callback($field)
    {
		$fieldname = $field['field'][0].'_mandatory';
		$options = get_option( 'field_options' );
		printf(
			//print_r( $field['field'][0] ).
			'<input type="checkbox" id="'.$fieldname.'" name="field_options['.$fieldname.']" value="1" '.checked( $options[$fieldname], 1, false ).' />',
			isset( $this->field_options[$fieldname] ) ? esc_attr( $this->field_options[$fieldname] ) : ''
		);
    }

    public function field_label_callback($field)
    {
		$fieldname = $field['field'][0].'_label';
        printf(
            '<input type="text" id="'.$fieldname.'" name="field_options['.$fieldname.']" class="regular-text" value="%s" />',
			isset( $this->field_options[$fieldname] ) ? esc_attr( $this->field_options[$fieldname] ) : ''
        );
    }
	
    public function field_description_callback($field)
    {
		$fieldname = $field['field'][0].'_description';
        printf(
            '<textarea type="text" id="'.$fieldname.'" name="field_options['.$fieldname.']" >%s</textarea>',
            isset( $this->field_options[$fieldname] ) ? esc_attr( $this->field_options[$fieldname] ) : ''
        );
    }
	
}

if( is_admin() )
    $my_settings_page = new OpenContractrSettings();
	
	
?>