<?php

namespace Postmedia\Web\Theme;

/**
 * Capabilities setup & control the configurations
 */
class Capabilities {

	/**
	 * List of roles to remove
	 * @var array
	 */
	public $deprecated_roles;

	/**
	 * List of core roles to modify
	 * @var array
	 */
	public $core_roles;

	/**
	 * List of custom roles and capabilities
	 * @var array
	 */
	public $custom_roles;

	/**
	 * List of all available capabilities
	 * We disable access to these for roles then add back the ones are allowed
	 * @var array
	 */
	public $available_capabilities;



	public function __construct() {
		// Define deprecated roles
		$this->deprecated_roles = array(
				'web_admin',
				'inactive',
				'columnist',
			);

		// Define core roles
		$this->core_roles = array(
				'administrator' => array(
						'add' => array(
								'is_admin',
								'manage_menus',
								'manage_easysidebars',
								'manage_zones',
								'manage_galleries',
								'manage_pointers',
								'manage_storylines',
								'manage_layouts',
								'manage_advertising',
								'manage_alerts',
								'manage_sked',
								'view_sked',
								'manage_events',
								'manage_coauthors',
								'schedule_others',
								'manage_sidebars',
								'edit_post_subscriptions',
								'manage_saxo',
								'debug_code',
								'import',
								'export',
								'push_storyline_notifications',
								'storyline_topics',
							),
						'remove' => array(
								'coauthors',
								'create_fork',
							),
					),

				'editor' => array(
						'add' => array(
								'is_editor',
								'delete_others_posts',
								'delete_posts',
								'delete_private_posts',
								'delete_published_posts',
								'edit_posts',
								'edit_others_posts',
								'edit_private_posts',
								'edit_published_posts',
								'manage_categories',
								'manage_links',
								'publish_posts',
								'read',
								'read_private_posts',
								'upload_files',
								'manage_sidebars',
								'edit_post_subscriptions',
								'manage_coauthors',
								'list_users',
								'manage_sked',
								'view_sked',
								'manage_events',
								'schedule_others',
								'manage_zones',
								'manage_alerts',
								'edit_forks',
								'edit_fork',
								'edit_others_forks',
								'edit_private_forks',
								'edit_published_forks',
								'read_forks',
								'read_private_forks',
								'delete_forks',
								'delete_others_forks',
								'delete_private_forks',
								'delete_published_forks',
								'publish_forks',
								'push_storyline_notifications',
							),
						'remove' => array(
								'is_admin',
								'manage_advertising',
								'manage_menus',
								'manage_easysidebars',
								'delete_others_pages',
								'delete_pages',
								'delete_private_pages',
								'delete_published_pages',
								'manage_saxo',
								'manage_categories',
								'moderate_comments',
								'manage_storyline',
								'debug_code',
								'read_private_pages',
								'publish_pages',
								'edit_pages',
								'edit_others_pages',
								'edit_private_pages',
								'edit_published_pages',
								'import',
								'export',
							),
					),

				'author' => array(
						'add' => array(
								'delete_posts',
								'edit_posts',
								'read',
								'upload_files',
								'edit_post_subscriptions',
								'publish_posts',
								'edit_published_posts',
								'edit_forks',
								'edit_fork',
								'read_forks',
								'delete_forks',
								'publish_forks',
								'view_sked',
							),
						'remove' => array(
								'is_admin',
								'is_editor',
								'manage_menus',
								'manage_easysidebars',
								'manage_layouts',
								'manage_advertising',
								'debug_code',
								'coauthors',
								'manage_coauthors',
								'delete_published_posts',
								'delete_published_forks',
								'read_private_posts',
								'edit_private_forks',
								'delete_private_forks',
								'import',
								'export',
							),
					),

				'contributor' => array(
						'add' => array(
								'read',
								'delete_posts',
								'edit_posts',
								'upload_files',
								'edit_post_subscriptions',
								'edit_forks',
								'edit_fork',
								'read_forks',
							),
						'remove' => array(
								'is_admin',
								'is_editor',
								'manage_menus',
								'manage_layouts',
								'manage_advertising',
								'manage_easysidebars',
								'edit_others_posts',
								'delete_published_posts',
								'delete_others_posts',
								'delete_others_pages',
								'import',
								'export',
							),
					),
			);

		// Define custom roles
		$this->custom_roles = array(
				'developer_admin' 	=> array(
						'name' => 'Developer',
						'capabilities' => array(
								'delete_forks',
								'delete_others_forks',
								'delete_others_pages',
								'delete_others_posts',
								'delete_pages',
								'delete_posts',
								'delete_private_forks',
								'delete_private_pages',
								'delete_private_posts',
								'delete_published_forks',
								'delete_published_pages',
								'delete_published_posts',
								'edit_fork',
								'edit_forks',
								'edit_others_forks',
								'edit_others_pages',
								'edit_others_posts',
								'edit_pages',
								'edit_post_subscriptions',
								'edit_posts',
								'edit_private_forks',
								'edit_private_pages',
								'edit_private_posts',
								'edit_published_forks',
								'edit_published_pages',
								'edit_published_posts',
								'edit_theme_options',
								'edit_theme_options',
								'edit_usergroups',
								'editflow_notifcations',
								'export',
								'import',
								'is_admin',
								'list_users',
								'manage_advertising',
								'manage_alerts',
								'manage_categories',
								'manage_coauthors',
								'manage_easysidebars',
								'manage_layouts',
								'manage_links',
								'manage_menus',
								'manage_options',
								'manage_pointers',
								'manage_saxo',
								'manage_sidebars',
								'manage_sked',
								'manage_storyline',
								'manage_zones',
								'moderate_comments',
								'publish_forks',
								'publish_pages',
								'publish_posts',
								'push_storyline_notifications',
								'read',
								'read_forks',
								'read_private_forks',
								'read_private_pages',
								'read_private_posts',
								'storyline_topics',
								'upload_files',
								'view_sked',
							),
					),

				'news_admin' 	=> array(
						'name' => 'Newsroom Admin',
						'capabilities' => array(
								'add_users',
								'create_users',
								'delete_forks',
								'delete_others_forks',
								'delete_others_pages',
								'delete_others_posts',
								'delete_pages',
								'delete_posts',
								'delete_private_forks',
								'delete_private_pages',
								'delete_private_posts',
								'delete_published_forks',
								'delete_published_pages',
								'delete_published_posts',
								'edit_fork',
								'edit_forks',
								'edit_others_forks',
								'edit_others_pages',
								'edit_others_posts',
								'edit_pages',
								'edit_post_subscriptions',
								'edit_posts',
								'edit_private_forks',
								'edit_private_pages',
								'edit_private_posts',
								'edit_published_forks',
								'edit_published_pages',
								'edit_published_posts',
								'edit_theme_options',
								'edit_theme_options',
								'edit_usergroups',
								'edit_users',
								'editflow_notifcations',
								'is_editor',
								'level_1',
								'list_users',
								'manage_alerts',
								'manage_categories',
								'manage_coauthors',
								'manage_easysidebars',
								'manage_layouts',
								'manage_links',
								'manage_menus',
								'manage_pointers',
								'manage_sidebars',
								'manage_sked',
								'manage_storyline',
								'manage_zones',
								'moderate_comments',
								'promote_users',
								'publish_forks',
								'publish_pages',
								'publish_posts',
								'push_storyline_notifications',
								'read',
								'read_forks',
								'read_private_forks',
								'read_private_pages',
								'read_private_posts',
								'storyline_topics',
								'upload_files',
								'view_sked',
							),
					),

				'news_admin_super' 	=> array(
						'name' => 'Newsroom Admin Super',
						'capabilities' => array(
								'add_users',
								'create_users',
								'delete_forks',
								'delete_others_forks',
								'delete_others_pages',
								'delete_others_posts',
								'delete_pages',
								'delete_posts',
								'delete_private_forks',
								'delete_private_pages',
								'delete_private_posts',
								'delete_published_forks',
								'delete_published_pages',
								'delete_published_posts',
								'edit_fork',
								'edit_forks',
								'edit_others_forks',
								'edit_others_pages',
								'edit_others_posts',
								'edit_pages',
								'edit_post_subscriptions',
								'edit_posts',
								'edit_private_forks',
								'edit_private_pages',
								'edit_private_posts',
								'edit_published_forks',
								'edit_published_pages',
								'edit_published_posts',
								'edit_theme_options',
								'edit_theme_options',
								'edit_usergroups',
								'edit_users',
								'editflow_notifcations',
								'is_editor',
								'level_1',
								'list_users',
								'manage_alerts',
								'manage_categories',
								'manage_coauthors',
								'manage_easysidebars',
								'manage_layouts',
								'manage_links',
								'manage_menus',
								'manage_options',
								'manage_pointers',
								'manage_sidebars',
								'manage_sked',
								'manage_storyline',
								'manage_zones',
								'moderate_comments',
								'promote_users',
								'publish_forks',
								'publish_pages',
								'publish_posts',
								'push_storyline_notifications',
								'read',
								'read_forks',
								'read_private_forks',
								'read_private_pages',
								'read_private_posts',
								'storyline_topics',
								'upload_files',
								'view_sked',
							),
					),

				'web_editor' 	=> array(
						'name' => 'Web Editor',
						'capabilities' => array(
								'delete_forks',
								'delete_others_forks',
								'delete_others_pages',
								'delete_others_posts',
								'delete_pages',
								'delete_posts',
								'delete_private_forks',
								'delete_private_posts',
								'delete_published_forks',
								'delete_published_pages',
								'delete_published_posts',
								'edit_fork',
								'edit_forks',
								'edit_others_forks',
								'edit_others_pages',
								'edit_others_posts',
								'edit_pages',
								'edit_post_subscriptions',
								'edit_posts',
								'edit_private_forks',
								'edit_private_posts',
								'edit_published_forks',
								'edit_published_pages',
								'edit_published_posts',
								'edit_theme_options',
								'editflow_notifcations',
								'is_editor',
								'level_1',
								'list_users',
								'manage_alerts',
								'manage_coauthors',
								'manage_layouts',
								'manage_links',
								'manage_pointers',
								'manage_sidebars',
								'manage_sked',
								'manage_storyline',
								'manage_zones',
								'moderate_comments',
								'publish_forks',
								'publish_pages',
								'publish_posts',
								'push_storyline_notifications',
								'read',
								'read_forks',
								'read_private_forks',
								'read_private_posts',
								'storyline_topics',
								'upload_files',
								'view_sked',
							),
					),

				'basic_author' 	=> array(
						'name' => 'Basic Author',
						'capabilities' => array(
								'delete_forks',
								'delete_posts',
								'edit_fork',
								'edit_forks',
								'edit_post_subscriptions',
								'edit_posts',
								'edit_published_forks',
								'ef_view_calendar',
								'ef_view_story_budget',
								'manage_categories',
								'read',
								'read_forks',
								'upload_files',
								'view_sked',
							),
					),

				'ad_ops_user' 	=> array(
						'name' => 'AdOps',
						'capabilities' => array(
								'delete_posts',
								'edit_others_posts',
								'edit_posts',
								'edit_published_posts',
								'edit_theme_options',
								'edit_theme_options',
								'list_users',
								'manage_advertising',
								'manage_coauthors',
								'manage_easysidebars',
								'publish_posts',
								'read',
								'upload_files',
							),
					),

				'bt_developer' 	=> array(
						'name' => 'BT Developer',
						'capabilities' => array(
								'delete_forks',
								'delete_others_posts',
								'delete_posts',
								'edit_fork',
								'edit_forks',
								'edit_others_posts',
								'edit_post_subscriptions',
								'edit_posts',
								'edit_published_forks',
								'edit_published_posts',
								'is_admin',
								'manage_options',
								'manage_saxo',
								'publish_forks',
								'publish_posts',
								'read',
								'read_forks',
								'upload_files',
								'view_sked',
							),
					),

				'no_perm_user' 	=> array(
						'name' => 'No permissions',
						'capabilities' => array(),
					),
			);

		// Define all of the available capabilities
		$this->available_capabilities = array(
				'add_users',
				'create_users',
				'debug_code',
				'delete_forks',
				'delete_others_forks',
				'delete_others_pages',
				'delete_others_posts',
				'delete_pages',
				'delete_posts',
				'delete_private_forks',
				'delete_private_pages',
				'delete_private_posts',
				'delete_published_forks',
				'delete_published_pages',
				'delete_published_posts',
				'edit_dashboard',
				'edit_fork',
				'edit_forks',
				'edit_others_forks',
				'edit_others_pages',
				'edit_others_posts',
				'edit_pages',
				'edit_post_subscriptions',
				'edit_posts',
				'edit_private_forks',
				'edit_private_pages',
				'edit_private_posts',
				'edit_published_forks',
				'edit_published_pages',
				'edit_published_posts',
				'edit_theme_options',
				'edit_themes',
				'edit_usergroups',
				'edit_users',
				'edit_zones',
				'editflow_notifcations',
				'ef_view_calendar',
				'ef_view_story_budget',
				'export',
				'import',
				'is_admin',
				'is_editor',
				'list_users',
				'manage_advertising',
				'manage_alerts',
				'manage_categories',
				'manage_coauthors',
				'manage_easysidebars',
				'manage_layouts',
				'manage_links',
				'manage_menus',
				'manage_network_users',
				'manage_options',
				'manage_pointers',
				'manage_saxo',
				'manage_sidebars',
				'manage_sked',
				'manage_storyline',
				'manage_zones',
				'moderate_comments',
				'promote_users',
				'publish_forks',
				'publish_pages',
				'publish_posts',
				'push_storyline_notifications',
				'read',
				'read_forks',
				'read_private_forks',
				'read_private_pages',
				'read_private_posts',
				'storyline_topics',
				'switch_themes',
				'upload_files',
				'view_sked',
				'level_1',
			);

		// Initialize extra capability modifiers
		new Capabilities\Zoninator();
		new Capabilities\CoAuthors();
		new Capabilities\EditFlow();
		new Capabilities\EasySidebars();

		// Setup roles on init
		add_action( 'init', array( $this, 'action_setup_roles' ), 2 );

		// Modify query on admin_init
		add_action( 'admin_init', array( $this, 'action_capability_filters' ), 2 );
	}

	/**
	 * Action callback to setup roles
	 * @return void
	 */
	public function action_setup_roles() {
		$this->remove_deprecated_roles();
		$this->setup_core_roles();
		$this->setup_custom_roles();
	}

	/**
	 * Action callback to setup filters
	 * @return void
	 */
	public function action_capability_filters() {
		add_filter( 'request', array( $this, 'filter_capability_query_mod' ) );
	}

	/**
	 * Filter callback to modify the query if the user is a contributor
	 * @param  array $request
	 * @return array
	 */
	public function filter_capability_query_mod( $request ) {
		// the query isn't run if we don't pass any query vars
		$_query = new \WP_Query();
		$_query->parse_query( $request );

		$_user = wp_get_current_user();

		// If current user is a contributor add query filter
		if ( isset( $_user->roles[0] ) && 'contributor' == $_user->roles[0] ) {
			$request['author__in'] = $_user->ID;
		}

		return $request;
	}

	/**
	 * Remove roles that have been deprecated
	 * @return void
	 */
	private function remove_deprecated_roles() {
		if ( is_array( $this->deprecated_roles ) ) {
			foreach ( $this->deprecated_roles as $role ) {
				remove_role( $role );
			}
		}
	}

	/**
	 * Setup core WP roles with provided capabilities
	 * @return void
	 */
	private function setup_core_roles() {
		if ( is_array( $this->core_roles ) ) {
			foreach ( $this->core_roles as $role => $capabilities ) {
				if ( isset( $capabilities['add'] ) ) {
					wpcom_vip_add_role_caps( $role, $capabilities['add'] );
				}

				if ( isset( $capabilities['remove'] ) ) {
					wpcom_vip_remove_role_caps( $role, $capabilities['remove'] );
				}
			}
		}
	}

	/**
	 * Setup custom WP roles and capabilities
	 * @return void
	 */
	private function setup_custom_roles() {
		if ( is_array( $this->custom_roles ) ) {
			foreach ( $this->custom_roles as $role => $detail ) {
				$capabilities = $this->generate_role_capabilities( $detail['capabilities'] );

				wpcom_vip_add_role( $role, $detail['name'], $capabilities );
			}
		}
	}

	/**
	 * Generate a full list of capabilities based on the provided 'permitted' caps
	 * @param  array $permitted_capabilities
	 * @return array
	 */
	private function generate_role_capabilities( $permitted_capabilities ) {
		$role_capabilities = array();

		// Set [false] for available capabilities
		if ( is_array( $this->available_capabilities ) ) {
			foreach ( $this->available_capabilities as $c ) {
				$role_capabilities[ $c ] = false;
			}
		}

		// Set [true] for permitted capabilities
		if ( is_array( $permitted_capabilities ) ) {
			foreach ( $permitted_capabilities as $c ) {
				$role_capabilities[ $c ] = true;
			}
		}

		return $role_capabilities;
	}
}
