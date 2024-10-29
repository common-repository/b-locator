<?php
if ( ! defined( 'ABSPATH' ) ) 
	exit;

if( ! class_exists( 'BFrontend' ) )
{
	/**
	 * @class 		BFrontend
	 * @package 	B-Locator/Backend
	 * @version  	1.0.0 Initialize Backend Process
	 * @author  	Bryan Sebastian <bryanrsebastian@gmail.com>
	 */
	class BFrontend
	{
		private $_options;

		public function __construct()
		{
			add_action( 'wp_enqueue_scripts', array( $this, 'includeAssets' ) );
			$this->_options = get_option( 'b_locator' );
			$this->shortcodes();

			add_action( 'wp_ajax_nopriv_b_location_action', array( $this, 'bLocationAction' ) );
			add_action( 'wp_ajax_b_location_action', array( $this, 'bLocationAction' ) );
		}

		/**
		 * Include CSS and JS
		 * @return void
		 */
		public function includeAssets()
		{
			wp_enqueue_style( 'frontend-style', plugin_dir_url( __FILE__ ) .'/css/frontend.css');

			/* Get the B-Locator settings */
			$map_api_key = ( isset( $this->_options['map_api_key'] ) && $this->_options['map_api_key'] != '' ) ? $this->_options['map_api_key'] : '';

			$map_center_longitude = ( isset( $this->_options['map_center_longitude'] ) && $this->_options['map_center_longitude'] != '' ) ? $this->_options['map_center_longitude'] : '14.617178';
			$map_center_latitude = ( isset( $this->_options['map_center_latitude'] ) && $this->_options['map_center_latitude'] != '' ) ? $this->_options['map_center_latitude'] : '120.974644';
			$map_zoom_level = ( isset( $this->_options['map_zoom_level'] ) && $this->_options['map_zoom_level'] != '' ) ? $this->_options['map_zoom_level'] : '4';
			$google_map_theme = ( isset( $this->_options['google_map_theme'] ) && $this->_options['google_map_theme'] != '' ) ? $this->_options['google_map_theme'] : '';
			$google_map_marker = ( isset( $this->_options['google_map_marker'] ) && $this->_options['google_map_marker'] != '' ) ? $this->_options['google_map_marker'] : '';

			wp_register_script( 'map', '//maps.googleapis.com/maps/api/js?key='.$map_api_key, array( 'jquery' ), NULL, true );
		    wp_enqueue_script( 'map' );

			wp_register_script( 'frontend-js', plugin_dir_url( __FILE__ ) .'/js/frontend.js', array('jquery'), NULL, true );
			wp_enqueue_script( 'frontend-js' );

			wp_localize_script( 'frontend-js', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
			wp_localize_script( 'frontend-js', 'center_long', $map_center_longitude );
			wp_localize_script( 'frontend-js', 'center_lat', $map_center_latitude );
			wp_localize_script( 'frontend-js', 'zoom_level', $map_zoom_level );
			wp_localize_script( 'frontend-js', 'google_map_theme', $google_map_theme );
			wp_localize_script( 'frontend-js', 'google_map_marker', $google_map_marker );

			$map_settings_data = array(
				'google_map_doubleclickzoom',
				'google_map_draggable',
				'google_map_scrollwheel',
				'google_map_streetview',
				'google_map_zoomcontrol'
			);
			foreach ($map_settings_data as $key => $value) {
				$value_data = ( isset( $this->_options[ $value ] ) ) ? $this->_options[ $value ] : '';
				wp_localize_script( 'frontend-js', $value, $value_data );
			}
		}

		/**
		 * Add shortcodes and their functionalities
		 * @return void
		 */
		public function shortcodes()
		{
			$shortcodes = [
				'b-locator' 		=> [ $this, 'bLocator' ],
				'b-locator-map' 	=> [ $this, 'bMap' ]
			];
			foreach ($shortcodes as $shortcode => $func):
				add_shortcode( $shortcode, $func );
			endforeach;
		}

		/**
		 * Shortcode function of b-locator
		 * @return void generate list of location and their categories and search bar if its on
		 */
		public function bLocator()
		{
			/* Get the location */
			$args = array(
				'post_type' 		=> 'b-locator',
				'posts_per_page' 	=> -1
			);
			$locations = get_posts( $args );

			/* Get the B-Locator settings */
			$categories = ( isset( $this->_options['location_categories'] ) ) ? $this->_options['location_categories'] : false;
			$search = ( isset( $this->_options['location_search'] ) ) ? $this->_options['location_search'] : false;

			/* Get the location categories */
			if ( $categories ) {
				$args = array(
					'taxonomy' => 'location-categories',
				);
				$categories = get_terms( $args );
			}
			include_once 'partials/locationList.php';
		}

		/**
		 * Shortcode function of b-locator-map
		 * @return void show the google map
		 */
		public function bMap()
		{
			$map_height = ( isset( $this->_options['map_height'] ) && $this->_options['map_height'] != '' ) ? $this->_options['map_height'] : '200';
			
			include_once 'partials/map.php';
		}

		/**
		 * Get all the location filtered with selected category via ajax
		 * @return json location data
		 */
		function bLocationAction() 
		{
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				header('Content-Type: application/json');

				$data = array();

				/* Filter the locations */
				$args = array(
					'post_type' 		=> 'b-locator',
					'posts_per_page' 	=> -1
				);

				if ( ! is_null( $_POST['category'] ) ) {
					$args['tax_query'] = array(
						array(
							'taxonomy' => 'location-categories',
							'field'    => 'slug',
							'terms'    => $_POST['category'],
						),
					);
				}
				$locations = get_posts( $args );

				/* Get the only data that need in map */
				if ( ! empty( $locations ) ) {
					foreach ( $locations as $location ) {
						$data['filtered_locations'][] = array(
							'location_id' 	    => $location->ID,
							'location_name' 	=> $location->post_title,
							'location_slug' 	=> $location->post_name,
							'location_long'  	=> get_post_meta( $location->ID, '_location_long', true ),
							'location_lat'  	=> get_post_meta( $location->ID, '_location_lat', true ),
							'location_details'  => get_post_meta( $location->ID, '_location_details', true )
						);
					}
				} else {
					$data['filtered_locations'] = false;
				}

				$data['map_settings'] = get_option( 'b_locator' );

				echo json_encode( $data );
		 	}
		 	die();
		}
	}

	new BFrontend;
}