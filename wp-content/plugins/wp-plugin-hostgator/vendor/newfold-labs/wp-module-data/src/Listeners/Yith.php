<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors Yith events
 */
class Yith extends Listener {

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		// Paypal Connection
		add_filter( 'pre_update_option_yith_ppwc_merchant_data_production', array( $this, 'paypal_connection' ), 10, 2 );
	}

	/**
	 * PayPal connected
	 *
	 * @param string $new_option New value of the yith_ppwc_merchant_data_production option
	 * @param string $old_option Old value of the yith_ppwc_merchant_data_production option
	 *
	 * @return string The new option value
	 */
	public function paypal_connection( $new_option, $old_option ) {
		if ( $new_option !== $old_option && ! empty( $new_option ) ) {
			$this->push(
				'yith_payment_connected',
				array(
					'provider' => 'paypal',
				)
			);
		}

		return $new_option;
	}
}
