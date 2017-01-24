<?php

/**
 *
 * PHP version 5
 *
 * Created: 12/2/15, 10:33 AM
 *
 * LICENSE:
 *
 * @author         Jeff Behnke <code@validwebs.com>
 * @copyright  (c) 2015 ValidWebs.com
 *
 * dashboard
 * vvv-dashboard.php
 */

namespace vvv_dash;

/**
 * Class vvv_dashboard
 *
 * @author         Jeff Behnke <code@validwebs.com>
 * @copyright  (c) 2009-15 ValidWebs.com
 *
 */
class dashboard {

	private $_cache;

	private $_pages = array();
	private $_database_commands;

	public function __construct() {

		$this->_cache = new cache();
		//$this->_hosts = new host();

		$this->_set_pages();

	}

	/**
	 * Setup the dynamic pages from URI query
	 *
	 * @author         Jeff Behnke <code@validwebs.com>
	 * @copyright  (c) 2009-15 ValidWebs.com
	 *
	 * Created:    12/16/15, 5:44 PM
	 *
	 */
	private function _set_pages() {
		$this->_pages = array(
			'dashboard',
			'plugins',
			'themes',
			'backups',
			'about',
			'commands',
			'tools',
			'testing' // for testing purposes without breaking the dashboard
		);
	}

	/**
	 * Check the request and return if available.
	 *
	 * @author         Jeff Behnke <code@validwebs.com>
	 * @copyright  (c) 2009-15 ValidWebs.com
	 *
	 * Created:    12/16/15, 5:45 PM
	 *
	 * @return bool|string
	 */
	public function get_page() {

		if ( isset( $_REQUEST['page'] ) && ! empty( $_REQUEST['page'] ) ) {

			if ( in_array( $_REQUEST['page'], $this->_pages ) ) {
				return $_REQUEST['page'];
			} else {
				// Changing to 404 as it is confuzing when testing
				return '404';
			}

		} else {
			return false;
		}

	}

	/**
	 * Process $_POST supper globals used in the dashboard
	 *
	 * @author         Jeff Behnke <code@validwebs.com>
	 * @copyright  (c) 2009-15 ValidWebs.com
	 *
	 * Created:    12/8/15, 4:01 PM
	 *
	 * @return bool|string
	 */
	public function process_post() {

		$status = false;

		if ( isset( $_POST ) ) {

			//			if ( isset( $_POST['install_dev_plugins'] ) && isset( $_POST['host'] ) ) {
			//				$status = $this->_plugin_commands->install_dev_plugins( $_POST );
			//
			//			}


			if ( isset( $_POST['backup'] ) && isset( $_POST['host'] ) ) {
				$this->_database_commands = new commands\database( $_POST['host'] );
				$status                   = $this->_database_commands->create_db_backup( $_POST['host'] );
			}

			if ( isset( $_POST['roll_back'] ) && $_POST['roll_back'] == 'Roll Back' ) {
				$this->_database_commands = new commands\database( $_POST['host'] );
				$status                   = $this->_database_commands->db_roll_back( $_POST['host'], $_POST['file_path'] );

				if ( $status ) {
					$status = vvv_dash_notice( $status );
				}
			}

			if ( isset( $_POST['purge_hosts'] ) ) {
				$purge_status = $this->_cache->purge( 'host-sites' );
				$sub_sites = $this->_cache->purge( '-subsites' );
				$purge_status = $purge_status + $sub_sites;
				$status       = vvv_dash_notice( $purge_status . ' files were purged from cache!' );
			}

			if ( isset( $_POST['purge_themes'] ) ) {
				$purge_status = $this->_cache->purge( '-themes' );
				$status       = vvv_dash_notice( $purge_status . ' files were purged from cache!' );
			}

			if ( isset( $_POST['purge_plugins'] ) ) {
				$purge_status = $this->_cache->purge( '-plugins' );
				$status       = vvv_dash_notice( $purge_status . ' files were purged from cache!' );
			}

			if ( isset( $_POST['toggle_darkmode'] ) ) {
				$dark_status = $this->toggle_darkmode();
				$status       = vvv_dash_notice( 'Dark Mode is now ' . $dark_status . '.' );
				header( "location:" . VVV_WEB_ROOT );
			}

			// @ToDo move this to the correct commands/
			if ( isset( $_POST['update_item'] ) && isset( $_POST['host'] ) ) {

				if ( ! empty( $_POST['type'] ) && 'plugins' == $_POST['type'] ) {
					$plugin = new commands\plugin( $_POST['host'] );
					$status = $plugin->update();
				}

				if ( ! empty( $_POST['type'] ) && 'themes' == $_POST['type'] ) {
					$theme  = new commands\theme( $_POST['host'] );
					$status = $theme->update();
				}
			}
		}

		return $status;
	}

	/**
	 *	Enables/Disables Dark Mode for the VVV Dash
	 *
	 * @author         Tyler Kemme <tylerkemme@gmail.com>
	 *
	 * Created:    01/23/2017
	 *
	 * @return  string darkmode status
	 */
	public function toggle_darkmode() {

		$current_mode = file_get_contents( 'views/themes/theme.txt' );
		$current_mode = explode( "\n", $current_mode )[0];

		// Theme is currently set to default, change to darkmode
		if( strcmp( $current_mode, 'default' ) == 0 ){
			file_put_contents( 'views/themes/theme.txt', 'darkmode' );
			// Return Dark Mode status
			return 'enabled';
		}

		// Theme is currently set to dark Mode, change to default
		else{
			file_put_contents( 'views/themes/theme.txt', 'default' );
			// Return Dark Mode status
			return 'disabled';
		}
	}

	public function __destruct() {
		// TODO: Implement __destruct() method.
	}

	/**
	 *
	 *
	 * @author         Jeff Behnke <code@validwebs.com>
	 * @copyright  (c) 2009-15 ValidWebs.com
	 *
	 * Created:    12/8/15, 4:00 PM
	 *
	 * @param $vvv_dash
	 */
	public function process_get( $vvv_dash ) {

	}


}
// End vvv-dashboard.php
