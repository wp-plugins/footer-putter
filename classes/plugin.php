<?php
$dir = dirname(__FILE__) . '/';
require_once($dir . 'footer-credits.php');
require_once($dir . 'footer-trademarks.php');
if (is_admin()) {
	require_once($dir . 'tooltip.php');
	require_once($dir . 'admin.php');
	require_once($dir . 'footer-credits-admin.php');
	require_once($dir . 'footer-trademarks-admin.php');
}

class FooterCreditsPlugin {

	public static function init() {
		FooterCredits::init();
		if (is_admin()) {
			FooterPutterAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);
			FooterCreditsAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);	
			FooterTrademarksAdmin::init(FOOTER_PUTTER_PLUGIN_NAME);	
		}
	}
	
}
?>