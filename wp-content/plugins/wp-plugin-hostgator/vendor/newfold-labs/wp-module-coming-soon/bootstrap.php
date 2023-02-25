<?php

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\ComingSoon;
use function NewfoldLabs\WP\ModuleLoader\register;

if ( function_exists( 'add_action' ) ) {

	add_action(
		'plugins_loaded',
		function () {

			register(
				array(
					'name'     => 'coming-soon',
					'label'    => __( 'Coming Soon', 'newfold-module-coming-soon' ),
					'callback' => function ( Container $container ) {
						$coming_soon = new ComingSoon( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);

		}
	);

}
