<?php

namespace Postmedia\Web\Content;

use Postmedia\Web\Content;

/**
 * TODO: Move to Longform Plugin
 */
class Longform extends Content {

	/**
	 * Color for title (hex)
	 * @var string
	 */
	private $title_color;

	/**
	 * Color parent story title color (hex)
	 * @var string
	 */
	private $first_chapter_title_color;

	/**
	 * Extra credits field
	 * @var string
	 */
	private $extra_credits;

	/**
	 * ??? What's this for...
	 * @var string
	 */
	private $parent_title;

	/**
	 * Custom author name
	 * @var string
	 */
	private $author_name;

	/**
	 * Custom author affiliation
	 * @var string
	 */
	private $author_affiliation;

	/**
	 * Table of contents eg. chapter, series
	 * @var string
	 */
	private $table_of_contents;



	/**
	 * Title Color (hex)
	 * @return string
	 */
	public function get_title_color() {
		return $this->meta['ft_title_color'];
	}

	/**
	 * Parent title color (hex)
	 * NOTE: The parent title usually appears below the title when on a child story
	 * @return string
	 */
	public function get_first_chapter_title_color() {
		return $this->meta['ft_first_chapter_title_color'];
	}

	/**
	 * Custom extra credits field
	 * @return string
	 */
	public function get_extra_credits() {
		return $this->meta['ft_extra_credits'];
	}

	/**
	 * TODO: Get Parent Title Somehow
	 * @return string
	 */
	public function get_parent_title() {
		if ( ! ( $this->meta['suppress_parent_title'] ) ) {
			//return ( 0 != $parent_id ) ? get_the_title( $parent_id ) : '';
		}

		return '';
	}

	/**
	 * Type of table of contents eg. chapter, series, part
	 * @return string
	 */
	public function get_table_of_contents() {
		return $this->meta['ft_table_of_contents'];
	}

	/**
	 * Custom author name
	 * @return string
	 */
	public function get_author_name() {
		return $this->meta['ft_author_name'];
	}

	/**
	 * Custom author affiliation
	 * @return string
	 */
	public function get_author_affiliation() {
		return $this->meta['ft_author_affiliation'];
	}

	/**
	 * Flag to only show the custom author
	 * @return bool
	 */
	public function show_custom_author() {
		return ( 'on' == $this->meta['ft_author_show_only'] );
	}

	public function is_advertorial() {
		// ft_advertorial_is_advertorial
		// ft_advertorial_sponsor
	}
}
