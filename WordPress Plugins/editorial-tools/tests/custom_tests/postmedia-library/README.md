# Postmedia Class Library
Core classes for themes, plugins, widgets...etc. This library lives as a plugin under the postmedia-plugins folder.

Contributors: Chris Murphy <cmurphy@postmedia.com>


## Usage
Example Plugin Init File:
```
	// Initialize Postmedia Library
	require_once( WP_CONTENT_DIR . '/themes/vip/postmedia-plugins/postmedia-library/init.php' );

	define( 'POSTMEDIA_PLUGIN_SNAPGALLERIES_DIR', plugin_dir_path( __FILE__ ) );
	define( 'POSTMEDIA_PLUGIN_SNAPGALLERIES_URI', plugins_url( '', __FILE__ ) );

	$class_loader = new Postmedia\ClassLoader();
	$class_loader->add_prefix( 'Postmedia', POSTMEDIA_PLUGIN_SNAPGALLERIES_DIR . 'classes/Postmedia', true );
	$class_loader->register();

	new Postmedia\Web\Plugins\SnapGallery();
```
We register the 'Postmedia' prefix with the ClassLoader because this plugin defines a set of its own classes locally that follow the namespace convention.


## Committing Back to Library
If you have made changes to the library files you will need to follow the same procedure as a
standard repo. Change into the 'classes/Postmedia' folder and checkout a new branch off master
before making changes. Submit your changes through a pull request and they will be reviewed.

## History

#### 2.0.13 - 2017-01-17
* Add environment selection for Piano (Production|Sandbox). Defaults to production.
* Remove Press Plus scripts.

#### 2.0.12 - 2017-01-16
* Add option to exclude IE users from the paywall.

#### 2.0.11 - 2017-01-11
* Update Breadcrumb template params to build proper cache key

#### 2.0.10 - 2017-01-05
* Update template cache key to also include device type [desktop|mobile|tablet]

#### 2.0.9 - 2016-09-05
* Add option for custom names on image registration (4th Parameter)
* Sync capabilities with postmedia-web
* Add Utilities::main_category() helper

#### 2.0.8 - 2016-09-20
* Add option to set global $content_width from child theme. Default 640.
* Added /classes/Postmedia/Web/Theme/Settings/Modules/PianoVX.php
* Activated new Janrain implementation
* Removed PressPlus 
* Add support for the TemplateEngine to look to the parent theme for templates. Now it will look to CHILD > PARENT > PLUGIN.

#### 2.0.7 - 2016-08-03
* Add option to set / override default set of robots.txt entries for themes.

#### 2.0.6 - 2016-07-21
* Added Schema Markup in classes/Postmedia/Web/Theme/Breadcrumbs.php for SEO

#### 2.0.5 - 2016-06-29
* Remove base_uri/base_dir from Plugin and Widget classes. Remove deprecated esc_layouts function.
* Add Postmedia\Web\Storage class. Centralized data storage method to be used for plugins, themes...etc.

#### 2.0.4 - 2016-06-16
* Add iPerceptions & Google Survey settings modules. Settings > Child Theme : iPerceptions, Google Survey
* Only load vip plugin 'wp_enqueue_media_override' in prod. Featured image on a new post cannot be set otherwise.
* Added Postmedia\Web\Utilities\Device.php 
* Changed unit tests and Device class as per pull review feedback; descriptive method names
* Postmedia\Web\Utilities\Utilities.php updated to make use of the Device class
* Add iPerceptions & Google Survey settings modules. Settings > Child Theme : iPerceptions, Google Survey

#### 2.0.3 - 2016-06-09
* Add new role 'Newsroom Admin Super' same as 'Newsroom Admin' with the addition of the manage_options role.

#### 2.0.2 - 2016-06-09
* Added Postmedia\Web\Utilities\Device.php 
* Changed unit tests and Device class as per pull review feedback; descriptive method names
* Postmedia\Web\Utilities\Utilities.php updated to make use of the Device class
* Add permissions to 'moderate_comments' to the Newsroom Admin, Web Editor & Developer roles.

#### 2.0.1 - 2016-05-18
* Changing validate_file to use a directory traversal check for '..'. Issues on PC vs. MAC.
* Update Theme settings modules to provide a way to access objects.
* Fix JS path reference image uploader js.
* Update json_encode to wp_json_encode for template id generation.

#### 2.0.0 - 2016-05-02
* Change library from a submodule to a plugin. Plugins and Themes must load this through a require.

#### 1.0.18 - 2016-04-25
* Add settings option for child theme multi site support. postmedia_theme()->settings->supported_sites.

#### 1.0.17 - 2016-04-21
* Fix newsletter script escaping
* Enqueue media for galleries.

#### 1.0.16 - 2016-04-19
* Add zoninator support for SnapGallery plugin.
* Add SnapGallery search by id support.

#### 1.0.15 - 2016-04-18
* Add SnapGallery Plugin & Widget

#### 1.0.14 - 2016-04-15
* Ensure before require to check if file_exists and validate_file.
* Set default cache times to 5 min.
* Escape Newsletter JS a bit different.

#### 1.0.13 - 2016-04-14
* Update Newsletter plugin/widgets to use defined vars

#### 1.0.12 - 2016-04-13
* Add Plugin & Widget classes for Plugins and you guessed it ... Widgets to inherit from
* Add Newsletter Plugin & Widget

#### 1.0.11 - 2016-04-08
* Enable wp plugin wp_enqueue_media_override.
* CI Updates.

#### 1.0.10 - 2016-04-08
* Rename Utilities::escape_layouts() to Utilities::escaped_layouts()
* Add coding standards exclusion on cached Template echo.

#### 1.0.9 - 2016-04-06
* Re-organize theme related classes. Move capabilities, modifiers, SEO under Theme folder.
* Add new Theme\Settings class previously theme-settings.php. These settings were specifically used for
the child themes that do not have a home elsewhere.
* Deprecated Utilities::esc_layouts() use Utilities::escape_layouts()

#### 1.0.8 - 2016-03-21
* Update bulk user management usernames.

#### 1.0.7 - 2016-03-14
* Add check for breadcrumb child url before adding link

#### 1.0.6 - 2016-03-12
* Fix undefined variable in TemplateEngine.
* Only require initialize.php scripts once via require_once. If they are included more than once we can run into 'already declared' issues for functions.
* Add new Sharing class.

#### 1.0.5 - 2016-03-11
* Add layouts escaping function to help with standardization. Utilities::esc_layouts().

#### 1.0.4 - 2016-03-09
* Add option for TemplateEngine to load an initialize.php script before rendering templates. See Postmedia\Web\TemplateEngine->load_init_script().

#### 1.0.3 - 2016-03-07
* Remove default pn-feature-templates plugin load
* Fix wrong scope of 'request' filter callback. Was causing the invalid lists problem in wp-admin.
* Add image size registration configuration. postmedia_theme()->images_sizes.
* Refactor SEO class. Add 'seo' property to Theme class.

#### 1.0.2 - 2016-03-02
* Update Template->render to be able to return the output instead of just an echo

#### 1.0.1 - 2016-02-26
* Update TemplateEngine functionality to allow child theme template overrides

#### 1.0.0 - 2016-02-25
* Initial copy of classes from postmedia-theme-core
