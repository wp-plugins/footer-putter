<?php
class FooterPutterAdmin {
    const CREDITS = 'FooterCreditsAdmin';
    private $pagehook;

	function __construct() {
		add_filter('screen_layout_columns', array(&$this, 'screen_layout_columns'), 10, 2);
		add_filter('plugin_action_links',array(&$this, 'plugin_action_links'), 10, 2 );
		add_action('admin_menu', array(&$this,'admin_menu'));
		call_user_func (array(self::CREDITS,'init'),FOOTER_PUTTER_PLUGIN_NAME,FOOTER_PUTTER_VERSION);
	}

	function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == $this->pagehook) {
				$columns[$this->pagehook] = 2;
			}
		}
		return $columns;
	}

	function plugin_action_links( $links, $file ) {
		if ( is_array($links) && (FOOTER_PUTTER_PATH == $file )) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page='.FOOTER_PUTTER_PLUGIN_NAME) . '">Settings</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	function admin_menu() {
		$this->pagehook = add_menu_page(FOOTER_PUTTER_FRIENDLY_NAME, FOOTER_PUTTER_FRIENDLY_NAME, 'manage_options', 
			FOOTER_PUTTER_PLUGIN_NAME, array(&$this,'resources_panel'),plugins_url('/menu-icon.png',__FILE__) );
	}
	
	function resources_panel() {
    	$footer_url = call_user_func(array(self::CREDITS, 'get_url')); 
    	$home_url = FOOTER_PUTTER_HOME_URL;
    	$version = FOOTER_PUTTER_VERSION;
    	$plugin = FOOTER_PUTTER_FRIENDLY_NAME;
		$screenshot = plugins_url('screenshot-1.jpg',__FILE__);    	
		$logo = plugins_url('logo.png',__FILE__);    	
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
<li>Create a page with a contact form.</li>
<li>Create a WordPress menu <i>Footer Menu</i> with the above 3 pages.</li>
<li>Go to <a href="{$footer_url}">Footer Credits</a> and update the Site Owner details and set the Footer Hook according to your choice of WordPress theme.</li>
<li>Drag a <i>Footer Copyright Widget</i> into the <i>Custom Footer Widget Area</i> and select the <i>Footer Menu</i> and optional text if you want to have a "Return To Top" link</li>
<li>Add a link for each of your trademarks and put each in a <i>Trademarks</i> link category - you can call this link category as you like</li>
<li>Drag a Trademarks widget into the Custom Footer Widget and choose your <i>Trademarks</i> link category</li>
</ol>

<h3>Footer Hook</h3>

<p>The footer hook is only required if your theme does not already have a footer widget area into which you can drag the two widgets.</p>

<p>For some themes, the footer hook is left blank, for others use a WordPress hook such as <i>get_footer</i> or <i>wp_footer</i>, 
or use a theme-specific hook such as <i>twentyten_credits</i>, <i>twentyeleven_credits</i>, <i>genesis_footer</i>, <i>pagelines_leaf</i>, etc</p>

<h3>Getting Help</h3>
<p>Check out the <a href="{$home_url}">Footer Putter Plugin page</a> for more information about the plugin.</p> 
ADMIN_PANEL;
	}
}
$footer_putter_admin = new FooterPutterAdmin();
?>
