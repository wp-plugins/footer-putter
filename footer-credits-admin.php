<?php
class FooterCreditsAdmin {
    const CLASSNAME = 'FooterCreditsAdmin'; //this class
    const CODE = 'footer-credits'; //prefix ID of CSS elements
    const DOMAIN = 'FooterCredits'; //text domain for translation
    const FOOTER = 'FooterCredits'; //class that builds footer
	const SLUG = 'footer';
    private static $version;
    private static $parenthook;
    private static $slug;
    private static $screen_id;
    private static $initialized = false;
    private static $keys = array('owner', 'site', 'address', 'country', 'telephone', 
				'email', 'courts', 'updated', 'copyright_start_year', 'return_text', 'return_href', 'return_class',
				'footer_class','footer_hook');
	private static $tips = array(
			'owner' => array('heading' => 'Owner or Business Name', 'tip' => 'Enter the name of the legal entity that owns and operates the site.'), 
			'address' => array('heading' => 'Full Address', 'tip' => 'Enter the full address that you want to appear in the footer and the privacy and terms pages.'), 
			'country' => array('heading' => 'Country', 'tip' => 'Enter the country where the legal entity is domiciled.'), 
			'telephone' => array('heading' => 'Telephone Number', 'tip' => 'Enter a telephone number here if you want it to appear in the footer of the installed site.'), 
			'email' => array('heading' => 'Email Address', 'tip' => 'Enter the email address here if you want it to appear in the privacy statement.'), 
			'courts' => array('heading' => 'Legal Jurisdiction' , 'tip' => 'The Courts that have jurisdiction over any legal disputes regarding this site. For example: <i>the state and federal courts in Santa Clara County, California</i>, or <i>the Law Courts of England and Wales</i>'),
			'updated' => array('heading' => 'Last Updated' , 'tip' => 'This will be defaulted as today. For example, Oct 23rd, 2012'),
			'copyright_start_year' => array('heading' => 'Copyright Start' , 'tip' => 'The start year of the business appears in the copyright statement in the footer and an on the Terms and Conditions page.'),
			'return_text' => array('heading' => 'Link Text' , 'tip' => 'The text of the Return To Top link. For example, <i>Return To Top</i> or <i>Back To Top</i>.'),
			'return_href' => array('heading' => 'Link Anchor' , 'tip' => 'The destination of the Return To Top link. This depends on our theme and also whether you want to go to the top of the page or the top of the content section. Typical values are #content, #header, #top, #page, #wrap or #container.'),
			'return_class' => array('heading' => 'Return To Top Class' , 'tip' => 'Add any custom class you want to apply to the Return To Top link.'),
			'footer_class' => array('heading' => 'Footer Class' , 'tip' => 'Add any custom class you want to apply to the footer. The plugin comes with a class <i>white</i> that marks the text in the footer white. This is useful where the footer background is a dark color.'),
			'footer_hook' => array('heading' => 'Footer Hook' , 'tip' => 'The hook where the footer widget area is added to the page. This field is only required if the theme does not already provide a suitable widget area where the footer widgets can be added.')
	);
	private static $tooltips;

	public static function init($parent,$version) {
	    if (self::$initialized) return true;
		self::$initialized = true;
		self::$version = $version;
		self::$parenthook = $parent;
	    self::$slug = self::$parenthook . '-' . self::SLUG;
		add_filter('screen_layout_columns', array(self::CLASSNAME, 'screen_layout_columns'), 10, 2);
		add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
		self::$tooltips = new FooterTooltip(self::$tips);
	}
	
    private static function get_parenthook(){
		return self::$parenthook;
	}

    private static function get_version(){
		return self::$version;
	}

    private static function get_slug(){
		return self::$slug;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}

    private static function get_keys(){
		return self::$keys;
	}
	
 	public static function get_url($id='', $noheader = false) {
		return admin_url('admin.php?page='.self::get_slug().(empty($id) ? '' : ('&amp;id='.$id)).(empty($noheader) ? '' : '&amp;noheader=true'));
	}

 	private static function get_nonce_url($action, $id='', $noheader = false) {
		return WPWhooshUtils::admin_url(self::get_slug(), $action, $id, $noheader,true,'site'); 
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
		self::$screen_id =  add_submenu_page(self::get_parenthook(), __('Footer Credits'), __('Footer Credits'), 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page'));
		add_action('admin_print_styles-'.self::get_screen_id(), array(self::CLASSNAME, 'print_styles'));
		add_action('admin_footer-'.self::get_screen_id(), array(self::CLASSNAME, 'toggle_postboxes'));
	}

	public static function print_styles() {
		wp_enqueue_style(self::CODE, plugins_url('style.css', __FILE__), array(),self::get_version());
		wp_enqueue_style(self::CODE.'-admin', plugins_url('admin.css', __FILE__), array(),self::get_version());
 	}		

	public static function load_page() {
 		$message =  isset($_POST['options_update']) ? self::save() : '';	
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		$options = call_user_func(array(self::FOOTER, 'get_options'));
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Introduction',self::DOMAIN), array(self::CLASSNAME, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-owner', __('Site Owner Details',self::DOMAIN), array(self::CLASSNAME, 'owner_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-contact', __('Contact Details',self::DOMAIN), array(self::CLASSNAME, 'contact_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-legal', __('Legal Details',self::DOMAIN), array(self::CLASSNAME, 'legal_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-return', __('Return To Top',self::DOMAIN), array(self::CLASSNAME, 'return_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-classes', __('Custom Classes (Optional)',self::DOMAIN), array(self::CLASSNAME, 'classes_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-example', __('Preview Footer',self::DOMAIN), array(self::CLASSNAME, 'preview_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-advanced', __('Advanced',self::DOMAIN), array(self::CLASSNAME, 'advanced_panel'), self::get_screen_id(), 'normal', 'core');

		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {
			$current_screen->add_help_tab( 
				array( 'id' => self::CODE.'-overview', 'title' => 'Overview', 'content' => self::help_panel()));	
		}
	}

	public static function save() {
		check_admin_referer(self::CLASSNAME);
  		$page_options = explode(',', stripslashes($_POST['page_options']));
  		if ($page_options) {
  			$options = call_user_func(array(self::FOOTER, 'get_options'));
    		foreach ($page_options as $option) {
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
       			if (array_key_exists($option,$options['terms']))
    				$options['terms'][$option] = $val;
 				else switch($option) {
 					case 'return_href': $val = '#'.preg_replace('/\W/','',$val);
    				default: $options[$option] = $val; 				
					}
    		} //end for	
  		    $class='updated fade';
   			$saved =  call_user_func(array(self::FOOTER, 'save'), $options) ;
   			if ($saved)  {
       			$message = 'Footer Settings saved.';
   			} else
       			$message = 'Footer Settings have not been changed.';
  		} else {
  		    $class='error';
       		$message= 'Footer Settings not found!';
  		}
  		return sprintf('<div id="message" class="%1$s "><p>%2$s</p></div>',$class, __($message,self::FOOTER));
	}

    public static function toggle_postboxes() {
    $hook = self::get_screen_id();
    print <<< SCRIPT
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('{$hook}');
		});
		//]]>
	</script>
SCRIPT;
    }
    
	public static function help_panel(){	
		$result = <<< HELP_PANEL
<p>This admin screen is used to set up the values that appear in the footer, and in the Terms and Conditions and Privacy pages.</p>			
<p>This information supplied here is substituted automatically into the Privacy and Terms pages and footer in the appropriate places.</p>
<h4>Copyright Footer</h4>
<p>You can set up as many of the following items as you want to appear on a single line in a footer widget:</p>
<ul>
<li>- Link to About Us page</li>
<li>- Link to Contact Us page</li>
<li>- Link to Privacy page</li>
<li>- Link to Terms page</li>
<li>- Copyright sSatement</li>
<li>- Business Owner</li>
<li>- Telephone</li>
<li>- Address</li>
</ul>
HELP_PANEL;
		return $result;
	}
 
	public static function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		print <<< INTRO_PANEL
<p>The following information can be used in both the footer widget and on the Privacy and Terms pages. See the Help section above for more information.</p>
{$message}
INTRO_PANEL;
	}
	
	public static function owner_panel($post,$metabox){	
		$terms = $metabox['args']['options']['terms'];	 	
		$tip1 = self::$tooltips->tip('owner');	
		$tip2 = self::$tooltips->tip('address');
		$tip3 = self::$tooltips->tip('country');					
		print <<< OWNER_PANEL
<label>{$tip1}</label><input type="text" name="owner" size="25" value="{$terms['owner']}" /><br/>
<label>{$tip2}</label><input type="text" name="address" size="80" value="{$terms['address']}" /><br/>
<label>{$tip3}</label><input type="text" name="country" size="25" value="{$terms['country']}" /><br/>
OWNER_PANEL;
	}	
    
	public static function contact_panel($post,$metabox){	
		$terms = $metabox['args']['options']['terms'];	 	
		$tip2 = self::$tooltips->tip('telephone');
		$tip1 = self::$tooltips->tip('email');		
		print <<< CONTACT_PANEL
<label>{$tip1}</label><input type="text" name="email" size="30" value="{$terms['email']}" /><br/>
<label>{$tip2}</label><input type="text" name="telephone" size="20" value="{$terms['telephone']}" /><br/>
CONTACT_PANEL;
	}
	
 	public static function legal_panel($post,$metabox){		
		$terms = $metabox['args']['options']['terms'];	 	
		$tip1 = self::$tooltips->tip('courts');
		$tip2 = self::$tooltips->tip('updated');
		$tip3 = self::$tooltips->tip('copyright_start_year');			
		print <<< LEGAL_PANEL
<label>{$tip1}</label><input type="text" name="courts" size="80" value="{$terms['courts']}" /><br/>
<label>{$tip2}</label><input type="text" name="updated" size="20" value="{$terms['updated']}" /><br/>
<label>{$tip3}</label><input type="text" name="copyright_start_year" size="5" value="{$terms['copyright_start_year']}" /><br/>
LEGAL_PANEL;
	}

 	public static function return_panel($post,$metabox){		
		$options = $metabox['args']['options'];	 	
		$tip1 = self::$tooltips->tip('return_text');
		$tip2 = self::$tooltips->tip('return_href');
		print <<< RETURN_PANEL
<label>{$tip1}</label><input type="text" name="return_text" size="20" value="{$options['return_text']}" /><br/>
<label>{$tip2}</label><input type="text" name="return_href" size="20" value="{$options['return_href']}" /><br/>
RETURN_PANEL;
	}

 	public static function classes_panel(){		
		$options = call_user_func(array(self::FOOTER, 'get_options'));	
		$tip1 = self::$tooltips->tip('footer_class');
		$tip2 = self::$tooltips->tip('return_class');
		print <<< CLASSES_PANEL
<label>{$tip1}</label><input type="text" name="footer_class" size="30" value="{$options['footer_class']}" /><br/>
<label>{$tip2}</label><input type="text" name="return_class" size="30" value="{$options['return_class']}" /><br/>
CLASSES_PANEL;
	}

 	public static function preview_panel($post,$metabox){		
		$options = $metabox['args']['options'];	 	
		echo call_user_func(array(self::FOOTER, 'footer'),array('nav_menu' => 'Footer Menu'));
	}

 	public static function advanced_panel(){		
		$options = call_user_func(array(self::FOOTER, 'get_options'));	
		$tip1 = self::$tooltips->tip('footer_hook');
		print <<< ADVANCED_PANEL
<p>You can place the Copyright and Trademark widgets in any existing Widget area.</p>
<p>However, if your theme does not have a suitably located Widget Area in the footer then you can create one by specifying the hook
where the Widget Area will be located.</p>
<p>You may use a standard WordPress hook like <i>get_footer</i> or <i>wp_footer</i> or choose a hook that is theme-specific such as <i>twentyten_credits</i>, 
<i>twentyeleven_credits</i> or <i>pagelines_leaf</i>.</p> 
<p>If you using a Genesis child theme and the theme does not have a suitable widget area then use the hook <i>genesis_footer</i>.</p> 
<label>{$tip1}</label><input type="text" name="footer_hook" size="30" value="{$options['footer_hook']}" /><br/>
ADVANCED_PANEL;
	}

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$keys = implode(',',self::get_keys());
		$title = sprintf('<h2>%1$s</h2>', __('Footer Credits Settings', self::DOMAIN));
		
?>
<div class="wrap">
    <?php screen_icon(); echo $title; ?>
    <div id="poststuff" class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
			<form id="footer_options" method="post" action="<?php echo $this_url; ?>">
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
			<p class="submit">
			<input type="submit"  class="button-primary" name="options_update" value="Save Changes" />
			<input type="hidden" name="page_options" value="<?php echo $keys; ?>" />
			<?php wp_nonce_field(self::CLASSNAME); ?>
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
			</p>
			</form>
 			</div>
        </div>
        <br class="clear"/>
    </div>
</div>
<?php
	}    
}

class FooterTooltip {
    const DOMAIN = "FooterCredits";
	private $labels = array();
	function __construct($labels) {
		$this->labels = $labels;
	}
	function tip($label) {
		$heading = array_key_exists($label,$this->labels) ? __($this->labels[$label]['heading'],self::DOMAIN) : ''; 
		$tip = array_key_exists($label,$this->labels) ? __($this->labels[$label]['tip'],self::DOMAIN) : ''; 
		return sprintf('<a href="#" class="tooltip">%1$s<span>%2$s</span></a>',$heading, $tip);
	}
}
?>