<?php

use NewfoldLabs\WP\Module\Data\Data;
use NewfoldLabs\WP\Module\Data\Helpers\Encryption;
use NewfoldLabs\WP\Module\Data\Helpers\Transient;
use NewfoldLabs\WP\ModuleLoader\Container;

use function NewfoldLabs\WP\ModuleLoader\register as registerModule;

// Do not allow multiple copies of the module to be active
if ( defined( 'NFD_DATA_MODULE_VERSION' ) ) {
	exit;
}

define( 'NFD_DATA_MODULE_VERSION', '2.2.5' );

/**
 * Register the data module
 */
if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			registerModule(
				array(
					'name'     => 'data',
					'label'    => __( 'Data', 'newfold-data-module' ),
					'callback' => function () {
						$module = new Data();
						$module->start();
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);

		}
	);

	// Auto-encrypt token on save.
	add_filter(
		'pre_update_option_nfd_data_token',
		function ( $value ) {
			$encryption = new Encryption();

			return $encryption->encrypt( $value );
		}
	);

	// Register activation hook
	add_action(
		'newfold_container_set',
		function ( Container $container ) {
			register_activation_hook(
				$container->plugin()->file,
				function () use ( $container ) {
					Transient::set( 'nfd_plugin_activated', $container->plugin()->basename );
				}
			);
		}
	);

}
