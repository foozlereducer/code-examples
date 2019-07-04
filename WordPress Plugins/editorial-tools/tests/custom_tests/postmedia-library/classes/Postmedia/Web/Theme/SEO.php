<?php

namespace Postmedia\Web\Theme;

class SEO {

	public function __construct() {
		// Actions
		add_action( 'wp_head', array( $this, 'action_head_meta_gwt' ) );
		add_action( 'wp_head', array( $this, 'action_head_meta_genre' ) );
		add_action( 'wp_head', array( $this, 'action_head_meta_keywords' ), -1 ); // Don't change priority
		add_action( 'wp_head', array( $this, 'action_head_link_publisher' ) );
		add_action( 'wp_head', array( $this, 'action_feed_links' ), 2 );
		add_action( 'wp_head', array( $this, 'action_rel_canonical' ) );
		add_action( 'create_term', array( $this, 'action_seo_category_save' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'action_seo_category_save' ), 10, 3 );
		add_action( 'category_edit_form', array( $this, 'action_seo_category_form' ), 1, 3 );
		add_action( 'admin_menu', array( $this, 'action_custom_seo_admin_page' ) );

		// Remove actions
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}


	/**
	 * Add GWT verification code to <head />
	 * @todo This should be in a class that implements the 'google_site_verification' setting
	 * @return void
	 */
	public function action_head_meta_gwt() {
		$google_site_verification = get_option( 'google_site_verification' );

		if ( ! empty( $google_site_verification ) ) {
			?>
			<meta name="google-site-verification" content="<?php echo esc_attr( $google_site_verification ); ?>" />
			<?php
		} else {
			?>
			<meta name="google-site-verification" content="ljXFsNxnI10A5Du893byKprf90Ycw4E4nL7sYyc7wTU" />
			<?php
		}
	}

	/**
	 * Add genre meta to <head />
	 * @return void
	 */
	public function action_head_meta_genre() {
		if ( is_single() ) {
			?>
			<meta itemprop="genre" content="News" />
			<?php
		}
	}

	/**
	 * Add keywords/description meta to <head />
	 * Overrides Add Meta Tags Mod plugin
	 * @return void
	 */
	public function action_head_meta_keywords() {
		global $posts, $mt_add_meta_tags;
		// Override Add Meta Tags Mod plugin for story pages
		if ( isset( $mt_add_meta_tags ) && is_single() ) {

			// Remove existing hook for Add_Meta_Tags plugin
			remove_action( 'wp_head', array( $mt_add_meta_tags, 'amt_add_meta_tags' ), 0 );

			// Description
			$meta_description = get_post_meta( $posts[0]->ID, 'mt_seo_description', true );
			if ( empty( $meta_description ) && method_exists( $mt_add_meta_tags, 'amt_get_the_excerpt' ) ) {
				 $meta_description = $mt_add_meta_tags->amt_get_the_excerpt();
			}
			$meta_description = apply_filters( 'amt_meta_description', $meta_description );
			if ( ! empty( $meta_description ) ) {
				?>
				<meta name="description" content="<?php echo esc_attr( $mt_add_meta_tags->amt_clean_desc( $meta_description ) ); ?>" />
				<?php
			}

			// Keywords
			$news_keywords = get_post_meta( $posts[0]->ID, 'mt_seo_keywords', true );
			if ( empty( $news_keywords ) && method_exists( $mt_add_meta_tags, 'amt_get_post_tags' ) ) {
				$news_keywords = strtolower( $mt_add_meta_tags->amt_get_post_tags() );
			}

			?>
			<meta name="news_keywords" content="<?php echo esc_attr( $news_keywords ); ?>" />
			<?php
		}
	}

	/**
	 * Add Publisher link to <head />
	 * @todo This should be in a class that implements the 'google_plus' setting
	 * @return void
	 */
	public function action_head_link_publisher() {
		$google_plus = get_option( 'google_plus' );

		if ( ! empty( $google_plus ) ) {
			?>
			<link rel="publisher" href="<?php echo esc_url( $google_plus ); ?>" />
			<?php
		} else {
			?>
			<link rel="publisher" href="https://plus.google.com/100051279444980527829" />
			<?php
		}
	}

	/**
	 * Display the links to the general feeds.
	 * Taken from feed_links() with comments feed link removed
	 * @param  array  $args
	 * @return void
	 */
	public function action_feed_links( $args = array() ) {
		if ( ! current_theme_supports( 'automatic-feed-links' )
			|| is_single() ) {
			return;
		}

		$defaults = array(
			/* translators: Separator between blog name and feed type in feed links */
			'separator'	=> _x( '&raquo;', 'feed link' ),
			/* translators: 1: blog title, 2: separator (raquo) */
			'feedtitle'	=> __( '%1$s %2$s Feed' ),
			/* translators: 1: blog title, 2: separator (raquo) */
			'comstitle'	=> __( '%1$s %2$s Comments Feed' ),
		);

		$args = wp_parse_args( $args, $defaults );

		echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' . esc_attr( sprintf( $args['feedtitle'], get_bloginfo( 'name' ), $args['separator'] ) ) . '" href="' . esc_url( get_feed_link() ) . "\" />\n";
	}

	/**
	 * Add canonical link to <head /> excluding single post/page
	 * Borrowed from Yoast's WP SEO
	 * @return void
	 */
	public function action_rel_canonical() {
		if ( is_singular() ) {
			return;
		}

		if ( is_search() ) {
			$canonical = get_search_link();
		} else if ( is_front_page() ) {
			$canonical = home_url( '/' );
		} else if ( ( is_home() && 'page' == get_option( 'show_on_front' ) ) ) {
			$canonical = get_permalink( get_option( 'page_for_posts' ) );
		} else if ( is_tax() || is_tag() || is_category() ) {
			$term      = get_queried_object();
			$canonical = wpcom_vip_get_term_link( $term, $term->taxonomy );
		} else if ( is_post_type_archive() ) {
			$canonical = get_post_type_archive_link( get_query_var( 'post_type' ) );
		} else if ( is_author() ) {
			$canonical = get_author_posts_url(
				get_query_var( 'author' ),
				get_query_var( 'author_name' )
			);
		} else if ( is_archive() ) {
			if ( is_date() ) {
				if ( is_day() ) {
					$canonical = get_day_link(
						get_query_var( 'year' ),
						get_query_var( 'monthnum' ),
						get_query_var( 'day' )
					);
				} else if ( is_month() ) {
					$canonical = get_month_link(
						get_query_var( 'year' ),
						get_query_var( 'monthnum' )
					);
				} else if ( is_year() ) {
					$canonical = get_year_link( get_query_var( 'year' ) );
				}
			}
		}

		if ( $canonical && get_query_var( 'paged' ) > 1 ) {
			global $wp_rewrite;

			if ( ! $wp_rewrite->using_permalinks() ) {
				$canonical = add_query_arg( 'paged', get_query_var( 'paged' ), $canonical );
			} else {
				if ( is_front_page() ) {
					$base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '/';
					$canonical = home_url( $base );
				}

				$canonical = user_trailingslashit(
					trailingslashit( $canonical )
					. trailingslashit( $wp_rewrite->pagination_base )
					. get_query_var( 'paged' )
				);
			}
		}

		if ( $canonical && ! is_wp_error( $canonical ) ) {
			printf(
				'<link rel="canonical" href="%s" />%s',
				esc_url( $canonical, null, 'other' ),
				"\n"
			);
		}
	}

	/**
	 * Get the category SEO title from a large option record and display it in the form on the category edit page
	 * @param  string $_tag
	 * @return void
	 */
	public function action_seo_category_form( $_tag ) {
		if ( function_exists( 'wlo_get_option' ) ) {
			// Get the current category ID
			$_term_id = $_tag->term_id;

			// Get the SEO title from large option
			$_title_seo = wlo_get_option( 'pn_category_seo_title_' . intval( $_term_id ), '' );

			wp_nonce_field( 'update_seo_category_title', 'seo_category_title_nonce' );

			echo '<table class="form-table"><tbody>';
			echo '<tr class="form-field">';
			echo '<th valign="top" scope="row"><label for="pn_category_seo_title">SEO Title</label></th>';
			echo '<td><input type="text" name="pn_category_seo_title" id="pn_category_seo_title" value="' . esc_attr( $_title_seo ) . '" /></td>';
			echo '</tr>';
			echo '</tbody></table>' . "\n";
		}
	}

	/**
	 * Set the category SEO title in a large option record
	 * @param  int $_term_id  Term ID
	 * @param  int $_ttid     Term taxonomy ID
	 * @param  string $_taxonomy Taxonomy slug
	 * @return void
	 */
	public function action_seo_category_save( $_term_id, $_ttid, $_taxonomy ) {
		// Only on category edit pages, not other terms like Easy Sidebars and tags
		if ( 'category' === $_taxonomy && function_exists( 'wlo_update_option' ) ) {
			if ( isset( $_POST['seo_category_title_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['seo_category_title_nonce'] ) ), 'update_seo_category_title' ) ) {
				if ( isset( $_POST['pn_category_seo_title'] ) ) {
					$_title_seo = sanitize_text_field( wp_unslash( $_POST['pn_category_seo_title'] ) );

					// Save the SEO title as large option
					wlo_update_option( 'pn_category_seo_title_' . intval( $_term_id ), $_title_seo );
				}
			}
		}
	}

	/**
	 * Creates 'Custom SEO' option under Settings Menu
	 * @return void
	 */
	public function action_custom_seo_admin_page() {
		// This page will be under "Settings"
		add_options_page(
			'Custom SEO Settings',
			'Custom SEO',
			'manage_options',
			'custom-seo',
			array( $this, 'display_custom_seo_admin_page' )
		);
	}

	/**
	 * Sets up the admin form for customizing the title and meta description tags
	 * @return void
	 */
	public function display_custom_seo_admin_page() {

		// Update Options table
		if ( isset( $_POST['custom_seo_values_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['custom_seo_values_nonce'] ) ), 'update_custom_seo_values' ) ) {
			if ( isset( $_POST['custom_seo_submit'] ) ) {

				$page_list = array( 'home', 'category', 'tag', 'static_page', 'gallery', 'author', 'archive_day', 'archive_month', 'archive_year' );
				// Loop to sanitize and save each option
				foreach ( $page_list as $each ) {
					$title = 'seo_title_' . $each;
					$meta = 'seo_meta_' . $each;

					if ( isset( $_POST[ $title ] ) ) {
						update_option( $title, sanitize_text_field( wp_unslash( $_POST[ $title ] ) ), $title );
					}
					if ( isset( $_POST[ $meta ] ) ) {
						update_option( $meta, sanitize_text_field( wp_unslash( $_POST[ $meta ] ) ), $meta );
					}
				}
			}
		}

		// Retrieve values from Options table
		$meta_array = array(
							'home'          => get_option( 'seo_meta_home' ),
							'category'      => get_option( 'seo_meta_category' ),
							'tag'           => get_option( 'seo_meta_tag' ),
							'static_page'	=> get_option( 'seo_meta_static_page' ),
							'gallery'       => get_option( 'seo_meta_gallery' ),
							'author'        => get_option( 'seo_meta_author' ),
							'archive_day'	=> get_option( 'seo_meta_archive_day' ),
							'archive_month' => get_option( 'seo_meta_archive_month' ),
							'archive_year'  => get_option( 'seo_meta_archive_year' ),
						);

		$title_array = array(
							'home'          => get_option( 'seo_title_home' ),
							'category'      => get_option( 'seo_title_category' ),
							'tag'           => get_option( 'seo_title_tag' ),
							'static_page'	=> get_option( 'seo_title_static_page' ),
							'gallery'       => get_option( 'seo_title_gallery' ),
							'author'        => get_option( 'seo_title_author' ),
							'archive_day'	=> get_option( 'seo_title_archive_day' ),
							'archive_month' => get_option( 'seo_title_archive_month' ),
							'archive_year'  => get_option( 'seo_title_archive_year' ),
						);
		?>

		<div class='wrap'>
			<h1>Custom SEO Settings</h1>
			<form method='post'>
				<?php wp_nonce_field( 'update_custom_seo_values', 'custom_seo_values_nonce' ); ?>
				<h4>HOME PAGE:</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_home' id='seo_title_home' value='<?php echo esc_html( $title_array['home'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td><textarea id='seo_meta_home' name='seo_meta_home' rows='2' cols='60'><?php echo esc_html( $meta_array['home'] ) ?></textarea></td>
					</tr>
				</table>
				<br /><hr />
				<h4>CATEGORY:</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_category' id='seo_title_category' value='<?php echo esc_html( $title_array['category'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td>Category description can be configured on the Edit Category page. (Posts > Categories > [Category])</td>
					</tr>
				</table>
				<br /><hr />
				<h4>TAG:</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_tag' id='seo_title_tag' value='<?php echo esc_html( $title_array['tag'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td><textarea id='seo_meta_tag' name='seo_meta_tag' rows='2' cols='60'><?php echo esc_html( $meta_array['tag'] ) ?></textarea></td>
					</tr>
				</table>
				<br /><hr />
				<h4>STATIC PAGE</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_static_page' id='seo_title_tag' value='<?php echo esc_html( $title_array['static_page'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td>Static Page description can be configured on the Edit Page page. (Pages > [Page])</td>
					</tr>
				</table>
				<br /><hr />
				<h4>GALLERY ARCHIVE</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_gallery' id='seo_title_gallery' value='<?php echo esc_html( $title_array['gallery'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td><textarea id='seo_meta_gallery' name='seo_meta_gallery' rows='2' cols='60'><?php echo esc_html( $meta_array['gallery'] ) ?></textarea></td>
					</tr>
				</table>				
				<br /><hr />
				<h4>AUTHOR:</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_author' id='seo_title_author' value='<?php echo esc_html( $title_array['author'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td><textarea id='seo_meta_author' name='seo_meta_author' rows='2' cols='60'><?php echo esc_html( $meta_array['author'] ) ?></textarea></td>
					</tr>
				</table>
				<br /><hr />
				<h4>ARCHIVE (Full date):</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_archive_day' id='seo_title_archive_day' value='<?php echo esc_html( $title_array['archive_day'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td><textarea id='seo_meta_archive_day' name='seo_meta_archive_day' rows='2' cols='60'><?php echo esc_html( $meta_array['archive_day'] ) ?></textarea></td>
					</tr>
				</table>
				<br /><hr />
				<h4>ARCHIVE (Month/Year):</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_archive_month' id='seo_title_archive_month' value='<?php echo esc_html( $title_array['archive_month'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td><textarea id='seo_meta_archive_month' name='seo_meta_archive_month' rows='2' cols='60'><?php echo esc_html( $meta_array['archive_month'] ) ?></textarea></td>
					</tr>
				</table>
				<br /><hr />
				<h4>ARCHIVE (Year):</h4>
				<table>
					<tr>
						<td>Title Tag:</td>
						<td><input type='text' size='62' name='seo_title_archive_year' id='seo_title_archive_year' value='<?php echo esc_html( $title_array['archive_year'] ) ?>' /></td>
					</tr>
					<tr>
						<td>Meta Description:</td>
						<td><textarea id='seo_meta_archive_year' name='seo_meta_archive_year' rows='2' cols='60'><?php echo esc_html( $meta_array['archive_year'] ) ?></textarea></td>
					</tr>
				</table>
				<p><input type='submit' value='Save' class='button button-primary' name='custom_seo_submit' /></p>
			</form>
		</div>
		<hr />
		<div class='meta_tag_placeholders'>
			<h2>Placeholders</h2>
			<p>Use the following placeholders in the fields above. They will be replaced with their actual value when the title and meta tags are rendered:</p>
			<table cellpadding='5' border='1' style='border-collapse:collapse'>
				<tr>
					<td bgcolor="#e3e3e3"><b>Placeholder</b></td>
					<td bgcolor="#e3e3e3"><b>Description</b></td>
					<td bgcolor="#e3e3e3"><b>Example</b></td>
					<td bgcolor="#e3e3e3"><b>WP-Admin Location</b></td>
				</tr>
				<tr>
					<td>#site_name#</td>
					<td>Website's Name</td>
					<td>The Province</td>
					<td>Settings > General</td>
				</tr>
				<tr>
					<td>#tagline#</td>
					<td>Website's Tagline</td>
					<td>Vancouver, BC News, Sports &amp; Breaking Headlines</td>
					<td>Settings > General</td>
				</tr>
				<tr>
					<td>#seo_title#</td>
					<td>Category's SEO Title</td>
					<td>Latest News From BC, Canada &amp; The World</td>
					<td>Posts > Categories > [Category]</td>
				</tr>
				<tr>
					<td>#category#</td>
					<td>Category Name</td>
					<td>Local News</td>
					<td>Posts > Categories > [Category]</td>
				</tr>
				<tr>
					<td>#tag#</td>
					<td>Tag Name</td>
					<td>Culture &amp; Lifestyle</td>
					<td>Posts > Tags > [Tag]</td>
				</tr>
				<tr>
					<td>#page_title#</td>
					<td>Static Page Title</td>
					<td>Local Weather Page</td>
					<td>Pages > [Page]</td>
				</tr>
				<tr>
					<td>#author#</td>
					<td>Author's Name</td>
					<td>John Doe</td>
					<td>Users > All Users</td>
				</tr>
				<tr>
					<td>#archive_day#</td>
					<td>Archive Day</td>
					<td>23</td>
					<td><i>n/a</i></td>
				</tr>
				<tr>
					<td>#archive_month#</td>
					<td>Archive Month</td>
					<td>January</td>
					<td><i>n/a</i></td>
				</tr>
				<tr>
					<td>#archive_year#</td>
					<td>Archive Year</td>
					<td>2016</td>
					<td><i>n/a</i></td>
				</tr>
				<tr>
					<td>#page#</td>
					<td>Page Number</td>
					<td>Page 3</td>
					<td><i>n/a</i></td>
				</tr>
			</table>
		</div>
		<?php
	}


	/**
	* Gets the meta description tag value for the current page
	* @param  string $page_type
	* @param  array  $meta_options
	* @return string $meta_description
	*/
	public function get_meta_description( $meta_options ) {
		global $post;
		$meta_description = '';

		if ( is_home() ) {
			add_filter( 'amt_metatags', 'remove_amt_meta_tags', 10, 3 ); //remove AMT-set meta tags
			$meta_description = trim( get_option( 'seo_meta_home' ) );
		} elseif ( is_category() ) {
			add_filter( 'amt_metatags', 'remove_amt_meta_tags', 10, 3 ); //remove AMT-set meta tags
			$meta_description = wpcom_vip_get_meta_desc();
		} elseif ( is_tag() ) {
			add_filter( 'amt_metatags', 'remove_amt_meta_tags', 10, 3 ); //remove AMT-set meta tags
			$meta_description = trim( get_option( 'seo_meta_tag' ) );
			if ( '' != $meta_description ) {
				global $wp_query;
				$term_id = intval( $wp_query->get_queried_object()->term_id );
				$term = get_tag( $term_id );
				$term_name = $term->name;
				$meta_options['tag'] = $term_name;
			}
		} elseif ( is_author() ) {
			$meta_description = trim( get_option( 'seo_meta_author' ) );
			$author_id = get_the_author_meta( 'ID' );
			$meta_options['author'] = get_the_author_meta( 'first_name', $author_id ) . ' ' . get_the_author_meta( 'last_name', $author_id );
		} elseif ( is_archive() ) {
			if ( is_post_type_archive( 'gallery' ) ) {
				$meta_description = get_option( 'seo_meta_gallery' );
			} else {
				$year  = intval( get_query_var( 'year' ) );
				$month = intval( get_query_var( 'monthnum' ) );
				$day   = intval( get_query_var( 'day' ) );

				$meta_options['archive_year'] = $year; // all archive fields need a year

				if ( 0 < $year && is_year() ) {
					$meta_description = trim( get_option( 'seo_meta_archive_year' ) );
				} elseif ( 0 < $month && is_month() ) {
					$meta_description = trim( get_option( 'seo_meta_archive_month' ) );
					$month = $GLOBALS['wp_locale']->get_month( $month ); // month name
					$meta_options['archive_month'] = $month;
				} elseif ( 0 < $day && is_day() ) {
					$meta_description = trim( get_option( 'seo_meta_archive_day' ) );
					$month = $GLOBALS['wp_locale']->get_month( $month ); // month name
					$meta_options['archive_month'] = $month;
					$meta_options['archive_day'] = $day;
				}
			}
		}

		if ( '' != $meta_description ) {
			// Replace placeholders with actual values
			foreach ( $meta_options as $key => $value ) {
				$regex = '/#' . $key . '#/';
				$meta_description = preg_replace( $regex, $value, $meta_description );
			}
		}

		return trim( $meta_description );
	}


	/**
	* Gets the title tag value for the current page
	* @param  string $page_type
	* @param  array  $title_options
	* @return string $title_text
	*/
	public function get_title_tag_value( $title_options = '' ) {

		$page_number = intval( get_query_var( 'paged' ) );
		$title_options['page'] = $page_number;
		$title_text = '';

		if ( is_home() ) {
			$title_text = get_option( 'seo_title_home' );
		} elseif ( is_category() ) {
			$title_text = get_option( 'seo_title_category' );

			if ( '' != $title_text ) {
				global $wp_query;
				$term_id = intval( $wp_query->get_queried_object()->term_id );

				if ( 0 < $term_id && function_exists( 'wlo_get_option' ) ) {
					$seo_title = wlo_get_option( 'pn_category_seo_title_' . intval( $term_id ), '' );
				}
				if ( '' != $seo_title ) {
					$title_options['seo_title'] = $seo_title;
				} else {
					$title_text = '';
				}
			}
		} elseif ( is_tag() ) {
			$title_text = get_option( 'seo_title_tag' );

			if ( '' != $title_text ) {
				global $wp_query;
				$term_id = intval( $wp_query->get_queried_object()->term_id );

				if ( 0 < $term_id ) {
					$tag_object = get_tag( $term_id );
					$tag_name = $tag_object->name;
				}
				if ( '' != $tag_name ) {
					$title_options['tag'] = $tag_name;
				} else {
					$title_text = '';
				}
			}
		} elseif ( is_page() ) {
			$title_text = get_option( 'seo_title_static_page' );

			if ( '' != $title_text ) {
				$post_id = get_the_ID();
				$seo_title = (string) get_post_meta( $post_id, 'mt_seo_title', true );
				$page_title = get_the_title();

				if ( '' != $seo_title ) {
					// replace %title% placeholder
					$title_options['page_title'] = str_replace( '%title%', $page_title, $seo_title );
				} else {
					$title_options['page_title'] = $page_title;
				}
			}
		} elseif ( is_author() ) {
			$title_text = get_option( 'seo_title_author' );

			if ( '' != $title_text ) {
				$author_id = get_the_author_meta( 'ID' );
				if ( '' != $author_id ) {
					$title_options['author'] = get_the_author_meta( 'first_name', $author_id ) . ' ' . get_the_author_meta( 'last_name', $author_id );
				}
			}
		} elseif ( is_archive() ) {

			if ( is_post_type_archive( 'gallery' ) ) {
				$title_text = get_option( 'seo_title_gallery' );
			} else {
				$year  = intval( get_query_var( 'year' ) );
				$month = intval( get_query_var( 'monthnum' ) );
				$day   = intval( get_query_var( 'day' ) );

				$title_options['archive_year'] = $year; // all archive fields need a year

				if ( 0 < $year && is_year() ) {
					$title_text = get_option( 'seo_title_archive_year' );
				} elseif ( 0 < $month && is_month() ) {
					$title_text = get_option( 'seo_title_archive_month' );
					if ( '' != $title_text ) {
						$month = $GLOBALS['wp_locale']->get_month( $month ); // month name
						$title_options['archive_month'] = $month;
					}
				} elseif ( 0 < $day && is_day() ) {
					$title_text = get_option( 'seo_title_archive_day' );
					if ( '' != $title_text ) {
						$month = $GLOBALS['wp_locale']->get_month( $month ); // month name
						$title_options['archive_month'] = $month;
						$title_options['archive_day'] = $day;
					}
				}
			}
		}

		if ( '' != $title_text ) {
			// Replace placeholders with actual values
			foreach ( $title_options as $key => $value ) {
				$regex = '/#' . $key . '#/';
				if ( 'page' == $key ) {
					if ( 1 < $page_number ) {
						$title_text = preg_replace( $regex, 'Page ' . $page_number, $title_text );
					} else {
						$title_text = preg_replace( '/#page# \|/ ', '', $title_text );
					}
					$title_text = preg_replace( $regex, $value, $title_text );
				} else {
					$title_text = preg_replace( $regex, $value, $title_text );
				}
			}
		} else {
			// Use default WP title
			$title_text = wp_title( '|', false, 'right' ) . $title_options['site_name'];
		}

		return trim( $title_text );
	}
}
