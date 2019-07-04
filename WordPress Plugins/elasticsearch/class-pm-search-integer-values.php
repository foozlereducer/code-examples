<?php
/*
 * Applies business logic and stores integer values used for the search
 *
 * This is a immutable parameter object
 *
 * This object can organically grow to add different type of filtering for the String values
 * should the requirements of the strings needs to change
 *
 * Applies wordpress and numeric filtering to avert a web attacks
 *
 * @uses:	pm_Clean::get_clean_val()				- static class used for sanitation
 * @uses:	( bool ) 							- ensures a value is bool
 * @uses:	$this->check_if_mobile()			- determines if the search is run on a mobile device
 *
 * @param:	$from								- ( integer ) 	*required, denotes the result to start from 
 *                                                                  ( used in combination with pagination )
 * @param:	$limit								- ( string ) 	*required, for desktop sets the number of results per page
 * @param:	$mobile_size						- ( string ) 	*required, the total number of results for each 'load more' on mobile
 * $mobile_num_of_results_per_segment_before_ad	- ( string ) 	*required, defines the number of results on mobile before an ad
 */
class pm_Search_Integer_Values
{
	private $from;
	private $limit;
	private $mobile_limit;
	private $mobile_size;
	private $mobile_num_of_results_per_segment_before_ad;
	
	public function __construct( $from, $limit, $mobile_size = 6, $mobile_num_of_results_per_segment_before_ad = 3, $template_prefix = 'des', $mobile_limit = 6 ) {
		$this->from                                        = $from;
		$this->limit                                       = $limit;
		$this->mobile_limit                                = $mobile_limit;
		$this->mobile_size                                 = $mobile_size;
		$this->mobile_num_of_results_per_segment_before_ad = $mobile_num_of_results_per_segment_before_ad;
		$this->template_prefix                             = $template_prefix;
	}
	
	/*
	 * This 'from' value will be used by the json query
	 * It is not the presentation from or the query string parameter from_page
	 * when 0 it is the first page of results; otherwise multiply 'from' by the
	 * results page number limit to match the correct Elastic offset for the next page
	 */
	public function get_from() {
		$is_mobile = $this->check_if_mobile();
		
		if ( 0 == ( $this->from ) ) {
			
			return pm_Clean::get_clean_val( $this->from, $strip_tags = true, 'integer' );
		
		} else if ( $is_mobile ) {
			
			$this->from = pm_Clean::get_clean_val( $this->from, $strip_tags = true, 'integer' );
			
			return $this->from * $this->get_mobile_size();
		
		} else {
			
			$this->from = pm_Clean::get_clean_val( $this->from, $strip_tags = true, 'integer' );
			
			return $this->from * $this->get_limit();
		}
	}
	
	public function get_limit() {
		
		return pm_Clean::get_clean_val( $this->limit, $strip_tags = true, 'integer' );
	
	}
	
	public function get_mobile_limit() {
		
		return pm_Clean::get_clean_val( $this->mobile_limit, $strip_tags = true, 'integer' );
	
	}
	
	public function get_results_start() {
		
		if ( $this->get_from() > 0 ) {
			
			$result_start = $this->get_from() * $this->get_limit() - ( $this->get_limit() - 1 );
			
		} else {
			
			$result_start = 0;
			
		}
		
		return $result_start;
	}
	
	public function get_results_end() {
		
		return ( $this->get_results_start() + $this->get_limit() - 1 );
	
	}
	
	public function get_mobile_size() {
		
		return pm_Clean::get_clean_val( $this->mobile_size, $strip_tags = true, 'integer' );
	
	}
	
	public function get_mobile_num_of_results_per_segment_before_ad() {
		
		return pm_Clean::get_clean_val( $this->mobile_num_of_results_per_segment_before_ad, $strip_tags = true, 'integer' );
	
	}
	
	public function check_if_mobile() {
		
		$prefix = pm_Clean::get_clean_val( $this->template_prefix );
		
		if ( 'mob' == $prefix ) {
			
			return true;
		
		} else {
			
			return false;
		
		}
	}
}
