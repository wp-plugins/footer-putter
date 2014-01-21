<?php
/*
 * Plugin Name: Footer Putter
 * Plugin URI: http://www.diywebmastery.com/plugins/footer-putter/
 * Description: Put a footer on your site that boosts your credibility with both search engines and human visitors.
 * Version: 1.7
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com/about/
 * License: GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
define('FOOTER_PUTTER_VERSION','1.7');
define('FOOTER_PUTTER_FRIENDLY_NAME', 'Footer Putter') ;
define('FOOTER_PUTTER_PLUGIN_NAME', plugin_basename(dirname(__FILE__))) ;
define('FOOTER_PUTTER_HOME_URL','http://www.diywebmastery.com/plugins/footer-putter/');
require_once(dirname(__FILE__) . '/classes/plugin.php');
add_action ('init',  array('FooterCreditsPlugin', 'init'),0);
?>