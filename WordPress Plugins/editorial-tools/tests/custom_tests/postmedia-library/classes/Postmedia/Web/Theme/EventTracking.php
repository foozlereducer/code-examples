<?php

namespace Postmedia\Web\Theme;

class EventTracking {

	/**
	 * Reference to the global layouts object
	 * @var Postmedia_Layouts
	 */
	protected $postmedia_layouts;


	public function __construct() {
		global $postmedia_layouts;

		$this->postmedia_layouts = $postmedia_layouts;
	}

	/**
	 * Generate a string of data values separated by '|' for the data-event-tracking attribute
	 * @param  string $sponsored_key Sponsored location
	 * @return string
	 */
	public function get_data( $sponsored_key = '', $outfit_name_key = '', $list_type_key = '', $list_name_key = '' ) {
		$attr = '';

		if ( ! $this->postmedia_layouts ) {
			return $attr;
		}

		$outfit_type = ucfirst( $this->postmedia_layouts->pmlay_settings['outfit_type'] );
		$outfit_number = -1;
		$post_number = $this->postmedia_layouts->pmlay_settings['count_posts'];
		$list_number = intval( $this->postmedia_layouts->pmlay_count['module'] ) - 1;
		$list_type = $this->postmedia_layouts->pmlay_settings['list_type'];
		$list_name = $this->postmedia_layouts->pmlay_settings['list_name'];

		if ( 'Outfit' == $outfit_type ) {
			$outfit_number = intval( $this->postmedia_layouts->pmlay_count['section'] );
			$outfit_id = intval( $this->postmedia_layouts->pmlay_settings[ $outfit_number ]['template'] );
			$outfit_name = $this->postmedia_layouts->outfit_settings[ $outfit_id ][0];
		} else {
			$outfit_number = ( array_key_exists( 'widget_count', $this->postmedia_layouts->pmlay_count ) ) ? intval( $this->postmedia_layouts->pmlay_count['widget_count'] ) : -1;
			$widget_id	= ( array_key_exists( 'widget', $this->postmedia_layouts->pmlay_count ) ) ? intval( $this->postmedia_layouts->pmlay_count['widget'] ) : 0;
			$outfit_name = $this->postmedia_layouts->widget_settings[ $widget_id ][0];
			$outfit_type = 'Sidebar Outfit';
			$list_number = 0;
		}

		if ( 'sponsored' == $sponsored_key ) {
			$post_number = 'Sponsored';
			$list_number = 0;

			if ( 'Outfit' == $outfit_type ) {
				$list_type = 'No type';
				$list_name = 'sponsored-posts';
			}
		} else if ( 'header' == $sponsored_key ) {
			$post_number = 'Header';
			$list_number = $list_number + 1;
			$list_type = 'No type';
			$list_name = 'No name';
		} else if ( 'related' == $sponsored_key ) {
			$outfit_type = 'Outfit';
			$outfit_number = 0;
			$outfit_name = 'Related Posts';
			$list_number = $list_number + 1;
			$list_type = 'Related Posts List';
			$list_name = 'related-posts';
		} else if ( 'search' == $sponsored_key ) {
			$outfit_type = 'Outfit';
			$outfit_number = 0;
			$outfit_name = 'Default Outfit';
			$list_number = $list_number + 1;
			$list_type = 'Search List';
			$list_name = 'search-posts';
		} else if ( 'more_news' == $sponsored_key ) {
			$outfit_type = 'Outfit';
			$outfit_number = 0;
			$outfit_name = 'More News';
			$list_number = $list_number + 1;
			if ( '' != $list_type_key && '' != $list_name_key ) {
				$list_type = $list_type_key;
				$list_name = $list_name_key;
			} else {
				$list_type = 'zone';
				$list_name = 'hp-top-stories';	
			}			
		} else if ( '404' == $sponsored_key ) {
			$outfit_type = 'Outfit';
			$outfit_number = 0;
			$outfit_name = '404 Top Stories';
			$list_number = $list_number + 1;			
			if ( '' != $list_type_key && '' != $list_name_key ) {
				$list_type = $list_type_key;
				$list_name = $list_name_key;
			} else {
				$list_type = 'zone';
				$list_name = 'hp-top-stories';	
			}	
		} else if ( 'weather' == $sponsored_key ) {
			$outfit_type = 'Outfit';
			$outfit_number = 0;
			$outfit_name = 'Weather Stories';
			$list_number = $list_number + 1;
			$list_type = 'Weather Stories List';
			$list_name = 'weather-slug';
		} else if ( 'storyline' == $sponsored_key ) {
			$outfit_type = 'Outfit';
			$outfit_number = 0;
			$outfit_name = 'Storyline Stories';
			$list_number = $list_number + 1;
			if ( '' != $list_type_key && '' != $list_name_key ) {
				$list_type = $list_type_key;
				$list_name = $list_name_key;
			} else {
				$list_type = 'zone';
				$list_name = 'hp-top-stories';	
			}	
		} else if ( '' != $sponsored_key && '' != $outfit_name_key && '' != $list_type_key && '' != $list_name_key ) {
			$outfit_name = $outfit_name_key;
			$list_type = $list_type_key;
			$list_name = $list_name_key;
		} else if ( '' != $sponsored_key && '' == $outfit_name_key && '' == $list_type_key && '' == $list_name_key ) {
			$outfit_name = $sponsored_key;
			$list_type = $sponsored_key;
			$list_name = $sponsored_key;
		}

		$attr = sprintf('%s|%s|%s|%s|%s|%s|%s',
			$outfit_type,
			$outfit_number,
			$outfit_name,
			$list_number,
			$post_number,
			$list_type,
			$list_name
		);

		return $attr;
	}
}
