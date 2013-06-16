<?php
/*
 * Plugin Name: Footer Putter
 * Plugin URI: http://www.diywebmastery.com/plugins/footer-putter/
 * Description: Put a footer on your site that boosts your credibility with both search engines and human visitors.
 * Version: 1.3
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com/about/
 * License: GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
define('FOOTER_PUTTER_VERSION','1.3');
define('FOOTER_PUTTER_FRIENDLY_NAME', 'Footer Putter') ;
define('FOOTER_PUTTER_PLUGIN_NAME', 'footer-putter') ;
define('FOOTER_PUTTER_HOME_URL','http://www.diywebmastery.com/plugins/footer-putter/');
define('FOOTER_CREDITS','FooterCredits');
$dir = dirname(__FILE__) . '/classes/';
require_once($dir . 'footer-credits.php');
require_once($dir . 'footer-trademarks.php');
if (is_admin()) {
	require_once($dir . 'admin.php');
	require_once($dir . 'footer-credits-admin.php');
	require_once($dir . 'footer-trademarks-admin.php');
	FooterCreditsAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);	
	FooterTrademarksAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);
} 
?>