<?php
class FooterCreditsPlugin {

	public static function init() {
		$dir = dirname(__FILE__) . '/';
		require_once($dir . 'footer-credits.php');
		require_once($dir . 'footer-trademarks.php');
		FooterCredits::init();
	}
	
	public static function admin_init() {
		$dir = dirname(__FILE__) . '/';
		require_once($dir . 'tooltip.php');
		require_once($dir . 'admin.php');
		require_once($dir . 'footer-credits-admin.php');
		require_once($dir . 'footer-trademarks-admin.php');
		FooterPutterAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);
		FooterCreditsAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);	
		FooterTrademarksAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);	
	}
}
add_action ('init',  array('FooterCreditsPlugin', 'init'), 0);
if (is_admin()) add_action ('init',  array('FooterCreditsPlugin', 'admin_init'), 0);
?>