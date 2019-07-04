<?php

namespace Postmedia\Web\Theme\Settings;

/**
 * Generic Settings Module Class
 *
 * Functions declared as abstract must be implemented by classes that extend Module
 */
abstract class Module {

	/**
	 * Name for Option Group
	 * When a module is added via a menu, this is set from the menu key
	 * @var string
	 */
	public $option_group;

	/**
	 * All modules need to implement a register_settings function
	 * @param  string $option_group
	 * @return void
	 */
	abstract public function register_settings();

	/**
	 * Get the value of an option
	 * Also Check if key is in format pn_socialmedia_site[facebook]
	 * @param  string $option_key
	 * @param  mixed $default
	 * @return mixed
	 */
	public function get_option( $option_key, $default = null ) {
		$option_value = $default;

		// Check if key is in format pn_socialmedia_site[facebook]
		preg_match( '/(.+?)\[(.+)\]/i', $option_key, $matches );

		if ( isset( $matches[2] ) ) {
			$option = get_option( $matches[1] );

			if ( isset( $option[ $matches[2] ] ) ) {
				$option_value = $option[ $matches[2] ];
			}
		} else {
			$option_value = get_option( $option_key );
		}

		return $option_value;
	}

	/**
	 * Render a standard checkbox input
	 * @param  array $args
	 * @return void
	 */
	public function render_input_checkbox( $args ) {
		if ( ! isset( $args['key'] ) ) {
			return;
		}

		?>
		<input type="checkbox" name="<?php echo esc_attr( $args['key'] ) ?>" <?php echo checked( ( boolean ) $this->get_option( $args['key'] ), true ) ?> />
		<?php

		if ( isset( $args['help'] ) ) {
			?>
			<br /><span style="display:block;margin-top:10px;"><?php echo esc_html( $args['help'] ) ?></span>
			<?php
		}
	}

	/**
	 * Render a standard text input
	 * @param  array $args
	 * @return void
	 */
	public function render_input_text( $args ) {
		if ( ! isset( $args['key'] ) ) {
			return;
		}

		?>
		<input type="text" name="<?php echo esc_attr( $args['key'] ) ?>" value="<?php echo esc_attr( $this->get_option( $args['key'] ) ) ?>" class="regular-text" />
		<?php

		if ( isset( $args['help'] ) ) {
			?>
			<br /><span style="display:block;margin-top:10px;"><?php echo esc_html( $args['help'] ) ?></span>
			<?php
		}
	}

	/**
	 * Redner a standard dropdown selector
	 * @param  array $args
	 * @return void
	 */
	public function render_input_dropdown( $args ) {
		if ( ! isset( $args['key'] ) || ! isset( $args['options'] ) ) {
			return;
		}

		?>
		<select name="<?php echo esc_attr( $args['key'] ) ?>">
			<?php

			foreach ( (array) $args['options'] as $description => $value ) {
				?>
				<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $this->get_option( $args['key'] ), $value ) ?>><?php echo esc_html( $description ) ?></option>
				<?php
			}

			?>
		</select>
		<?php
	}

	/**
	 * Settings sanitization for input
	 * @param  string $input
	 * @return string
	 */
	public function sanitize_input_text( $input ) {
		return sanitize_text_field( $input );
	}

	/**
	 * Sanitization for url input
	 * @param  array $input
	 * @return array
	 */
	public function sanitize_url_list( $input ) {
		foreach ( $input as &$url ) {
			$url = esc_url( $url );
		}

		return ( $input );
	}

	/**
	 * Settings sanitization for input checkbox
	 * @param  string $input
	 * @return string
	 */
	public function sanitize_input_checkbox( $input ) {
		return ( boolean ) $input;
	}

	/**
	 * Sanitize settings as json
	 * @param  string $input
	 * @return string
	 */
	public function sanitize_as_json( $input ) {
		return wp_json_encode( $input );
	}
}
