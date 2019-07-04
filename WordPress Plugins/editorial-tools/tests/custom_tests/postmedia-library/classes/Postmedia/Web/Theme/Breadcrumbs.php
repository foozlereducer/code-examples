<?php

namespace Postmedia\Web\Theme;

use Postmedia\Web\TemplateEngine;

class Breadcrumbs {

	public $items = array();

	public $top_most_cat = 0;

	public $category_ids = array();

	public $menu_items = array();

	public $child_items = array();

	public $color_classes = array(
		2 => 'light',
		3 => 'lighter',
	);


	public function __construct() {
		$this->collect_items();
	}


	public function build_item( $item, $is_primary = true ) {
		$cat_object = get_term( $item->object_id, 'category' );

		if ( $is_primary ) {
			$classes = array_unique(
				array_merge(
					array( $cat_object->slug, 'sub' ),
					array_filter( $item->classes )
				)
			);

			if ( null == $cat_object && '' !== $item->ID ) {
				wp_reset_postdata();
				$the_post = get_post( $item->object_id );
				$classes = array_unique(
					array_merge(
						array( $the_post->post_name, 'sub' ),
						array_filter( $item->classes )
					)
				);
			}
		} else {
			$classes = array();
		}

		$url = $item->url;

		if ( 'custom' != $item->type && $cat_object ) {
			$url = wpcom_vip_get_term_link( $cat_object );
		}

		$item = array(
			'type'    => 'category',
			'id'      => ( null !== $item->object_id ? $item->object_id : $item->ID ),
			'label'   => $this->get_label( $item, $is_primary ),
			'url'     => $url,
			'object'  => $item,
			'class'   => $classes,
			'primary' => $is_primary,
		);

		return $item;
	}


	/**
	 * Add category to breadcrumbs items
	 *
	 * @param object $cat_object Category object
	 */
	public function add_category( $item ) {
		if ( ! is_object( $item ) ) {
			return;
		}

		$this->category_ids[] = $item->object_id;
		$cat_item = $this->build_item( $item );

		if ( ! empty( $item->menu_item_parent ) ) {
			$this->add_category( $this->get_category_from_menu( $item->menu_item_parent ) );
		} else {
			$this->top_most_cat = $item->db_id;
		}

		$this->items[] = $cat_item;
	}


	/**
	 * Get current category or current posts's main category
	 *
	 * @return object
	 */
	public static function get_main_category() {
		global $current_main_cat;

		if ( is_single() ) {
			$post = get_queried_object();

			$categories = get_the_category( $post->ID );
			if ( ! empty( $categories ) ) {
				$main_category = array_shift( $categories );
			}
		} else if ( is_category() ) {
			$main_category = get_queried_object();
		} else if ( is_page() ) {
			$main_category = get_queried_object();
		}

		$current_main_cat = $main_category;
		return $main_category;
	}


	/**
	 * Collect breadcrumb items
	 */
	public function collect_items() {
		$main_category = self::get_main_category();
		$this->get_menu_items();
		if ( ! empty( $main_category ) ) {
			$custom_breadcrumb = apply_filters( 'pn_breadcrumb_use_custom', false, $main_category );
			if ( false !== $custom_breadcrumb ) {
				return;
			}
			if ( '' == $main_category->term_id ) {
				$object_id_to_get = $main_category->ID;
			} else {
				$object_id_to_get = $main_category->term_id;
			}
			$this->add_category( $this->get_category_from_menu( $object_id_to_get, 'object_id' ) );
		}

		$this->add_menu_items();

		foreach ( $this->items as $index => &$item ) {
			if ( ! empty( $item['primary'] ) && ! empty( $this->color_classes[ $index ] ) ) {
				$item['class'][] = $this->color_classes[ $index ];
			}
		}
	}


	public function get_category_from_menu( $id, $key = 'db_id' ) {
		$item = array_shift(
			wp_list_filter(
				$this->menu_items,
				array( $key => $id )
			)
		);

		return $item;
	}


	public function get_label( $item, $is_primary = true ) {
		$label = esc_html( $item->title );
		return $label;
	}


	/**
	 * Print breadcrumb
	 */
	public function display( $template_root, $template_file, $params = array() ) {
		if ( empty( $this->items ) ) {
			// if no breadcrumbs show feature items on those pages
			if ( false === ( is_home() || is_search() || is_page() || is_author() || is_tag() || is_404() ) ) {
				return false;
			}
		}

		if ( ! $template_root && ! $template_file ) {
			return;
		}

		// Set params if not already provided
		if ( ! isset( $params['items'] ) ) {
			$params['items'] = $this->items;
		}

		if ( ! isset( $params['child_items'] ) ) {
			$params['child_items'] = $this->child_items;
		}

		if ( ! isset( $params['featured_items_global'] ) ) {
			$params['featured_items_global'] = get_option( 'menu-item-global_featured_item' );
		}

		if ( ! isset( $params['featured_items_old'] ) ) {
			$params['featured_items_old'] = get_option( 'menu-item-' . $this->items[0]['object']->ID );
		}

		if ( ! isset( $params['breadcrumb_class'] ) ) {
			$params['breadcrumb_class'] = implode( ' ', (array) apply_filters( 'pn_breadcrumbs_class', $this->items[0]['class'] ) );
		}

		$template_engine = new TemplateEngine( $template_root );
		$template_engine->initialize();

		$template = $template_engine->load_template(
			$template_file,
			$params,
			array(
				'breadcrumbs_context' => &$this,
			)
		);

		$template->allow_override = true;

		$template->render();
	}



	public function get_menu_items() {
		$locations = get_nav_menu_locations();

		if ( empty( $locations['primary'] ) ) {
			return;
		}

		$menu = wp_get_nav_menu_object( $locations['primary'] );
		$menu_items = wp_get_nav_menu_items(
			$menu->term_id,
			array( 'update_post_term_cache' => false )
		);

		$this->menu_items = $menu_items;
	}


	public function add_menu_items() {
		global $current_main_cat;

		if ( empty( $this->menu_items ) || empty( $this->top_most_cat ) ) {
			return;
		}

		$current_main_category_cat_id = $current_main_cat->{'cat_ID'};
		$current_main_category_id = $current_main_cat->{'ID'};

		if ( '' !== $current_main_category_cat_id ) {
			$current_category_id = $current_main_category_cat_id;
		} elseif ( '' !== $current_main_category_id ) {
			$current_category_id = $current_main_category_id;
		}

		$children2 = wp_list_filter(
			$this->menu_items,
			array(
				'object_id' => $current_category_id,
			)
		);

		$parent_menu = array_shift( $children2 );
		$children = wp_list_filter(
			$this->menu_items,
			array(
				'menu_item_parent' => $parent_menu->db_id,
			)
		);

		if ( empty( $children ) ) {
			return;
		}

		foreach ( $children as $item ) {
			if ( in_array( $item->object_id, $this->category_ids ) ) {
				continue;
			}

			$this->child_items[] = $this->build_item( $item, false );
		}
	}
}
