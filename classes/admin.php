<?php
class FooterPutterAdmin {
    const CLASSNAME = 'FooterPutterAdmin'; //this class
    const CODE = 'footer-putter';
    const DOMAIN = 'FooterPutter';
    
	private static $path = FOOTER_PUTTER_PATH;
    private static $slug;
    private static $screen_id;

    private static function get_slug(){
		return self::$slug;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}

	static function init($root) {
	    self::$slug = $root;
		add_filter('pre_option_link_manager_enabled', '__return_true' );
		add_filter('plugin_action_links',array(self::CLASSNAME, 'plugin_action_links'), 10, 2 );
		add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
	}

	static function plugin_action_links( $links, $file ) {
		if ( is_array($links) && (self::$path == $file )) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page='.FOOTER_PUTTER_PLUGIN_NAME) . '">Settings</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	static function admin_menu() {
		self::$screen_id = add_menu_page(FOOTER_PUTTER_FRIENDLY_NAME, FOOTER_PUTTER_FRIENDLY_NAME, 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'),plugins_url('images/icon-16.png',dirname(__FILE__)) );
		add_submenu_page(FOOTER_PUTTER_PLUGIN_NAME, FOOTER_PUTTER_FRIENDLY_NAME, 'Intro', 'manage_options', FOOTER_PUTTER_PLUGIN_NAME,array(self::CLASSNAME,'settings_panel') );
	}
	
	static function settings_panel() {
    	$footer_url = FooterCreditsAdmin::get_url(); 
    	$home_url = FOOTER_PUTTER_HOME_URL;
    	$version = FOOTER_PUTTER_VERSION;
    	$plugin = FOOTER_PUTTER_FRIENDLY_NAME;
		$screenshot = plugins_url('screenshot-1.jpg',dirname(__FILE__));    	
		$logo = plugins_url('images/logo.png', dirname(__FILE__));    	
    	print <<< ADMIN_PANEL
<div class="wrap">
<h2>{$plugin} {$version} Overview</h2>
<img class="alignright" src="{$logo}" alt="Footer Putter Plugin" />

<p>{$plugin} allows you to put a footer to your site that adds credibility to your site, with BOTH visitors and search engines.</p>
<p>Google is looking for some indicators that the site is about a real business.</p>
<ol>
<li>The name of the business or site owner</li>
<li>A copyright notice that is up to date</li>
<li>A telephone number</li>
<li>A postal address</li>
<li>Links to Privacy Policy and Terms of Use pages</p>
</ol>

<p>Human visitors may pay some credence to this information but will likely be more motivated by trade marks, trust marks and service marks.</p>

<h2>{$plugin} Widgets</h2>

The plugins define two widgets: 
<ol>
<li>a <b>Footer Copyright Widget</b> that places a line at the foot of your site containing as many of the items listed above that you want to disclose.</li>
<li>a <b>Trademarks Widget</b> that displays a line of trademarks that you have previously set up as "Links".
</ol>
<p>All footer links are rel=nofollow in line with best SEO recommendations.</p>
<p>Typically you will drag both widgets into the Custom Footer Widget Area.</p>
<p>Your footer will look something like this:</p>
<img src="{$screenshot}" alt="Screenshot of Footer Credits Widget Area" />

<h2>Instructions For WP Whoosh Users</h2>
<p>If you have <a href="http://www.wpwhoosh.com/">whooshed</a> this site then all you need to do is replace the sample trademarks with the real trademarks.

<h2>Instructions For Other Users</h2>
<ol>
<li>Create a <i>Privacy Policy</i> page with no sidebar and set robots meta as noindex, noarchive.</li>
<li>Create a <i>Terms of Use</i> page with no sidebar and set robots meta as noindex, noarchive.</li>
<li>Create a <i>Contact</i> page with a contact form.</li>
<li>Create a WordPress menu <i>Footer Menu</i> with the above 3 pages.</li>
<li>Go to <a href="{$footer_url}">Footer Credits</a> and update the Site Owner details and set the Footer Hook according to your choice of WordPress theme.</li>
<li>Drag a <i>Footer Copyright Widget</i> into the <i>Custom Footer Widget Area</i> and select the <i>Footer Menu</i> and optional text if you want to have a "Return To Top" link</li>
<li>Add a link for each of your trademarks and put each in a <i>Trademarks</i> link category - you can call this link category as you like</li>
<li>Drag a Trademarks widget into the Custom Footer Widget and choose your <i>Trademarks</i> link category</li>
</ol>

<h3>Footer Hook</h3>

<p>The footer hook is only required if your theme does not already have a footer widget area into which you can drag the two widgets.</p>

<p>For some themes, the footer hook is left blank, for others use a WordPress hook such as <i>get_footer</i> or <i>wp_footer</i>, 
or use a theme-specific hook such as <i>twentyten_credits</i>, <i>twentyeleven_credits</i>, <i>twentytwelve_credits</i>, <i>genesis_footer</i>, <i>pagelines_leaf</i>, etc</p>

<h3>Getting Help</h3>
<p>Check out the <a href="{$home_url}">Footer Putter Plugin page</a> for more information about the plugin.</p> 
ADMIN_PANEL;
	}
}
?>