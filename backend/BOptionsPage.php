<?php
if ( ! defined( 'ABSPATH' ) ) 
	exit;

if( ! class_exists( 'BOptionsPage' ) )
{
    /**
     * @class       BOptionsPage
     * @package     b-Locator/Backend
     * @version     1.0.0 Create Options page and their field
     * @author      Bryan Sebastian <bryanrsebastian@gmail.com>
     */
    class BOptionsPage
    {
        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'addMenuPage' ) );
            add_action( 'admin_init', array( $this, 'optionDescription' ) );
            $this->_includeBackendExtension();
        }

        /**
         * Include files
         * @return void include some files to generate fields in this option page
         */
        private function _includeBackendExtension()
        {
            include_once 'BOptionsPageFields.php';
        }

        /**
    	 * Add menu and options page
        * @return void
    	 */
    	public function addMenuPage()
    	{
    		add_menu_page( 
    			'b-Locator Settings',
    			'b-Locator',
    			'manage_options',
    			'b-locator-settings',
    			array( $this, 'createBOptionsPage' ),
    			'dashicons-location',
    			100
    		);
    	}

        /**
         * Options page callback
         * @return  void include the form
         */
        public function createBOptionsPage()
        {
            include_once 'partials/form.php';
        }

        /**
         * Add Option Description Section
         * @return void create option description section
         */
        public function optionDescription()
        {
            add_settings_section(
                'setting_section_id', // ID
                'b-Locator', // Title
                array( $this, 'optionCallback' ), // Callback
                'b-locator-settings' // Page
            );

            add_settings_section(
                'b_locator_setting_id', // ID
                'Location & Map Settings', // Title
                array( $this, 'locationMapDescriptionCallback' ), // Callback
                'b-locator-location-map-settings' // Page
            );
        }

        /**
         * Call back for setting_section_general_id field
         * @return void generate information
         */
        public function optionCallback()
        {
            include_once 'partials/instruction.php';
        }

        /**
         * Call back for setting_section_location_map_id field
         * @return string generate information
         */
        public function locationMapDescriptionCallback()
        {
            print 'Enter your location and map settings below:';
        }
    }

    new BOptionsPage();
}