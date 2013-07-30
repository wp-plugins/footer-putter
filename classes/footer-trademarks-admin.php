<?php
class FooterTrademarksAdmin {
    const CLASSNAME = 'FooterTrademarksAdmin'; //this class
    const CODE = 'footer-putter'; //prefix ID of CSS elements
    const DOMAIN = 'FooterPutter'; //text domain for translation
	const SLUG = 'trademarks';
    const FIELDNAME = 'not_on_404';

    private static $initialized = false;
    private static $version;
    private static $parenthook;
    private static $slug;
    private static $screen_id;

	public static function init($parent) {		
	    if (self::$initialized) return true;
		self::$initialized = true;
		self::$version = FooterCredits::VERSION;		
		self::$parenthook = $parent;
	    self::$slug = self::$parenthook . '-' . self::SLUG;
	    self::$screen_id = self::$parenthook.'_page_' . self::$slug;
		add_filter('screen_layout_columns', array(self::CLASSNAME, 'screen_layout_columns'), 10, 2);
		add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
	}

    private static function get_version(){
		return self::$version;
	}

    private static function get_parenthook(){
		return self::$parenthook;
	}

    public static function get_slug(){
		return self::$slug;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}
	
	public static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	
	
	public static function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == self::get_screen_id()) {
				$columns[self::get_screen_id()] = 2;
			}
		}
		return $columns;
	}

	public static function admin_menu() {
		add_submenu_page(self::get_parenthook(), __('Trademarks'), __('Trademarks'), 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page'));			
	}

	public static function load_page() {
 		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_styles'));		
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css', dirname(__FILE__)), array(),self::get_version());
 	}		

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$title = sprintf('<h2>%1$s</h2>', __('Footer Trademarks', self::DOMAIN));		
		$screenshot2 = plugins_url('images/add-link-category.jpg',dirname(__FILE__));		
		$screenshot3 = plugins_url('images/add-link.jpg',dirname(__FILE__));
		$linkcat = admin_url('edit-tags.php?taxonomy=link_category');
		$addlink = admin_url('link-add.php');
		$widgets = admin_url('widgets.php');
?>
<div class="wrap">
<?php screen_icon(); echo $title; ?>
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<p class="notice">There are no settings on this page.</p>
<p class="notice">However, links are provided to where you set up trademarks or other symbols you want to appear in the footer.</p>

<p class="important">Firstly go to the <a href="<?php echo $linkcat;?>">Link Categories</a> and set up a link category called <i>Trademarks</i> or something similar.</p>
<p class="important">Next go to the <a href="<?php echo $addlink;?>">Add Link</a> and add a link for each trademark
specifying the Image URL, and optionally the link URL and of course adding each link to your chosen link category.</p>
<p class="important">Finally go to the <a href="<?php echo $widgets;?>">Appearance | Widgets</a> and drag a trademark widget into the custom footer widget
area and select <i>Trademarks</i> as the link category.</p>

<h2>Help On Trademarks</h2>
<p>Below are annotated screenshots of creating the link category and adding a link .
<p><img src="<?php echo $screenshot2;?>" alt="Screenshot of adding a trademark link category" /></p>
<p><img src="<?php echo $screenshot3;?>" alt="Screenshot of adding a trademark link " /></p>
<form id="misc_options" method="post" action="<?php echo $this_url; ?>">
<p>
<?php wp_nonce_field(self::CLASSNAME); ?>
<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
</p>
</form>
</div></div><br class="clear"/></div></div>
<?php
	}   
}
?>