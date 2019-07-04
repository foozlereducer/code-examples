<?php

namespace Postmedia\Web;

use Postmedia\Web\TemplateEngine;

class Sharing {

	public function __construct() { }

	/**
	 * Render sharing template
	 * @param  string $template_root Template root
	 * @param  string $template_file Template relative to root
	 * @param  array  $params Additional params to send to template
	 * @return void
	 */
	public function render( $template_root, $template_file, $params = array() ) {
		if ( ! $template_root && ! $template_file ) {
			return;
		}

		$template_engine = new TemplateEngine( $template_root );
		$template_engine->initialize();

		// Add params
		$params['post_id'] = $this->share_post()->ID; // Needed for cache key

		// Load template
		$template = $template_engine->load_template(
			$template_file,
			$params,
			array(
				'sharing_context' => &$this,
			)
		);

		// Allow theme to provide templates that override defaults
		$template->allow_override = true;

		// Render template
		$template->render();
	}

	/**
	 * Get the post that we need to share. If share_post is set use that otherwise
	 * fall back to global $post
	 * @return object Post
	 */
	private function share_post() {
		global $post;

		$share_post = $post;

		if ( isset( $this->share_post ) ) {
			$share_post = $this->share_post;
		}

		return $share_post;
	}

	/**
	 * Get the title of the post to share
	 * @return string
	 */
	public function share_title() {
		$post_title = html_entity_decode( $this->share_post()->post_title, ENT_COMPAT, 'UTF-8' );

		if ( false != ( $alternate_title = trim( get_post_meta( absint( $this->share_post()->ID ), '_pn_title_alternate', true ) ) ) ) {
			$post_title = $alternate_title;
		}

		return $post_title;
	}

	/**
	 * Get the url of the post to share
	 * @param  string $name Name of sharing service eg. facebook
	 * @return string
	 */
	public function share_url( $name ) {
		global $postmedia_layouts;

		$share_post = $this->share_post();

		if ( ! is_object( $postmedia_layouts ) ) {
			$excerpt = get_the_excerpt();
		} else {
			$excerpt = $postmedia_layouts->get_excerpt( $share_post );
		}

		if ( has_post_thumbnail() ) {
			$image_id   = get_post_thumbnail_id( absint( $share_post->ID ) );
			$attachment = get_post( absint( $image_id ) );
			$image_url  = wp_get_attachment_image_src( absint( $image_id ), 'pn_1000', true );
		} else {
			$image_url = '';
		}

		$post_title = $this->share_title();

		$post_link = rtrim( get_permalink( $share_post->ID ), '/' ) . '/';

		$share_urls = array(
			'email' => add_query_arg(
				array(
					'subject' => rawurlencode(
						sprintf(
							'%s: %s',
							get_bloginfo( 'name' ),
							$post_title
						)
					),
					'body'    => rawurlencode(
						sprintf(
							__( 'I wanted to share a story with you from %2$s:%1$s%1$s%3$s%1$s%4$s%1$s%5$s%1$s', 'postmedia' ),
							"\n",
							esc_attr( get_bloginfo( 'name' ) ),
							$post_title,
							str_replace( " \n", '', strip_tags( $excerpt ) ),
							$post_link
						)
					),
				),
				'mailto:'
			),
			'twitter' => add_query_arg(
				array(
					'url'  => urlencode( $post_link ),
					'text' => urlencode( $post_title ),
				),
				'https://twitter.com/intent/tweet'
			),
			'facebook' => add_query_arg(
				array(
					'u' => urlencode( $post_link ),
					't' => urlencode( $post_title ),
				),
				'http://www.facebook.com/sharer/sharer.php'
			),
			'pinterest' => add_query_arg(
				array(
					'url'         => urlencode( $post_link ),
					'media'       => isset( $image_url[0] ) ? urlencode( $image_url[0] ) : '',
					'description' => urlencode( $post_title ),
				),
				'http://pinterest.com/pin/create/button/'
			),
			'google' => add_query_arg(
				array(
					'url' => urlencode( $post_link ),
				),
				'https://plus.google.com/share'
			),
			'linkedin' => add_query_arg(
				array(
					'mini'    => 'true',
					'url'     => urlencode( $post_link ),
					'title'   => urlencode( $post_title ),
					'summary' => urlencode( strip_tags( $excerpt ) ),
				),
				'http://www.linkedin.com/shareArticle'
			),
			'reddit' => add_query_arg(
				array(
					'url' => urlencode( $post_link ),
					'title'   => urlencode( $post_title ),
				),
				'http://www.reddit.com/submit?'
			),
			'whatsapp' => add_query_arg(
				array(
					'text' => urlencode( $post_link ),
				),
				'whatsapp://send?'
			),
			'tumblr' => add_query_arg(
				array(
					'canonicalUrl' => urlencode( $post_link ),
					'name'   => urlencode( $post_title ),
					'description' => urlencode( strip_tags( $excerpt ) ),
				),
				'http://tumblr.com/widgets/share/tool?'
			),
		);

		if ( isset( $share_urls[ $name ] ) ) {
			return $share_urls[ $name ];
		}

		return null;
	}
}
