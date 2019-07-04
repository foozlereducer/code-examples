<?php

namespace Postmedia\Web\Theme;

use Postmedia\Web\Utilities\Timer;

/**
 * Postmedia Theme Stats Related to load times of plugins etc.
 */
class Stats {

	/**
	 * List of timers
	 * @var array
	 */
	public $timers;

	/**
	 * To enable stats tracking or not
	 * @var bool
	 */
	public $stats_enabled;


	public function __construct( $stats_enabled = false ) {
		$this->timers = array();

		$this->stats_enabled = $stats_enabled;

		if ( $this->stats_enabled ) {
			// register meta tag rendering
			add_action( 'wp_head', array( $this, 'render_meta_stats' ) );
		}
	}

	/**
	 * Output meta tag html for stats
	 * @return void
	 */
	public function render_meta_stats() {
		foreach ( $this->timers as $name => $timer ) {
			echo sprintf( '<meta name="%s" value="%s" />%s', esc_attr( 'stats-' . $name ), esc_attr( $timer->elapsed() ), "\n" );
		}
	}

	/**
	 * Create a new timer for Stats
	 * @param  string $name
	 * @return Timer
	 */
	public function new_timer( $name ) {
		if ( $this->stats_enabled ) {
			$this->timers[ $name ] = new Timer();

			return $this->timers[ $name ];
		}

		return null;
	}

	/**
	 * Stop a timer and get the elapsed time in seconds
	 * @param  string $name
	 * @return float
	 */
	public function stop_timer( $name ) {
		if ( $this->stats_enabled && isset( $this->timers[ $name ] ) ) {
			return $this->timers[ $name ]->stop();
		}

		return null;
	}
}
