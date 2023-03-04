<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors Yith events
 */
class Commerce extends Listener {

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'newfold_wp_data_module_cron_data_filter', array( $this, 'products_count' ) );
		add_filter( 'newfold_wp_data_module_cron_data_filter', array( $this, 'orders_count' ) );
	}

	/**
	 * Products Count
	 *
	 * @param string $data Array of data to be sent to hiive
	 *
	 * @return string Array of data
	 */
	public function products_count( $data ) {
		if ( ! isset( $data['meta'] ) ) {
			$data['meta'] = array();
		}
		$data['meta']['products_count'] = (int) wp_count_posts( 'product' )->publish;

		return $data;
	}

	/**
	 * Orders Count
	 *
	 * @param string $data Array of data to be sent to hiive
	 *
	 * @return string Array of data
	 */
	public function orders_count( $data ) {
		if ( ! isset( $data['meta'] ) ) {
			$data['meta'] = array();
		}
		$data['meta']['orders_count'] = (int) wp_count_posts( 'shop_order' )->publish;

		return $data;
	}
}
