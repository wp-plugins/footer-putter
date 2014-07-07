<?php

if (!class_exists('Footer_Credits_Admin')) {
class Footer_Credits_Admin {
    const CODE = 'footer-credits'; //prefix ID of CSS elements
    const FOOTER = 'Footer_Credits'; //class that builds footer
	const SLUG = 'credits';
    private static $slug;
    private static $screen_id;
    private static $keys;
	private static $tips = array(
			'owner' => array('heading' => 'Owner or Business Name', 'tip' => 'Enter the name of the legal entity that owns and operates the site.'),
            'microdata' => array('heading' => 'Use Microdata', 'tip' => 'Markup the organization details with HTML5 microdata.'),
			'address' => array('heading' => 'Full Address', 'tip' => 'Enter the full address that you want to appear in the footer and the privacy and terms pages.'),
	        'street_address' => array('heading' => 'Street Address', 'tip' => 'Enter the firat line of the address that you want to appear in the footer and the privacy and terms pages.'),
			'locality' => array('heading' => 'Locality (City)', 'tip' => 'Enter the town or city.'),
			'region' => array('heading' => 'State (Region)', 'tip' => 'Enter the state, province, region or county.'),
			'postal_code' => array('heading' => 'Postal Code', 'tip' => 'Enter the postal code.'),
			'country' => array('heading' => 'Country', 'tip' => 'Enter the country where the legal entity is domiciled.'),
			'latitude' => array('heading' => 'Latitude', 'tip' => 'Enter the latitude of the organization&#39;s location - maybe be used by Google or local search.'),
			'longitude' => array('heading' => 'Longitude', 'tip' => 'Enter the longitude of the organization&#39;s location - maybe be used by Google or local search.'),
			'map' => array('heading' => 'Map URL', 'tip' => 'Enter the URL of a map that shows the organization&#39;s location.'),
			'telephone' => array('heading' => 'Telephone Number', 'tip' => 'Enter a telephone number here if you want it to appear in the footer of the installed site.'),
			'email' => array('heading' => 'Email Address', 'tip' => 'Enter the email address here if you want it to appear in the footer and in the privacy statement.'),
			'courts' => array('heading' => 'Legal Jurisdiction' , 'tip' => 'The Courts that have jurisdiction over any legal disputes regarding this site. For example: <i>the state and federal courts in Santa Clara County, California</i>, or <i>the Law Courts of England and Wales</i>'),
			'updated' => array('heading' => 'Last Updated' , 'tip' => 'This will be defaulted as today. For example, Oct 23rd, 2012'),
			'copyright_start_year' => array('heading' => 'Copyright Start' , 'tip' => 'The start year of the business appears in the copyright statement in the footer and an on the Terms and Conditions page.'),
			'return_text' => array('heading' => 'Link Text' , 'tip' => 'The text of the Return To Top link. For example, <i>Return To Top</i> or <i>Back To Top</i>.'),
			'return_class' => array('heading' => 'Return To Top Class' , 'tip' => 'Add any custom class you want to apply to the Return To Top link.'),
			'footer_class' => array('heading' => 'Footer Class' , 'tip' => 'Add any custom class you want to apply to the footer. The plugin comes with a class <i>white</i> that marks the text in the footer white. This is useful where the footer background is a dark color.'),
			'footer_hook' => array('heading' => 'Footer Action Hook' , 'tip' => 'The hook where the footer widget area is added to the page. This field is only required if the theme does not already provide a suitable widget area where the footer widgets can be added.'),
			'footer_remove' => array('heading' => 'Remove Existing Actions?' , 'tip' => 'Click the checkbox to remove any other actions at the above footer hook. This may stop you getting two footers; one created by your theme and another created by this plugin. For some themes you will check this option as you will typically want to replace the theme footer by the plugin footer.'),
			'footer_filter_hook' => array('heading' => 'Footer Filter Hook' , 'tip' => 'If you want to kill off the footer created by your theme, and your theme allows you to filter the content of the footer, then enter the hook where the theme filters the footer. This may stop you getting two footers; one created by your theme and another created by this plugin.'),
			'privacy_contact' => array('heading' => 'Add Privacy Contact?', 'tip' => 'Add a section to the end of the Privacy page with contact information'),
			'terms_contact' => array('heading' => 'Add Terms Contact?', 'tip' => 'Add a section to the end of the Terms page with contact and legal information'),
	);
	private static $tooltips;

	public static function init() {
		self::$slug = Footer_Credits_Plugin::get_slug() . '-' . self::SLUG;  
		add_action('admin_menu',array(__CLASS__, 'admin_menu'));
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
	
 	public static function get_url() {
		return admin_url('admin.php?page='.self::get_slug());
	}
		
	public static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	

	public static function admin_menu() {
		self::$screen_id = add_submenu_page(Footer_Credits_Plugin::get_slug(), __('Footer Credits'), __('Footer Credits'), 'manage_options', 
			self::get_slug(), array(__CLASS__,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(__CLASS__, 'load_page'));
	}


	public static function load_page() {
 		$message =  isset($_POST['options_update']) ? self::save() : '';	
		$options = Footer_Credits::get_options();
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Introduction'), array(__CLASS__, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-owner', __('Site Owner Details'), array(__CLASS__, 'owner_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-contact', __('Contact Details'), array(__CLASS__, 'contact_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-legal', __('Legal Details'), array(__CLASS__, 'legal_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-return', __('Return To Top'), array(__CLASS__, 'return_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-example', __('Preview Footer Content'), array(__CLASS__, 'preview_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-advanced', __('Advanced'), array(__CLASS__, 'advanced_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));
 		add_action('admin_enqueue_scripts',array(__CLASS__, 'enqueue_scripts'));	
	    self::$keys = array_keys(self::$tips);	
		self::$tooltips = new DIY_Tooltip(self::$tips);
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE, plugins_url('styles/footer-credits.css', dirname(__FILE__)), array(),Footer_Credits_Plugin::get_version());
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),Footer_Credits_Plugin::get_version());
		wp_enqueue_style(self::CODE.'-tooltip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(),Footer_Credits_Plugin::get_version());
 	}		

	public static function enqueue_scripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action('admin_footer-'.self::get_screen_id(), array(__CLASS__, 'toggle_postboxes'));
	}

	public static function save() {
		check_admin_referer(__CLASS__);
  		$page_options = explode(',', stripslashes($_POST['page_options']));
  		if ($page_options) {
  			$options = Footer_Credits::get_options();
    		foreach ($page_options as $option) {
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
				if (Footer_Credits::is_terms_key($option))
					$options['terms'][$option] = $val;
 				else switch($option) {
					case 'footer_remove' : $options[$option] = !empty($val); break;
 					case 'footer_hook': 
 					case 'footer_filter_hook': $options[$option] = preg_replace('/\W/','',$val); break;
					default: $options[$option] = trim($val); 				
					}
    		} //end for	
    		$class='updated fade';
   			$saved =  Footer_Credits::save($options) ;
   			if ($saved)  {
       			$message = 'Footer Settings saved.';
   			} else
       			$message = 'Footer Settings have not been changed.';
  		} else {
  		    $class='error';
       		$message= 'Footer Settings not found!';
  		}
  		return sprintf('<div id="message" class="%1$s "><p>%2$s</p></div>',$class, __($message));
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
<p>The following information is used in the Footer Copyright Widget and optionally at the end of the Privacy Statement and Terms and Conditions pages.</p>
{$message}
INTRO_PANEL;
	}
	
	public static function owner_panel($post,$metabox){	
		$terms = $metabox['args']['options']['terms'];	 	
		$tip1 = self::$tooltips->tip('owner');
		$tip2 = self::$tooltips->tip('country');
		$tip3 = self::$tooltips->tip('address');
		$tip4 = self::$tooltips->tip('street_address');
		$tip5 = self::$tooltips->tip('locality');
		$tip6 = self::$tooltips->tip('region');
		$tip7 = self::$tooltips->tip('postal_code');
		$tip8 = self::$tooltips->tip('latitude');
		$tip9 = self::$tooltips->tip('longitude');
		$tip10 = self::$tooltips->tip('map');
	print <<< OWNER_PANEL
<label>{$tip1}</label><input type="text" name="owner" size="30" value="{$terms['owner']}" /><br/>
<label>{$tip2}</label><input type="text" name="country" size="30" value="{$terms['country']}" /><br/>
<label>{$tip3}</label><input type="text" name="address" size="80" value="{$terms['address']}" /><br/>
OWNER_PANEL;
        if (Footer_Credits::is_html5()) print <<< ADDRESS_DATA
<p>Leave the above address field blank and fill in the various parts of the organization address below if you want to be able to use HTML5 microdata.</p>
<h4>Organization Address</h4>
<label>{$tip4}</label><input type="text" name="street_address" size="30" value="{$terms['street_address']}" /><br/>
<label>{$tip5}</label><input type="text" name="locality" size="30" value="{$terms['locality']}" /><br/>
<label>{$tip6}</label><input type="text" name="region" size="30" value="{$terms['region']}" /><br/>
<label>{$tip7}</label><input type="text" name="postal_code" size="12" value="{$terms['postal_code']}" /><br/>
<h4>Geographical Co-ordinates</h4>
<p>The geographical co-ordinates are optional and are visible only to the search engines.</p>
<label>{$tip8}</label><input type="text" name="latitude" size="12" value="{$terms['latitude']}" /><br/>
<label>{$tip9}</label><input type="text" name="longitude" size="12" value="{$terms['longitude']}" /><br/>
<label>{$tip10}</label><input type="text" name="map" size="30" value="{$terms['map']}" /><br/>
ADDRESS_DATA;
	}

	public static function contact_panel($post,$metabox){
		$terms = $metabox['args']['options']['terms'];
		$tip1 = self::$tooltips->tip('email');
		$tip2 = self::$tooltips->tip('telephone');
		$tip3 = self::$tooltips->tip('privacy_contact');
		$tip4 = self::$tooltips->tip('terms_contact');
		$privacy_contact = $terms['privacy_contact'] ? 'checked="checked"' : '';
		$terms_contact = $terms['terms_contact'] ? 'checked="checked"' : '';
		print <<< CONTACT_PANEL
<label>{$tip1}</label><input type="text" name="email" size="30" value="{$terms['email']}" /><br/>
<label>{$tip2}</label><input type="text" name="telephone" size="30" value="{$terms['telephone']}" /><br/>
<label>{$tip3}</label><input type="checkbox" name="privacy_contact" {$privacy_contact} value="1" /><br/>
<label>{$tip4}</label><input type="checkbox" name="terms_contact" {$terms_contact} value="1" /><br/>
CONTACT_PANEL;
	}

 	public static function legal_panel($post,$metabox){
		$terms = $metabox['args']['options']['terms'];
		$tip1 = self::$tooltips->tip('courts');
		$tip2 = self::$tooltips->tip('updated');
		$tip3 = self::$tooltips->tip('copyright_start_year');
		print <<< LEGAL_PANEL
<label>{$tip1}</label><input type="text" name="courts" size="80" value="{$terms['courts']}" /><br/>
<label>{$tip2}</label><input type="text" name="updated" size="30" value="{$terms['updated']}" /><br/>
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
		echo Footer_Credits::footer(array('nav_menu' => 'Footer Menu'));
	}

 	public static function advanced_panel($post,$metabox){		
		$options = $metabox['args']['options'];	 	
		$tip1 = self::$tooltips->tip('footer_hook');
		$tip2 = self::$tooltips->tip('footer_remove');
		$tip3 = self::$tooltips->tip('footer_filter_hook');
		$url = 'http://www.diywebmastery.com/footer-credits-compatible-themes-and-hooks';
		$footer_remove = $options['footer_remove'] ? 'checked="checked" ' : '';
		print <<< ADVANCED_PANEL
<p>You can place the Copyright and Trademark widgets in any existing widget area. However, if your theme does not have a suitably located widget area in the footer then you can create one by specifying the hook
where the Widget Area will be located.</p>
<p>You may use a standard WordPress hook like <i>get_footer</i> or <i>wp_footer</i> or choose a hook that is theme-specific such as <i>twentyten_credits</i>, 
<i>twentyeleven_credits</i>, <i>twentytwelve_credits</i>,<i>twentythirteen_credits</i> or <i>twentyfourteen_credits</i>. If you using a Genesis child theme and the theme does not have a suitable widget area then use 
the hook <i>genesis_footer</i> or maybe <i>genesis_after</i>. See what looks best. Click for <a href="{$url}">suggestions of which hook to use for common WordPress themes</a>.</p> 
<label>{$tip1}</label><input type="text" name="footer_hook" size="30" value="{$options['footer_hook']}" /><br/>
<label>{$tip2}</label><input type="checkbox" name="footer_remove" {$footer_remove}value="1" /><br/>
<p>If your WordPress theme supplies a filter hook rather than an action hook where it generates the footer, and you want to suppress the theme footer,
then specify the hook below. For example, entering <i>genesis_footer_output</i> will suppress the standard Genesis child theme footer.</p>
<label>{$tip3}</label><input type="text" name="footer_filter_hook" size="30" value="{$options['footer_filter_hook']}" /><br/>
ADVANCED_PANEL;
	}

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$keys = implode(',',self::get_keys());
		$title = sprintf('<h2 class="title">%1$s</h2>', __('Footer Credits Settings'));
		
?>
<div class="wrap">
    <?php echo $title; ?>
    <div id="poststuff" class="metabox-holder">
        <div id="post-body" class="with-tooltips">
            <div id="post-body-content">
			<form id="footer_options" method="post" action="<?php echo $this_url; ?>">
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
			<p class="submit">
			<input type="submit"  class="button-primary" name="options_update" value="Save Changes" />
			<input type="hidden" name="page_options" value="<?php echo $keys; ?>" />
			<?php wp_nonce_field(__CLASS__); ?>
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
}
