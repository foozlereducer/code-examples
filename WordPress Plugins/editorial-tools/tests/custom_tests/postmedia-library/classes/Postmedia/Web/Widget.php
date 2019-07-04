<?php

namespace Postmedia\Web;

/**
 * Base Widget Class to Extend
 *
 * 	eg. class MyAwesomeWidget extends \Postmedia\Web\Widget { ... }
 */
abstract class Widget extends \WP_Widget {

	/**
	 * An array of allowed tags for widget header / footer (before/after)
	 * @var array
	 */
	public $allowed_tags;



	public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {
		parent::__construct( $id_base, $name, $widget_options, $control_options );

		$this->allowed_tags = array(
			'div' => array(
				'class' => true,
				'id' => true,
			),
			'header' => array(
				'class' => true,
			),
		);
	}

	/**
	 * Echoes the widget content.
	 * See WP_Widget->widget
	 * NOTE: We can't make this abstract as it should be. The inherited class/function is not.
	 * @param  array $args
	 * @param  array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		die( 'function Postmedia\Web\Widget::widget() must be over-ridden in a sub-class.' );
	}

	/**
	 * Updates a particular instance of a widget.
	 * See WP_Widget->update
	 * @param  array $new_instance
	 * @param  array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	/**
	 * Outputs the settings update form.
	 * See WP_Widget->form
	 * @param  array $instance
	 * @return string
	 */
	public function form( $instance ) {
		return;
	}
}
