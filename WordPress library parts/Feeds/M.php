<?php

namespace Postmedia\Web\Feeds;

use \Postmedia\Web\Feed;

class MSN extends Feed {

	/**
	 * Query var
	 * @var string
	 */
	private static $query_var = 'partner';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		add_filter( 'query_vars', array( $this, 'filter_query_vars' ) );
		add_filter( 'pre_get_posts', array( $this, 'filter_pre_get_posts' ) );
		add_filter( 'the_guid', array( $this, 'filter_the_guid' ), 10, 2 );
	}

	/**
	 * Adds query var to indicate that this is an MSN feed.
	 *
	 * @param  array $public_query_vars
	 * @return array
	 */
	public function filter_query_vars( $public_query_vars ) {
		$public_query_vars[] = self::$query_var;
		return $public_query_vars;
	}

	/**
	 * Removes wire content from feed.
	 *
	 * @param WP_Query $query
	 */
	public function filter_pre_get_posts( $query ) {
		if ( ! self::is_feed() ) {
			return;
		}

		$user_slugs = array( 'associatedpressnp', 'canadianpressnp' );
		$user_ids = array();
		foreach ( $user_slugs as $user_slug ) {
			$user = get_user_by( 'slug', $user_slug );
			$user_ids[] = $user->ID;
		}

		$query->set( 'author__not_in', $user_ids );
	}

	/**
	 * Replaces the guid.
	 *
	 * @param  string $guid
	 * @param  int    $id
	 * @return string
	 */
	public function filter_the_guid( $guid, $id ) {
		if ( ! self::is_feed() ) {
			return $guid;
		}

		return 'pn-' . $id;
	}

	/**
	 * Returns true if this is an MSN feed.
	 *
	 * @return boolean
	 */
	public static function is_feed() {
		return is_feed() && 'msn' === get_query_var( self::$query_var, '' );
	}
}
