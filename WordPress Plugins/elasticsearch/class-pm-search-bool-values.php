<?php
/*
 * Applies business logic and stores Bool values used for the search
 *
 * This is a immutable parameter object
 *
 * This object can organically grow to add different type of filtering for the String values
 * should the requirements of the strings needs to change
 *
 * Applies wordpress and numeric filtering to avert a web attacks
 *
 * @uses:	pm_Clean::get_clean_val()		- static class used for sanitation
 * @uses:	( bool ) 					- ensures a value is bool
 * 
 * @param:	$filter_cache		- ( bool ) 		optional, defaults to true
 * @param:	$query_match_all			- ( bool ) 		optional if set it triggers the search to wire-up a match-all search
 */
class pm_Search_Bool_Values
{
	private $query_match_all;
	private $filter_cache;
	
	public function __construct( $filter_cache = 1, $query_match_all = 0 ) {

		$this->query_match_all = $query_match_all;
		
		$this->filter_cache    = $filter_cache;
	}
	
	public function get_filter_cache() {
		
		if ( is_bool( $this->filter_cache ) ) {
			
			return pm_Clean::get_clean_val( $this->filter_cache, $strip_tags = true );
		
		} else {
			
			return 1;
		}
	}
	
	public function get_query_match_all() {
		
		return pm_Clean::get_clean_val( $this->query_match_all, $strip_tags = true, 'bool' );
	
	}
}
