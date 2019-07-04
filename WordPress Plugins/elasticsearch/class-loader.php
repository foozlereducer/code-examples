<?php

define( 'ES_SEARCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'ES_SEARCH_CSS_URL', plugins_url( 'css/' , __FILE__ ) );
define( 'ES_SEARCH_JS_URL', plugins_url( 'js/', __FILE__ ) );

	class pm_Load { 
	
		public function __construct() {
			$res = spl_autoload_register(array( $this, 'loader' ) );
		}
		
		protected function loader( $class_name ) {
			/*
			 * Change underscores used in Class naming conventions to
			* use hypens used in the WordPress Code Standards. It is also uses the 
			* standard and appends 'class-' to each class file name
			*/
			$class_name = str_replace('_', '-', $class_name);
	
			/*
			 * Load either classes in the root plugin directory or the namespaced
			* Elastica library directory
			*/
			switch( $class_name ) {
					
				case file_exists( __DIR__ . DIRECTORY_SEPARATOR   . 'class-' . strtolower (  $class_name  ) . '.php' ):
					require_once( __DIR__ . DIRECTORY_SEPARATOR   . 'class-' . strtolower (  $class_name  ) . '.php' ); // load class in root directory
					break;
				case file_exists( __DIR__ . DIRECTORY_SEPARATOR . $class_name  . '.php' ):
					require_once( __DIR__ . DIRECTORY_SEPARATOR . $class_name  . '.php' ); // load class in Elastica directory
			}
		}
	}
	
	new pm_Load();

?>