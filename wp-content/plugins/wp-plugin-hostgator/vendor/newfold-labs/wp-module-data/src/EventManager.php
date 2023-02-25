<?php

namespace NewfoldLabs\WP\Module\Data;

/**
 * Class to manage event subscriptions
 */
class EventManager {

	/**
	 * List of default listener category classes
	 *
	 * @var array
	 */
	const LISTENERS = array(
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Admin',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Content',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Cron',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Jetpack',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Plugin',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\BluehostPlugin',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\SiteHealth',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Theme',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Yith',
		'\\NewfoldLabs\\WP\\Module\\Data\\Listeners\\Commerce',
	);

	/**
	 * List of subscribers receiving event data
	 *
	 * @var array
	 */
	private $subscribers = array();

	/**
	 * The queue of events logged in the current request
	 *
	 * @var array
	 */
	private $queue = array();

	/**
	 * Initialize the Event Manager
	 *
	 * @return void
	 */
	public function init() {
		$this->initialize_listeners();
		$this->initialize_cron();

		// Register the shutdown hook which sends or saves all queued events
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	/**
	 * Initialize the REST API endpoint.
	 */
	public function initialize_rest_endpoint() {
		// Register REST endpoint.
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	/**
	 * Handle setting up the scheduled job for sending updates
	 *
	 * @return void
	 */
	public function initialize_cron() {
		// Ensure there is a minutely option in the cron schedules
		add_filter( 'cron_schedules', array( $this, 'add_minutely_schedule' ) );

		// Minutely cron hook
		add_action( 'nfd_data_sync_cron', array( $this, 'send_batch' ) );

		// Register the cron task
		if ( ! wp_next_scheduled( 'nfd_data_sync_cron' ) ) {
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'minutely', 'nfd_data_sync_cron' );
		}
	}

	/**
	 * Register the event route.
	 */
	public function rest_api_init() {
		$controller = new API\Events( Data::$instance->hiive, $this );
		$controller->register_routes();
	}

	/**
	 * Add the weekly option to cron schedules if it doesn't exist
	 *
	 * @param array $schedules List of cron schedule options
	 *
	 * @return array
	 */
	public function add_minutely_schedule( $schedules ) {
		if ( ! array_key_exists( 'minutely', $schedules ) || MINUTE_IN_SECONDS !== $schedules['minutely']['interval'] ) {
			$schedules['minutely'] = array(
				'interval' => MINUTE_IN_SECONDS,
				'display'  => __( 'Once Every Minute' ),
			);
		}

		return $schedules;
	}

	/**
	 * Sends or saves all queued events at the end of the request
	 *
	 * @return void
	 */
	public function shutdown() {

		// Separate out the async events
		$async = array();
		foreach ( $this->queue as $index => $event ) {
			if ( 'pageview' === $event->key ) {
				$async[] = $event;
				unset( $this->queue[ $index ] );
			}
		}

		// Save any async events for sending later
		if ( ! empty( $async ) ) {
			if ( ! get_option( 'nfd_data_queue_lock' ) ) {
				$saved_queue = get_option( 'nfd_data_queue', array() );
				update_option( 'nfd_data_queue', array_merge( $saved_queue, $async ), false );
			}
		}

		// Any remaining items in the queue should be sent now
		if ( ! empty( $this->queue ) ) {
			$this->send( $this->queue );
		}
	}

	/**
	 * Register a new event subscriber
	 *
	 * @param SubscriberInterface $subscriber Class subscribing to event updates
	 *
	 * @return void
	 */
	public function add_subscriber( SubscriberInterface $subscriber ) {
		$this->subscribers[] = $subscriber;
	}

	/**
	 * Returns filtered list of registered event subscribers
	 *
	 * @return array List of susbscriber classes
	 */
	public function get_subscribers() {
		return apply_filters( 'newfold_data_subscribers', $this->subscribers );
	}

	/**
	 * Return an array of registered listener classes
	 *
	 * @return array List of listener classes
	 */
	public function get_listeners() {
		return apply_filters( 'newfold_data_listeners', $this::LISTENERS );
	}

	/**
	 * Initialize event listener classes
	 *
	 * @return void
	 */
	public function initialize_listeners() {
		foreach ( $this->get_listeners() as $listener ) {
			$class = new $listener( $this );
			$class->register_hooks();
		}
	}

	/**
	 * Push event data onto the queue
	 *
	 * @param Event $event Details about the action taken
	 *
	 * @return void
	 */
	public function push( Event $event ) {
		do_action( 'nfd_event_log', $event->key, $event );
		array_push( $this->queue, $event );
	}

	/**
	 * Send queued events to all subscribers
	 *
	 * @param array $events A list of events
	 *
	 * @return void
	 */
	public function send( $events ) {
		foreach ( $this->get_subscribers() as $subscriber ) {
			$subscriber->notify( $events );
		}
	}

	/**
	 * Retrieve a batch of events from the DB queue and shorten it
	 *
	 * @param integer $count Number of events to grab from the queue
	 *
	 * @return array
	 */
	public function get_batch( $count = 20 ) {
		$events = get_option( 'nfd_data_queue', array() );
		// Bail early if no saved events
		if ( empty( $events ) ) {
			return $events;
		}

		for ( $i = 0; $i < $count; $i ++ ) {
			$batch[] = array_shift( $events );
		}

		update_option( 'nfd_data_queue', $events, false );

		return $batch;
	}

	/**
	 * Send queued events to all subscribers
	 *
	 * @return void
	 */
	public function send_batch() {
		add_option( 'nfd_data_queue_lock', true );
		$events = $this->get_batch();
		if ( ! empty( $events ) ) {
			$this->send( $events );
		}
		delete_option( 'nfd_data_queue_lock' );
	}
}
