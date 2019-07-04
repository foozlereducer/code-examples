<?php

namespace Postmedia\Web\Feeds;

class Alexa extends \Postmedia\Web\Feed {

	/**
	 * Constructor that Hook into the rss2 namespace and item WordPress hooks to set a custom
	 * Alexa element with a namespace
	 * @return object instance
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'rss2_ns', array( $this, 'set_alexa_namespace' ) );
		add_action( 'rss2_item', array( $this, 'add_custom_rss_fields' ) );
	}

	/**
	 * Set Alexa Namespace
	 * sets the custom namespace when hooked by rss2_ns.
	 * @return void
	 */
	public function set_alexa_namespace() {
		$url = sanitize_text_field( get_site_url() );
		printf( "xmlns:postmedia=\"%s\"\n", esc_url( $url ) );
	}

	/**
	 *  Add the newly namespaced postmedia:alexa title field
	 * @return void
	 */
	public function add_custom_rss_fields() {
		global $post;
		$title = $post->post_title;
		$content = $this->get_paragraph( $post->post_content );
		print ( '<postmedia:alexa><![CDATA[' . wp_kses_post( html_entity_decode( esc_attr( $title . '. ' . $content ), ENT_QUOTES, 'UTF-8' ) ) . ']]></postmedia:alexa>' );
	}

	/**
	 * Get the first Paragraph of the post's content body
	 * @param string $content ~ the content of the current post
	 * @return string
	 */
	public function get_paragraph( $content ) {
		$content = strip_tags( $content );
		$content = wpautop( $content );

		$content_paragraphs = explode( '</p>', $content );
		$paragraph_count = count( $content_paragraphs );

		for ( $i = 0; $i < $paragraph_count; $i++ ) {
			$content_paragraphs[ $i ] = trim( $content_paragraphs[ $i ] );
			$content_paragraphs[ $i ] = $this->strip_place( $content_paragraphs[ $i ] );

			$ltfw = $this->larger_than_four_words( $content_paragraphs[ $i ] );
			$ac = ctype_upper( $content_paragraphs[ $i ] );

			if ( true === $ltfw && false === $ac ) {
				return trim( strip_tags( $content_paragraphs[ $i ] ) );
			}
		}
	}

	/**
	 * Removes place names like OTTAWA -- from beginning of text.
	 * @param  string $paragraph
	 * @return string
	 */
	public function strip_place( $paragraph ) {
		$paragraph = preg_replace( '/^<p>[^a-z]+(, [\w\. ]+)? (&mdash;|--) /', '<p>', $paragraph );
		return $paragraph;
	}

	public function larger_than_four_words( $paragraph ) {
		$words = explode( ' ', $paragraph );
		if ( 4 < count( $words ) ) {
			return true;
		}

		return false;
	}
}
