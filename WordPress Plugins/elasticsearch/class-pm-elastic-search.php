<?php

require_once( 'class-pm-clean.php' );
require_once( 'class-pm-query-string-values.php' );
require_once( 'class-pm-search-bool-values.php' );
require_once( 'class-pm-search-integer-values.php' );

class pm_Elastic_Search
{
	protected $client;
	protected $connection;
	protected $protocol;
	protected $host;
	protected $port;
	protected $config;
	protected $allowed_protocols;
	protected $indexes = array();
	protected $args = array();
	protected $url;
	protected $json;
	protected $Query_String_Values;
	protected $Search_Bool_Values;
	protected $Search_Integer_Values;
	protected $match_this_txt_and_return_index;
	
	public function __construct( pm_Query_String_Values $QS, pm_Search_Bool_Values $SB, pm_Search_Integer_Values $SI ) {

		$this->Query_String_Values   = $QS;
		$this->Search_Bool_Values    = $SB;
		$this->Search_Integer_Values = $SI;
		
	}
	
	/*
	 * Wire up authentication
	 * 
	 * base64 encode the username and password
	 * add the basic authentication to headers for CURL
	 */
	protected function set_auth() {

		$this->args = array(
			
			 'headers' => array(
				
				 'Authorization' => 'Basic ' . base64_encode( $this->Query_String_Values->get_username() . ':' . $this->Query_String_Values->get_password() ) 
			) 
		);
		
	}
	
	protected function set_url( $search_or_write_or_verify = 'search', $index ) {

		$protocol_and_port = $this->Query_String_Values->get_protocol_and_port( $search_or_write_or_verify );
		
		switch ( $search_or_write_or_verify ) {
			
			case 'search':
				
			case 'closest_search':
			
			case 'verify':
				
				$this->url  = 'http:' . '//'; // Must build this like so, otherwise this->url is set to http:/ and not http://
				$this->port = $protocol_and_port[ 'http' ];
				break;
			
			case 'write':
			
			case 'delete':
				
				$this->url  = 'https:' . '//'; // Must build this like so, otherwise this->url is set to https:/ and not https://
				$this->port = $protocol_and_port[ 'https' ];
				break;
		}
		
		$this->url .= $this->Query_String_Values->get_host() . ':' . $this->port;
		$this->url .= '/' . $index;
		
		switch ( $search_or_write_or_verify ) {
			
			case 'search':
				$this->url .= '/' . '_search';
				break;
			case 'verify':
				$this->url .= '/' . '_status';
				break;
			case 'closest_search':
				$this->url .= '/' . 'style/_search?search_type=dfs_query_then_fetch';
				break;
		}
	}
	
	public function get_url( $search_or_write_or_verify = 'search', $index ) {

		$this->set_url( $search_or_write_or_verify, $index );
		return $this->url;
		
	}
	
	public function get_index( $indexes, $pattern_target = 'post' ) {
	
		foreach ( $indexes as $index ) {
			
			$pattern = "/$pattern_target/";
			
			preg_match( $pattern, $index, $matches );
			
			if ( $matches ) {
				
				return $index;
			}
		}
	}

	public function get_aggregation_terms ( $fields = array(), $filters = array(), $limit=50 ){
		
		$facets = array();

		if ( !empty( $fields ) ){
			
			$aggs_array = is_array( $fields ) ? $fields : json_decode( str_replace('\\','', $fields ) ,true );

			foreach ( $aggs_array as $key => $value ) {
				
				if ( is_array( $value ) ) {
					
					$facets = array_merge( $facets, $this->get_aggs_term_obj( array( $key ), $filters ) );

					foreach ( $value as $_key => $_val ) {

						$_k = is_array( $_val ) ? $_key : $_val;

						$_term = $this->get_aggs_term_obj( array( $_k ), $filters );

						$facets[ $key ]['aggs'][ $_k ] = $_term[ $_k ];

						if ( is_array( $_val ) ) {
							
							foreach ( $_val as $v ) {

								$_t = $this->get_aggs_term_obj( array( $v ), $filters );

								$facets[ $key ]['aggs'][ $_key ]['aggs'][ $v ] = $_t[ $v ];

							}

						} 
					}
				} else {
					$facets = array_merge( $facets, $this->get_aggs_term_obj( array( $key => $value ), $filters ) );
				} 
			}
		}
		return $facets;
	}

	public function get_aggregation_script ( $fields, $limit = 50 ) {
		$facets = array ();
		if ( !empty( $fields ) ) {
			$fields = ! is_array( $fields ) ? explode( ',', $fields ) : $fields;
			$field = $fields[0];

			$facets[ $field ] = array(
				"terms" => array(
					'size' => $limit,
					'order' => array(
						'_term' => "asc"
					)
				)
			);

			if( count( $fields ) > 1 ) {
				$source = array();
				foreach( $fields as $key => $_val ) {
					$source[] = "doc['" . $_val . "'].value";
				}
				$script =  implode( "+'::'+", array_reverse( $source ) );
				$facets[ $field ]['terms']['script'] = $script;
			} else {
				$facets[ $field ]['terms']['field'] = $field;
			}
		}

		return $facets;
	}

	public function get_queries ( $filters ) {
		$_return = array();
		
		$queries = isset( $filters['query'] ) ? $filters['query'] : $filters;
		
		if ( ! empty( $queries ) ) {

			foreach ( $queries as $query_key => $query_array ) {
				$or_query = array();
				$terms = array();

				foreach ( $query_array as $key => $value ) {
					$term = array();
					if ( is_array( $value ) ) {
						reset( $value );
						$array_key = key( $value );

						$term[] = array(
								'term' => array(
									$array_key => $value[ $array_key ]
							)
						);

						if ( isset( $value['query'] ) ) {
							foreach( $value['query'] as $q_key => $q_array ){
								$_or_query = array();
								foreach ( $q_array as $_k => $_v ){
									$_or_query['query']['bool']['should'][] = array( 
										"term" => array(
											$q_key => $_v 
										)
									);
								}
								if ( !empty( $_or_query ) ) {
									$term [] = $_or_query;
								}
							}
						}
					} else {
						$term[] = array(
								'term' => array(
									$key => $value
							)
						);
					}
					$terms [] = array ( "query" => array( "bool" => array( "must" => array( $term ) ) ) );
				}
				
				if ( !empty ( $terms ) ){
					$_return['query']['bool']['should'][] = $terms;
				}
				
			}
		}

		return $_return;
	}

	public function get_filters (  $filters ) {
		
		$_return = array();

		if ( !empty( $filters ) ) { // If filters exist then add them
				
				if ( isset( $filters['field'] ) ) { unset( $filters['field'] ); }
			
				foreach ( $filters as $filter_key => $filter_array ) {
					$or_filters = array();
					
					if( ! empty( $filter_array[0]['_range'] ) ) {
						$range_keys = array( 'gte', 'lte' );
						$range_values = $range_array = array();
						foreach ( $filter_array as $key => $value ) {
							
							if ( is_array( $value ) ) {
								reset( $value );
								
								$array_key = key( $value );
	
								if( $value[ $array_key ] >= 0 && $value[ $array_key ] != '' ) {
									$range_values[ $key ] = $value[ $array_key ];
								}
							}
						}
						
						foreach( $range_values as $key => $val ) {
							$range_array[ $range_keys[ $key ] ] = $range_values[ $key ];
						}
						
						//$range_array = array_combine( $range_keys, $range_values );
						$or_filters[ 'or' ][ 'filters' ][] = array(
								'range' => array(
									$array_key => $range_array
							) 
						);
					}
					else {
						foreach ( $filter_array as $key => $value ) {
							
							if ( is_array( $value ) ) {
								reset( $value );
								$array_key = key( $value );
								
								$term = array(
									 'term' => array(
										 $array_key => $value[ $array_key ]
									) 
								);

								if ( isset( $value['filter'] ) ) {

									$_f = $this->get_filters( $value['filter'] );
									
									$or_filters[ 'or' ][ 'filters' ][] = array(
										"and" => array (
											"filters" => array (
												$term,
												array ( "or" => array( "filters" => $_f['and']['filters'] ) )
											) 
										)
									);
									
								} else {
									$or_filters[ 'or' ][ 'filters' ][] = $term;
								} 
								
							} else {
								
								$_k = is_numeric ( $key ) ? $filter_key : $key;
								
								$_return[ 'and' ][ 'filters' ][] = array(
									 'term' => array(
										 $_k => $value
									) 
								);
							}
						}
					}
					
					if ( !empty( $or_filters ) ) {
					
						$_return[ 'and' ][ 'filters' ][] = $or_filters;

					}
				}

				// Single filters such as 'reviews', 'news', 'Galleries', 'Authors' ...
				$y             = 0; // foreach loop index comparion variable
				
				if ( isset( $filters[ 'pf' ] ) ) {
					
					$_return[ 'and' ][ 'filters' ][] = array(
						 'term' => array(
							 'attachedmedia' => 'PhotoGallery' 
						) 
					);
					
					if ( isset( $filters[ 'mpvf' ] ) ) {
						
						$_return[ 'and' ][ 'filters' ][] = array(
							 'term' => array(
								 'model' => $filters[ 'mpvf' ]
							) 
						);
					}
				}
				
				if ( isset( $filters[ 'vf' ] ) ) {
					$_return[ 'and' ][ 'filters' ][] = array(
						 'term' => array(
							 'attachedmedia' => 'Video' 
						) 
					);
				}
				
		} // End filter empty check
		return $_return;
	}
	
	public function get_aggs_term_obj ( $aggs_array, $filters = array(), $limit = 50 ) {
		
		$facets = array ();
		if ( !empty( $aggs_array ) ) {
			foreach ( $aggs_array as $key => $value ) {
				$_k = ! is_numeric( $key ) ? $key : $value;
				$term = array (
					"terms" => array(
						'field' => $_k,
						'size' => $limit,
						'order' => array( '_term' => "asc" )
					)
				);
				if ( isset( $filters[ $_k ] ) && ! empty( $filters[ $_k ] ) ){
					$facets[ $_k ] = array( 
						"aggs" => array(
							$_k => $term
						),
						"filter" => $this->get_filters ( $filters[ $_k ] )
					);
				} else {
					$facets[ $_k ] = $term;
				}
				
				if ( ! is_numeric( $key ) ) {
					$term = array( "terms" => array(
						'field' => $value,
						'size' => $limit,
						'order' => array( '_term' => "asc" )
					) );
					if ( isset( $filters[ $value ] ) && ! empty( $filters[ $value ] ) ){
						$facets[ $key ]['aggs'][ $value ] = array( 
							"aggs" => array(
								$value => $term
							),
							"filter" => $this->get_filters ( $filters[ $value ] )
						);
					} else {
						$facets[ $key ]['aggs'][ $value ] = $term;
					}
				}
			}
		}

		return $facets;
	} 

	/*
	 * Build the raw JSON string, when they exist, add filters and/or sort to the query
	 *
	 * @parma:	suggest_terms							- ( string ) if user selects 'suggested search terms' then these terms are passed
	 * @uses:	set_auth()								- ( null ) sets up the ElasticSearch credentials for the query
	 * @uses:	set_url()								- ( null ) wires if it is a search or update, delete or write operation
	 * 														 and uses http, or https
	 * @uses:	Query_String_Values->get_terms()		- ( string) gets the 'terms' from the QSV value object
	 * @uses:	Search_Integer_values->get_from()		- ( integer ) gets the 'from' value from the SIV value object
	 * @uses:	Search_Integer_Values ->get_mobile_size() - ( integer ) gets the total number of results per mobile segment
	 * @uses:	Search_Integer_Values->get_limit()		- ( integer ) gets the toal number of records to retrieve for the desktop or tablet
	 * @uses:	Query_String_Values->get_filters()		- ( array ) get all filters for the current search
	 * @uses:	Search_Bool_Values->get_filter_cache() 	- ( bool ) gets the filter cache value from the SBI value object
	 * @uses:	Query_String_Values->get_sort_order()	- ( string ) gets 'asc' or 'desc' as per the QSV value
	 * @uses:	Query_String_Values->get_sort_by()		- ( string ) get Elastic field to sort by from the QSV value object
	 * @uses:	Search_Bool_Values->get_match_all()		- ( bool ) flag that determines if a match_all search should be run
	 * @uses:	count()									- returns the array count
	 * @uses:	str_replace()							- ( string ) used to remove \n and \r
	 * @uses:	trim_whitespace()						- ( string ) removes whitespace 
	 * @uses:	wp_remote_get()							- ( string ) calls the ElasticSearch and returns a JSON result set
	 * @uses:	json_decode()							- ( array ) decode the JSON result set
	 * @uses:	Search_Integer_Values
	 * ->get_mobile_num_of_results_per_segment_before_ad()	- ( integer ) gets the number of records before an ad on mobile
	 * @uses:	empty()									- ( bool )	determines if the value is empty	
	 
	 */
	public function get_json_search_string( $suggest_terms = NULL, $es_index = NULL ) {

		$this->set_auth();
		$indexes = $this->Query_String_Values->get_indexes();
		$index   = $this->get_index( $indexes, $es_index );
		//$this->set_url( 'search', $index );
		
		$original_terms = '';

		/*
		 * Get the dynamic search values
		 */
		if ( !empty( $suggest_terms ) ) {
			
			$terms = $suggest_terms;
			
		} else {
			
			$terms = $this->Query_String_Values->get_terms();
		}
		
		$from_page = $this->Search_Integer_Values->get_from();
		
		$is_mobile = $this->Query_String_Values->check_if_mobile();
		
		if ( $is_mobile ) {
			
			$limit        = $this->Search_Integer_Values->get_mobile_size();
			$mobile_limit = $this->Search_Integer_Values->get_mobile_limit();
			
		} else {
			
			$limit = $this->Search_Integer_Values->get_limit();
			$mobile_limit = 6;

		}
		
		$filters         = $this->Query_String_Values->get_filters();
		
		$filter_cache    = $this->Search_Bool_Values->get_filter_cache();
		
		$match_all_query = $this->Search_Bool_Values->get_query_match_all();
		
		$sort_field      = $this->Query_String_Values->get_sort_by();

		/*
		 * Start building query based on query terms and if sort or filters exist 
		 */

		$json           = array();

		if( isset( $filters['dfs_query_then_fetch'] ) && ! empty( $filters['dfs_query_then_fetch'] ) ) {
			$this->get_url( 'closest_search', $index );
		}
		else {
			$this->set_url( 'search', $index );
		}
		
		if( isset( $filters['search'] ) && ! empty( $filters['search'] ) ) {
			
			$json['query'] = array(
					'filtered' => array(
							'query' => array()
						)
					);
			
			if ( !empty( $filters['search'] ) ) { // If filters exist then add them
				
				$or_filters = array();
				
				foreach ( $filters['search']['key_value'] as $filter_key => $filter_array ) {
					
					$or_filters[] = array(
						 'match' => $filter_array
					);

				}
				
				if ( !empty( $or_filters ) ) {
					
					$json['query']['filtered']['query']['bool' ]['must'] = $or_filters;
					$json['query']['filtered']['filter']['bool']['must_not'] = array(
													'missing' => array(
															"field" => "msrp",
															"existence" => true,
															"null_value" => true
															)	
													);
				}

				$json[ 'aggs' ] = $this->get_aggregation_script( $filters['search']['field'] );
				
			}
		} else {
			
			$json[ 'from' ] = $from_page;
			$json[ 'size' ] = $limit;

			if ( 0 != preg_match( '/Search/', $terms ) &&  false != preg_match( '/Search/', $terms ) ) {
	
				$json[ 'highlight' ] = array(
					 'fields' => array(
						 'body' => array(),
						'title' => array ()
					),
					'pre_tags' => array(
						 "<span class=\"highlight\">" 
					),
					'post_tags' => array(
						 "</span>" 
					) 
				);
			}
			
			if ( !empty( $filters ) ) { 
				
				//create aggregation terms and unset field from filters.
				if ( isset( $filters['field'] ) && !empty( $filters['field'] ) ) {
					$aggregation_filters = isset ( $filters['aggs'] ) ? $filters['aggs'] : array();
					$json[ 'aggs' ] = $this->get_aggregation_terms( $filters['field'], $aggregation_filters );
				}
				unset( $filters['field'] );
				unset( $filters['aggs'] );
				
				if ( isset( $filters['query'] ) && !empty( $filters['query'] ) ) {
					$json[ 'query' ] = $this->get_filters( $filters['query'] ); 
					unset( $filters['query'] );
				}

				//return MSRP avg,max,min,sum,count of the search result set
				$json["aggs"]["stats"] = array( "stats" => array( "field" => "msrp" ) );

				if ( !empty( $filters  ) ) {
					$_filter = $this->get_filters ( $filters );
					if( !empty( $_filter ) ) {
						$json[ 'filter' ] = $_filter;
					}
					//Apply filters to stats
					$stats_filters = $filters;
					unset( $stats_filters['msrp'] );
					if( !empty( $stats_filters ) ){
						$stats_filters = $this->get_filters ( $stats_filters );
						if( !empty( $stats_filters ) ){
							$json["aggs"]["stats"] = array(
								'filter' => $stats_filters,
								'aggs' => array(
									"msrp" => array(
										"stats" => array( "field" => "msrp" )
									)
								)
							);
						}
					}
				}
			}

			if ( ! isset( $json[ 'query' ] ) ||  empty( $json[ 'query' ] ) ) {
				if ( 1 == $match_all_query ) {

					$json[ 'query' ] = array(
						'match_all' => array ()
					);

				} else {

					$json[ 'query' ] = array(
						'match' => array(
							'_all' => array(
								'query' => $terms,
								'operator' => 'and' 
							) 
						) 
					);

				}
			}
		}

		// Sort
		if ( !empty( $sort_field ) ) {
			$sort = array();
			$all_sorts = array();

			foreach( $sort_field as $val ) {
				$double_sort = explode( ',', $val );
				
				for( $i=0; $i<= count( $double_sort ); $i++ ) {
					$additional_sort = array_filter( explode( ':', $double_sort[ $i ] ) );
					if( ! empty( $additional_sort ) ) {
						
						$sort[] = array(
								     $additional_sort[0] => array(
												'order' => $additional_sort[1],
												'missing' => '_last'
												)
								    );
						
					}
				}
				
			}

			$json[ 'sort' ] = $sort;
		}
		
		if ( 0 != preg_match( '/Search/', $terms ) &&  false != preg_match( '/Search/', $terms ) ) {
			
			$json[ 'suggest' ][ 'check' ] = array(
				 'text' => $terms,
				'term' => array(
					 'field' => 'body',
					'suggest_mode' => 'popular',
					'min_word_len' => 4 
				) 
			);
		}
		
		
		$this->json = json_encode( $json );

		$this->json           = str_replace( PHP_EOL, '', $this->json ); // remove \n and \r
		
		$this->json           = $this->trim_whitepace( $this->json ); // remove whitespace
		
		$this->args[ 'body' ] = $this->json;

		$results              = wp_remote_post( $this->url, $this->args );
		
		if ( is_wp_error( $results ) ) {
			return $results;
		}
		
		$results              = json_decode( $results[ 'body' ], true );

		$count                = $results[ 'hits' ][ 'total' ];

		if ( 0 == $count && isset( $results[ 'suggest' ] ) ) {
			
			$suggestions       = $results[ 'suggest' ][ 'check' ];
			$suggestions_count = is_array( $suggestions ) ? count( $suggestions ) : 0;
			$suggested_terms   = '';
			
			for ( $i = 0; $i < $suggestions_count; $i++ ) {
				if ( !empty( $suggestions[ $i ][ 'options' ] ) ) { // a spelling suggestion exists
					if ( $i < $suggestions_count ) {
						$suggested_terms .= $suggestions[ $i ][ 'options' ][ 0 ][ 'text' ] . ' ';
					} else {
						$suggested_terms .= $suggestions[ $i ][ 'options' ][ 0 ][ 'text' ];
					}
				} else { // use the orginal term as no spelling suggestion exists
					if ( $i < $suggestions_count ) {
						$suggested_terms .= $suggestions[ $i ][ 'text' ] . ' ';
					} else {
						$suggested_terms .= $suggestions[ $i ][ 'text' ];
					}
				}
			}
			
			if ( !empty( $suggested_terms ) ) {
				
				$original_terms = $terms;
				$terms          = $suggested_terms;
				
			}
			
		}

		$hits                 = ( ! empty( $results[ 'hits' ][ 'hits' ] ) ) ? $results[ 'hits' ][ 'hits' ] : array();
		$facets 		      = ( isset( $results['aggregations'] ) ) ? $results['aggregations'] : array();
		$i                            = 1;
		$mobile_max_results_before_ad = $this->Search_Integer_Values->get_mobile_num_of_results_per_segment_before_ad();
		$res                          = Array();
		
		foreach ( $hits as $hit ) {
			
			if ( $is_mobile ) {
				
				if ( $mobile_max_results_before_ad == $i ) {
					
					$res[ 'Ad' ] = $this->Query_String_Values->get_big_box_ad( $i );
					
					//$i           = 0;
					
				} else { // don't let the add repeatedly get added to each result
					
					if ( $res[ 'Ad' ] ) {
						
						unset( $res[ 'Ad' ] );
					}
				}
			}
			
			/* Set Title */
			if ( isset( $hit[ '_source' ][ 'title' ] ) ) {
				
				$res[ 'title' ] = $hit[ '_source' ][ 'title' ];
				
				$res[ 'non_highlighted_title' ] = $hit[ '_source' ][ 'title' ];

			}
			if ( ! empty( $hit[ 'highlight' ][ 'title' ][ 0 ] ) ) { // highlighted terms exit in the title
			
				$res[ 'title' ] = $hit[ 'highlight' ][ 'title' ][ 0 ];
				
			}

			/* Set Excerpt */
			/*
			 * First try to get the term highlighted body will retreive up to 100 characters.
			 *
			 *  If highlighted terms don't exist then grab the first 100 characters of the body
			 *
			 *  If the body is empty ( highly unlikely ) then try using the excerpt
			 *
			 *  If none of these catch ( even less likely ) then provide a default string
			 *
			 */
			if ( !empty( $hit[ 'highlight' ][ 'body' ] ) ) { // use the highlighted body term text
				
				$res[ 'excerpt' ] = $hit[ 'highlight' ][ 'body' ][ 0 ];
				
			} else if ( !empty( $hit[ 'highlight' ][ 'excerpt' ] ) ) {
				
				$res[ 'excerpt' ] = $hit[ 'highlight' ][ 'excerpt' ][ 0 ];
				
			} else if ( empty( $hit[ 'highlight' ][ 'body' ] ) ) { // If highlighted terms don't exist
				
				if ( !empty( $hit[ '_source' ][ 'body' ] ) ) { // check for body text and if it exists get the first 100 chars
					
					$res[ 'excerpt' ] = substr( $hit[ '_source' ][ 'body' ], 0, 200 );
					
				} else if ( !empty( $hit[ '_source' ][ 'excerpt' ] ) ) { // highlight and body don't exist so try using the excerpt
					
					$res[ 'excerpt' ] = $hit[ '_source' ][ 'excerpt' ];
					
				} else { // Default text
					
					$res[ 'excerpt' ] = 'A Driving article for you to enjoy!';
					
				}
			}
			if ( isset( $hit[ '_source' ] ) && ! empty( $hit[ '_source' ] ) ) {

				/* Set Publish Date */
				$res[ 'publishdate_unix_timestamp' ]  = isset( $hit[ '_source' ][ 'pubdate' ] ) ? $hit[ '_source' ][ 'pubdate' ] : '';
				
				/* Set Modification Date */
				$res[ 'lastmodified_unix_timestamp' ] = isset( $hit[ '_source' ][ 'lastmodified' ] ) ? $hit[ '_source' ][ 'lastmodified' ] : '';
				
				/* Set Distributor */
				$res[ 'distributor' ]                 = isset( $hit[ '_source' ][ 'distributor' ] ) ? $hit[ '_source' ][ 'distributor' ] : '';
				
				/* Set Thumbnail */
				$res[ 'thumbnail' ]                   = isset( $hit[ '_source' ][ 'thumbnail' ] ) ? $hit[ '_source' ][ 'thumbnail' ] : '';
				
				/* Set Advertorial */
				$res[ 'advertorial' ]                 = isset( $hit[ '_source' ][ 'advertorial' ] ) ? $hit[ '_source' ][ 'advertorial' ] : '';
				
				/* Set Attached Media */
				$res[ 'attachedmedia' ]               = isset( $hit[ '_source' ][ 'attachedmedia' ] ) ? $hit[ '_source' ][ 'attachedmedia' ] : ''; // is an array of attached medias
				
				/* Set Body Style */
				$res[ 'bodystyle' ]                   = isset( $hit[ '_source' ][ 'bodystyle' ] ) ? $hit[ '_source' ][ 'bodystyle' ] : ''; // is an array
				
				/* Set Classification */
				$res[ 'classification' ]              = isset( $hit[ '_source' ][ 'classification' ] ) ? $hit[ '_source' ][ 'classification' ] : ''; // is an array
				
				/* Set Tags */
				$res[ 'tags' ]                        = isset( $hit[ '_source' ][ 'tags' ] ) ? $hit[ '_source' ][ 'tags' ] : ''; // is an array of tags
				
				/* Set Make */
				$res[ 'make' ]                        = isset( $hit[ '_source' ][ 'make' ] ) ? $hit[ '_source' ][ 'make' ] : ''; // is an array
				
				/* Set Model */
				$res[ 'model' ]                       = isset( $hit[ '_source' ][ 'model' ] ) ? $hit[ '_source' ][ 'model' ] : ''; // is an array
				
				/* Set Make / Model */
				$res[ 'makemodel' ]                   = isset( $hit[ '_source' ][ 'makemodel' ] ) ? $hit[ '_source' ][ 'makemodel' ] : ''; // is an array
				
				/* Set category */
				$res[ 'category' ]                    = isset( $hit[ '_source' ][ 'category' ] ) ? $hit[ '_source' ][ 'category' ] : ''; // is an array
				
				/* Set Sub Category */
				$res[ 'subcategory' ]                 = isset( $hit[ '_source' ][ 'subcategory' ] ) ? $hit[ '_source' ][ 'subcategory' ] : ''; // is an array
				
				$res[ 'featuredlogo' ]                = isset( $hit[ '_source' ][ 'featuredlogo' ] ) ? $hit[ '_source' ][ 'featuredlogo' ] : '';
			
				/* Set Author */
				if ( isset( $hit[ '_source' ][ 'author' ] ) && empty( $hit[ '_source' ][ 'author' ][ 'byline' ] ) ) {
					
					$author = $this->pm_get_author_gravatar_data( $hit[ '_source' ][ 'author' ][ 'authorid' ], false );
					
					if ( $author[ 'last_name' ] === '' ) { // and author could not be found that matched the ID in Elastic Search
						
						$author[ 'name' ] = 'Driving.ca';
						
					}
					
					$res[ 'author' ] = $author[ 'name' ];
					
				} else {
					
					$res[ 'author' ] = '';
					
				}
				
				/* Set URL */
				$res[ 'url' ]    = isset( $hit[ '_source' ][ 'url' ] ) ? $hit[ '_source' ][ 'url' ] : ''; // is an array
				
				/*Compare results fields*/
				$res[ 'styleid' ] = isset( $hit[ '_source' ][ 'styleid' ] ) ? $hit[ '_source' ][ 'styleid' ] : '';
				$res[ 'year' ] = isset( $hit[ '_source' ][ 'year' ] ) ? $hit[ '_source' ][ 'year' ] : '';
				$res[ 'msrp' ] = isset( $hit[ '_source' ][ 'msrp' ] ) ? $hit[ '_source' ][ 'msrp' ] : '';
				$res[ 'stylename' ] = isset( $hit[ '_source' ][ 'stylename' ] ) ? $hit[ '_source' ][ 'stylename' ] : '';
				$res[ 'stylenamewotrim' ] = isset( $hit[ '_source' ][ 'stylenamewotrim' ] ) ? $hit[ '_source' ][ 'stylenamewotrim' ] : '';
				$res[ 'trim' ] = isset( $hit[ '_source' ][ 'trim' ] ) ? $hit[ '_source' ][ 'trim' ] : '';
				$res[ 'fueleconcity' ] = isset( $hit[ '_source' ][ 'fueleconcity' ] ) ? $hit[ '_source' ][ 'fueleconcity' ] : '';
				$res[ 'fueleconhwy' ] = isset( $hit[ '_source' ][ 'fueleconhwy' ] ) ? $hit[ '_source' ][ 'fueleconhwy' ] : '';
				$res[ 'jpg' ] = isset( $hit[ '_source' ][ 'jpg' ] ) ? $hit[ '_source' ][ 'jpg' ] : '';
			}

			$array_results[] = $res;
			
			$i++;
			
		}
		
		// Display purposes the number of the result the query starts from: $results_start and the end number: $results_end. 
		// Add one to the values to adjust for the elastic search zero-based indexing
		$results_start = ( $from_page + 1 );
		$results_end   = ( ( $results_start + $limit ) - 1 );
		$results       = array(
			'json' => json_decode( $this->json ),
			'terms' => $terms,
			'count' => $count,
			'from' => $this->Search_Integer_Values->get_from(),
			'results_start' => $results_start,
			'results_end' => $results_end,
			'number_of_records_per_page' => $limit,
			'mobile_limit' => $mobile_limit,
			'results' => $array_results,
			'original_terms' => $original_terms,
			'is_mobile' => $is_mobile,
			'facets' => $facets
		);

		return $results;
		
	}
	
	public function create_upate_term_or_post( $url, $data ) {
	
		if ( $data ) {
			
			$this->set_auth();
			
			$this->args[ 'method' ] = 'POST';
			
			$this->json             = json_encode( $data );
			
			$this->args[ 'body' ]   = $this->json;
			//return $this->args;
			return wp_remote_post( $url, $this->args );
			
		}
	}
	
	public function delete_term_or_post( $url ) {
	
		$this->set_auth();
		
		$this->args[ 'method' ] = 'DELETE';
		
		return wp_remote_post( $url, $this->args );
		
	}
	
	/**
	 * Display author posts link with first and last name ( always given priority to gravatar info, if not exists, then use the wordpress user info )
	 *
	 * @param int $author_id	author id
	 * @param bool $href		Does the name need a link
	 * @param bool $italics		Does the last name need italicized
	 * @param string $class		Customize class name
	 *
	 * @uses	get_author_posts_url()  Retrieve the author's post URL
	 * @uses	get_the_author_meta()  Retrieve the author's meta
	 * @uses	wpcom_vip_get_user_profile()  Gets the gravatar information
	 * @uses	drv_get_author_excerpt()	Gets author experpt
	 *
	 * @return	array of author information
	 * @author Vasu Kuppam
	 * @since 1.0.0
	 */
	protected function pm_get_author_gravatar_data( $author_id_or_email = null, $href = true, $italics = true, $class = 'author-name' ) {

		$author = array();
		
		if ( is_int( $author_id_or_email ) ) {
			
			$author_id    = $author_id_or_email;
			
			$author_email = get_the_author_meta( 'email', $author_id );
			
		} else {
			
			$author_email = $author_id_or_email;
			
			$author_obj   = get_user_by( 'email', $author_email );
			
			$author_id    = isset( $author_obj->ID ) ? $author_obj->ID : '';
			
		}
		
		$key       = 'drv_get_author_posts_link_' . $author_id_or_email . '_' . $href . '_' . $italics;
		
		$transient = get_transient( $key );
		
		if ( !empty( $transient ) ) {
			
			return $transient;
			
		} else {
			//Get profile information from gravatar
			$profile                 = wpcom_vip_get_user_profile( $author_email );
			$author[ 'description' ] = get_the_author_meta( 'description', $author_id );
			
			if ( !empty( $profile ) ) {
				
				if ( !empty( $profile[ 'name' ] ) ) {
					
					$author[ 'first_name' ] = ucwords( esc_html( $profile[ 'name' ][ 'givenName' ] ) );
					
					$author[ 'last_name' ]  = ucwords( esc_html( $profile[ 'name' ][ 'familyName' ] ) );
					
					if ( $italics ) {
						
						$author[ 'name' ] = $author[ 'first_name' ] . ' <span>' . $author[ 'last_name' ] . '</span>';
						
					} else {
						
						$author[ 'name' ] = $author[ 'first_name' ] . ' ' . $author[ 'last_name' ];
						
					}
					
				} else {
					
					$author[ 'name' ] = $author[ 'first_name' ] = ucwords( esc_html( $profile[ 'displayName' ] ) );
					
				}
				
				if ( !empty( $profile[ 'aboutMe' ] ) ) {
					
					$author_description = $profile[ 'aboutMe' ];
					
					if ( strlen( $author_description ) > 200 ) //Get custom excerpt if content length is > 200 characters
						$author_description = drv_get_author_excerpt( $author_description, get_author_posts_url( $author_id ) );
					
					$author[ 'description' ] = preg_replace( '/@(\S+?)\b/', ' <a target="_blank" href="http://twitter.com/$1">@$1</a>', $author_description );
					
				}
				
				if ( !empty( $profile[ 'accounts' ] ) ) {
					
					foreach ( $profile[ 'accounts' ] as $accounts ) {
						
						$social[ $accounts[ 'shortname' ] . '-display' ] = $accounts[ 'display' ];
						
						$social[ $accounts[ 'shortname' ] ]              = $accounts[ 'url' ];
						
					}
					
					$author[ 'social' ] = $social;
				}
				
			} else {
				
				$author[ 'first_name' ] = ucwords( esc_html( get_the_author_meta( 'first_name', $author_id ) ) );
				$author[ 'last_name' ]  = ucwords( esc_html( get_the_author_meta( 'last_name', $author_id ) ) );
				
				if ( $italics ) {
					
					$author[ 'name' ] = $author[ 'first_name' ] . ' <span>' . $author[ 'last_name' ] . '</span>';
					
				} else {
					
					$author[ 'name' ] = $author[ 'first_name' ] . ' ' . $author[ 'last_name' ];
					
				}
			}
			
			if ( $href )
				$author[ 'name' ] = '<a class="' . $class . '" href="' . get_author_posts_url( $author_id ) . '" rel="author" title="' . $author[ 'first_name' ] . ' ' . $author[ 'last_name' ] . '">' . $author[ 'name' ] . '</a>';
			
			set_transient( $key, $author, 30 * 60 );
			
			return $author;
			
		}
	}
	
	protected function trim_whitepace( $string ) {
	
		return preg_replace( '/\s+/', ' ', $string );
		
	}
	
	/*
	 * Verfiy index(es) exist and that they are assessible
	 * 
	 * @param:	$indexes	- ( Array )	Array of Elasticsearch index names as strings
	 * 
	 * @uses:	$this->set_auth()
	 * @uses:	$this->set_url()
	 * @uses:	wp_remote_get()
	 * @uses:	json_decode()
	 */
	public function verify_indexes( Array $indexes ) {
	
		/*
		 * Ensure configuration is setup
		 */
		$this->set_auth();
		$this->indexes_status = array();
		
		foreach ( $indexes as $idx ) {
			
			$current_index_status = array();
			$this->set_url( 'verify', $idx );
			$status       = wp_remote_post( $this->url, $this->args );
			if( ! is_wp_error( $status ) ) {
				$index_status = json_decode( $status[ 'body' ] );
				
				if ( $index_status->ok ) {
					
					$current_index_status[ 'status' ] = $index_status->ok;
					
				} else {
					
					$current_index_status[ 'status' ] = 0;
					
				}
				
				$response = $status[ 'response' ];
				
				if ( '200' == $response[ 'code' ] && 'OK' == $response[ 'message' ] ) {
					
					$current_index_status[ 'response' ] = 1;
					
				} else {
					
					$current_index_status[ 'response' ] = 0;
					
				}
				
				$this->indexes_status[ $idx ] = $current_index_status;
			}
			
		}
		
		return $this->indexes_status;
	}
}