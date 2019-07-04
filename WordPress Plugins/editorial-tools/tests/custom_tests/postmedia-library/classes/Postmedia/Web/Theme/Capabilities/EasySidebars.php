<?php

namespace Postmedia\Web\Theme\Capabilities;

class EasySidebars {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'verify_access' ), 1, 1 );
	}

	/**
	 * Action callback to verify access to easy sidebars
	 * @param  mixed $_args
	 * @return void
	 */
	public function verify_access( $_args ) {
		global $plugin_page, $typenow, $taxnow, $pagenow;

		$_ok = true;
		$_file = isset( $pagenow ) ? trim( $pagenow ) : '';
		$_page = isset( $plugin_page ) ? trim( $plugin_page ) : '';

		if ( ( 'themes.php' == $_file ) && ( 'easy_sidebars' == $_page ) && ( ! current_user_can( 'manage_easysidebars' ) ) ) {
			// trying to edit Easy Sidebars without capability - changes to Easy Sidebars plugin will make this unnecessary
			$_ok = false;
		} else if ( ( 'nav-menus.php' == $_file ) && ( ! current_user_can( 'manage_menus' ) ) ) {
			// trying to edit Menus without capability
			$_ok = false;
		}

		if ( ! $_ok ) {
			do_action( 'admin_page_access_denied' );
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}
	}
}
