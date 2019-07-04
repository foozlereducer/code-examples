<?php
namespace Postmedia\Plugins\Edash;
// need this required as Travis can't load the trait via the autoloader'
require_once( plugin_dir_path( __FILE__ ) . 'CoreMethods.php' );

/**
 * Class OriginSite ~ Manage Origin Site's Data
 */
class OriginSite {
	// include the core methods via a trait
	use CoreMethods;

	private $client_transient_slug = 'edash_wcm_clients';

	public function get_clients() {
		$clients = get_transient( $this->client_transient_slug );
		if ( false === $clients ) {
			$path = 'clients';
			$clients = $this->call_wcm_data( $path );
			// Set transient to store clients for 1 hour
			set_transient( $this->client_transient_slug, $clients, ( 60 * 60 * 1 ) );
		}

		return $clients;
	}

	public function get_stored_clients() {
		return get_transient( $this->client_transient_slug );
	}

	public function parse_domain( $origin_url ) {
		if ( ! empty( $origin_url ) ) {
			$origin_name_parts = explode( '.', $origin_url );
			$count = count( $origin_name_parts ) - 1;
			// Grab the TDL
			$tdl = $origin_name_parts[ $count ];
			if ( 'www' === $origin_name_parts[0] ) {
				unset( $origin_name_parts[0] );
			}
			if ( isset( $origin_name_parts[0] ) ) {
				return $origin_name_parts[0] . '.' . $tdl;
			}
		} else {
			return 'canadianpress.com';
		}
	}

	public function get_brand( $origin_url ) {
		$origin_url = wp_parse_url( $origin_url );

		if ( isset( $origin_url['host'] ) ) {
			$search_domain = $this->parse_domain( $origin_url['host'] );
			$clients = $this->get_clients();
			return $this->parse_brand( $clients, $search_domain );
		}
	}

	private function parse_brand( $clients, $search_domain ) {
		foreach ( $clients as $client ) {
			if ( isset( $client->domain ) ) {
				if ( $this->parse_domain( $client->domain ) === $search_domain ) {
					if ( isset( $client->name ) ) {
						return $client->name;
					}
				}
			}
		}

		return $search_domain;
	}
}
