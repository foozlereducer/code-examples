<?php

namespace Postmedia\Web\Theme;

/**
 * Modifiers ... modify configurations, filters & actions for plugins, etc.
 * They are broken down and managed here.
 */
class Modifiers {

	public function __construct() {
		// Load Modifiers
		new Modifiers\TinyMCE();
		new Modifiers\CustomMetadataManager();
		new Modifiers\CustomFields();
		new Modifiers\Image();
	}
}
