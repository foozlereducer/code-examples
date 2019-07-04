<?php
/*
 * Applies business logic and stores string values used for search
 * 
 * This is a immutable parameter object
 * 
 * This object can organically grow to add different type of filtering for the String values 
 * should the requirements of the strings needs to change
 * 
 * Applies wordpress and numeric filtering to avert a web attacks
 * 
 * @uses:	pm_Clean::get_clean_val()				- static class used for sanitation
 * @uses:	pn_dfp_ads()->call_ad('big-box');	- calls the DFP Ad plugin 
 * @param:	$host				- ( string ) 	*required host name of the ElasticSearch cloud service
 * @param:	$username			- ( string ) 	*required username of the ElasticSearch cloud service
 * @param:	$password			- ( string ) 	*required password of the ElasticSearch cloud service
 * @param:	$terms				- ( string ) 	*required search terms used in the ElasticSearch cloud service json search
 * @param:	$indexes			- ( Array )		*required indexes array used in the application that exist in the 
 * 												    ElasticSearch cloud service
 * @param: 	$protocols_and_ports - ( Array ) 	*required ports array used for the ElaticSearch cloud service connnect
 * @param:	$filters			- ( string ) 	optional filter array that contains each filter applied to the current search
 * @param:	$default_operator	- ( string ) 	optional 'AND' or 'OR' search operator; defaults to "AND'
 * @param:	$sort_field			- ( string ) 	optional filter array that contains each filter applied to the current search
 * @param:	$sort_order			- ( string ) 	optional filter array that contains each filter applied to the current search
 * @param:	$template_prefix	- ( string )	*required prefix that denotes either 'mob' or 'des'; mobile or desktop/tablet
 * 
 */
class pm_Query_String_Values
{
	private $index;
	private $terms;
	private $default_operator;
	private $filters;
	private $sort_by;
	private $sort_order;
	private $filter_cache;
	private $protocols_and_ports;
	
	public function __construct( $host, $username, $password, $indexes, $protocols_and_ports, $terms = '', $filters = null, $default_operator = 'AND', $sort_by = false, $template_prefix = false ) {

		$this->host                = $host;
		$this->username            = $username;
		$this->password            = $password;
		$this->indexes             = $indexes;
		$this->terms               = $terms;
		$this->default_operator    = $default_operator;
		$this->filters             = $filters;
		$this->sort_by             = $sort_by;
		$this->sort_order          = $sort_order;
		$this->protocols_and_ports = $protocols_and_ports;
		$this->template_prefix     = $template_prefix;
	}
	
	public function get_host() {
	
		return pm_Clean::get_clean_val( $this->host, $strip_tags = true );
		
	}
	
	public function get_username() {
	
		return pm_Clean::get_clean_val( $this->username, $strip_tags = true );

	}
	
	public function get_password() {
	
		return pm_Clean::get_clean_val( $this->password, $strip_tags = true );

	}
	
	public function get_indexes() {
	
		return pm_Clean::get_clean_val( $this->indexes, $strip_tags = true );

	}
	
	public function get_terms() {
	
		return $this->terms = pm_Clean::get_clean_val( $this->terms, $strip_tags = true );

	}
	
	public function get_default_operator() {
	
		return $this->default_operator = pm_Clean::get_clean_val( $this->default_operator, $strip_tags = true );

	}
	
	/*
	 * Clean and return an array of filters
	 */
	public function get_filters() {
	
		if ( is_array( $this->filters ) ) {
			
			$valid_filter_pairs = array();
			$models             = isset( $this->filters['mf'] ) ? $this->filters['mf'] : '';
			$valid_models       = array();
			$valid_model_pairs  = array();

			return $this->filters;
			
		} else {
			
			return false;
			
		}
	}
	
	public function get_sort_by() {
		
		return pm_Clean::get_clean_val( $this->sort_by, $strip_tags = true );

	}
	
	public function get_sort_order() {
	
		return pm_Clean::get_clean_val( $this->sort_order, $strip_tags = true );

	}
	
	public function get_protocol_and_port( $search_or_write ) {

		if ( 'search' == strtolower( $search_or_write ) ) {
			
			if ( !empty( $this->protocols_and_ports['http'] ) ) {
				
				$this->protocols_and_ports['http'] = pm_Clean::get_clean_val( $this->protocols_and_ports['http'], 'integer' );
				
			} else {
				
				$this->protocols_and_ports['https'] = pm_Clean::get_clean_val( $this->protocols_and_ports['https'], 'integer' );
				
			}
		}
		
		return $this->protocols_and_ports;
	}
	
	public function check_if_mobile() {
	
		$prefix = pm_Clean::get_clean_val( $this->template_prefix );
		
		if ( 'mob' === $prefix )
			return true;
	}
	
	public function get_big_box_ad( $i ) {
	
		ob_start();

		if( function_exists( 'pn_dfp_ads') ) {
			pn_dfp_ads()->call_ad( 'big-box', array(), FALSE, FALSE, '_elastic_' . $i );
		}
		
		$big_box_ad = '<div class="big-box-ad">' . ob_get_clean() . '</div>';
		
		return $big_box_ad;

	}
}