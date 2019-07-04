<?php
namespace Postmedia\Plugins\Edash;

use Postmedia\Web\Data;
use Postmedia\Web\Data\Ajax;
use Postmedia\Web\Utilities\Input;

// require the traits, the classloader won't load these
require_once( plugin_dir_path( __FILE__ ) . 'CoreMethods.php' );

class Settings {
	// include the Core Methods trait
	use CoreMethods;

	private $taxonomy;

	public function __construct( Taxonomy $taxonomy ) {
		$this->taxonomy = $taxonomy->get_taxonomy();
	}

	/**
	 * get_default_settings - provides the default settings
	 * @return string 		- JSON string with key / value pairs
	 */
	public function get_default_settings() {
		return false;
	}

	/**
	 * Add admin menu
	 * @return  null
	 */
	public function set_main_menu() {
		// Need manage options rights ( admin ) to load this menu
		add_menu_page(
			'Editorial Dashboard Menu',
			'Editorial Dashboard',
			'edit_others_posts',
			'ed_wcm_editorial_dashboard',
			array( $this, 'editorial_dashboard_menu_page' ),
			'dashicons-dashboard',
			21
		);
	}

	public function editorial_dashboard_menu_page() {
		if ( false === $this->validate_role() ) {
			 return esc_html__( 'Sorry, you do not have permission to this page.' );
		}

		$current_user = wp_get_current_user();
		$_name = $current_user->user_firstname . ' ' . $current_user->user_lastname;
		$_site = get_bloginfo( 'name' );
		echo '<script type="text/javascript">' . "\n";
		echo "var pn_edash_user_name = '" . esc_js( $_name ) . "';\n";
		echo "var pn_edash_site_name = '" . esc_js( $_site ) . "';\n";
		echo '</script>' . "\n";
		?>
		<h1>Manage WCM Posts</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( $this->taxonomy );
			do_settings_sections( $this->taxonomy );

			wp_nonce_field( PN_EDASH_URI, 'pn_edash_noncename' );
			?>
		</form>

		<div id="app" data-id="pointers" class="pointers">

			<div id="page" class="showPage2">
				<?php

				// Display market dropdown on multi-market sites.
				$multi_market = get_option( 'wcm_multi_market', false );
				$default_market = '';
				if ( $multi_market ) {
					$args = array(
						'type' => 'autocomplete',
						'key' => 'wcm_client_id',
						'options' => Ajax::get_configuration_options( array( 'value' => 'client_id' ) ),
						'name' => 'Select destination market',
						'attributes' => array(
							'data-ajax-action' => 'data_search_configurations',
							'data-option-value' => 'client_id',
							'data-select-callback' => '$q.pointers.clientSelectCallback',
						),
						'wrapper_attributes' => array(
							'data-force-value' => '1',
							'data-max' => '1',
						),
					);
					$current_user = wp_get_current_user();
					$user = Data::get_user( $current_user->ID );
					if ( ! empty( $user ) && ! isset( $user['error'] ) && ! empty( $user['clients'] ) ) {
						$default_market = $user['clients'][0];
					}
					Input::label( $args );
					Input::render( $args, $default_market );
				} else {
					$client_id = get_option( 'wcm_client_id', '' );
					printf( '<input type="hidden" id="wcm_client_id" name="wcm_client_id" value="%s" />',  $client_id );
				}

				echo '<div id="page2"';
				if ( $multi_market && ! $default_market ) {
					echo ' style="display:none"';
				}
				echo '>';

				?>

					<h1>Search</h1>
					<div id="msg"></div>
					<table class="container">

						<tr class="spacer alwaysVisiblePanel">
							<td colspan="5">
								<hr />
							</td>
						</tr>

						<tr class="spacer">
							<td colspan="5">
								<button class="btn blue shadow" id="buttonAddFilter" type="button">+ Add a filter</button>
							</td>
						</tr>

						<tr class="spacer runPanel">
							<td colspan="5">
								<hr />
							</td>
						</tr>

						<tr class="spacer runPanel">
							<td colspan="5">
								<button class="btn blue shadow" type="button" id="buttonRunFilters">Search</button>
							</td>
						</tr>


						<tr>
							<td class="col1"><br /></td>
							<td class="col2"><br /></td>
							<td class="col3"><br /></td>
							<td class="col4"><br /></td>
							<td class="col5"><br /></td>
						</tr>

					</table>

					<h2 class="resultsPanel">Results</h2>

					<table class="results striped resultsPanel">
						<thead>
							<tr>
								<th class="num">#</th>
								<th class="title">Name</th>
								<th class="source">Source</th>
								<th class="status">Status</th>
								<th class="status">Published</th>
								<th class="icons"><br /></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
					<button class="btnListPrev btn blue shadow hidden" style="display:none;">&lt;&lt;Prev 25</button>
					<button class="btnListNext btn blue shadow" style="display:none;">Next 25 &gt;&gt;</button>
					<p><br /></p>
					<p><br /></p>
					<p><br /></p>

				</div>
				<!-- end: #page2 -->
			</div>
			<!-- end: #page -->

		</div>
		<?php
	}
}
