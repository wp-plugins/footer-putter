<?php
class Footer_Credits_Plugin {

 	private static $path = FOOTER_PUTTER_PATH;
 	private static $slug = FOOTER_PUTTER_PLUGIN_NAME;
 	private static $version = FOOTER_PUTTER_VERSION;
 	
    public static function get_path(){
		return self::$path;
	}

    public static function get_slug(){
		return self::$slug;
	}
	
	public static function get_version(){
		return self::$version;
	}

	public static function init() {
		$dir = dirname(__FILE__) . '/';
		require_once($dir . 'class-footer.php');
		require_once($dir . 'class-footer-widgets.php');
		Footer_Credits::init();
	}
	
	public static function admin_init() {
		$dir = dirname(__FILE__) . '/';
		require_once($dir . 'class-tooltip.php');
		require_once($dir . 'class-admin.php');
		require_once($dir . 'class-credits-admin.php');
		require_once($dir . 'class-trademarks-admin.php');
		Footer_Putter_Admin::init();
		Footer_Credits_Admin::init();	
		Footer_Trademarks_Admin::init();	
		add_filter('pre_option_link_manager_enabled', '__return_true' );
		add_filter('plugin_action_links',array(__CLASS__, 'plugin_action_links'), 10, 2 );
	}

	static function plugin_action_links( $links, $file ) {
		if ( is_array($links) && (self::get_path() == $file )) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page='.self::get_slug()) . '">Settings</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}
