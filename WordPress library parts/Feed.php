<?php

namespace Postmedia\Web;

abstract class Feed {

	/**
	 * Constructor
	 * @return object instance
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'remove_sponsored_posts_from_feed' ), 1 );
		add_filter( 'the_posts', array( $this, 'filter_the_posts' ), 10, 2 );
	}

	/**
	 * Increases the number of posts in a feed query. The number of posts is
	 * later limited to 10.
	 * @param  WP_Query $query
	 * @return void
	 */
	public function remove_sponsored_posts_from_feed( $query ) {
		if ( $query->is_feed() && $query->is_main_query() ) {
			$query->set( 'post_type', 'post' );
			$query->set( 'posts_per_rss', 50 );
		}
	}

	/**
	 * Removes sponsored posts from feeds.
	 * @param  array $posts
	 * @param  WP_Query $query
	 * @return array
	 */
	public function filter_the_posts( $posts, $query ) {
		if ( ! $query->is_feed ) {
			return $posts;
		}

		$output = array();
		$max = 10;
		$i = 0;

		foreach ( $posts as $p ) {
			$advertorial_meta_box = get_post_meta( $p->ID, 'advertorial_meta_box', true );
			$allow_in_feed = ( 'on' === get_post_meta( $p->ID, 'pn_visible_rss_feed', true ) ) ? true : false;

			if ( ! $allow_in_feed ) {
				if ( $advertorial_meta_box ) {
					continue;
				}

				$pn_adv_sponsor_name = get_post_meta( $p->ID, 'pn_adv_sponsor_name', true );
				if ( $pn_adv_sponsor_name ) {
					continue;
				}
			}

			$output[] = $p;
			$i++;
			if ( $i >= $max ) {
				break;
			}
		}

		return $output;
	}

	/**
	 * This Feed class allows a developer to use this default Feed header.
	 * When inheriting a developer can override the get_custom_feed_base_header() method to
	 * provide new content types, and namespaces.
	 * @return string
	 */
	public function get_custom_feed_base_header() {
		header( 'Content-Type: ' . feed_content_type( 'rss-http' ) . '; charset=' . get_option( 'blog_charset' ), true );

		$feed_header = '<?xml version="1.0" encoding="' . get_option( 'blog_charset' ) . '"?' . '>' . "\n\n";
		$feed_header .= '<rss version="2.0"
			xmlns:content="http://purl.org/rss/1.0/modules/content/"
			xmlns:wfw="http://wellformedweb.org/CommentAPI/"
			xmlns:dc="http://purl.org/dc/elements/1.1/"
			xmlns:atom="http://www.w3.org/2005/Atom"
			xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
			xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
		>' . "\n\n";

		return $feed_header;
	}
}
