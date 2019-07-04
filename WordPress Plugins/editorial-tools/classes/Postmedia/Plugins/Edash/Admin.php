<?php
namespace Postmedia\Plugins\Edash;

/**
 * Admin Class
 * Registers the pn_pointer custom post type and adds admin pages to manage pointers.
 */
class Admin {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	function init() {
		$this->register_pointer_post_type();
	}

	function admin_init() {
		add_action( 'add_meta_boxes', array( $this, 'add_pointer_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 1, 2 );
	}

	function admin_notices() {
		global $pagenow, $typenow;

		if ( 'post-new.php' !== $pagenow || 'pn_pointer' !== $typenow ) {
			return;
		}

		echo '<div class="notice notice-warning">';
		echo '<p>';
		echo 'This page is for external pointers only. If you want to create a pointer to another Postmedia property, go to the <a href="' . esc_url( admin_url( 'admin.php?page=ed_wcm_editorial_dashboard' ) ) . '">Editorial Dashboard</a>.';
		echo '</p>';
		echo '</div>';
	}

	/**
	* Defines pointer custom post type
	* Adds custom metaboxes for pointer post type
	*/
	function register_pointer_post_type() {

		$labels = array(
			'name'                => _x( 'Pointers', 'Post Type General Name', 'text_domain' ),
			'singular_name'       => _x( 'Pointer', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'           => __( 'Pointers', 'text_domain' ),
			'parent_item_colon'   => __( 'Parent Pointer:', 'text_domain' ),
			'all_items'           => __( 'All Pointers', 'text_domain' ),
			'view_item'           => __( 'View Pointer', 'text_domain' ),
			'add_new_item'        => __( 'Add New External Pointer', 'text_domain' ),
			'add_new'             => __( 'New External Pointer', 'text_domain' ),
			'edit_item'           => __( 'Edit Pointer', 'text_domain' ),
			'update_item'         => __( 'Update Pointer', 'text_domain' ),
			'search_items'        => __( 'Search pointers', 'text_domain' ),
			'not_found'           => __( 'No pointers found', 'text_domain' ),
			'not_found_in_trash'  => __( 'No pointers found in Trash', 'text_domain' ),
		);

		$args = array(
			'label'               => __( 'pn_pointer', 'text_domain' ),
			'description'         => __( 'Pointers', 'text_domain' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'excerpt', 'thumbnail', 'zoninator_zones' ),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);

		register_post_type( 'pn_pointer', $args );
	}

	function add_pointer_meta_boxes() {
		add_meta_box( 'pn_pointer_box', 'More Information', array( $this, 'pointer_meta_box' ), 'pn_pointer', 'normal', 'default' );
	}

	function pointer_meta_box() {
		global $pagenow, $post;

		// Noncename needed to verify where the data originated
		wp_nonce_field( 'pn-pointers-nonce', 'pointermeta_noncename' );

		// Get the URL data if its already been entered
		$pointer_ext_url = get_post_meta( $post->ID, 'pn_pointer_ext_url', true );
		$pointer_byline = get_post_meta( $post->ID, 'pn_pointer_byline', true );
		$pointer_author_email = get_post_meta( $post->ID, 'pn_pointer_author_email', true );
		$pointer_distributor = get_post_meta( $post->ID, 'pn_pointer_distributor', true );
		$pointer_date = get_post_meta( $post->ID, 'pn_pointer_date', true );

		// Echo out the field
		if ( 'post-new.php' !== $pagenow ) {
			$pointer_url = get_post_meta( $post->ID, 'pn_pointer_url', true );
			echo '<strong>Relative URL</strong> ( e.g. /category/subcategory/story+title+here/12345/story.html )';
			echo '<input type="text" name="pn_pointer_url" value="' . esc_attr( $pointer_url ) . '" class="widefat" />';
		}
		echo '<strong>External URL</strong> ( e.g. http://www.domain.com )';
		echo '<input type="text" name="pn_pointer_ext_url" value="' . esc_attr( $pointer_ext_url ) . '" class="widefat" />';
		echo '<strong>Byline</strong>';
		echo '<input type="text" name="pn_pointer_byline" value="' . esc_attr( $pointer_byline ) . '" class="widefat" />';
		echo '<strong>Author Email</strong>';
		echo '<input type="text" name="pn_pointer_author_email" value="' . esc_attr( $pointer_author_email ) . '" class="widefat" />';
		echo '<strong>Distributor</strong>';
		echo '<input type="text" name="pn_pointer_distributor" value="' . esc_attr( $pointer_distributor ) . '" class="widefat" />';
		echo '<strong>Original Publish Date</strong>';
		echo '<input type="text" name="pn_pointer_date" value="' . esc_attr( $pointer_date ) . '" class="widefat" />';
	}

	function save_post( $post_id, $post ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Is the user allowed to edit the post or page?
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( ! isset( $_POST['pointermeta_noncename'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pointermeta_noncename'] ) ), 'pn-pointers-nonce' ) ) {
			return $post->ID;
		}

		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop through.
		$pointers_meta = array();

		if ( isset( $_POST['pn_pointer_url'] ) ) {
			$pointers_meta['pn_pointer_url'] = esc_url( sanitize_text_field( wp_unslash( $_POST['pn_pointer_url'] ) ) );
		}
		if ( isset( $_POST['pn_pointer_ext_url'] ) ) {
			$pointers_meta['pn_pointer_ext_url'] = esc_url( sanitize_text_field( wp_unslash( $_POST['pn_pointer_ext_url'] ) ) );
		}
		if ( isset( $_POST['pn_pointer_byline'] ) ) {
			$pointers_meta['pn_pointer_byline'] = sanitize_text_field( wp_unslash( $_POST['pn_pointer_byline'] ) );
		}
		if ( isset( $_POST['pn_pointer_author_email'] ) ) {
			$pointers_meta['pn_pointer_author_email'] = sanitize_email( wp_unslash( $_POST['pn_pointer_author_email'] ) );
		}
		if ( isset( $_POST['pn_pointer_distributor'] ) ) {
			$pointers_meta['pn_pointer_distributor'] = sanitize_text_field( wp_unslash( $_POST['pn_pointer_distributor'] ) );
		}
		if ( isset( $_POST['pn_pointer_date'] ) ) {
			$pointers_meta['pn_pointer_date'] = sanitize_text_field( wp_unslash( $_POST['pn_pointer_date'] ) );
		}

		// Add values of $pointers_meta as custom fields
		// Cycle through the $pointers_meta array!
		foreach ( $pointers_meta as $key => $value ) {
			// If $value is an array, make it a CSV (unlikely)
			$value = implode( ',', (array) $value );

			// Delete if blank
			if ( empty( $value ) ) {
				delete_post_meta( $post->ID, $key );
			} else {
				// Use update_post_meta so it will add/update as needed
				update_post_meta( $post->ID, $key, $value );
			}
		}
	}
}
