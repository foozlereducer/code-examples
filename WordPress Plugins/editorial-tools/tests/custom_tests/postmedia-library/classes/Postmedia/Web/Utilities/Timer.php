<?php

namespace Postmedia\Web\Utilities;

/**
 * Timer object
 */
class Timer {

	/**
	 * Timer start time in seconds
	 * @var float
	 */
	public $start_time;

	/**
	 * Timer stop time in seconds
	 * @var [type]
	 */
	public $stop_time;


	public function __construct() {
		$this->start_time = microtime( true );
	}

	/**
	 * Stop this timer and return the Elapsed time since the timer started in seconds
	 * @return float
	 */
	public function stop() {
		$this->stop_time = microtime( true );

		return $this->elapsed();
	}

	/**
	 * Return the time elapsed since the timer started in seconds
	 * @return float
	 */
	public function elapsed() {
		if ( isset( $this->stop_time ) ) {
			return number_format( $this->stop_time - $this->start_time, 3 );
		} else {
			return number_format( microtime( true ) - $this->start_time, 3 );
		}
	}
}
