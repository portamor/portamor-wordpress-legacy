<?php
/**
 * Plugin Name: Hotjar
 * Description: The fast & visual way to understand your users.
 * Author: Hotjar
 * Author URI: https://www.hotjar.com/
 * Version: 1.0.14
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain: hotjar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'plugins_loaded', 'hotjar_plugin_init' );

function hotjar_plugin_init() {

	if ( ! class_exists( 'WP_Hotjar' ) ) :

		class WP_Hotjar {
			/**
			 * @var Const Plugin Version Number
			 */
			const VERSION = '1.0.14';

			/**
			 * @var Singleton The reference the *Singleton* instance of this class
			 */
			private static $instance;

			/**
			 * Returns the *Singleton* instance of this class.
			 *
			 * @return Singleton The *Singleton* instance.
			 */
			public static function get_instance() {
				if ( null === self::$instance ) {
					self::$instance = new self();
				}
				return self::$instance;
			}

			private function __clone() {}

			public function __wakeup() {}

			/**
			 * Protected constructor to prevent creating a new instance of the
			 * *Singleton* via the `new` operator from outside of this class.
			 */
			private function __construct() {
				add_action( 'admin_init', array( $this, 'install' ) );
				$this->init();
			}

			/**
			 * Init the plugin after plugins_loaded so environment variables are set.
			 *
			 * @since 1.0.0
			 */
			public function init() {
				require_once( dirname( __FILE__ ) . '/includes/class-hotjar.php' );
				$hotjar = new Hotjar();
				$hotjar->init();
			}

			/**
			 * Updates the plugin version in db
			 *
			 * @since 1.0.0
			 */
			public function update_plugin_version() {
				delete_option( 'hotjar_version' );
				update_option( 'hotjar_version', self::VERSION );
			}

			/**
			 * Handles upgrade routines.
			 *
			 * @since 1.0.0
			 */
			public function install() {
				if ( ! is_plugin_active( plugin_basename( __FILE__ ) ) ) {
					return;
				}

				if ( ( self::VERSION !== get_option( 'hotjar_version' ) ) ) {

					$this->update_plugin_version();
				}
			}

			/**
			 * Adds plugin action links.
			 *
			 * @since 1.0.0
			 */
			public function plugin_action_links( $links ) {
				$plugin_links = array(
					'<a href="admin.php?page=hotjar-settings">Settings</a>',
					'<a href="https://www.hotjar.com/">Support</a>',
				);
				return array_merge( $plugin_links, $links );
			}
		}

		WP_Hotjar::get_instance();
	endif;
}
