<?php
namespace AW\ThemeName;
/**
 *	Base function sheet for Timber-based themes
 */
defined('ABSPATH') or die('No direct access, please');

if(!class_exists('\Timber\Timber')){
	\add_action('admin_notices', function(){
		print("<div class='error'><h1>Please activate the Timber plugin</h1></div>");
	});
	return;
}

require_once('includes/Functions.php');
$fn = new Functions();

\add_action('init',					array($fn,'setUpTheme'));
\add_action('widgets_init',		array($fn,'registerWidgets'));
\add_filter('upload_mimes',		array($fn,'enableSVGUpload'));

\add_action('wp_enqueue_scripts',		array($fn,'queueResources'));
\add_filter('timber_context',			array($fn,'addToContext'));
\add_filter('the_content',			array($fn,'autoEscape'),0,1);

\add_filter('allowed_block_types', [$fn, 'whitelistBlocks'], 0, 2);