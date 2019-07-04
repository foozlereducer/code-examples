<?php

namespace Postmedia\Web;

use Wholesite\Foundation\Component;
use Postmedia\Web\Data;
use Postmedia\Web\Data\DataHelper;
use Postmedia\Web\Content\Image;
use Postmedia\Web\Content\Video;
use Postmedia\Web\Content\Sponsor;
use Postmedia\Web\Content\Author;
use Postmedia\Web\Content\Tag;
use Postmedia\Web\Content\Category;
use Postmedia\Web\Content\Gallery;
use Postmedia\Web\Content\PullQuote;
use Postmedia\Web\Content\Origin;

/**
 *	Content
 *
 * 	This is equivalent to a Post or Custom Post Type eg. Longform, Versus, Gallery
 *
 * 	eg. class MyAwesomeCustomPostType extends \Postmedia\Web\Content { ... }
 */
class Content extends Component {

	/**
	 * Content Id - From DataSource - WordPress or WCM
	 * @var string
	 */
	private $id = null;

	/**
	 * Origin information
	 * @var Origin
	 */
	public $origin;

	/**
	 * Title
	 * @var string
	 */
	public $title;

	/**
	 * Subtitle
	 * @var string
	 */
	public $subtitle;

	/**
	 * Alternate Title ( Limited to 40 chars )
	 * Used in outfits, sharing...
	 * @var string
	 */
	public $alternate_title;

	/**
	 * SEO Title
	 * @var string
	 */
	public $seo_title;

	/**
	 * Excerpt
	 * @var string
	 */
	private $excerpt;

	/**
	 * Date
	 * @var datetime
	 */
	private $date;

	/**
	 * Date GMT
	 * @var datetime
	 */
	private $date_gmt;

	/**
	 * Modified Date
	 * @var datetime
	 */
	private $modified_date;

	/**
	 * Modified Date GMT
	 * @var datetime
	 */
	private $modified_date_gmt;

	/**
	 * Content status eg. published
	 * @var string
	 */
	public $status;

	/**
	 * Distributor
	 * @var string
	 */
	public $distributor;

	/**
	 * Content parts broken down into an array of elements
	 * @var array
	 */
	private $content;

	/**
	 * Type of content eg. longform, versus, post
	 * @var string
	 */
	public $type = 'post';

	/**
	 * Content images 'featured' or 'linked'
	 * @var array Image
	 */
	public $images = array();

	/**
	 * Associated videos
	 * @var array Video
	 */
	public $videos = array();

	/**
	 * Sponsorship info
	 * @var Sponsor
	 */
	private $sponsor;

	/**
	 * Content authors
	 * @var array Author
	 */
	public $authors = array();

	/**
	 * Tags
	 * @var array Tag
	 */
	public $tags = array();

	/**
	 * Categories
	 * @var array Category
	 */
	public $categories = array();

	/**
	 * Associated galleries
	 * @var array Gallery
	 */
	public $galleries = array();

	/**
	 * Get a list of Content that share the same parent
	 * @var array [mixed|Longform]
	 */
	private $siblings = array();

	/**
	 * Content meta extras
	 * @var array
	 */
	private $meta = array();

	/**
	 * Saxo Global Slug
	 * @var string
	 */
	private $global_slug;

	/**
	 * Emotion Bubble eg. NONE, WOW
	 * @var string
	 */
	private $emotion_bubble;

	/**
	 * Pull Quote
	 * @var PullQuote
	 */
	private $pull_quote;

	/**
	 * Show comments?
	 * @var bool
	 */
	private $commenting_enabled;



	/**
	 * TODO - Review
	 * Fallback to origin id if id is not set
	 * @return string
	 */
	public function get_id() {
		if ( empty( $this->id ) && ! empty( $this->origin->id ) ) {
			return $this->origin->id;
		}

		return $this->id;
	}

	/**
	 * Return the formatted date
	 * @param  string  $format
	 * @param  boolean $gmt
	 * @return string
	 */
	public function get_date( $format = 'Y-m-d', $gmt = false ) {
		if ( $gmt ) {
			return gmdate( $format, strtotime( $this->date_gmt ) );
		}
		return get_date_from_gmt( $this->date_gmt, $format );
	}

	/**
	 * Return the formatted modified date
	 * @param  string  $format
	 * @param  boolean $gmt
	 * @return string
	 */
	public function get_modified_date( $format = 'Y-m-d', $gmt = false ) {
		if ( $gmt ) {
			return gmdate( $format, strtotime( $this->modified_date_gmt ) );
		}
		return get_date_from_gmt( $this->modified_date_gmt, $format );
	}

	/**
	 * Saxo Global Slug
	 * @return string
	 */
	public function get_global_slug() {
		return $this->meta['pni_slug'];
	}

	/**
	 * Emotion bubble text
	 * @return string
	 */
	public function get_emotion_bubble() {
		return $this->meta['pn_emotion_bubble'];
	}

	/**
	 * Return the Pull Quote
	 * @return PullQuote
	 */
	public function get_pull_quote() {
		$data = json_decode( $this->meta['pni_pull_quote'] );

		$quote = new PullQuote();

		$quote->text = $data->text;
		$quote->source = $data->source;

		return $quote;
	}

	/**
	 * Get content body by processing content elements
	 * @return string
	 */
	public function get_content() {
		foreach ( $this->content as $element ) {
			$content[] = DataHelper::get_content_element( $element, 'html', $element['type'] );
		}

		$content = implode( "\n\n", $content );

		return apply_filters( 'the_content', $content );
	}

	/**
	 * TODO: Run query to get siblings - cache...etc.
	 * Get a list of Content that share the same parent
	 * @return array Content
	 */
	public function get_siblings() {
		return $this->siblings;
	}

	/**
	 * Advertorial / Sponsor Info
	 * Content sponsorship trumps Category sponsorship
	 * @return Sponsor
	 */
	public function get_sponsor() {
		// Sponsored at Content Level
		if (  isset( $this->meta['advertorial_meta_box'] ) && 'on' == $this->meta['advertorial_meta_box'] ) {
			$sponsor = new Sponsor();

			$sponsor->type = $this->meta['advertorial_type'];
			$sponsor->name = $this->meta['pn_adv_sponsor_name'];
			$sponsor->info = $this->meta['pn_adv_info_box'];
			$sponsor->logo_url = $this->meta['pn_adv_company_logo'];
			$sponsor->logo_click_url = $this->meta['pn_adv_logo_click_url'];
			$sponsor->pressboard_enabled = ( 'on' == $this->meta['pn_pressboard'] );
			$sponsor->allow_advertisements = ( $this->meta['pn_adv_allow_third_party_ads'] );

			return $sponsor;
		}

		// Check for matching Local Category Sponsor settings
		$main_category = $this->main_category();

		if ( $main_category ) {
			$local_category = wpcom_vip_get_term_by( 'slug', $main_category->slug, 'category' );

			if ( $local_category ) {
				$local_category_meta = get_option( sprintf( 'category_%d_meta', $local_category->term_id ) );

				// Sponsored at Category Level
				if ( isset( $local_category_meta['sponsored_editorial'] ) && 'on' == $local_category_meta['sponsored_editorial'] ) {
					$sponsor = new Sponsor();

					$sponsor->type = $local_category_meta['logo_label'];
					$sponsor->name = $local_category_meta['cat_company_name'];
					$sponsor->info = $local_category_meta['info_box'];
					$sponsor->logo_url = $local_category_meta['company_logo'];
					$sponsor->logo_click_url = $local_category_meta['counter_url'];
					$sponsor->pressboard_enabled = false; // No Option Available
					$sponsor->allow_advertisements = $local_category_meta['pn_adv_allow_third_party_ads'];

					$category = new Category();

					$category->id = $local_category->term_id;
					$category->name = $local_category->name;
					$category->slug = $local_category->slug;

					$sponsor->category = $category;
				}

				return $sponsor;
			}
		}

		return null;
	}

	/**
	 * Get the excerpt. Fallback and create an excerpt from the content.
	 * @return string
	 */
	public function get_excerpt() {
		$excerpt = $this->excerpt;

		if ( ! $excerpt && $this->get_content() ) {
			$excerpt = strip_tags( $this->get_content() );
			$excerpt = preg_replace( '/\[[^\]]+\]/i', '', $excerpt );

			if ( strlen( $excerpt ) > 200 ) {
				if ( strpos( $excerpt, '.', 100 ) > 0 ) {
					$excerpt = substr( $excerpt, 0, strpos( $excerpt, '.', 100 ) + 1 );
				} else {
					$excerpt = substr( $excerpt, 0, strpos( $excerpt, ' ', 200 ) ) . ' ...';
				}
			}
		}

		return $excerpt;
	}



	public function __construct( $content_id = null ) {
		$data = Data::get_content( $content_id );

		if ( ! $data ) {
			return;
		}

		$this->id = $data['_id'];
		$this->origin = $this->origin_from_data( $data );
		$this->type = $data['type'];
		$this->title = $data['titles']['main'];
		$this->subtitle = $data['titles']['subtitle'];
		$this->alternate_title = $data['titles']['alternate'];
		$this->seo_title = $data['titles']['seo'];
		$this->excerpt = $data['excerpt'];
		$this->date_gmt = $data['published_on'];
		$this->date = get_date_from_gmt( $this->date_gmt );
		$this->modified_date_gmt = $data['modified_on'];
		$this->modified_date = get_date_from_gmt( $this->modified_date_gmt );
		$this->status = $data['status'];
		$this->distributor = $data['credits']['distributor'];
		$this->content = $data['content_elements'];
		$this->tags = $this->tags_from_data( $data['taxonomies']['tags'] );
		$this->categories = $this->categories_from_data( $data['taxonomies']['categories'] );
		$this->authors = $this->authors_from_data( $data['credits']['authors'] );

		if ( isset( $data['featured_media'] ) ) {
			if ( isset( $data['featured_media']['image'] ) ) {
				$this->images['featured'] = $this->image_from_data( $data['featured_media']['image'] );
			}

			if ( isset( $data['featured_media']['video'] ) ) {
				$this->videos['featured'] = $this->video_from_data( $data['featured_media']['video'] );
			}

			if ( isset( $data['featured_media']['s2n_video'] ) ) {
				$this->videos['s2n'] = $this->video_from_data( $data['featured_media']['s2n_video'] );
			}

			if ( isset( $data['featured_media']['gallery'] ) ) {
				$this->galleries['featured'] = new Gallery( $data['featured_media']['gallery']['gallery_id'] );
			}
		}

		$this->meta = $data['metadata'];
		$this->sponsor = $this->sponsor_from_data( $data );

		// TODO
		$this->commenting_enabled = true;
	}

	/**
	 * Featured / Primary Image
	 * @return Image
	 */
	public function featured_image() {
		if ( ! isset( $this->images['featured'] ) ) {
			return null;
		}

		$image = $this->images['featured'];

		if ( isset( $this->meta['pn_featured_image_caption'] ) && ! empty( $this->meta['pn_featured_image_caption'] ) ) {
			$image->caption = $this->meta['pn_featured_image_caption'];
		}

		if ( Utilities::is_mobile() ) {
			//$image = pn_get_post_thumbnail_data( get_the_ID(), 'pn_302' );
		} else {
			//$image = pn_get_post_thumbnail_data( get_the_ID(), 'pn_840' );
		}

		return $image;
	}

	/**
	 * Featured Video ( featured or s2n featured )
	 * @return Video
	 */
	public function featured_video() {
		if ( isset( $this->videos['featured'] ) ) {
			return $this->videos['featured'];
		} else if ( isset( $this->videos['s2n'] ) ) {
			return $this->videos['s2n'];
		}

		return null;
	}

	/**
	 * Get video by type
	 * @param  string $type
	 * @return Video
	 */
	public function video( $type = 'featured' ) {
		if ( isset( $this->videos[ $type ] ) ) {
			return $this->videos[ $type ];
		}

		return null;
	}

	/**
	 * The featured Gallery
	 * @return Gallery
	 */
	public function featured_gallery() {
		if ( ! isset( $this->galleries['featured'] ) ) {
			return null;
		}

		return $this->galleries['featured'];
	}

	/**
	 * URL for this content ( WordPress=permalink, WCM=id )
	 * @param $absolute Return with domain portion eg. http://...
	 * @return string
	 */
	public function url( $absolute = true ) {
		if ( $absolute ) {
			return $this->origin->url;
		}

		return $this->origin->path;
	}

	/**
	 * If this content is from Wordpress we can offer an edit link
	 * @return string
	 */
	public function edit_url() {
		if ( 'wordpress' == $this->origin->cms ) {
			return get_edit_post_link( $this->id );
		}

		return null;
	}

	/**
	 * Get the Main Category based on the Type field set in our Category list
	 * @return Category
	 */
	public function main_category() {
		foreach ( $this->categories as $category ) {
			if ( 'main' == $category->type ) {
				return $category;
			}
		}

		return null;
	}

	/**
	 * Is commenting allowed for this post
	 * @return bool
	 */
	public function commenting_enabled() {
		return $this->commenting_enabled;
	}

	/**
	 * Is native commenting enabled for this post
	 * Lookup local tags and main category to find if this setting is enabled
	 * @return bool
	 */
	public function native_commenting_enabled() {
		if ( ! $this->commenting_enabled() ) {
			return false;
		}

		$terms = array();

		if ( $this->main_category() ) {
			if ( $local_category = $this->main_category()->get_local() ) {
				$terms[] = $local_category;
			}
		}

		foreach ( $this->tags as $tag ) {
			if ( $local_tag = $tag->get_local() ) {
				$terms[] = $local_tag;
			}
		}

		foreach ( $terms as $term ) {
			if ( (bool) get_option( 'category_' . $term->term_id . '_meta_native_commenting', false ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * TODO: What's this for??? template-tags.php
	 * @param  string  $post_type
	 * @return boolean
	 */
	public function is_post_type_archive( $post_type ) {
		return false;
	}

	/**
	 * Helper to convert datasource images into Image objects
	 * @param  array $data
	 * @return Image
	 */
	private function image_from_data( $data ) {
		$image = new Image();

		if ( ! is_array( $data ) ) {
			// Assume passed data is just the url
			$image->url = $data;
		} else {
			$image->id = $data['origin_id'];
			$image->type = $data['type'];
			$image->mime = $data['mime'];
			$image->created_on = $data['created_on'];
			$image->url = $data['url'];
			$image->title = $data['title'];
			$image->caption = $data['caption'];
			$image->description = $data['description'];
			$image->credit = $data['credit'];
			$image->distributor = $data['distributor'];
			$image->width = $data['width'];
			$image->height = $data['height'];
		}

		return $image;
	}

	/**
	 * Helper to convert datasource videos into Video objects
	 * @param  array $data
	 * @return Video
	 */
	private function video_from_data( $data ) {
		$video = new Video();

		$video->id = $data['origin_id'];
		$video->type = $data['type'];
		$video->title = $data['title'];
		$video->description = $data['description'];
		$video->add_image( 'thumb', $this->image_from_data( $data['thumbnail'] ) ); // TODO: Is this just the ID?
		$video->url = $data['url'];
		$video->inline = $data['inline'];
		$video->shortcode = $data['shortcode'];

		return $video;
	}

	/**
	 * Helper to convert datasource tags into Tag objects
	 * @param  array $data
	 * @return array Tag
	 */
	private function tags_from_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = array( $data );
		}

		$list = array();

		foreach ( $data as $item ) {
			$tag = new Tag();

			$tag->id = $item['_id'];
			$tag->name = $item['name'];
			$tag->slug = $item['slug'];

			$list[] = $tag;
		}

		return $list;
	}

	/**
	 * Helper to convert datasource categories into Category objects
	 * @param  array $data
	 * @return array Category
	 */
	private function categories_from_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = array( $data );
		}

		$list = array();

		foreach ( $data as $item ) {
			$category = new Category();

			$category->id = $item['_id'];
			$category->type = ( $item['main'] ) ? 'main' : 'standard';
			$category->name = $item['name'];
			$category->slug = $item['slug'];
			$category->path = $item['path'];

			$list[] = $category;
		}

		return $list;
	}

	/**
	 * Helper to convert datasource authors into Author objects
	 * @param  array $data
	 * @return array Author
	 */
	private function authors_from_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = array( $data );
		}

		$list = array();

		foreach ( $data as $item ) {
			$author = new Author();

			$author->id = $item['_id'];
			$author->name = $item['name'];
			$author->slug = $item['email'];
			$author->photo = $this->image_from_data( $item['photo'] );
			$author->org = $item['org'];
			$author->bio = $item['bio'];
			$author->url = $item['url'];
			$author->social = $item['social_links'];

			$list[] = $author;
		}

		return $list;
	}

	/**
	 * Helper to convert datasource into a Sponsor object
	 * @param  array $data From Datasource
	 * @return Sponsor
	 */
	private function sponsor_from_data( $data ) {
		if ( ! isset( $data['advertorial'] ) || ! $data['advertorial'] ) {
			return null;
		}

		$sponsor = new Sponsor();

		$sponsor->type = $data['metadata']['advertorial_type'];
		$sponsor->name = $data['metadata']['pn_adv_sponsor_name'];
		$sponsor->info = $data['metadata']['pn_adv_info_box'];
		$sponsor->logo_url = $data['metadata']['pn_adv_company_logo'];
		$sponsor->logo_click_url = $data['metadata']['pn_adv_logo_click_url'];
		$sponsor->pressboard_enabled = ( 'on' == $data['metadata']['pn_pressboard'] );
		$sponsor->allow_advertisements = ( $data['metadata']['pn_adv_allow_third_party_ads'] );

		return $sponsor;
	}

	/**
	 * Helper to convert datasource into a Origin object
	 * @param  array $data From Datasource
	 * @return Origin
	 */
	private function origin_from_data( $data ) {
		$origin = new Origin();

		$origin->id = $data['origin_id'];
		$origin->cms = $data['origin_cms'];
		$origin->slug = $data['origin_slug'];
		$origin->url = $data['origin_url'];
		$origin->path = $data['origin_url_path'];

		return $origin;
	}
}
