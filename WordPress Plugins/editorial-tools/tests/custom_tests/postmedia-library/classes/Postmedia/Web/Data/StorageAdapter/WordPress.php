<?php

/**
 * Reads data from WordPress and returns it in WCM format.
 */

namespace Postmedia\Web\Data\StorageAdapter;

use Postmedia\Web\Data\DataHelper;
use Postmedia\Web\Data\IStorageAdapter;
use Postmedia\Web\Data\ContentElement\Gallery;
use Postmedia\Web\Data\ContentElement\Image;

class WordPress implements IStorageAdapter {
	/**
	 * Parses WordPress content data and returns it in WCM format.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content.json
	 *
	 * @param  int        $post_id Post ID
	 * @return array|null
	 */
	public static function get_content( $post_id ) {
		$p = get_post( $post_id );

		if ( empty( $p ) ) {
			return null;
		}

		$required = array(
			'_id'          => get_post_meta( $p->ID, 'pn_wcm_id', true ),
			'client_id'    => get_option( 'wcm_client_id' ),
			'license_id'   => get_post_meta( $p->ID, 'pn_wcm_license', true ),
			'type'         => DataHelper::get_wcm_type( $p->post_type ),
			'imported_on'  => null, // TODO
			'modified_on'  => DataHelper::format_date( $p->post_modified_gmt ),
			'published_on' => DataHelper::format_date( $p->post_date_gmt ),
			'status'       => DataHelper::get_wcm_status( $p->post_status ),
			'credits'      => self::get_credits( $p ),
			'titles'       => self::get_titles( $p ),
			'version'      => '0.1',
		);

		$meta = get_post_meta( $p->ID, 'advertorial_meta_box', true );
		$sponsored = (  ! empty( $meta ) ? true : false );
		$origin_url = get_permalink( $p->ID );
		$optional = array(
			'origin_id'        => (string) $p->ID,
			'origin_cms'       => 'wordpress',
			'origin_slug'      => $p->post_name,
			'origin_url'       => $origin_url,
			'origin_url_path'  => str_replace( home_url(), '', $origin_url ),
			'global_slug'      => get_post_meta( $p->ID, 'pni_slug', true ),
			'label'            => null, // TODO
			'advertorial'      => $sponsored,
			'derived_from'     => get_post_meta( $p->ID, 'pn_derived_from', true ),
			'excerpt'          => $p->post_excerpt,
			'taxonomies'       => self::get_taxonomies( $p ),
			'content_elements' => self::get_content_elements( $p ),
			'featured_media'   => self::get_featured_media( $p ),
			'related_content'  => self::get_related_content( $p ),
			'stock_symbols'    => get_post_meta( $p->ID, 'company_symbols', true ),
			'parent_id'        => (string) $p->post_parent,
			'order'            => $p->menu_order,
			'metadata'         => self::get_metadata( $p ),
			'commenting_enabled' => comments_open( $p->ID ),
		);

		$order = array(
			'_id',
			'client_id',
			'license_id',
			'origin_id',
			'origin_cms',
			'origin_slug',
			'origin_url',
			'origin_url_path',
			'global_slug',
			'label',
			'type',
			'advertorial',
			'imported_on',
			'modified_on',
			'published_on',
			'derived_from',
			'status',
			'credits',
			'titles',
			'excerpt',
			'taxonomies',
			'content_elements',
			'featured_media',
			'related_content',
			'stock_symbols',
			'parent_id',
			'order',
			'metadata',
			'version',
			'commenting_enabled',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	public static function add_content( $json ) {
		return null;
	}

	public static function update_content( $json ) {
		return null;
	}

	/**
	 * TODO - get_list_data should be used as get_content_list
	 * @param  string  $qs       [description]
	 * @param  boolean $expand   [description]
	 * @param  integer $maxposts [description]
	 * @param  integer $from     [description]
	 * @return [type]            [description]
	 */
	public static function get_list_data( $qs, $expand, $maxposts ) { self::get_content_list( '' ); }
	public static function get_content_list( $id ) {
		$zone = z_get_zone( $id );
		$zone_posts = z_get_posts_in_zone( $id );

		$content_list = array(
			'id' => $zone->term_id,
			'type' => $zone->taxonomy,
			'origin_id' => $zone->term_id,
			'origin_cms' => 'wordpress',
			'client_id' => null,
			'modified_on' => null,
			'title' => $zone->name,
			'slug' => $zone->slug,
			'description' => $zone->description,
			'status' => null,
			'content' => null,
			'query' => null,
		);

		$content = array();
		
		foreach( $zone_posts as $zone_post ) {
			$content[] = self::get_content( $zone_post->ID );
		}

		$content_list['content'] = $content;

		return $content_list;
	}

	public static function add_content_list( $json ) {
		return null;
	}

	public static function update_content_list( $json ) {
		return null;
	}

	public static function get_license( $id ) {
		return null;
	}

	public static function get_licenses() {
		return null;
	}

	/**
	 * Gathers title data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content.json
	 *
	 * @param  WP_Post $p Post object
	 * @return array
	 */
	private static function get_titles( $p ) {
		$required = array(
			'main' => $p->post_title,
		);

		$optional = array(
			'subtitle'  => get_post_meta( $p->ID, '_subheading', true ),
			'alternate' => null, // TODO
			'concise'   => get_post_meta( $p->ID, '_pn_title_alternate', true ),
			'seo'       => get_post_meta( $p->ID, 'mt_seo_title', true ),
		);

		return DataHelper::merge_fields( $required, $optional );
	}

	/**
	 * Gathers credits data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/credits.json
	 *
	 * @param  WP_Post $p Post object
	 * @return array
	 */
	private static function get_credits( $p ) {
		$required = array(
			'authors' => self::get_authors( $p ),
		);

		$optional = array(
			'distributor' => get_bloginfo( 'name' ),
		);

		return DataHelper::merge_fields( $required, $optional );
	}

	/**
	 * Gathers author data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/credits.json
	 *
	 * @param  WP_Post $p Post object
	 * @return array
	 */
	private static function get_authors( $p ) {
		$output = array();

		if ( function_exists( 'get_coauthors' ) ) {
			$users = get_coauthors( $p->ID );
		}

		if ( empty( $users ) ) {
			$users = array();
			if ( ! empty( $p->post_author ) ) {
				$users[] = get_userdata( $p->post_author );
			}
		}

		foreach ( $users as $user ) {
			if ( ! empty( $user ) ) {
				$output[] = self::get_user( $user );
			}
		}

		return $output;
	}

	/**
	 * Gathers user data for a WordPress user.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/credits.json
	 *
	 * @param  WP_User $user User object
	 * @return array
	 */
	private static function get_user( $user ) {
		$name = $user->display_name;

		if ( empty( $name ) ) {
			$name = $user->first_name . ' ' . $user->last_name;
		}

		$required = array(
			'name' => trim( $name ),
		);

		$args = apply_filters( 'coauthors_posts_link', array( 'href' => get_author_posts_url( $user->ID, $user->user_nicename ) ), $user );

		$optional = array(
			'_id'          => (string) $user->ID,
			'org'          => get_option( 'pn_theme_city' ),
			'slug'         => $user->user_login,
			'email'        => $user->user_email,
			'bio'          => $user->description,
			'url'          => $args['href'],
			'photo'        => self::get_user_photo( $user ),
			'social_links' => self::get_user_social( $user ),
		);

		$order = array(
			'_id',
			'name',
			'org',
			'slug',
			'email',
			'bio',
			'url',
			'photo',
			'social_links',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * Gathers image data for a WordPress user's avatar.
	 *
	 * @param  WP_User $user User object
	 * @return array
	 */
	private static function get_user_photo( $user ) {
		if ( function_exists( 'coauthors_get_avatar_url' ) ) {
			$url = coauthors_get_avatar_url( $user );
		} else {
			$url = get_avatar_url( $user );
		}

		$output = Image::html_to_json( '<img src="' . $url . '">' );

		// Remove the shortcode attributes because we aren't actually generating the data from a shortcode.
		unset( $output['shortcode'] );
		unset( $output['shortcode_tag'] );

		return $output;
	}

	/**
	 * Gathers social data for a WordPress user.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/social.json
	 *
	 * @param  WP_User $user User object
	 * @return array
	 */
	private static function get_user_social( $user ) {
		$output = array();

		$username = get_the_author_meta( 'twitter', $user->ID );
		if ( ! empty( $username ) ) {
			$url = 'https://twitter.com/' . $username;
			$output[] = array(
				'site' => 'Twitter',
				'url'  => $url,
			);
		}

		return $output;
	}

	/**
	 * Gathers taxonomy data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/taxonomies.json
	 *
	 * @param  WP_Post $p Post object
	 * @return array
	 */
	private static function get_taxonomies( $p ) {
		$main_taxonomies = array();
		$main_category_term_id = get_post_meta( $p->ID, '_pn_main_category', true );
		if ( ! empty( $main_category_term_id ) ) {
			$main_category_term = wpcom_vip_get_term_by( 'id', $main_category_term_id, 'category' );
			$main_category = self::get_term( $main_category_term, $main_category_term_id );
			if ( ! empty( $main_category ) ) {
				$main_taxonomies[] = $main_category;
			}
		}

		$required = array(
			'categories'      => self::get_taxonomy_terms( $p, 'category', $main_category_term_id ),
			'main_taxonomies' => $main_taxonomies,
		);

		$optional = array();
		$taxonomy_labels = DataHelper::get_taxonomy_labels();
		foreach ( $taxonomy_labels as $taxonomy => $label ) {
			$optional[ $label ] = self::get_taxonomy_terms( $p, $taxonomy, $main_category_term_id );
		}

		$order = array(
			'tags',
			'categories',
			'makes',
			'bodystyles',
			'classifications',
			'model_years',
			'specialsections',
			'main_taxonomies',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * Gathers terms in a single taxonomy for a WordPress post.
	 *
	 * @param  WP_Post $p                     Post object
	 * @param  string  $taxonomy              Taxonomy name
	 * @param  int     $main_category_term_id Term ID of post's main category
	 * @return array
	 */
	private static function get_taxonomy_terms( $p, $taxonomy, $main_category_term_id ) {
		$terms = get_the_terms( $p->ID, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}
		$output = array();
		foreach ( $terms as $term ) {
			$output[] = self::get_term( $term, $main_category_term_id );
		}
		return $output;
	}

	/**
	 * Gathers term data for a WordPress term.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/tag.json
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/taxonomy.json
	 *
	 * @param  WP_Term    $term                  Term object
	 * @param  int        $main_category_term_id Term ID of post's main category
	 * @return array|null
	 */
	private static function get_term( $term, $main_category_term_id ) {
		if ( empty( $term ) || is_wp_error( $term ) ) {
			return null;
		}

		$required = array(
			'name' => $term->name,
			'slug' => $term->slug,
		);
		if ( 'post_tag' != $term->taxonomy ) {
			$required['type'] = $term->taxonomy;
			$required['path'] = self::get_term_path( $term );
		}

		$optional = array(
			'_id' => $term->term_id,
		);
		if ( 'post_tag' != $term->taxonomy ) {
			if ( $term->term_id == $main_category_term_id ) {
				$optional['main'] = true;
			}
		}

		$order = array(
			'_id',
			'type',
			'name',
			'slug',
			'path',
			'main',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * Formats path string for a WordPress term.
	 *
	 * @param  WP_Term $term Term object
	 * @return string
	 */
	private static function get_term_path( $term ) {
		$output = array( $term->name );

		while ( ! empty( $term->parent ) ) {
			$term = wpcom_vip_get_term_by( 'id', $term->parent, $term->taxonomy );
			$output[] = $term->name;
		}

		return '/' . implode( '/', array_reverse( $output ) ) . '/';
	}

	/**
	 * Gathers content body data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/content_element.json
	 *
	 * @param  WP_Post $p Post object
	 * @return array
	 */
	public static function get_content_elements( $p ) {
		// Get list of block level elements.
		$block_level_elements = DataHelper::get_block_level_elements();
		$block_level_elements = implode( '|', $block_level_elements );

		// Ensure block level elements in the post content are on their own line.
		$lines = $p->post_content;
		$lines = preg_replace( '/<\/(' . $block_level_elements . ')>/', '</$1>' . "\n", $lines );
		$lines = preg_replace( '/<(' . $block_level_elements . ')(\s.*)?>/', "\n" . '<$1$2>', $lines );

		// Split post content into elements.
		$lines = explode( "\n", $lines );
		$lines = array_map( 'trim', $lines );
		$lines = array_filter( $lines );
		$lines = array_values( $lines );
		$i = 1;

		// Get the JSON for each element.
		$output = array();
		foreach ( $lines as $line ) {
			$element = DataHelper::get_content_element( $line, 'json' );
			$element = array_merge( array( '_id' => null ), $element );
			$element['_id'] = $i;
			$output[] = $element;
			$i++;
		}

		return $output;
	}

	/**
	 * Gathers featured media data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content.json
	 *
	 * @param  WP_Post    $p Post object
	 * @return array|null
	 */
	private static function get_featured_media( $p ) {
		$image = self::get_featured_image( $p );
		if ( empty( $image ) ) {
			return null;
		}

		$required = array(
			'image' => $image,
		);

		$optional = array(
			'video'     => self::get_featured_video( $p ),
			's2n_video' => self::get_featured_s2n_video( $p ),
			'gallery'   => self::get_featured_gallery( $p ),
		);

		return DataHelper::merge_fields( $required, $optional );
	}

	/**
	 * Gathers featured image data for a WordPress post.
	 *
	 * @param  WP_Post    $p Post object
	 * @return array|null
	 */
	private static function get_featured_image( $p ) {
		$attachment_id = get_post_thumbnail_id( $p->ID );
		if ( empty( $attachment_id ) ) {
			return null;
		}
		$attachment = get_post( $attachment_id );
		return self::get_image( $attachment );
	}

	/**
	 * Gathers image data for a WordPress attachment.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/image.json
	 *
	 * @param  WP_Post $attachment Attachment object
	 * @return array
	 */
	private static function get_image( $attachment ) {
		$meta = get_post_meta( $attachment->ID, '_wp_attachment_metadata', true );

		$required = array(
			'url'   => $attachment->guid,
			'title' => $attachment->post_title,
		);

		$optional = array(
			'_id'          => null,
			'type'         => 'image',
			'origin_id'    => (string) $attachment->ID,
			'mime_type'    => $attachment->post_mime_type,
			'created_on'   => DataHelper::format_date( $attachment->post_date_gmt ),
			'caption'      => $attachment->post_excerpt,
			'description'  => $attachment->post_content,
			'credit'       => get_post_meta( $attachment->ID, 'pn_attachment_credit', true ),
			'distributor'  => get_post_meta( $attachment->ID, 'pn_attachment_distributor', true ),
			'width'        => isset( $meta['width'] ) ? $meta['width'] : null,
			'height'       => isset( $meta['height'] ) ? $meta['height'] : null,
		);

		$order = array(
			'_id',
			'type',
			'origin_id',
			'mime_type',
			'created_on',
			'url',
			'title',
			'caption',
			'description',
			'credit',
			'distributor',
			'width',
			'height',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * Gathers featured video data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/video.json
	 *
	 * @param  WP_Post    $p Post object
	 * @return array|null
	 */
	private static function get_featured_video( $p ) {
		$id = get_post_meta( $p->ID, 'pn_featured_video_id', true );
		if ( empty( $id ) ) {
			return null;
		}

		$required = array(
			'origin_id'  => $id,
			'origin_cms' => 'kaltura',
		);
		$meta = get_post_meta( $p->ID, 'pn_featured_video_inline', true );
		$inline = ( ! empty( $meta ) ? true : false );
		$optional = array(
			'_id'         => null,
			'type'        => 'video',
			'title'       => get_post_meta( $p->ID, 'pn_featured_video_title', true ),
			'description' => get_post_meta( $p->ID, 'pn_featured_video_description', true ),
			'thumbnail'   => get_post_meta( $p->ID, 'pn_featured_video_image', true ),
			'url'         => null, // TODO
			'inline'      => $inline,
		);

		$order = array(
			'_id',
			'type',
			'origin_id',
			'origin_cms',
			'title',
			'description',
			'thumbnail',
			'url',
			'inline',
		);

		return DataHelper::merge_fields( $required, $optional, $order );
	}

	/**
	 * Gathers featured Send2News video player data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content.json
	 *
	 * @param  WP_Post    $p Post object
	 * @return array|null
	 */
	private static function get_featured_s2n_video( $p ) {
		$id = get_post_meta( $p->ID, 'pn_sendtonews_player_id', true );
		if ( empty( $id ) ) {
			return null;
		}

		$required = array();

		$optional = array(
			'_id' => $id,
		);

		return DataHelper::merge_fields( $required, $optional );
	}

	/**
	 * Gathers featured gallery data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content_elements/gallery.json
	 *
	 * @param  WP_Post    $p Post object
	 * @return array|null
	 */
	private static function get_featured_gallery( $p ) {
		$id = get_post_meta( $p->ID, 'featured-gallery-id', true );
		if ( empty( $id ) ) {
			return null;
		}

		$output = Gallery::html_to_json( '[snapgallery id="' . $id . '"]' );

		// Remove the shortcode attributes because we aren't actually generating the data from a shortcode.
		unset( $output['shortcode'] );
		unset( $output['shortcode_tag'] );

		return $output;
	}

	/**
	 * Gathers related content data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/traits/related_content.json
	 *
	 * @param  WP_Post $p Post object
	 * @return array
	 */
	private static function get_related_content( $p ) {
		$related_content = get_post_meta( $p->ID, '_pn_related_links', true );
		if ( empty( $related_content ) ) {
			return array();
		}

		$output = array();

		$order = array(
			'_id',
			'title',
			'url',
		);
		$i = 1;

		foreach ( $related_content as $row ) {
			$required = array(
				'title' => $row->text,
				'url'   => $row->url,
			);

			$optional = array(
				'_id' => $i,
			);

			$output[] = DataHelper::merge_fields( $required, $optional, $order );
			$i++;
		}

		return $output;
	}

	/**
	 * Gathers meta data for a WordPress post.
	 *
	 * https://github.com/Postmedia-Digital/postmedia-schema-mercury/blob/master/schema/0.1/content.json
	 *
	 * @param  WP_Post $p Post object
	 * @return array
	 */
	private static function get_metadata( $p ) {
		$output = array();
		$meta = get_post_meta( $p->ID );

		foreach ( $meta as $meta_key => $meta_value ) {
			if ( empty( $meta_value ) || empty( $meta_value[0] ) ) {
				continue;
			}
			$output[ $meta_key ] = $meta_value[0];
		}

		ksort( $output );

		return $output;
	}
}
