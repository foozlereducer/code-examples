<?php
/*
Plugin Name: Postmedia Elastic Search
Plugin URI: http://
Description: Connect and search on remote elastic search service
Version: 1.0
Author: Steve Browning
*/

require_once( 'class-loader.php' );

/**
 * Class PM_ElasticSearch
 * 
 * @author SBrowning
 * @version 1.0.0
 * 
 * Methods are not prefixed with pm_ as they are protected in the class scope
 */
class pm_Elasticsearch
{
	protected $ES;
	protected $valid;
	protected $hook = 'es_search_options';
	
	/**
	 * Singleton to have a single ElasticSearch Instance
	 */
	public function &init() {
	
		static $instance = false;
		
		if ( !$instance ) {
			$instance = new pm_Elasticsearch;
		}
		
		return $instance;
	}
	
	public function __construct() {
	
		$this->valid      = array();
		$this->ES         = null;
		
		add_action( 'admin_menu', array(
			 $this,
			'elastic_search_menu' 
		) );
		add_action( 'wp_footer', array(
			 $this,
			'register_site_search_scripts' 
		) );
		add_action( 'admin_init', array(
			 $this,
			'page_init' 
		) );
		add_action( 'wp_ajax_nopriv_do_search', array(
			 $this,
			'do_search_callback' 
		) );
		add_action( 'wp_ajax_do_search', array(
			 $this,
			'do_search_callback' 
		) );
		add_action( 'save_post', array(
			 $this,
			'create_update_post' 
		) );
		add_action( 'delete_post', array(
			 $this,
			'delete_post' 
		) );
		//add_action( 'created_term', array(
		//	 $this,
		//	'manage_terms' 
		//), 0 ); // Hook to the creation of a new term within the make, bodystyle, classification, and model years taxonomies
		//add_action( 'delete_term_taxonomy', array(
		//	 $this,
		//	'manage_terms' 
		//), 0 ); // Hook to the deletion of a term within the make, bodystyle, classification, and model years taxonomies
		//add_filter( 'edited_terms', array(
		//	 $this,
		//	'manage_terms' 
		//), 0 );
		
		
	}
	
	/**
	 * Adds the Elastic Search menu item to the settings dashboard
	 *
	 * @uses add_options_page
	 * @since 1.0.0
	 * @author Steve Browning
	 */
	public function elastic_search_menu() {
	
		$page_hook_suffix = add_options_page( 'Search Configuration', 'Elastic Search', 'manage_options', 'es_search_configuration', array(
			 $this,
			'create_es_search_config_page' 
		) );
		
		add_action( 'admin_print_scripts-' . $page_hook_suffix, array(
			 $this,
			'register_admin_scripts_and_styles' 
		) );
		
	}
	
	
	/**
	 * Registers and enqueue site-wide JS that will be used on most site pages that include elasticsearch
	 * 
	 * @uses wp_register_script()
	 * @uses wp_enqueue_script()
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	public function register_site_search_scripts() {
		
		wp_enqueue_script( 'jquery-form' );
		wp_register_script( 'pm_search', ES_SEARCH_JS_URL . 'search.js' ); // register the browser history plugin
		wp_enqueue_script( 'pm_search', array(
			 'jquery' 
		), '2.0.0', true );
		wp_localize_script( 'pm_search', 'cat_scat', $this->get_categories_names() );
		wp_localize_script( 'pm_search', 'taxonomy_obj', $this->drv_get_queried_object() );
		wp_localize_script( 'pm_search', 'PM_Search', array(
			 'ajaxurl' => admin_url( 'admin-ajax.php' ) 
		) );
		
		wp_register_script( 'native-history', ES_SEARCH_JS_URL . 'native-history.js' ); // register the browser history plugin
		wp_enqueue_script( 'native-history' );
		
		wp_register_style( 'pm_es_pagination_css', ES_SEARCH_CSS_URL . 'pm_es_pagination.css' ); // register the pagination styles
		wp_enqueue_style( 'pm_es_pagination_css' );
	}
	
	/**
	 * Theme prefix function
	 *
	 * @uses:	 jetpack_is_mobile()
	 *
	 * @author:	 Edward de Groot
	 */
	protected function drv_smrt_theme_prefix() {
	
		static $prefix = null;
		
		// only need to run this once
		if ( empty( $prefix ) ) {
			$prefix = ( jetpack_is_mobile() ) ? 'mob' : 'des';
		}
		
		return $prefix;
	}
	
	protected function drv_get_queried_object() {
	
		$obj  = get_queried_object();
		$data = array();
		if ( ! empty( $obj ) && isset( $obj->term_id ) ) {
			
			$ancestors = $this->get_ancestors( $obj->term_id, $obj->taxonomy );
		
			if ( ! empty( $ancestors ) ) {
				$data[ 'parent' ] = esc_html( $ancestors[ 0 ]->name );
				if ( count( $ancestors ) > 1 ) {
					$data[ 'child' ] = esc_html( $ancestors[ 1 ]->name );
				}
			}
		}

		return $data;
	}
	
	/**
	 * Gets the current categories names ( category and subcategory ) 
	 *
	 * @uses:	 $this->get_all_current_categories()
	 *
	 * @author:	 Steve Browning
	 */
	
	protected function get_categories_names() {
	
		global $post;
		
		$data               = array();
		$categories_data    = $this->get_all_current_categories();
		if( ! empty( $categories_data ) ) {
			$data[ 'category' ] = esc_html( $categories_data[ 0 ]->name );
			if ( $categories_data[ 1 ] ) {
				$data[ 'subcategory' ] = esc_html( $categories_data[ 1 ]->name );
			} else {
				$data[ 'subcategory' ] = "";
			}
			
			if ( empty( $data[ 'category' ] ) ) {
				$data[ 'category' ] = esc_html( $post->post_name );
			}
		}
		else {
			$data[ 'category' ] = esc_html( $post->post_name );
			$data[ 'subcategory' ] = "";
		}
		$data[ 'post' ] = $post;
		
		
		return $data;
	}
	
	/**
	 * Gets the raw category data for the category and subcategory 
	 * returns mutidimensional array of: 
	 * ( term_id, name, slug, term_group, term_taxonmy_id, taxonomy, description, parent_id, and count )
	 *
	 * @uses:	get_queried_object()()
	 * @uses:	is_category() 
	 * @uses:	is_single()
	 * @uses:	wp_get_post_categories( $obj->ID )
	 * @uses:	$this->get_ancestors( $cat_id )
	 * 
	 * @author:	Vasu Kappam
	 */
	
	protected function get_all_current_categories() {
	
		$obj  = get_queried_object();
		$term = array();
		
		if ( is_category() ) {
			$cat_id = $obj->term_id;
			$term   = $this->get_ancestors( $cat_id );
		} else if ( is_single() ) {
			$cats            = array();
			$post_categories = wp_get_post_categories( $obj->ID );
			
			foreach ( $post_categories as $c ) {
				$cat    = get_category( $c );
				$cats[] = array(
					 'id' => $cat->cat_ID 
				);
			}
			
			$category_array = $cats;
			
			if ( !empty( $category_array ) ) {
				$cat_id = $category_array[ 0 ][ 'id' ];
			}
			
			if ( !empty( $cat_id ) ) {
				$term = $this->get_ancestors( $cat_id );
			}
		}
		
		return $term;
	}
	
	protected function get_ancestors( $term_id, $taxonomy = 'category' ) {
	
		$term = array();
		
		$ancestors = get_ancestors( $term_id, $taxonomy );
		if ( !empty( $ancestors ) ) {
			$ancestor_array = array_reverse( $ancestors );
			
			foreach ( $ancestor_array as $key => $cat ) {
				$term[] = wpcom_vip_get_term_by( 'id', $cat, $taxonomy );
			}
		}
		$term[] = wpcom_vip_get_term_by( 'id', $term_id, $taxonomy );
		
		return $term;
	}
	
	
	/**
	 * Elastic Search callback method ( via AJAX call )
	 * 
	 * @uses:	$this->clean_value()					- returns sanitized value
	 * @uses:	get_option()							- returns wordpress options
	 * @uses:	empty()
	 * @uses:	is_array()
	 * @uses:	$this->drv_smrt_theme_prefix()			- returns 'mob_' or 'des_' ; the theme prefixes
	 * @uses:	file_exists()
	 * @uses:	new pm_Query_String_Values()				- string values business object
	 * @uses:	new pm_Search_Integer_Values()				- integer values business object
	 * @uses:	new pm_Search_Bool_Values()				- bool values business object
	 * @uses:	new pm_Elastic_Search()					- Elastic search object
	 * @uses:	Elastic_Search->get_json_search_string()- perform JSON search
	 * @uses:	json_encode()							- encode results as JSON and return to calling JS
	 * @uses:	die()									- ensure the method finishes
	 * 
	 *  @author:	 Steve Browning
	 */
	public function do_search_callback() {
	
		header( 'Content-Type: application/json' );
		
		$terms = $this->clean_value( $_POST[ "terms" ], $strip_tags = 1 );
		
		$options = get_option( $this->hook );
		
		// Even though these options have been sanitized in the validate() method clean each
		// value ( santize, typecast, transform doubles, floats and negative value to positive integers )
		$protocols_and_ports = array(
			 'http' => $this->clean_value( $options[ 'http_port' ], 1, 'integer' ),
			'https' => $this->clean_value( $options[ 'https_port' ], 1, 'integer' ) 
		);
		
		$es_index                                    = $this->clean_value( $_POST[ 'es_index' ] );
		$from_page                                   = $this->clean_value( $_POST[ 'from_page' ], $strip_tags = 1, 'integer' ) - 1; // adjust for the zero-based index;
		$host                                        = $this->clean_value( $options[ 'host' ], $strip_tags = 1 );
		$username                                    = $this->clean_value( $options[ 'username' ], $strip_tags = 1 );
		$password                                    = $this->clean_value( $options[ 'password' ], $strip_tags = 1 );
		$indexes                                     = array_map( 'esc_attr', explode( ',', $this->clean_value( $options[ 'verify_indexes' ], $strip_tags = 1 ) ) ); // define multiple indexes as an associative array
		$limit                                       = $this->clean_value( $options[ 'num_of_results_per_page' ], $strip_tags = 1, 'integer' );
		$hits                                        = $this->clean_value( $_POST[ 'hits' ], $strip_tags = 1, 'integer' );
		$default_operator                            = $this->clean_value( $options[ 'default_search_operator' ], $strip_tags = 1 );
		$media_type_field                            = $this->clean_value( $options[ 'media_type_field' ], $strip_tags = 1 );
		$mobile_num_of_results_per_segment_before_ad = $this->clean_value( $options[ 'mobile_num_of_results_per_segment_before_ad' ], $strip_tags = 1, 'integer' );
		
		$un_clean_filters = $_POST[ 'filters' ];

		// Sanitize $_POST string values and store in a parameter object
		// filter the sort_by field data to match the proper fields in the Elastic Index
		
		$filters = apply_filters( 'pn_apply_adapted_filters', $un_clean_filters );
		
		$adapted_filters = $filters['adapted_filters'];
		
		if ( is_array( $adapted_filters ) && ! empty( $adapted_filters['search'] ) ) { // If filters exist then add them
				
				foreach ( $adapted_filters['search']['key_value'] as $filter_key => $filter_array ) {
					
					if( array_key_exists( 'makesearch', $filter_array ) ) {
						
						$adapted_filters['search']['key_value'][$filter_key]['make.makesearch'] = $filter_array['makesearch'];
						
						unset( $adapted_filters['search']['key_value'][$filter_key]['makesearch'] );
					}
					
					if( array_key_exists( 'modelsearch', $filter_array ) ) {
						
						$adapted_filters['search']['key_value'][$filter_key]['model.modelsearch'] = $filter_array['modelsearch'];
						
						unset( $adapted_filters['search']['key_value'][$filter_key]['modelsearch'] );
					}
				}
		}

		$sort_by = $filters['sb'];
		
		$match_all_query = $this->clean_value( $un_clean_filters['maq'], $strip_tags = 1 );

		// Setup mobile loading
		$mobile_limit	= 6;
		$mobile_size	= 6;

		if ( 'mob' == $this->drv_smrt_theme_prefix() ) {
			
			$mobile_size  = $this->clean_value( $options[ 'mobile_total_results_per_segment' ], $strip_tags = 1, 'integer' );
			$mobile_limit = $mobile_size;
			
			if ( 0 < $from_page ) { // if not on the first mobile search page then reset the mobile size as 'load more' has been selected
				$mobile_size = $mobile_size * ( $from_page + 1 );
				
				if ( ( $hits - $mobile_size ) < $mobile_limit ) { // If on the last possible page get the correct mobile size
					if ( ( $hits / $mobile_size ) % $mobile_size < $mobile_limit ) {
						$mobile_size = $hits;
					}
				}
				
				$from_page = 0;
			}
		}
		
		$filter_cache = isset( $_POST[ 'filter_cache' ] ) ? $_POST[ 'filter_cache' ] : 1;

		if ( file_exists( ES_SEARCH_DIR . 'class-pm-elastic-search.php' ) ) {
			require_once( ES_SEARCH_DIR . 'class-pm-elastic-search.php' );
		} else {
			echo 'Elastic plugin class code did not load.';
			exit;
		}
		
		$template_prefix = $this->drv_smrt_theme_prefix();
		
		// Instantiate the Query String Values Objects. All filter and sort parameters are optional
		// The value objects sanitize, escape, strip tags and typecasts all data that is stored within them
		
		// string values
		$QS = new pm_Query_String_Values( $host, $username, $password, $indexes, $protocols_and_ports, $_POST[ 'terms' ], $adapted_filters, $default_operator, $sort_by, $template_prefix );
		
		// integer values
		$SI = new pm_Search_Integer_Values( $from_page, $limit, $mobile_size, $mobile_num_of_results_per_segment_before_ad, $template_prefix, $mobile_limit );
		
		// bool values
		$SB = new pm_Search_Bool_Values( $filter_cache, $match_all_query );
		
		/*
		 * Instantiate the Post Media specific cloud Elasticsearch object to search, update and delete search data
		 *
		 * @params	$QS		- Query String Value object		- Has string values required for the Elastisearch queries and connections
		 * @params	$SB		- Search Bool Value object		- Has bool values required for the Elastisearch queries and connections
		 * @params	$SI		- Search Integer Value object	- Has integer values required for the Elastisearch queries and connections
		 */
		$ES = new pm_Elastic_Search( $QS, $SB, $SI );
		
		// Pass the parameter objects into the search function
		$results = $ES->get_json_search_string( NULL, $es_index );
		
		if( ! is_wp_error( $results ) ) {
			wp_send_json( $results );
		}
	}
	
	/**
	 * Registers and enqueue admin CSS and Script that load only when the es_search settings menu is rendered
	 *
	 * @uses wp_register_style()
	 * @uses wp_enqueue_style()
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	public function register_admin_scripts_and_styles() {
	
		wp_register_style( 'pm_search_plugin_css', ES_SEARCH_CSS_URL . 'pm_search_plugin.css' ); // register the plugin styles
		wp_enqueue_style( 'pm_search_plugin_css' );
	}
	
	/**
	 * Render the Admin Page form
	 * 
	 * @uses settings_fields()
	 * @uses do_settings_sections()
	 * @uses $this->get_object_index_status()
	 * @uses submit_button()
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	public function create_es_search_config_page() {
		
?>
		<div class='options_page'>
		    <form id='es_configuration' method="post" action="options.php">
		    	<div class='option_wrapper'>
			        <?php
		$options = get_option( $this->hook );
		
		// This prints out all hidden setting fields
		settings_fields( $this->hook );
		
		do_settings_sections( 'es_search_configuration' );
?>
				</div>
		
			<?php
		$this->get_object_index_status();
?>
				<div class='submit_wrapper'>
		    <?php
		submit_button();
?>
		        </div>
		        
		    </form>
		</div>
		<?php
	}
	
	/**
	 * @uses:	new pm_Query_String_Values()
	 * @uses:	new pm_Search_Integer_Values()
	 * @uses:	new pm_Search_Bool_Values()
	 * @uses:	new pm_Elastic_Search()
	 * @uses:	get_option()
	 * 
	 * @return 	pm_Elastic_Search instance
	 * 
	 * @author:	 Steve Browning
	 */
	public function &get_ES_instance() {
	
		static $ES_instance = false;
		
		if ( !$ES_instance ) {
			
			$options                        = get_option( $this->hook );
			$host                           = $options[ 'host' ];
			$username                       = $options[ 'username' ];
			$password                       = $options[ 'password' ];
			$indexes                        = explode( ',', $options[ 'verify_indexes' ] );
			$index                          = $indexes[ 0 ];
			$protocols_and_ports            = array();
			$protocols_and_ports[ 'http' ]  = $options[ 'http_port' ];
			$protocols_and_ports[ 'https' ] = $options[ 'https_port' ];
			$filter_cache                   = 'true';
			
			$status = true;
			
			if ( !$host || !$protocols_and_ports[ 'http' ] || !$protocols_and_ports[ 'https' ] || !$username || !$password ) {
				$status = false;
			}
			
			if ( $status == true ) {
				require_once( 'class-pm-elastic-search.php' );
				
				$QS = new pm_Query_String_Values( $host, $username, $password, $index, $protocols_and_ports );
				
				$SI = new pm_Search_Integer_Values( $from = 0, $limit = 20 );
				
				// Normalize $_Post bool values and store in a parameter object
				$SB = new pm_Search_Bool_Values( $filter_cache );
				
				/*
				 * Instantiate the Post Media specific cloud Elasticsearch object to search, update and delete search data
				 *
				 * @params	$protocol		- either 'http' or 'https'
				 * @params	$host			- the cloud host domain
				 * @params	$port			- the port specific to each ES index
				 * @params	$username		- User associated with our ES account
				 * @params	$password		- Password associated with our ES account
				 */
				$ES_instance = new pm_Elastic_Search( $QS, $SB, $SI );
			}
			
			return $ES_instance;
		}
	}
	
	/**
	 * Create, Edit, Delete; Manage Terms
	 * 
	 * Constructs the proper url to the elastic index and determines 
	 * if an new, edited or deleted action occurs. It then calls the 
	 * Elasticsearch class with the appropriate action and changes 
	 * the appropriate taxonomy (term) data in the Elastic Search index
	 *
	 * @uses $this->clean_value()
	 * @uses get_option()
	 * @uses new pm_Query_String_Values() 		- instantiated into $QS
	 * @uses new pm_Search_Integer_Values()	- instantiated into $SI 
	 * @uses Search_Bool_Values()			- instantiated into $SB 
	 * @uses new pm_Elastic_Search() 		- instantiated into $ES
	 * @uses $ES->get_index()
	 * @uses $ES->get_url()
	 * @uses $ES->create_upate_term_or_post()
	 * @uses $ES->delete_term_or_post()
	 * @uses explode()
	 * @uses get_term_by()
	 * @uses empty()
	 * @uses preg_match()
	 * @uses file_exists()
	 * 
	 * 
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	public function manage_terms() {
	
		$taxonomy = $this->clean_value( $_POST[ 'taxonomy' ], $strip_tags = 1 );
		$action   = $this->clean_value( $_POST[ 'action' ], $strip_tags = 1 );
		
		$options = get_option( $this->hook );
		
		// Even though these options have been sanitized in the validate() method clean each
		// value ( santize, typecast, transform doubles, floats and negative value to positive integers )
		$protocols_and_ports = array(
			 'http' => $this->clean_value( $options[ 'http_port' ], 1, 'integer' ),
			'https' => $this->clean_value( $options[ 'https_port' ], 1, 'integer' ) 
		);
		
		
		// Even though these options have been sanitized in the validate() method clean each
		// value ( santize, typecast, transform doubles, floats and negative value to positive integers )
		$protocols_and_ports = array(
			 'http' => $this->clean_value( $options[ 'http_port' ], 1, 'integer' ),
			'https' => $this->clean_value( $options[ 'https_port' ], 1, 'integer' ) 
		);
		
		$host       = $this->clean_value( $options[ 'host' ], $strip_tags = 1 );
		$username   = $this->clean_value( $options[ 'username' ], $strip_tags = 1 );
		$password   = $this->clean_value( $options[ 'password' ], $strip_tags = 1 );
		$indexes    = explode( ',', $this->clean_value( $options[ 'verify_indexes' ], $strip_tags = 1 ) ); // define multiple indexes as an associative array
		$log_active = (bool) $this->clean_value( $options[ 'log' ] );
		
		if ( file_exists( ES_SEARCH_DIR . 'class-pm-elastic-search.php' ) ) {
			require_once( ES_SEARCH_DIR . 'class-pm-elastic-search.php' );
		} else {
			echo 'Elastic plugin class code did not load.';
			exit;
		}
		
		// Instantiate the Query String Values Objects.
		// The value objects sanitize, escape, strip tags and typecasts all data that is stored within them
		
		// core values needed for make management
		$QS = new pm_Query_String_Values( $host, $username, $password, $indexes, $protocols_and_ports );
		
		// integer values just defaults to meet the Elastic object's required parameters
		$SI = new pm_Search_Integer_Values( 0, 0 );
		
		// bool values just defaults to meet the Elastic object's required parameters
		$SB = new pm_Search_Bool_Values();
		
		$ES = new pm_Elastic_Search( $QS, $SB, $SI );
		
		
		$index = $ES->get_index( $indexes, 'taxonomy' );
		$host  = $ES->get_url( 'write', $index );
		$slug  = $this->clean_value( $_POST[ 'slug' ], $strip_tags = 1 );
		
		switch ( $taxonomy ) {
			case 'make':
				$make         = array();
				$term         = get_term_by( 'slug', $slug, 'make' );
				$make[ 'id' ] = (int) $term->term_id;
				
				if ( !empty( $make[ 'id' ] ) ) { // url used for create / edit
					$url = "$host/term/" . $make[ 'id' ];
				} else { // url used for delete 
					$id  = $this->clean_value( $_POST[ 'tag_ID' ], $strip_tags = 1 );
					$url = "$host/term/" . $id;
				}
				
				// ensure that url contains /term and a "/" and an numberic id before creating, editing or deleting a document
				// this is needed to protect the non-existance of these things clearing the Elasticsearch index(es)
				$pattern = "/term\/([0-9]+)/";
				if ( preg_match( $pattern, $url ) ) {
					switch ( $action ) {
						case 'add-tag':
						case 'editedtag':
							
							$make[ 'display' ] = $this->clean_value( $_POST[ 'description' ], $strip_tags = 1 );
							
							if ( !empty( $_POST[ 'tag-name' ] ) ) { // create
								$make[ 'name' ] = $this->clean_value( $_POST[ 'tag-name' ], $strip_tags = 1 );
							} else { // edit
								$make[ 'name' ] = $this->clean_value( $_POST[ 'name' ], $strip_tags = 1 );
							}
							
							$make[ 'slug' ]      = $slug;
							$make[ 'term_type' ] = '';
							$make[ 'count' ]     = (int) $term->count;
							
							$is_parent = $this->clean_value( $_POST[ 'parent' ], $strip_tags = 1 );
							if ( 0 < $is_parent ) { // model
								$make[ 'term_type' ] = 'model';
							} else { // make
								$make[ 'term_type' ] = 'make';
							}
							
							// post to the Elastic index
							$response = $ES->create_upate_term_or_post( $url, $make );
							
							break;
						case 'delete-tag':
							$response = $ES->delete_term_or_post( $url );
							break;
					}
				} else {
					
				}
				break;
			case 'bodystyle':
				
				$bodystyle         = array();
				$term              = get_term_by( 'slug', $slug, 'bodystyle' );
				$bodystyle[ 'id' ] = (int) $term->term_id;;
				
				if ( !empty( $bodystyle[ 'id' ] ) ) { // url used for create / edit
					$url = "$host/term/" . $bodystyle[ 'id' ];
				} else { // url used for delete 
					$id  = $this->clean_value( $_POST[ 'tag_ID' ], $strip_tags = 1 );
					$url = "$host/term/" . $id;
				}
				
				// ensure that url contains /term and a "/" and an numberic id before creating, editing or deleting a document
				$pattern = "/term\/([0-9]+)/";
				if ( preg_match( $pattern, $url ) ) {
					switch ( $action ) {
						case 'add-tag':
						case 'editedtag':
							
							$bodystyle[ 'display' ] = $this->clean_value( $_POST[ 'description' ], $strip_tags = 1 );
							
							if ( !empty( $_POST[ 'tag-name' ] ) ) { // create
								$bodystyle[ 'name' ] = $this->clean_value( $_POST[ 'tag-name' ], $strip_tags = 1 );
							} else { // edit
								$bodystyle[ 'name' ] = $this->clean_value( $_POST[ 'name' ], $strip_tags = 1 );
							}
							
							$bodystyle[ 'slug' ]      = 'bodystyle/' . $slug;
							$bodystyle[ 'term_type' ] = 'bodystyle';
							$bodystyle[ 'count' ]     = (int) $term->count;
							
							// post to the Elastic index
							$response = $ES->create_upate_term_or_post( $url, $bodystyle );
							
							break;
						case 'delete-tag':
							$response = $ES->delete_term_or_post( $url );
							break;
					}
				}
				break;
		}
	}
	
	/**
	 * Delete Posts
	 *
	 * Constructs the proper url to the elastic index and 
	 * issues a restful delete command
	 *
	 * @uses $this->clean_value()
	 * @uses get_option()
	 * @uses current_user_can()
	 * @uses new pm_Query_String_Values() 		- instantiated into $QS
	 * @uses new pm_Search_Integer_Values()	- instantiated into $SI
	 * @uses Search_Bool_Values()			- instantiated into $SB
	 * @uses new pm_Elastic_Search() 		- instantiated into $ES
	 * @uses $ES->get_index()
	 * @uses $ES->get_url()
	 * @uses $ES->delete_term_or_post()
	 * @uses explode()
	 * @uses file_exists()
	 *
	 *
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	public function delete_post( $post_id ) {
		
		// Check permissions
		if ( 'page' == $_POST[ 'post_type' ] ) {
			if ( !current_user_can( 'delete_page', $post_id ) )
				return $post_id;
		} else {
			if ( !current_user_can( 'delete_post', $post_id ) )
				return $post_id;
		}
		
		$options = get_option( $this->hook );
		
		// Even though these options have been sanitized in the validate() method clean each
		// value ( santize, typecast, transform doubles, floats and negative value to positive integers )
		$protocols_and_ports = array(
			 'http' => $this->clean_value( $options[ 'http_port' ], 1, 'integer' ),
			'https' => $this->clean_value( $options[ 'https_port' ], 1, 'integer' ) 
		);
		
		
		$host     = $this->clean_value( $options[ 'host' ], $strip_tags = 1 );
		$username = $this->clean_value( $options[ 'username' ], $strip_tags = 1 );
		$password = $this->clean_value( $options[ 'password' ], $strip_tags = 1 );
		$indexes  = explode( ',', $this->clean_value( $options[ 'verify_indexes' ], $strip_tags = 1 ) ); // define multiple indexes as an associative array
		
		if ( file_exists( ES_SEARCH_DIR . 'class-pm-elastic-search.php' ) ) {
			require_once( ES_SEARCH_DIR . 'class-pm-elastic-search.php' );
		} else {
			echo 'Elastic plugin class code did not load.';
			exit;
		}
		
		// Instantiate the Query String Values Objects. All filter and sort parameters are optional
		// The value objects sanitize, escape, strip tags and typecasts all data that is stored within them
		
		// string values
		$QS = new pm_Query_String_Values( $host, $username, $password, $indexes, $protocols_and_ports );
		
		// integer values
		$SI = new pm_Search_Integer_Values( 0, 0 );
		
		// bool values
		$SB = new pm_Search_Bool_Values();
		
		/*
		 * Instantiate the Post Media specific cloud Elasticsearch object to search, update and delete search data
		 *
		 * @params	$QS		- Query String Value object		- Has string values required for the Elastisearch queries and connections
		 * @params	$SB		- Search Bool Value object		- Has bool values required for the Elastisearch queries and connections
		 * @params	$SI		- Search Integer Value object	- Has integer values required for the Elastisearch queries and connections
		 */
		$ES = new pm_Elastic_Search( $QS, $SB, $SI );
		
		$index = $ES->get_index( $indexes );
		
		$host = $ES->get_url( 'delete', $index );
		$url  = "$host/post/$post_id";
		
		$response = $ES->delete_term_or_post( $url );
		
	}
	
	/**
	 * Create or Update Posts
	 *
	 * Constructs the proper url to the elastic index and
	 * issues a restful delete command
	 *
	 * @uses wp_verify_nonce()
	 * @uses get_option()
	 * @uses current_user_can()
	 * @uses get_post_custom()
	 * @uses get_the_terms()
	 * @uses wp_get_post_tags()
	 * @uses get_category()
	 * @uses get_post()
	 * 
	 * @uses $this->clean_value()
	 * @uses $this->get_parent_or_child()
	 * @uses $this->get_value()
	 * @uses $this->get_thumbnail()
	 * 
	 * @uses new pm_Query_String_Values() 		- instantiated into $QS
	 * @uses new pm_Search_Integer_Values()	- instantiated into $SI
	 * @uses Search_Bool_Values()			- instantiated into $SB
	 * @uses new pm_Elastic_Search() 		- instantiated into $ES
	 * @uses $ES->get_index()
	 * @uses $ES->get_url()
	 * @uses $ES->create_upate_term_or_post()
	 * 
	 * @uses new DateTimeZone("GMT")		- instantiated into $GMT
	 * @uses new DateTime					- instantiated into $date
	 * @uses $date->getTimestamp()
	 * @uses explode()
	 * @uses unset()
	 * @uses file_exists()
	 *
	 *
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	public function create_update_post( $post_id ) {
		
		if ( isset( $_POST[ 'taxonomy_noncename' ] ) && ! wp_verify_nonce( $_POST[ 'taxonomy_noncename' ], 'taxonomy_category' ) ) {
			return $post_id;
		}
		
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// Check permissions
		if ( isset( $_POST[ 'post_type' ] ) && 'page' == $_POST[ 'post_type' ] ) {
			if ( !current_user_can( 'edit_page', $post_id ) )
				return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
		
		if( is_singular() ) {
			header( 'Content-Type: application/json' );
			//var_dump( get_post( $post_id ) );
			$post                   = array();
			$custom_fields          = get_post_custom();
			$post[ 'id' ]           = $this->get_value( 'ID', get_post( $post_id ) );
			$post[ 'title' ]        = $this->get_value( 'post_title', get_post( $post_id ) );
			$post[ 'excerpt' ]      = $this->get_value( 'post_excerpt', get_post( $post_id ) );
			$post[ 'body' ]         = $this->get_value( 'post_content', get_post( $post_id ) );
			$post[ 'modelyear' ]    = $this->get_value( 'name', get_the_terms( $post_id, 'model_year' ) );
			$post[ 'url' ]          = get_permalink( $post_id );
			$GMT                    = new DateTimeZone( "GMT" );
			$date                   = new DateTime( $this->get_value( 'post_date', get_post( $post_id ) ), $GMT ); // date object with time and GMT zimezone
			$post[ 'pubdate' ]      = $date->getTimestamp(); // create Unix Time Stamp
			$date                   = new DateTime( $this->get_value( 'post_modified_gmt', get_post( $post_id ) ) ); // already in GMT
			$post[ 'lastmodified' ] = $date->getTimestamp();
			$post[ 'distributor' ]  = $this->get_value( 'drv_distributor', $custom_fields );
			$post[ 'thumbnail' ]    = '';
			
			$advertorial = $this->get_value( 'drv_advertorial', $custom_fields );
			
			if ( 'on' == $advertorial ) {
				$post[ 'advertorial' ] = true;
			} else {
				$post[ 'advertorial' ] = false;
			}
			
			$attachedmedia = array();
			
			if ( 'on' === $this->get_value( 'drv_content_photogallery', $custom_fields ) && 'on' === $this->get_value( 'drv_content_video', $custom_fields ) ) {
				$attachedmedia[]     = 'PhotoGallery';
				$attachedmedia[]     = 'Video';
				$post[ 'thumbnail' ] = $this->get_thumbnail( $target = 'photogallery', $custom_fields, $post_id );
			} else if ( 'on' === $this->get_value( 'drv_content_video', $custom_fields ) ) {
				$attachedmedia[]     = 'Video';
				$post[ 'thumbnail' ] = $this->get_thumbnail( $target = 'featured_image', $custom_fields, $post_id );
			} else if ( 'on' === $this->get_value( 'drv_content_photogallery', $custom_fields ) ) {
				$attachedmedia[]     = 'PhotoGallery';
				$post[ 'thumbnail' ] = $this->get_thumbnail( $target = 'photogallery', $custom_fields, $post_id );
			} else {
				$attachedmedia[]     = null;
				$post[ 'thumbnail' ] = $this->get_thumbnail( $target = 'featured_image', $custom_fields, $post_id );
			}
			$post[ 'attachedmedia' ] = $attachedmedia;
			
			$bodystyle           = array();
			$bodystyle[]         = $this->get_value( 'name', get_the_terms( $post_id, 'bodystyle' ) );
			$post[ 'bodystyle' ] = $bodystyle;
			
			$classification           = array();
			$classification[]         = $this->get_value( 'name', get_the_terms( $post_id, 'classification' ) );
			$post[ 'classification' ] = $classification;
			
			$post[ 'tags' ] = $this->get_value( 'name', wp_get_post_tags( $post_id ) );
			
			// Query just 
			$post[ 'make' ] = $this->get_parent_or_child( 'makes', wp_get_object_terms( $post_id, 'make' ) );
			
			// The $models_makes holds both model and make data, 
			$models_makes        = $this->get_parent_or_child( 'models', wp_get_object_terms( $post_id, 'make' ) );
			$post[ 'makemodel' ] = ( isset( $models_makes[ 'makes_models' ] ) ) ? $models_makes[ 'makes_models' ] : ''; // grab the makes_models array from the queried make data
			unset( $models_makes[ 'makes_models' ] ); // remove the makes_models data from the array, leaving just the models
			$post[ 'model' ] = $models_makes;
			
			$category_id           = isset( $_POST['category'] ) ? (int) $_POST[ 'category' ] : '';
			
			if( ! empty( $category_id ) ) {
				$category_data         = $this->get_parent_or_child( 'category', get_category( $category_id ) );
				$post[ 'category' ]    = $category_data[ 'category' ];
				$post[ 'subcategory' ] = $category_data[ 'sub_category' ];
			}
			
			$special_section_data        = $this->get_parent_or_child( 'section', get_the_terms( $post_id, 'specialsection' ) );
			
			if( ! empty( $special_section_data ) ) {
				$post[ 'specialsection' ]    = $special_section_data[ 'section' ];
				$post[ 'specialsubsection' ] = $special_section_data[ 'sub_section' ];
			}
			
			$author[ 'authorid' ] = $this->get_value( 'post_author', get_post( $post_id ) );
			$author[ 'byline' ]   = $this->get_value( 'drv_byline', $custom_fields );
			$post[ 'author' ]     = $author;
			//$post['sp6contentid']		= false;
			
			
			$options = get_option( $this->hook );
			
			// Even though these options have been sanitized in the validate() method clean each
			// value ( santize, typecast, transform doubles, floats and negative value to positive integers )
			$protocols_and_ports = array(
				 'http' => $this->clean_value( $options[ 'http_port' ], 1, 'integer' ),
				'https' => $this->clean_value( $options[ 'https_port' ], 1, 'integer' ) 
			);
			
			
			$from_page                                   = $this->clean_value( $_POST[ 'from_page' ], $strip_tags = 1, 'integer' ) - 1; // adjust for the zero-based index;
			$host                                        = $this->clean_value( $options[ 'host' ], $strip_tags = 1 );
			$username                                    = $this->clean_value( $options[ 'username' ], $strip_tags = 1 );
			$password                                    = $this->clean_value( $options[ 'password' ], $strip_tags = 1 );
			$indexes                                     = explode( ',', $this->clean_value( $options[ 'verify_indexes' ], $strip_tags = 1 ) ); // define multiple indexes as an associative array
			$limit                                       = $this->clean_value( $options[ 'num_of_results_per_page' ], $strip_tags = 1, 'integer' );
			$hits                                        = $this->clean_value( $_POST[ 'hits' ], $strip_tags = 1, 'integer' );
			$default_operator                            = $this->clean_value( $options[ 'default_search_operator' ], $strip_tags = 1 );
			$media_type_field                            = $this->clean_value( $options[ 'media_type_field' ], $strip_tags = 1 );
			$mobile_num_of_results_per_segment_before_ad = $this->clean_value( $options[ 'mobile_num_of_results_per_segment_before_ad' ], $strip_tags = 1, 'integer' );
			$log_active                                  = (bool) $this->clean_value( $options[ 'log' ] );
			
			if ( file_exists( ES_SEARCH_DIR . 'class-pm-elastic-search.php' ) ) {
				require_once( ES_SEARCH_DIR . 'class-pm-elastic-search.php' );
			} else {
				echo 'Elastic plugin class code did not load.';
				exit;
			}
			
			// Instantiate the Query String Values Objects. All filter and sort parameters are optional
			// The value objects sanitize, escape, strip tags and typecasts all data that is stored within them
			
			// string values
			$QS = new pm_Query_String_Values( $host, $username, $password, $indexes, $protocols_and_ports );
			
			// integer values
			$SI = new pm_Search_Integer_Values( 0, 0 );
			
			// bool values
			$SB = new pm_Search_Bool_Values();
			
			/*
			 * Instantiate the Post Media specific cloud Elasticsearch object to search, update and delete search data
			 *
			 * @params	$QS		- Query String Value object		- Has string values required for the Elastisearch queries and connections
			 * @params	$SB		- Search Bool Value object		- Has bool values required for the Elastisearch queries and connections
			 * @params	$SI		- Search Integer Value object	- Has integer values required for the Elastisearch queries and connections
			 */
			$ES       = new pm_Elastic_Search( $QS, $SB, $SI );
			$index    = $ES->get_index( $indexes );
			$host     = $ES->get_url( 'write', $index );
			$url      = "$host/post/$post_id";
			$response = $ES->create_upate_term_or_post( $url, $post );
		
		}
		
	}
	
	/**
	 * Get the appropriate thumbnail
	 *
	 * @uses wp_get_attachment_image_src()
	 * 
	 * @uses $this->get_value()
	 * 
	 * @uses preg_match()
	 * 
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	protected function get_thumbnail( $target = NULL, $custom_fields = NULL, $post_id = NULL ) {
	
		if ( !empty( $target ) ) {
			switch ( $target ) {
				case 'photogallery':
					if ( $this->get_value( 'drv_featured_photogallery', $custom_fields ) ) { // Use photogallery's first image
						
						// Retrieve the first gallery in the post
						$pattern = '/\=\"[0-9]*\"/'; // pattern to match the first id in a gallery
						preg_match( $pattern, $this->get_value( 'drv_featured_photogallery', $custom_fields ), $matches );
						$pattern = '/[0-9]+/';
						preg_match( $pattern, $matches[ 0 ], $matches ); // Use the returned match ie '="40124"' to get only the first image id
						$src_data = wp_get_attachment_image_src( $matches[ 0 ] ); // Uses the match; it will be the first image id and return the src url
						return $src_data[ 0 ]; // return the source field of the sources data
					}
					break;
				case 'featured_video':
					$video_url = $this->get_value( 'cdc_featured_video_image', $custom_fields );
					if ( $video_url ) {
						return $video_url;
					} else {
						return null;
					}
					break;
				case 'featured_image':
					$image          = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
					$featured_image = $image[ 0 ];
					return $featured_image;
					break;
			}
		}
		
	}
	
	/**
	 * Check for value in multi-dimentional array
	 *
	 * @uses is_array()
	 * @uses is_object()
	 * @uses array_key_exists()
	 *
	 * @returns true if value found; false if value is not found
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	protected function in_array_r( $needle, $haystack, $strict = false ) {
	
		if ( is_object( $haystack ) ) {
			if ( $needle == $haystack->$needle )
				return true;
		} else if ( is_array( $haystack ) ) {
			foreach ( $haystack as $item ) {
				if ( is_object( $haystack ) ) {
					if ( $needle == $item->needle ) {
						return true;
					}
				} else if ( ( $strict ? $item === $needle : $item == $needle ) || ( is_array( $item ) && array_key_exists( $needle, $haystack ) ) ) {
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Check Data for multi-dimensional array or object
	 *
	 * Extracts value or values from array or PHP standard object
	 * 
	 * @uses $this::in_array_r()
	 * @uses is_object()
	 * @uses array_key_exists()
	 *
	 * @returns true if value found; false if value is not found
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	protected function get_value( $target_key, $data ) {
	
		if ( $this::in_array_r( $target_key, $data ) ) { // Multi dimensional array / object
			if ( is_object( $data ) ) { // object
				return $data->$target_key;
			} else { // array 
				foreach ( $data[ $target_key ] as $values ) {
					if ( 0 == count( $data ) ) {
						return $values[ 0 ];
					} else {
						return $values;
					}
				}
			}
		} else if ( is_object( $data ) ) {
			return $data->$target_key;
		} else if ( ! empty( $data ) && is_object( $data[ 0 ] ) ) { // flat stdClass object
			if ( 1 == count( $data ) ) {
				return $data[ 0 ]->$target_key;
			} else {
				$values = array();
				foreach ( $data as $key ) {
					$values[] = $key->$target_key;
				}
				
				return $values;
			}
		} else {
			return null;
		}
	}
	
	/**
	 * Get the Category Child and/or Parent
	 *
	 * @uses get_term_by()
	 * 
	 * @returns Array of parent and child ( if one exits )
	 * 
	 * @author Steve Browning
	 * @version 1.0.0
	 */
	protected function get_parent_or_child( $target = 'makes', $data_array ) {
	
		$values       = array();
		$makes_models = array();
		
		switch ( $target ) {
			case 'makes':
				foreach ( $data_array as $key ) {
					if ( 0 == $key->parent ) { // Make if true
						$values[] = $key->name;
					}
				}
				break;
			case 'models':
				foreach ( $data_array as $key ) {
					if ( 0 != $key->parent ) { // Model if true
						$make           = get_term_by( 'id', $key->parent, 'make' ); // get the model's make object
						$make           = $make->name; // get the models
						$model          = $key->name;
						$makes_models[] = $make . ' ' . $model;
						$values[]       = $model;
					}
					
					$values[ 'makes_models' ] = $makes_models;
				}
				break;
			case 'section':
				if ( $data_array ) { // Special section already existed so process accourdingly
					foreach ( $data_array as $key ) {
						if ( 0 == $key->parent ) { // Section if true
							$values[ 'section' ]     = $key->name;
							$values[ 'sub_section' ] = null;
						} else if ( 0 != $key->parent ) { // Model if true
							$section                 = get_term_by( 'term_id', $key->parent, 'specialsection' ); // get the model's make object
							$section                 = $section->name; // get the models
							$sub_section             = $key->name;
							$values[ 'section' ]     = $section;
							$values[ 'sub_section' ] = $sub_section;
						}
					}
				} else { // Special Section does not exist so grab the id of posted 'specialsection' and determine the section and sub_section values
					$special_section_id = (int) $_POST[ 'specialsection' ];
					$section            = get_term_by( 'term_id', $special_section_id, 'specialsection' ); // Get the special section; not known if parent or child
					if ( 0 != $section->parent ) { // Child
						$values[ 'sub_section' ] = $section->name; // As it is known as a child it is a sub_section so set its' name
						$section                 = get_term_by( 'term_id', $section->parent, 'specialsection' ); // Get the parent object
						$values[ 'section' ]     = $section->name; // set the section
					} else { // Parent
						$values[ 'section' ]     = $section->name; // name is parent so parent value is set
						$values[ 'sub_section' ] = null; // No child so sub_section is false 
					}
				}
				break;
			case 'category':
				
				if ( $data_array ) { // Special section already existed so process accourdingly				
					if ( 0 == $data_array->parent ) { // Section if true
						$values[ 'category' ]     = $data_array->name;
						$values[ 'sub_category' ] = null;
					} else if ( 0 != $data_array->parent ) { // Model if true
						$category                 = get_term_by( 'term_id', $data_array->parent, 'category' ); // get the category's make object
						$category                 = $category->name; // get the category
						$sub_category             = $data_array->name;
						$values[ 'category' ]     = $category;
						$values[ 'sub_category' ] = $sub_category;
					}
				}
				break;
		}
		
		return $values;
	}
	
	/**
	 * Gets Elastic Service instance and verifies the indexes
	 * 
	 * Returns html widget that renders red or green CSS circles 
	 * and index names with success / failure status
	 * 
	 * @uses get_option()
	 * 
	 * @uses new Elastic_Search()		- instantiated into $this->ES
	 * @uses $this->get_ES_instance()
	 * @uses $this->ES->verify_indexes()
	 * 
	 * @uses is_bool()
	 * @uses explode()
	 * @uses print()
	 * 
	 * @return HTML widget that renders red or green CSS circles 
	 *         and index names with success / failure statuses
	 */
	public function get_object_index_status() {
		
		$options = get_option( $this->hook );
		
		$status   = true;
		/*
		 * Get instance of the Post Media specific cloud Elasticsearch object needed 
		 * for verifying the indexes and testing the Elastic Client
		 */
		$this->ES = $this->get_ES_instance();
		
		if ( is_bool( $this->ES ) ) { // ensure that $this->ES is Elastic Search Instance
			
			$status = false;
		}
		
		$status_html = '<div class="object-index-status">';
		
		if ( $status == true ) {
			
			try {
				
				$indexes = explode( ',', $options[ 'verify_indexes' ] ); // try exploding string into array, if array then multiple indexes defined
				
				if ( $indexes == false ) { // When explode indexes fails 
					$indexes[] = $options[ 'verify_indexes' ]; // then add null or a single value
				}
				
				$index_status = $this->ES->verify_indexes( $indexes );
				
				$valid_indexes = false;
				
				foreach ( $index_status as $key => $statuses ) {
					$status_html .= '<div class="status">';
					foreach ( $statuses as $inner_key => $status ) {
						switch ( $status ) {
							case 1:
								if ( 'status' == $inner_key ) {
									$status_html .= '<span class="success circle"></span><span><strong>' . $key . '</strong>- successfully initialized </span>';
								}
								if ( 'response' == $inner_key ) {
									$status_html .= '<span class="success circle"></span><span> index:<strong>' . $key . '</strong> provided a successful response</span>';
								}
								break;
							case 0:
								if ( 'status' == $inner_key ) {
									$status_html .= '<span class="failure circle"></span><span>failed to initialize:' . $key . '</span>';
								}
								if ( 'response' == $inner_key ) {
									$status_html .= '<span class="failure circle"></span><span>index ' . $key . ' failed to provide a response</span>';
								}
								break;
						}
					}
					$status_html .= '</div>';
				}
			}
			catch ( Exception $e ) {
				$status_html .= '<div class="status">';
				$status_html .= '<div class="status"><span class="failure circle"></span><span> Elastic Search Failed Initialization. Verify the config details.</span>';
				$status_html .= '<div class="status"><span class="failure circle"></span><span> Index(es) <strong>did not</strong> verify.  Verify the config details.</span>';
				$status_html .= '</div>';
			}
			
		} else {
			$status_html .= '<div class="status">';
			$status_html .= '<span class="failure circle"></span><span> Elastic Search Failed Initialization. Verify the config details.</span>';
			$status_html .= '<span class="failure circle"></span><span> Index(es) <strong>did not</strong> verify Verify the config details.</span>';
			$status_html .= '</div>';
		}
		$status_html .= '</div>';
		
		return print( $status_html );
	}
	
	/**
	 * Adds this HTML to the Elastic Search settings page
	 * @since 1.0.0
	 * @author Steve Browning
	 */
	public function print_section_info() {
	
		$html = "
				<p>
					This page is used to set-up your Elasticsearch connection details. Here is what is needed:
				</p>
				<ul class='es-field-descriptions'>
					<li>
						<strong>Host</strong>: (Required) Set the host name of your elasticsearch server ie. 'localhost' or for a cloud/remote service use my-remote-host.com.  
					</li>
					<li>
						<strong>HTTP Port</strong>: (Required) Set the port for the main endpoint of your elasticsearch server ie. '9200' 
					</li>
					<li>
						<strong>HTTPS Port</strong>:  Set the port for the main endpoint of your elasticsearch server ie. '9243' 
					</li>
					<li>
						<strong>Username</strong>: (Required) Set to the username you've either been given by your cloud service or your local host authentication
					</li>
					<li>
						<strong>Password</strong>: (Required) Set to the password you've either been given by your cloud service or your local host authentication
					</li>
					<li>
						<strong>Verify Indexes (Required)</strong>: Adding a list of indexes like 'driving, driving-posts' will verify the configuration on save changes
					</li>
					<li>
						<strong>Number Of Results Per Page</strong>: (Optional) Set the number of results that will show per page. Defaults to 10.
					</li>
					<li>
						<strong>Mobile Number of Results Before Ad</strong>: (Optional) Set the number of results before and ad shows on mobile devices. Defaults to 3.
					</li>
					<li>
						<strong>Mobile Total Results Per 'Load More' Segment</strong>: (Optional) Set the total numbe rof results per 'load more' page on mobile devices. Defaults to 6.
					<li>
						<strong>Default Search Operator</strong>: (Optional) Set search to this AND that alternatively this OR that. Defaults to AND.
					</li>
					<li>
						<strong>Media Type Field ( In Elastic Index )</strong>: (Optional) Set Media Type Field (ie that holds 'Photo Gallery', 'Video Gallery'...).
					</li>
				</ul>
			";
		
		echo $html;
	}
	
	/**
	 * Initialize the options page
	 * 
	 * @uses:	register_settings()
	 * @uses:	add_settings_section()
	 * @uses:	add_settings_field()
	 */
	public function page_init() {
		
		/*
		 * Register our search options
		 * 
		 * @uses register_setting()
		 * @callback validate_options()
		 */
		register_setting( $this->hook, $this->hook, array(
			 $this,
			'validate_options' 
		) );
		
		/*
		 * Add a settings section where the plugin will group like items
		 */
		add_settings_section( 'es_search_settings_section', '<h2>About Elastic Search Configuration</h2>', array(
			 $this,
			'print_section_info' 
		), 'es_search_configuration' );
		
		
		/*
		 * Addd Fields
		 */
		
		add_settings_field( 'host', 'Host', array(
			 $this,
			'render_host_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'http_port', 'HTTP Port ( Search )', array(
			 $this,
			'render_http_port_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'https_port', 'HTTPS Port ( Writing )', array(
			 $this,
			'render_https_port_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'username', 'Username', array(
			 $this,
			'render_username_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'password', 'Password', array(
			 $this,
			'render_password_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'verify_indexes', 'Verify Indexes', array(
			 $this,
			'render_indexes_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'num_of_results_per_page', 'Number Of Results Per Page', array(
			 $this,
			'render_num_of_results_per_page_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		
		add_settings_field( 'mobile_num_of_results_per_segment_before_ad', 'Mobile Number of Results Before Ad', array(
			 $this,
			'render_mobile_num_of_results_per_segment_before_ad' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'mobile_total_results_per_segment', 'Mobile Total Results Per "Load More" Segment', array(
			 $this,
			'render_mobile_total_results_per_segment' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'default_search_operator', 'Default Search Operator', array(
			 $this,
			'render_default_search_operator_select' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'filter_cache', 'Filter Cache', array(
			 $this,
			'render_filter_cache_select' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'media_type_field', 'Media Type Field', array(
			 $this,
			'render_media_type_field_input' 
		), 'es_search_configuration', 'es_search_settings_section' );
		
		add_settings_field( 'log', 'Log', array(
			 $this,
			'render_log_select' 
		), 'es_search_configuration', 'es_search_settings_section' );
	}
	
	
	public function render_host_input() {
		$options = get_option( $this->hook );
?>
		<input name="es_search_options[host]" id="host" value="<?php
		echo ( isset( $options[ 'host' ] ) ? esc_attr( $options[ 'host' ] ) : false );
?>" />
		<?php
	}
	
	public function render_http_port_input() {
		$options = get_option( $this->hook );
		echo "<input name='es_search_options[http_port]' id='http_port' value='" . ( isset( $options[ 'http_port' ] ) ? intval( $options[ 'http_port' ] ) : false ) . "'  />";
	}
	
	public function render_https_port_input() {
		$options = get_option( $this->hook );
		echo "<input name='es_search_options[https_port]' id='http_port' value='" . ( isset( $options[ 'https_port' ] ) ? intval( $options[ 'https_port' ] ) : false ) . "'  />";
	}
	
	public function render_username_input() {
		$options = get_option( $this->hook );
		echo "<input id='username' name='es_search_options[username]' value='" . ( isset( $options[ 'username' ] ) ? esc_attr( $options[ 'username' ] ) : false ) . "'  />";
	}
	
	public function render_password_input() {
		$options = get_option( $this->hook );
		echo "<input type='password' id='password' name='es_search_options[password]' value='" . ( isset( $options[ 'password' ] ) ? esc_attr( $options[ 'password' ] ) : false ) . "'  />";
		
	}
	
	public function render_indexes_input() {
		$options = get_option( $this->hook );
		echo "<input id='verify-indexes' name='es_search_options[verify_indexes]' value='" . ( isset( $options[ 'verify_indexes' ] ) ? esc_attr( $options[ 'verify_indexes' ] ) : false ) . "'  />";
	}
	
	public function render_num_of_results_per_page_input() {
		$options = get_option( $this->hook );
		echo "<input id='num-of-results-per-page' name='es_search_options[num_of_results_per_page]' value='" . ( isset( $options[ 'num_of_results_per_page' ] ) ? intval( $options[ 'num_of_results_per_page' ] ) : false ) . "'  />";
	}
	
	public function render_mobile_num_of_results_per_segment_before_ad() {
		$options = get_option( $this->hook );
		echo "<input id='mobile-num-of-results-per-segment-befor-ad' name='es_search_options[mobile_num_of_results_per_segment_before_ad]' value='" . ( isset( $options[ 'mobile_num_of_results_per_segment_before_ad' ] ) ? intval( $options[ 'mobile_num_of_results_per_segment_before_ad' ] ) : false ) . "'  />";
	}
	
	public function render_mobile_total_results_per_segment() {
		$options = get_option( $this->hook );
		echo "<input id='mobile-total-results-per-segment' name='es_search_options[mobile_total_results_per_segment]' value='" . ( isset( $options[ 'mobile_total_results_per_segment' ] ) ? intval( $options[ 'mobile_total_results_per_segment' ] ) : false ) . "'  />";
	}
	
	public function render_default_search_operator_select() {
		$options = get_option( $this->hook );
?>
		<select id="default-search-operator" name="es_search_options[default_search_operator]">
			<option value="AND" <?php
		selected( ( isset( $options[ 'default_search_operator' ] ) ? esc_attr( $options[ 'default_search_operator' ] ) : false ), 'AND' );
?>>AND</option>
			<option value="OR" <?php
		selected( ( isset( $options[ 'default_search_operator' ] ) ? esc_attr( $options[ 'default_search_operator' ] ) : false ), 'OR' );
?>>OR</option>
		</select>
		<?php
	}
	
	public function render_filter_cache_select() {
		$options = get_option( $this->hook );
?>
		<select id="filter-cache" name="es_search_options[filter_cache]">
			<option value="true" <?php
		selected( ( isset( $options[ 'filter_cache' ] ) ? esc_attr( $options[ 'filter_cache' ] ) : false ), 'true' );
?>>true</option>
			<option value="false" <?php
		selected( ( isset( $options[ 'filter_cache' ] ) ? esc_attr( $options[ 'filter_cache' ] ) : false ), 'false' );
?>>false</option>
		</select>
		<?php
	}
	
	public function render_media_type_field_input() {
		$options = get_option( $this->hook );
		echo "<input id='media-type-field' name='es_search_options[media_type_field]' value='" . ( isset( $options[ 'media_type_field' ] ) ? esc_attr( $options[ 'media_type_field' ] ) : false ) . "'  />";
	}
	
	public function render_log_select() {
		$options = get_option( $this->hook );
?>
		<select id="log" name="es_search_options[log]">
			<option value="false" <?php
		selected( ( isset( $options[ 'log' ] ) ? esc_attr( $options[ 'log' ] ) : false ), 'false' );
?>>false</option>
			<option value="true" <?php
		selected( ( isset( $options[ 'log' ] ) ? esc_attr( $options[ 'log' ] ) : false ), 'true' );
?>>true</option>
		</select>
		<?php
	}
	
	/**
	 * 
	 * @param 	mixed  $val
	 * @param 	number $strip_tags
	 * @param 	string $enforce_type
	 * @param 	number $debug
	 * @return 	boolean|integer|string
	 * 
	 * @uses	gettype()
	 * @uses	function_exists()
	 * @uses	strip_tags()
	 * @uses	sanitize_text_field()
	 * @uses 	absint()
	 * @uses	abs()
	 * @uses	ceil()
	 * @uses	$this->remove_scheme_from_host();
	 */
	protected function clean_value( $val, $strip_tags = 1, $enforce_type = null, $debug = 0 ) {
		
		$val_type = gettype( $val );
		
		if ( $enforce_type == null ) {
			switch ( $val_type ) {
				case 'integer':
					if ( $val < 0 ) {
						$valid_value = abs( $val ); // convert negative integers to be positive
					} else {
						$valid_value = $val;
					}
					
					break;
				case 'string':
					if ( $strip_tags == 1 ) {
						if ( function_exists( 'sanitize_text_field' ) ) { // Check if we're in Wordpress
							$valid_value = sanitize_text_field( $val ); // Checks for invalid UTF-8, Convert single < characters to entity, strip all tags, remove line breaks, tabs and extra white space, strip octets.
						} else {
							$valid_value = strip_tags( $val ); //  Strip HTML and PHP tags from the value
						}
					}
					break;
				case 'float':
				case 'double':
					if ( function_exists( 'absint' ) ) {
						$val = absint( ceil( $val ) ); // convert positive and negative floats to positive integers the Wordpress Way
					} else {
						$val = abs( ceil( $val ) ); // convert positive and negative floats to positive integers
					}
					break;
				case 'array' :
					$valid_value = $val;
					break;
				case 'object':
				case 'resource':
				case "NULL":
				case "unknown type":
					$valid_value = false;
					break;
			}
		} else {
			
			if ( 'object ' == $val_type || 'resource' == $val_type || 'NULL' == $val_type || 'unknown type' == $val_type ) {
				return false;
			} else {
				
				switch ( $enforce_type ) {
					case 'integer':
						if ( empty( $val ) ) {
							return 0;
						}
						
						$val         = (int) $val;
						// if a float or double it will be rounded up to 
						// the nearest positive integer, if an integer then no 
						// need to round up. If a string then also converts to an int before processing
						$valid_value = abs( ceil( $val ) );
						break;
					case 'host':
						if ( $no_strip_tags == 0 ) {
							if ( function_exists( 'sanitize_text_field' ) ) {
								$val = sanitize_text_field( $val );
							} else {
								$val = strip_tags( $val );
							}
						}
						$valid_value = $this->remove_scheme_from_host( $val );
						break;
				}
			}
		}
		
		if ( TRUE == $debug ) {
			return 'debug gettype(): ' . gettype( $valid_value ) . ' value:' . $valid_value;
		}
		return $valid_value;
		
	}
	
	/**
	 * Remove the scheme ( 'https://' or 'http://' )
	 * @param string $host
	 * @return string|boolean
	 */
	protected function remove_scheme_from_host( $host ) {
	
		if ( $host ) {
			$pattern        = '/.*:\/\//'; //if scheme exists ie: http:// or https:// this pattern will remove this scheme
			$cleaned_scheme = preg_replace( $pattern, '', $host ); // strip http://, https:// or any other scheme from the hostname;
			return $cleaned_scheme;
		} else {
			return false;
		}
	}
	
	
	/*
	 * Validates the configuration options
	 * 
	 * Set default values if values are not set
	 * Strips HTML tags
	 * @uses in_array()
	 * @uses isset()
	 * @uses preg_replace()
	 * @uses empty() 
	 */
	public function validate_options( $input ) {
		
		$options = get_option( $this->hook );
		
		$problem = false;
		
		$this->valid[ 'host' ] = $this->clean_value( $input[ 'host' ], $dont_strip_tags = 0, $enforce_as = 'host' );
		
		// validate the http port
		$http_port                  = $input[ 'http_port' ];
		$this->valid[ 'http_port' ] = $this->clean_value( $http_port, $dont_strip_tags = 0, 'integer' ); // Ensure that the port field is not empty default to ''
		
		// validate the https port 
		$https_port                  = $input[ 'https_port' ];
		$https_port                  = (int) $https_port; // ensure port is an integer
		$this->valid[ 'https_port' ] = $this->clean_value( $https_port, $dont_strip_tags = 0, 'integer' ); // Ensure that the port field is not empty or conatins HTML tags, sets default to ''
		
		// Check that the username field is set and that it does not have any html tags 
		$username                  = $input[ 'username' ];
		$this->valid[ 'username' ] = $this->clean_value( $username ); // Ensure that the username field is not empty or conatins HTML tags, sets default to ''
		
		// Check that the password field is set and that it does not have any html tags
		$this->valid[ 'password' ] = $this->clean_value( $input[ 'password' ] ); // Ensure that the password field is not empty or conatins HTML tags, sets default to ''
		
		// process the indexes
		$indexes                         = preg_replace( '/\s+/', '', $input[ 'verify_indexes' ] ); // remove whitespace from the string 'driving, driving-posts' becomes 'driving,driving-posts'
		$this->valid[ 'verify_indexes' ] = $this->clean_value( $indexes ); // Ensure that the indexes field is not empty or conatins HTML tags, sets default to ''
		
		// Check that the num_of_results_per_page field is set and that it does not have any html tags
		$this->valid[ 'num_of_results_per_page' ] = $this->clean_value( $input[ 'num_of_results_per_page' ] ); // Ensure that the value is a postive integer
		
		// Check that the mobile_num_of_results_per_segment field is set and that it does not have any html tags
		$this->valid[ 'mobile_num_of_results_per_segment_before_ad' ] = $this->clean_value( $input[ 'mobile_num_of_results_per_segment_before_ad' ], $dont_strip_tags = 0, 'integer' ); // Ensure that the value is a postive integer
		
		$this->valid[ 'mobile_total_results_per_segment' ] = $this->clean_value( $input[ 'mobile_total_results_per_segment' ], $dont_strip_tags = 0, 'integer' ); // Ensure that the value is a postive integer
		
		$this->valid[ 'default_search_operator' ] = $this->clean_value( $input[ 'default_search_operator' ] );
		
		$this->valid[ 'filter_cache' ] = $this->clean_value( $input[ 'filter_cache' ] );
		
		$this->valid[ 'media_type_field' ] = $this->clean_value( $input[ 'media_type_field' ] );
		
		$this->valid[ 'log' ] = $this->clean_value( $input[ 'log' ] );
		
		return $this->valid;
	}
	
	
	/**
	 * Returns page ID of given slug
	 * @param unknown $page_slug
	 * @return slug_id|NULL
	 * 
	 * @uses get_id_by_slug()
	 */
	public function get_id_by_slug( $page_slug ) {
	
		$page = wpcom_vip_get_page_by_path( $page_slug );
		if ( $page ) {
			return $page->ID;
		} else {
			return null;
		}
	}
	
} // End Class


new pm_Elasticsearch();
