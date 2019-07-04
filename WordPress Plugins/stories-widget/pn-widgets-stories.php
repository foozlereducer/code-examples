<?php
// Creating the widget
class PN_Stories_Widget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'PN_Stories_Widget', // id
			$name = __( 'Postmedia Stories Widget', 'Postmedia Story Feed Widget' ),
			array( 'description' => 'Displays a feed with a thumbnail, title, and excerpt' ) // Arguments
		);

	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( isset( $args['before_widget'] ) ) {

			echo esc_attr( $args['before_widget'] );

		}

		//RSS feed source.
		if ( ! empty( $instance['rss_feed_url'] ) ) {

			$pn_rss = fetch_feed( $instance['rss_feed_url'] );

		} else {
			 esc_attr_e( 'The feed url is missing. In the admin set this in the Story Feed Widget' );
		}

		if ( ! is_wp_error( $pn_rss ) ) {

			if ( ! empty( $instance['image_width'] ) ) {

				$pn_image_width = $instance['image_width'];

			} else {
				$pn_image_width = 100;
			}

			if ( ! empty( $instance['num_of_feed_items'] ) ) {

				$pn_max_items = $pn_rss->get_item_quantity( $instance['num_of_feed_items'] );

			} else {

				$pn_max_items = $pn_rss->get_item_quantity( 3 );
			}

			if ( ! empty( $instance['excerpt_words_number'] ) ) {

				$pn_excerpt_words_number = (int) $instance['excerpt_words_number'];

			} else {

				$pn_excerpt_words_number = 14;
			}

			//build an array starting at the first item.
			$pn_rss_items = $pn_rss->get_items( 0, $pn_max_items );

		}

		if ( 0 == $pn_max_items ) { ?>
					
					<div><?php esc_attr_e( 'Sorry, there are currently no Feed Stories.', 'pn-widget-story-feed' ); ?></div>
						
	<?php } else { ?>
				<?php echo '<h3>' . esc_attr( $instance['title'] ) . '</h3>'; ?>
				  <ul id="pn-story-feed-wrapper">
					<?php foreach ( $pn_rss_items  as $item ) { ?>
						<li>
						<?php

							$pn_feed_title = $this->remove_phrases( $instance['remove_phrases'], $item->get_title() );

							$pn_feed_excerpt = $this->remove_phrases( $instance['remove_phrases'], wp_trim_words( $item->get_description(), ( (int) $pn_excerpt_words_number ) ) );

							$pn_story_feed_thumb = $item->get_enclosure();

							$pn_story_thumb_url = $pn_story_feed_thumb->get_link();

						if ( isset( $pn_story_thumb_url ) ) {

								echo '<a href="' . esc_url( $item->get_permalink() ) . '" target="_blank"><h3>' . esc_html( $pn_feed_title ) . '</h3></a>';

								echo '<a href="' . esc_url( $item->get_permalink() ) . '" target="_blank"><img  width="' . (int) $pn_image_width . '" src="' . esc_url( $pn_story_feed_thumb->get_link() ) . '" alt="' . esc_attr( $pn_feed_title ) . '"/></a>';

								echo '<p>' . esc_html( $pn_feed_excerpt ) . '</p>';

								echo '<br />';

								echo '<hr />';
						} else {

								echo '<a href="' . esc_url( $item->get_permalink() ) . '" target="_blank"><h2>' . esc_html( $item->get_title() ) . '</h2></a>';

								echo '<p>' . esc_html( $pn_feed_excerpt ) . '</p>';

								echo '<br />';

								echo '<hr />';
						} 
						?>
						   </li>
					   
						<?php } ?>
					</ul>			        
			<?php 
	}
	if ( isset( $args['after_widget'] ) ) {

			echo esc_attr( $args['after_widget'] );

	}

	}


	// Widget Backend
	public function form( $instance ) {

		// Widget admin form
		?>
		<ul id='pn-stories-feed'>
			<li>
				 <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"> Widget Title <br /></label>	
		
					<input 
						type='text' 
						class='widefat' 
						id='<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>' 
						name='<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>' 
						value='<?php echo esc_attr( $instance['title'] ); ?>' 
						placeholder='Title Here'
					/>
		
			</li>
			<li>
				<label for="<?php echo esc_attr( $this->get_field_id( 'rss_feed' ) ); ?>"> Feed Url <br /></label>
					
					<input 
						type='text' 
						class='widefat' 
						id='<?php echo esc_attr( $this->get_field_id( 'rss_feed_url' ) ); ?>' 
						name='<?php echo esc_attr( $this->get_field_name( 'rss_feed_url' ) ); ?>'  
						value='<?php echo esc_attr( $instance['rss_feed_url'] ); ?>' 
						placeholder='http://somedomain.com/parameters/feed'
					/>
			
			</li>
			<li>
				<label for="<?php echo esc_attr( $this->get_field_id( 'num_of_feed_items' ) ); ?>"> (Optional) Num Of Feed Items To Display <br /></label>
					
					<input 
						type='text' 
						class='widefat' 
						id='<?php echo esc_attr( $this->get_field_id( 'num_of_feed_items' ) ); ?>' 
						name='<?php echo esc_attr( $this->get_field_name( 'num_of_feed_items' ) ); ?>'  
						value='<?php echo esc_attr( $instance['num_of_feed_items'] ); ?>' 
						placeholder='5'
					/>
			
			</li>
			
			<li>
				<label for="<?php echo esc_attr( $this->get_field_id( 'image_width' ) ); ?>"> (Optional) Image Width ( numbers, no px ) <br /></label>
					
					<input 
						type='text' 
						class='widefat' 
						id='<?php echo esc_attr( $this->get_field_id( 'image_width' ) ); ?>' 
						name='<?php echo esc_attr( $this->get_field_name( 'image_width' ) ); ?>'  
						value='<?php echo esc_attr( $instance['image_width'] ); ?>' 
						placeholder='100'
					/>
			
				
			</li>
			<li>
				<label for="<?php echo esc_attr( $this->get_field_id( 'excerpt_words_number' ) ); ?>"> (Optional) Excerpt Words Number <br /></label>
					
					<input 
						type='text' 
						class='widefat' 
						id='<?php echo esc_attr( $this->get_field_id( 'excerpt_words_number' ) ); ?>' 
						name='<?php echo esc_attr( $this->get_field_name( 'excerpt_words_number' ) ); ?>'  
						value='<?php echo esc_attr( $instance['excerpt_words_number'] ); ?>' 
						placeholder='14'
					/>
			
			</li>
			<li id='pn-phrases-to-remove'>
				<label for=="<?php echo esc_attr( $this->get_field_id( 'remove_phrases' ) ); ?>"> (Optional) Phrases To Remove:<br /></label>	
				<p>
					You can add regex to the remove phrases. You do not need to specify regex delimiters.
				</p>
				
				<p>
					Here is an example of two valid remove phrases:	
				</p>
				
				<pre>Sponsored by company: ~ [a-zA-Z]+ in a series</pre>
				
				<p>
					This will remove "Sponsored by company" and " First in a series, Second in a series, Third in ..." from 
					the title and feed body.
				</p>
				
				<p>
					You can use any valid PHP compatible regex expression. These patterns will be applied to both the title 
					and body.
				</p>
				
				<p>
					The ~ character delimits your phrases.
				<p>
				
					<textarea 
						class='widefat' 
						rows='16' 
						cols='20' 
						id='<?php echo esc_attr( $this->get_field_id( 'remove_phrases' ) ); ?>'
						name='<?php echo esc_attr( $this->get_field_name( 'remove_phrases' ) ); ?>'
						placeholder='Sponsored By: ~ [a-zA-Z]+ in a series'
					><?php echo ( esc_textarea( $instance['remove_phrases'] ) ); ?></textarea>
				
			</li>
		</ul>
		<?php
	}

	// update
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] =
		( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		$instance['num_of_feed_items'] =
		( ! empty( $new_instance['num_of_feed_items'] ) ) ? (int) $new_instance['num_of_feed_items'] : '';

		$instance['rss_feed_url'] =
		( ! empty( $new_instance['rss_feed_url'] ) ) ? esc_url_raw( $new_instance['rss_feed_url'] ) : '';

		$instance['remove_phrases'] =
		sanitize_text_field( $new_instance['remove_phrases'] );

		$instance['image_width'] =
		(int) $new_instance['image_width'];

		$instance['excerpt_words_number'] =
		(int) $new_instance['excerpt_words_number'];

		return $instance;

	}

	/**
	 * txt_trim
	 */
	protected function txt_trim( $string ) {

		return ltrim( rtrim( $string ) );

	}

	/**
	 * Remove Phrases from titles and feed body.
	 *
	 *
	 * @param string $remove_phrases - in the widget
	 *     here is an example of two remove phrases; one has a regex match
	 *     : "Sponsored by Prostate Cancer Canada: ~ [a-zA-Z]+ in a series
	 * @param string $string
	 * @return string the processed string with any removed phrases that applied.
	 */
	protected function remove_phrases( $remove_phrases, $string ) {

		if ( ! empty( $remove_phrases ) ) {
			$remove_phrases = explode( '~', $remove_phrases );  // * is delimeter as it is not commonly used in feed titles or bodies

			if ( is_array( $remove_phrases ) ) {

				$altered_string = $string;

				foreach ( $remove_phrases as $remove_phrase ) { // apply each remove phrase to the passed in string.

					$pattern = $this->txt_trim( $remove_phrase ); // trim white space at the beginning and ending or phrase

					$pattern = '/' . $pattern . '/'; // Add the regex delimiters

					preg_match( $pattern, $string, $matches ); // see if reqex patter exists in the remove phrase

					if ( ! empty( $matches ) ) {

						// regex exists so replace based on the regex in the remove phrase.
						$altered_string = preg_replace( '/' . $matches[0] . '/', '', $altered_string );

					} else {

						// regex does not exist so do a more basic str_replace()
						$altered_string = str_replace( $this->txt_trim( $remove_phrase ), '', $altered_string );

					}
				}

				return $altered_string;

			} else {

				return $string; // Did nothing returning it as is

			}
		} else {

			return $string;

		}

	}
}

add_action(
	'widgets_init',
	function() {

		register_widget( 'PN_Stories_Widget' );

	}
);
