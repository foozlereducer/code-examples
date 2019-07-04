<?php

namespace Postmedia\Web;

abstract class Shortcode {

	/**
	 *
	 * @param type $content_elements
	 * @return type
	 */
	function shortcode_from_json( $name, $array ) {
		$shortcode = $name;
		$el = $array;
		//$el = ( json_decode ( $array, TRUE ) );
		switch ( $shortcode ) {

			case 'text':

				$order = $el['id'];
				$type = $el_type;
				$content = $el['content'];
				$paragraph = $el['paragraph']; //paragraph type wrap/none/close/open

				break;

			case 'related_list':

				$order = $el['id'];
				$type = $el_type;
				$origin_id = $el['origin_id'];
				$channels = $el['channels'];
				$location = $el['location'];
				$items = $el['items'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ' ]';

				// Related list
				$shortcode .= '[related_list]';

				//Related story
				foreach ( $rs as $items ) {
					$title = $el['title'];
					$link = $el['link'];
					$shortcode .= '[related title="' . esc_attr( $title ) .'" link="' . esc_url( $link ) . '"]';
				}
				//Close embed
				$shortcode .= '[/related_list][/pn_embed]<p></p>';

				break;

			case 'snapgallery':

				$order = $el['order'];
				$type = $el_type;
				$gallery_id = $el['id'];
				$immersive = $el['immersive'];
				$channels = $el['channels'];
				$location = $el['location'];
				$content_elements = $el['content_elements'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ' ]';

				// Snapgallery
				$shortcode .= '[snapgallery id="' . esc_attr( $id ) . '"';

				//Immersive
				if ( $immersive ) {
					$shortcode .= ' immersive="' . esc_attr( $immersive ) . '"';
				}

				//Close embed
				$shortcode .= ' /][/pn_embed]<p></p>';

				break;
			case 'pn_versus':

				$order = $el['order'];
				$type = $el_type;
				$gallery_id = $el['id'];
				$immersive = $el['immersive'];
				$channels = $el['channels'];
				$location = $el['location'];

				// Versus
				$shortcode .= '[pn_versus post_id="' . esc_attr( $id ) . '" ]';

				break;

			case 'flickr':
				$order = $el['id'];
				$type = $el_type;
				$video = $el['video'];

				//Flickr
				$shortcode .= '][flickr video= "' . esc_url( $video ) . '"';

				// Close embed
				$shortcode .= ']';

				break;

			case 'ted':
				$order = $el['id'];
				$type = $el_type;
				$origin_id = $el['origin_id'];
				$channels = $el['channels'];
				$location = $el['location'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}

				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ' ]';

				// Ted
				$shortcode .= '[ted id="' . esc_attr( $origin_id ) . '" /][/pn_embed]<p></p>';

				break;

			case 'soundcloud':

				$order = $el['id'];
				$type = $el_type;
				$url = $el['url'];
				$channels = $el['channels'];
				$location = $el['location'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ' ]';

				// Soundcloud
				$shortcode .= '[soundcloud url="' . esc_url( $url ) . '" /][/pn_embed]<p></p>';

				break;

			case 'scribd':

				$order = $el['id'];
				$type = $el_type;
				$origin_id = $el['origin_id'];
				$key = $el['key'];
				$mode = $el['mode'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ']';

				// Scribd
				$shortcode .= '[scribd id="' . esc_attr( $origin_id ) . '" key="' . esc_attr( $key ) . '" mode="' . esc_attr( $mode ) . '" /][/pn_embed]<p></p>';

				break;

			case 'blockquote':

				$order = $el['id'];
				$type = $el_type;
				$content = $el['content'];
				$content = sprintf( '%s%s', $content, Blockquote::html_to_json( sanitize_text_field( $el['content'] ) ) );
				break;

			case 'video':

				$order = $el['id'];
				$type = $el_type;
				$origin_id = $el['origin_id'];
				$uiconfid = $el['uiconfid'];
				$origin_cms = $el['origin_cms']; //[ "kaltura", "ooyala", "brightcove", "wordpress" ]
				$title = $el['title'];
				$description = $el['description'];
				$thumbnail = $el['thumbnail'];
				$playertype = $el['playertype'];
				$width = $el['width'];
				$height = $el['height'];
				$url = $el['url'];
				$inline = $el['inline'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}

				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ']';

				// Video
				if ( ! $thumbnail ) {
					$shortcode .= '[kaltura-widget entryid="'. esc_attr( $origin_id ) . '"';
				} else {
					$shortcode .= '[pn_featured_video_embed entryid="'. esc_attr( $origin_id ) . '"';
				}
				if ( $uiconfid ) {
					$shortcode .= ' uiconfid="' . esc_attr( $uiconfid ) . '"';
				}
				if ( $showplaylist ) {
					$shortcode .= ' showplaylist="' . esc_attr( $showplaylist ) . '"';
				}
				if ( $playertype ) {
					$shortcode .= ' playertype="' . esc_attr( $playertype ) . '"';
				}
				if ( $width ) {
					$shortcode .= '  width="' . esc_attr( $width ) . '"';
				}
				if ( $height ) {
					$shortcode .= ' height="' . esc_attr( $height ) . '"';
				}
				if ( $description ) {
					$shortcode .= ' description="' . esc_attr( $description ) . '"';
				}
				if ( $thumbnail ) {
					$shortcode .= ' thumbnail="' . esc_url( $thumbnail ) . '"';
				}

				// Close embed
				$shortcode .= ' /][/pn_embed]<p></p>';

				break;

			case 'oembed':

				$order = $el['id'];
				$type = $el_type;
				$subtype = $el['subtype'];
				$provider_name = $el['provider_name'];
				$provider_url = $el['provider_url'];
				$object_url = $el['object_url'];
				$channels = $el['channels'];
				$html = $el['html'];

				//ok youtube
				//ok twitter
				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= '][embed]' . esc_url( $object_url ) . '[embed][/pn_embed]<p></p>';

				break;

			case 'pd.twitter':

				$id = $el['id'];
				$type = $el_type;
				$origin_id = $el['origin_id'];

				$shortcode = '[pd.twitter';
				$shortcode .= 'id="'. esc_attr( $id ) .'"]';

				break;

			case 'pngallery':

				$id = $el['id'];
				$type = $el_type;
				$shortcode = $el['shortcode'];

				$shortcode = '[pngallery';
				$shortcode .= 'id="'. esc_attr( $id ) .'"]';

				break;

			case 'custom_embed':

				$id = $el['id'];
				$location = $el['location'];
				$type = $el_type;
				$channels = $el['channels'];
				$content = $el['content'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				//  Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ' ]';

				//Custom embed
				$shortcode .= $content;

				// Close embed
				$shortcode .= '[/pn_embed]<p/></p>';

				break;

			case 'image':

				$id = $el['id'];
				$type = $el_type;
				$origin_id = $el['origin_id'];
				$mime_type = $el['mime_type'];
				$created_on = $el['created_on'];
				$url = $el['url'];
				$title = $el['title'];
				$caption = $el['caption'];
				$description = $el['description'];
				$credit = $el['credit'];
				$distributor = $el['distributor'];
				$align = $el['align'];
				$location = $el['location'];
				$alt = $el['alt'];
				$class = $el['class'];
				$width = $el['width'];
				$height = $el['height'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				// Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= ']';

				// Set alignment if unavailable
				if ( ! $align ) {
					$align = 'alignnone';
				}

				// Set caption
				if ( $caption ) {
					$shortcode .= ' [caption id="attachment_'. esc_attr( origin_id ) . '" align="' . esc_attr( $align ) . '"]';
				}

				// Image
				$shortcode .= '<img src="'. esc_url( url ) . '"';
				if ( $width ) {
					$shortcode .= ' width="'. esc_attr( $width ) . '"' ;
				}
				if ( $height ) {
					$shortcode .= ' height="'. esc_attr( $height ) . '"' ;
				}
				if ( $alt ) {
					$shortcode .= ' alt="'. esc_attr( $alt ) . '"' ;
				}
				if ( $class ) {
					$shortcode .= ' class="'. esc_html( $class ) . '"' ;
				}
				$shortcode .= '/>';

				// Add caption
				if ( $caption ) {
					$shortcode .= ' '. esc_attr( $caption );
				}

				// Close embed
				if ( $caption ) {
					$shortcode .= '[/caption]';
				}

				$shortcode .= '[/pn_embed]<p></p>';

				break;

			case 'audio':

				$order = $el['id'];
				$type = $el_type;
				$mime_type = $el['mime_type'];
				$url = $el['url'];
				$title = $el['title'];
				$description = $el['description'];

				$shortcode = '[audio mp3="'. esc_url( $url ) . '"][/audio]';

				break;

			case 'dropcap':

				$order = $el['id'];
				$type = $el_type;
				$text_to_drop = $el['text_to_drop'];
				$channels = $el['channels'];
				$location = $el['location'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				// Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}
				$shortcode .= '][pn_dropcap]';

				//Dropcap
				$shortcode .= esc_attr( $text_to_drop );

				// Close embed
				$shortcode .= '[pn_dropcap][/pn_embed]<br /><br />';

				break;

			case 'google_static_map':

				$order = $el['id'];
				$type = $el_type;
				$center = $el['center'];
				$zoom = $el['zoom'];

				//Google map
				$shortcode .= '][google_static_map center= "' . esc_attr( $center ) . '" zoom= "' . esc_attr( $zoom ) . '"';

				// Close embed
				$shortcode .= ' ]';

				break;

			case 'pullquote': //pn-pullquote:

				$order = $el['id'];
				$type = $el_type;
				$text = $el['text'];
				$source = $el['source'];
				$featured = $el['featured'];
				$channels = $el['channels'];
				$location = $el['location'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				// Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}

				//Pullquote
				$shortcode .= '][pn-pullquote  text= "' . esc_attr( $text ) . '" source = "' . esc_attr( $source ) . '"';

				//Check for featured
				if ( $featured ) {
					$shortcode .= ' featured="true"';
				}

				// Close embed
				$shortcode .= ' /][/pn_embed]<p></p>';

				break;

			case 'infobox':

				$order = $el['id'];
				$type = $el_type;
				$main_title = $el['main_title'];
				$column1_title = $el['coloumn1_title'];
				$column1_content = $el['column1_content'];
				$column2_title = $el['coloumn2_title'];
				$column2_content = $el['column2_content'];
				$column3_title = $el['coloumn3_title'];
				$column3_content = $el['column3_content'];
				$channels = $el['channels'];
				$location = $el['location'];

				$shortcode = '<p></p>[pn_embed';

				// Set channels if avilable
				if ( $channels ) {
					if ( strpos( $channels, 'smartphone' ) ) {
						$shortcode .= ' mobile=show';
					} else {
						$shortcode .= ' mobile=hide';
					}
					if ( strpos( $channels, 'desktop' ) ) {
						$shortcode .= ' desktop=show';
					} else {
						$shortcode .= ' desktop=hide';
					}
					if ( strpos( $channels, 'tablet' ) ) {
						$shortcode .= ' tablet=show';
					} else {
						$shortcode .= ' tablet=hide';
					}
				}
				// Add location if available
				if ( $location ) {
					$shortcode .= ' location="'. esc_attr( $location ) . '"';
				}

				//Info box
				$shortcode .= '][pn_additional_info_boxes main_title = "' . esc_attr( $main_title ) . '" column1_title = "' . esc_attr( $column1_title ) . '"';
				$shortcode .= ' column1_content = "' . esc_attr( $column1_content ) . '" column2_title = "' . esc_attr( $column2_title ) . '"';
				$shortcode .= ' column2_content = "' . esc_attr( $column2_content ) . '" column3_title = "' . esc_attr( $column3_title ) . '"';
				$shortcode .= ' column3_content = "' . esc_attr( $column3_content ) . '" /]';

				// Close embed
				$shortcode .= '[/pn_embed]<p></p>';

				break;

			case 'playlist':

				$order = $el['id'];
				$type = $el_type;
				$ids = $el['ids'];

				$shortcode = '[playlist ids="'. esc_attr( $ids ) . '"]';

				break;

			case 'gallery':

				$order = $el['id'];
				$gallery_id = $el['gallery_id'];
				$type = $el_type;
				$shortcode = $el['shortcode'];

				break;

			case 'raw_html':

				$order = $el['id'];
				$type = $el_type;
				$content = $el['content'];

			break;

		}
	}

	/**
	 *
	 * @param type $content
	 * @return type
	 */
	function shortcode_to_json( $name, $array ) {
		switch ( $shortcode ) {
			case 'text':
				break;
			case 'image':

				$imgclass = 'contains-img';

				$img = array(
					'id'    => '',
					'type'  => 'image',
					'origin_id' => id,
					'mime_type' => '',
					'created_on' => '',
					'url' => '',			//required
					'title' => '',			//required
					'caption' => '',
					'align' => '',
					'description' => '',
					'credit' => '',
					'distributor' => '',
					'class' => array(),
					'alt' => '',
					'width' => '',
					'height' => '',
					'shortcode' => '',
				);

					$shortcode = wp_json_encode( $sh );

				break;
			case 'audio':

				$audio = array(
					'id'    => '',
					'type'  => 'audio', //required
					'mime_type' => '',
					'url' => '',		//required
					'title' => '',
					'description' => '',
					'shortcode' => '',
				);

					$shortcode = wp_json_encode( $sh );

				break;
			case 'dropcap';

				$dropcap = array(
					'id'    => '',
					'type'  => 'pn_dropcap',
					'text_to_drop' => '',	//required
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;

			case 'related_list':

				$related_list = array(
					'id'    => '',
					'type'  => 'related_list', //required
					'channels' => '',
					'location' => '',
					'shortcode' => '',
					'items'	=> '',
				);

				//related stories
				$related_story = array(
					'id'    => '',
					'type'  => 'related_story',
					'title' => '',	//required
					'url' => '',	//required
					'blank' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;

			case 'pn_additional_info_boxes':

				$info_box = array(
					'id'    => '',
					'type'  => 'pn_additional_info_boxes',
					'main_title' => '',	//required
					'column1_title' => '',
					'column1_content' => '',
					'column2_title' => '',
					'column2_content' => '',
					'column3_title' => '',
					'column3_content' => '',
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;
			case'snapgallery':

				$snapgallery = array(
					'id'    => '',
					'type'  => 'snapgallery',
					'gallery_id' => '',	//required
					'channels' => '',
					'location' => '',
					'immersive' => '',
					'shortcode' => '',
				);

				// gallery images
				$image = array(
					'id'    => '',
					'type'  => 'image',
					'origin_id' => '',
					'mime_type' => '',
					'created_on' => '',
					'url' => '',		//required
					'title' => '',		//required
					'caption' => '',
					'align' => '',
					'description' => '',
					'credit' => '',
					'distributor' => '',
					'class' => '',
					'alt' => '',
					'width' => '',
					'height' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;
			case 'ted':

				$ted = array(
					'id'    => '',		//required
					'type'  => 'ted',
					'origin_id' => '',
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;
			case 'custom_embed':
				$custom_embed = array(
					'id'    => '',
					'type'  => 'custom_embed',
					'content' => '',	//required
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				'break';
			case 'scribd':

				$scribd = array(
					'id'    => '',
					'type'  => 'scribd',
					'origin_id' => '',	//required
					'key' => '',		//required
					'mode' => '',		//required
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;
			case 'blockquote':

				$blockquote = array(
					'id'    => '',
					'type'  => 'blockquote',
					'content' => '',	//required
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;
			case 'video':

				$video = array(
					'id'    => '',
					'type'  => 'video',
					'origin_id'    => '',   //required
					'origin_cms' => '',		//required
					'title'  => '',
					'description'  => '',
					'thumbnail'  => '',
					'url'  => '',
					'uiconfid'  => '',
					'height'  => '',
					'weight'  => '',
					'showPlaylist'  => '',
					'inline'  => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;

			case 'pd.twitter':

				$twitter = array(
					'id'    => '',
					'type'  => 'oembed',
					'subtype' => '',
					'provider_name' => '',
					'provider_url' => '',	//required
					'object_url' => '',		//required
					'html' => '',
					'shortcode' => '',
				);
				break;

			case 'youtube':

				$youtube = array(
					'id'    => '',
					'type'  => 'oembed',
					'subtype' => '',
					'provider_name' => '',
					'provider_url' => '',	//required
					'object_url' => '',		//required
					'html' => '',
					'shortcode' => '',
				);
				break;

			case 'pngallery':

				break;

			case 'flickr':

				$flickr = array(
					'id'    => '',
					'type'  => 'flickr',
					'url' => '',	//required
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;

			case 'soundcloud':

				$soundcloud = array(
					'id'    => '',
					'type'  => 'soundcloud',
					'url' => '',	//required
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;

			case 'google_static_map':

				break;

			case 'pn-pullquote':

				$pullquote = array(
					'id'    => '',
					'type'  => 'pn-pullquote',
					'text' => '',			//required
					'source' => '',			//required
					'featured' => '',
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;

			case 'pn_versus':

				$versus = array(
					'id'    => '',
					'origin_id'    => '',
					'type'  => 'pn-versus',
					'versus_title_image1' => '',
					'versus_first_image' => '',
					'versus_title_image2' => '',
					'versus_first_image' => '',
					'channels' => '',
					'location' => '',
					'shortcode' => '',
				);

				//versus tabs
				$versus_tab = array(
					'id'    => '',
					'type'  => 'versus_tab',
					'versus_tab_label' => '',
					'versus_text_section1' => '',
					'versus_text_section2' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $sh );

				break;

			case 'oembed':

				$oembed = array(
					'id'    => $id,
					'type'  => 'oembed',
					'subtype' => $subtype,
					'provider_name' => $subtype,
					'provider_url' => '',	//required
					'object_url' => $url,		//required
					'html' => '',
					'channels' => '',
					'shortcode' => '',
				);

				$shortcode = wp_json_encode( $oembed );

				break;

			case 'playlist':

				break;
		}
	}
}

