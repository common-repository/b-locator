<?php
/*
Plugin Name: b-Locator
Plugin URI:  https://wordpress.org/plugins/b-locator
Description: Generate Custom Locator ( Store Locator, Distributor Locator, etc. ).
Version:     1.0.1
Author:      Bryan Sebastian
Author URI:  https://bryan-sebastian.github.io/
License:     GPLv2 or later

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
*/
if ( ! defined( 'ABSPATH' ) ) 
	exit;

if( ! class_exists( 'BLocator' ) )
{
	/**
	 * @class       BLocator
	 * @package     BLocator
	 * @category    Plugin
	 * @version     1.0.0 Initialize B-Locator Plugin
	 * @author      Bryan Sebastian <bryanrsebastian@gmail.com>
	 */
	class BLocator
	{
		public function __construct()
		{
			$this->_includeFiles();
		}

		/**
		 * Include Files
		 * @return void
		 */
		private function _includeFiles()
		{
			include_once 'backend/BBackend.php';
			include_once 'frontend/BFrontend.php';
		}

		/**
		 * set some initial value in witty map option page
		 */
		public static function install() {
			add_option( 'b_locator', array(
				'google_map_marker' => '',
				'google_map_theme' => '',
				'map_api_key' => '',
				'map_center_longitude' => 14.617178,
				'map_center_latitude' => 120.974644,
				'map_height' => 200,
				'map_zoom_level' => 4,
				'status' => 0,
			) );
		}
	}

	new BLocator;

	register_activation_hook( __FILE__, array( 'BLocator', 'install' ) );
}