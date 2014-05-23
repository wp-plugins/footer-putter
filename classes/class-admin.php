<?php
class Footer_Putter_Admin {
    const CODE = 'footer-putter';
    
    private static $screen_id;
		
    private static function get_screen_id(){
		return self::$screen_id;
	}

	static function init() {
		add_action('admin_menu',array(__CLASS__, 'admin_menu'));
		add_action('admin_print_styles',array(__CLASS__, 'style_icon'));		
	}

	static function style_icon() {
		print <<< STYLES
<style type="text/css">
#adminmenu .menu-icon-generic.toplevel_page_footer-putter div.wp-menu-image:before { content: '\\f346'; }
</style>
STYLES;
	}

	static function admin_menu() {
		$intro = sprintf('Intro (v%1$s)', Footer_Credits_Plugin::get_version());
		self::$screen_id = add_menu_page(FOOTER_PUTTER_FRIENDLY_NAME, FOOTER_PUTTER_FRIENDLY_NAME, 'manage_options', 
			Footer_Credits_Plugin::get_slug(), array(__CLASS__,'settings_panel') );
		add_submenu_page(Footer_Credits_Plugin::get_slug(), FOOTER_PUTTER_FRIENDLY_NAME, $intro, 'manage_options', Footer_Credits_Plugin::get_slug(), array(__CLASS__,'settings_panel') );
	}
	
	static function settings_panel() {
    	$home_url = FOOTER_PUTTER_HOME_URL;
     	$plugin = FOOTER_PUTTER_FRIENDLY_NAME;
  		$version = FOOTER_PUTTER_VERSION;
    	$widgets_url = admin_url('widgets.php');
    	$credits_url = Footer_Credits_Admin::get_url(); 
    	$trademarks_url = Footer_Trademarks_Admin::get_url(); 
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
<p>Typically you will drag both widgets into the Custom Footer Widget Area.</p>

<h2>Instructions For Building A Footer</h2>
<h3>Create Standard Pages And Footer Menu</h3>
<ol>
<li>Create a <i>Privacy Policy</i> page with the slug/permalink <em>privacy</em>, choose a page template with no sidebar.</li>
<li>Create a <i>Terms of Use</i> page with the slug/permalink <em>terms</em>, choose a page template with no sidebar.</li>
<li>Create a <i>Contact</i> page with a contact form.</li>
<li>Create an <i>About</i> page, with information either about the site or about its owner.</li>
<li>If the site is selling an information product you may want to create a <i>Disclaimer</i> page, regarding any claims about the product performance.</li>
<li>Create a WordPress menu called <i>Footer Menu</i> with the above pages.</li>
</ol>
<h3>Update Business Information</h3>
<ol>
<li>Go to <a href="{$credits_url}">Footer Credits</a> and update the Site Owner details, contact and legal information.</li>
<li>Optionally include contact details such as telephone and email. You may also want to add Geographical co-ordinates for your office location for the purposes of local search.</li>
</ol>
<h3>Create Trademark Links</h3>
<ol>
<li>Go to <a href="{$trademarks_url}"><i>Footer Trademarks</i></a> and follow the instructions:</li>
<li>Create a link category with a name such as <i>Trademarks</i></li>
<li>Add a link for each of your trademarks and put each in the <i>Trademarks</i> link category</li>
<li>For each link specify the link URL and the image URL</li>
</ol>
<h3>Set Up Footer Widgets</h3>
<ol>
<li>Go to <a href="{$widgets_url}"><i>Appearance > Widgets</i></a></li>
<li>Drag a <i>Footer Copyright Widget</i> and a <i>Footer Trademarks widget</i> into a suitable footer Widget Area</li>
<li>For the <i>Footer Trademarks</i> widget and choose your link category, e.g. <i>Trademarks</i>, and select a sort order</li>
<li>For the <i>Footer Copyright</i> widget, select the <i>Footer Menu</i> and choose what copyright and contact information you want to you display</li>
<li>Review the footer of the site. You can use the widget to change font sizes and colors using pre-defined classes such as <i>tiny</i>, <i>small</i>, <i>dark</i>, <i>light</i> or <i>white</i> or add your own custom classes</li> 
<li>You can also choose to suppress the widgets on special pages such as landing pages.</li> 
<li>If the footer is not in the right location you can use the <i>Footer Hook</i> feature described below to add a new widget area called <i>Credibility Footer</i> where you can locate the footer widgets.</li> 
</ol>

<h3>Footer Hook</h3>
<p>The footer hook is only required if your theme does not already have a footer widget area into which you can drag the two widgets.</p>
<p>For some themes, the footer hook is left blank, for others use a WordPress hook such as <i>get_footer</i> or <i>wp_footer</i>, 
or use a theme-specific hook such as <i>twentyten_credits</i>, <i>twentyeleven_credits</i>, <i>twentytwelve_credits</i>, 
<i>twentythirteen_credits</i>, <i>genesis_footer</i>, <i>pagelines_leaf</i>, etc.</p>

<h3>Getting Help</h3>
<p>Check out the <a href="{$home_url}">Footer Putter Plugin page</a> for more information about the plugin.</p> 
ADMIN_PANEL;
	}
}
