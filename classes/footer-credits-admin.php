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
    private static $keys = array('owner', 'site', 'address', 'country', 'telephone', 
				'email', 'courts', 'updated', 'copyright_start_year', 'return_text', 'return_class',
				'footer_class','footer_hook','footer_remove','footer_filter_hook','enable_html5');
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
			'return_class' => array('heading' => 'Return To Top Class' , 'tip' => 'Add any custom class you want to apply to the Return To Top link.'),
			'footer_class' => array('heading' => 'Footer Class' , 'tip' => 'Add any custom class you want to apply to the footer. The plugin comes with a class <i>white</i> that marks the text in the footer white. This is useful where the footer background is a dark color.'),
			'footer_hook' => array('heading' => 'Footer Action Hook' , 'tip' => 'The hook where the footer widget area is added to the page. This field is only required if the theme does not already provide a suitable widget area where the footer widgets can be added.'),
			'footer_remove' => array('heading' => 'Remove Existing Actions?' , 'tip' => 'Click the checkbox to remove any other actions at the above footer hook. This may stop you getting two footers; one created by your theme and another created by this plugin. For some themes you will check this option as you will typically want to replace the theme footer by the plugin footer.'),
			'footer_filter_hook' => array('heading' => 'Footer Filter Hook' , 'tip' => 'If you want to kill off the footer created by your theme, and your theme allows you to filter the content of the footer, then enter the hook where the theme filters the footer. This may stop you getting two footers; one created by your theme and another created by this plugin.'),
			'enable_html5' => array('heading' => 'Enable HTML5' , 'tip' => 'Use the HTML5 &lt;footer&gt; element for the custom footer widget area.')
	);
	private static $tooltips;

	public static function init($parent) {
		self::$version = FooterCredits::VERSION;
		self::$parenthook = $parent;
	    self::$slug = self::$parenthook . '-' . self::SLUG;
		add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
	}
	
    private static function get_parenthook(){
		return self::$parenthook;
	}

    private static function get_version(){
		return self::$version;
	}

    public static function get_slug(){
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
	
	public static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	

	public static function admin_menu() {
		self::$screen_id =  add_submenu_page(self::get_parenthook(), __('Footer Credits'), __('Footer Credits'), 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page'));
	}


	public static function load_page() {
 		$message =  isset($_POST['options_update']) ? self::save() : '';	
		$options = call_user_func(array(self::FOOTER, 'get_options'));
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Introduction',self::DOMAIN), array(self::CLASSNAME, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-owner', __('Site Owner Details',self::DOMAIN), array(self::CLASSNAME, 'owner_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-contact', __('Contact Details',self::DOMAIN), array(self::CLASSNAME, 'contact_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-legal', __('Legal Details',self::DOMAIN), array(self::CLASSNAME, 'legal_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-return', __('Return To Top',self::DOMAIN), array(self::CLASSNAME, 'return_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-example', __('Preview Footer',self::DOMAIN), array(self::CLASSNAME, 'preview_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-advanced', __('Advanced',self::DOMAIN), array(self::CLASSNAME, 'advanced_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_action('admin_enqueue_scripts', array(self::CLASSNAME, 'enqueue_styles'));
 		add_action('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_scripts'));	
		self::$tooltips = new DIYTooltip(self::$tips);
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE, plugins_url('styles/footer-credits.css', dirname(__FILE__)), array(),self::get_version());
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),FOOTER_PUTTER_VERSION);
		wp_enqueue_style(self::CODE.'-tooltip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(),FOOTER_PUTTER_VERSION);
 	}		

	public static function enqueue_scripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action('admin_footer-'.self::get_screen_id(), array(self::CLASSNAME, 'toggle_postboxes'));
	}

	public static function save() {
		check_admin_referer(self::CLASSNAME);
  		$page_options = explode(',', stripslashes($_POST['page_options']));
  		if ($page_options) {
  			$options = call_user_func(array(self::FOOTER, 'get_options'));
    		foreach ($page_options as $option) {
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
				if (call_user_func(array(self::FOOTER, 'is_terms_key'),$option))
					$options['terms'][$option] = $val;
 				else switch($option) {
					case 'footer_remove' : $options[$option] = !empty($val); break;
 					case 'footer_hook': 
 					case 'footer_filter_hook': $options[$option] = preg_replace('/\W/','',$val); break;
					default: $options[$option] = trim($val); 				
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
 
	public static function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		print <<< INTRO_PANEL
<p>The following information is in the Footer Copyright Widget and if this is a <a target="_blank" href="http://www.wpwhoosh.com">whooshed site</a> on the Privacy Statement and Terms and Conditions pages.</p>
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
		print <<< RETURN_PANEL
<label>{$tip1}</label><input type="text" name="return_text" size="20" value="{$options['return_text']}" /><br/>
RETURN_PANEL;
	}

 	public static function preview_panel($post,$metabox){		
		$options = $metabox['args']['options'];	 	
		echo call_user_func(array(self::FOOTER, 'footer'),array('nav_menu' => 'Footer Menu'));
	}

 	public static function advanced_panel($post,$metabox){		
		$options = $metabox['args']['options'];	 	
		$tip1 = self::$tooltips->tip('footer_hook');
		$tip2 = self::$tooltips->tip('footer_remove');
		$tip3 = self::$tooltips->tip('footer_filter_hook');
		$tip4 = self::$tooltips->tip('enable_html5');
		$url = 'http://www.diywebmastery.com/footer-credits-compatible-themes-and-hooks';
		$footer_remove = $options['footer_remove'] ? 'checked="checked" ' : '';
		$enable_html5 = $options['enable_html5'] ? 'checked="checked" ' : '';
		print <<< ADVANCED_PANEL
<p>You can place the Copyright and Trademark widgets in any existing widget area. However, if your theme does not have a suitably located widget area in the footer then you can create one by specifying the hook
where the Widget Area will be located.</p>
<p>You may use a standard WordPress hook like <i>get_footer</i> or <i>wp_footer</i> or choose a hook that is theme-specific such as <i>twentyten_credits</i>, 
<i>twentyeleven_credits</i> or <i>twentytwelve_credits</i>. If you using a Genesis child theme and the theme does not have a suitable widget area then use 
the hook <i>genesis_footer</i> or maybe <i>genesis_after</i>. See what looks best. Click for <a href="{$url}">suggestions of which hook to use for common WordPress themes</a>.</p> 
<label>{$tip1}</label><input type="text" name="footer_hook" size="30" value="{$options['footer_hook']}" /><br/>
<label>{$tip2}</label><input type="checkbox" name="footer_remove" {$footer_remove}value="1" /><br/>
<p>If your WordPress theme supplies a filter hook rather than an action hook where it generates the footer, and you want to suppress the theme footer,
then specify the hook below. For example, entering <i>genesis_footer_output</i> will suppress the standard Genesis child theme footer.</p>
<label>{$tip3}</label><input type="text" name="footer_filter_hook" size="30" value="{$options['footer_filter_hook']}" /><br/>
<label>{$tip4}</label><input type="checkbox" name="enable_html5" {$enable_html5}value="1" /><br/>
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
?>