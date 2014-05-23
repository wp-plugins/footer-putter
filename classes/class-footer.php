<?php
if (!class_exists('Footer_Credits')) {
  class Footer_Credits {
    const CODE = 'footer-credits'; //element prefix
	const OPTIONS_NAME = 'footer_credits_options';
	const SIDEBAR_ID = 'last-footer';
	
	protected static $is_html5 = false;
	protected static $is_landing = false;
	protected static $landing_page_templates = array('page_landing.php');

	protected static $options = array();
	protected static $defaults = array(
		'terms' => array(
			'site' => '',
			'owner' => '',
			'address' => '',
            'street_address' => '',
            'locality' => '',
            'region' => '',
            'postal_code' => '',
			'country' => '',
            'latitude' => '',
            'longitude' => '',
            'map' => '',
			'email' => '',
			'telephone' => '',
			'copyright' => '',
			'copyright_start_year' => '',
			'courts' => '',
            'updated' => '',
			'privacy_contact' => '',
			'terms_contact' => ''),
		'nav_menu' => 0,
		'separator' => '&nbsp;&middot;&nbsp;',
		'center' => true,
		'two_lines' => true,
		'show_copyright' => true,
		'show_telephone' => true,
		'show_email' => false,
		'show_address' => true,
		'show_return' => true,
		'return_text' => 'Return To Top',
		'return_class' => '',
		'footer_class' => '',
		'footer_hook' => '',
		'footer_remove' => true,
 		'footer_filter_hook' => '',
 		'visibility' => '' ,
        'use_microdata' => false
	);

    public static function is_html5(){
		return self::$is_html5;
	}

	public static function init() {
		self::$is_html5 = function_exists('current_theme_supports') && current_theme_supports('html5');
		self::theme_specific_defaults();
		add_action('widgets_init',array(__CLASS__,'register'),20);
		add_filter( 'wp_nav_menu_items', array(__CLASS__, 'fix_home_link'), 10, 2 );
		if (!is_admin()) add_action('wp',array(__CLASS__,'prepare'));
	}

	public static function register() {
		self::register_sidebars();
		self::register_widgets();
	}

    private static function register_sidebars() {
    	if (self::get_option('footer_hook')) {
			$tag = self::is_html5() ? 'section' : 'div';
			register_sidebar( array(
				'id' => self::SIDEBAR_ID,
				'name'	=> __( 'Credibility Footer', __CLASS__ ),
				'description' => __( 'Custom footer section for copyright, trademarks, etc', __CLASS__),
				'before_widget' => '<'.$tag.' id="%1$s" class="widget %2$s"><div class="widget-wrap">',
				'after_widget'  => '</div></'.$tag.'>'				
			) );
		}
    }
	
	private static function register_widgets() {
		register_widget( 'Footer_Credits_Copyright_Widget' );
		register_widget( 'Footer_Credits_Trademark_Widget' );
	}	
	
	public static function prepare() {
		add_shortcode(self::CODE.'-copyright', array(__CLASS__, 'copyright_owner' ) );
		add_shortcode(self::CODE.'-menu', array(__CLASS__, 'footer_menu' ) );
		add_shortcode(self::CODE, array(__CLASS__, 'footer' ) );
		add_filter('widget_text', 'do_shortcode', 11);
		add_action('wp_enqueue_scripts',array(__CLASS__, 'enqueue_styles' ));

		self::$is_landing = self::is_landing_page(); //is this a landing page
					
		//insert custom footer at specified hook
		if ($footer_hook = self::get_option('footer_hook'))  {
			if (self::get_option('footer_remove')) remove_all_actions( $footer_hook); 
			add_action( $footer_hook, array(__CLASS__, 'custom_footer')); 
		}
	
 		//suppress footer output
 		if ($ffs = self::get_option('footer_filter_hook')) 
 			add_filter($ffs, array(__CLASS__, 'no_footer'),100); 

		if (is_page('privacy') && self::get_term('privacy_contact'))
			add_filter('the_content', array(__CLASS__, 'add_privacy_footer'),9 );	

		if (is_page('terms') && self::get_term('terms_contact'))
			add_filter('the_content', array(__CLASS__, 'add_terms_footer'),9 );	

		if (is_page('terms') || is_page('privacy') || is_page('affiliates') || is_page('disclaimer'))
			add_filter('the_content', array(__CLASS__, 'terms_filter') );	
				
	}

	public static function enqueue_styles() {
		wp_enqueue_style(__CLASS__, plugins_url('styles/footer-credits.css',dirname(__FILE__)), array(), Footer_Credits_Plugin::get_version());
    }

	public static function fix_home_link( $content, $args) {
		$class =  is_front_page()? ' class="current_page_item"' : '';
		$home_linktexts = array('Home','<span>Home</span>');
		foreach ($home_linktexts as $home_linktext) {
			$home_link = sprintf('<a>%1$s</a>',$home_linktext);
			if (strpos($content, $home_link) !== FALSE) 
				$content = str_replace ($home_link,sprintf('<a href="%1$s"%2$s>%3$s</a>',home_url(),$class,$home_linktext),$content); 
		} 
		return $content;
	}

	private static function sanitize_terms($new_terms) {
		$new_terms = wp_parse_args($new_terms, self::$defaults['terms']); //ensure terms are complete		
		$new_terms['site'] = self::get_default_site();
		$new_terms['copyright'] = self::get_copyright($new_terms['copyright_start_year']); //generate copyright
		return $new_terms;
	}

	public static function save($new_options) {
		$options = self::get_options(false);
		$new_options = wp_parse_args( $new_options, $options);
		$new_options['terms'] = self::sanitize_terms($new_options['terms']);
		$updated = update_option(self::OPTIONS_NAME,$new_options);
		if ($updated) self::get_options(false);
		return $updated;
	}	

	public static function get_options ($cache = true) {
	   if ($cache && (count(self::$options) > 0)) return self::$options;
	
	   $the_options = array();
	   $the_options = get_option(self::OPTIONS_NAME);
	   if (empty($the_options)) {
	      self::$options = self::$defaults;
	   } else {
			self::$options = shortcode_atts( self::$defaults, $the_options);
	   }
	   return self::$options;
	}
	
	public static function get_option($option_name) {
    	$options = self::get_options();
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;
    }
    
 	private static function get_terms() {
    	return self::get_option('terms');
    }   
	
	private static function get_term($term_name) {
    	$options = self::get_options();
    	$terms = is_array($options) && array_key_exists('terms',$options) ? $options['terms'] : false;
    	if ($term_name && $terms && array_key_exists($term_name,$terms) && $terms[$term_name])
        	return $terms[$term_name];
    	else
        	return self::get_default_term($term_name);    		
    }	
	
    private static function get_default_term($key) {
		$default='';
    	switch ($key) {
   			case 'owner' : $default = self::get_term('site'); break;
   			case 'copyright' : $default = self::get_copyright(self::get_term('copyright_start_year')); break;
   			case 'copyright_start_year': $default = date('Y'); break;
   			case 'country' : $default = 'The United States'; break;
   			case 'courts' : $default = ucwords(sprintf('the courts of %1$s',self::get_term('country'))); break;
   			case 'email' : $default = 'privacy@'.strtolower(self::get_term('site')); break;
   			case 'site' : $default = self::get_default_site(); break;
   			case 'updated' : $default = date('d M Y'); break;
 			default: $default='';  //default is blank for others
   		}
   		return $default;
    }
	
	private static function get_default_site() { 
		$domain = strtolower(parse_url(site_url(),PHP_URL_HOST));
		$p = strpos($domain,'www.') ;
		if (($p !== FALSE) && ($p == 0)) $domain = substr($domain,4);
		return $domain; 
	}
	
	public static function get_copyright($startyear){
  		$thisyear = date("Y");
		if(empty( $startyear)) $startyear = $thisyear;
  		return sprintf('Copyright &copy; %1$s%2$s', $startyear, $thisyear == $startyear ? '' : ("-".$thisyear));
	}

	public static function copyright_owner($attr){
		$defaults['owner'] = self::get_term('owner');
		$defaults['copyright_start_year'] = self::get_term('copyright_start_year');		
  		$params = shortcode_atts( $defaults, $attr ); //apply plugin defaults  		
  		return sprintf('<span class="copyright">%1$s %2$s</span>', 
  			self::get_copyright($params['copyright_start_year']), $params['owner']);
	}	
	
    private static function format_address ($address, $separator) {
		$s='';
		$addlines = explode(',', trim($address));
		foreach ($addlines as $a) {
			$a = trim($a);
			if (!empty($a)) $s .= $a . $separator;
		}
		return $s;
    }	
	
	public static function footer_menu($menu) {
        return wp_nav_menu(array('menu' => $menu, 'echo' => false, 'container' => false));
	}

	public static function return_to_top( $text, $class) {
		return sprintf( '<div id="footer-return" class="%1$s"><a rel="nofollow" href="#" onclick="window.scrollTo(0,0); return false;" >%2$s</a></div>', trim($class), $text);
	}

    private static function contact_info($params, $item_separator, $section_separator) {
        $org ='';
        if ($address = self::contact_address($params['show_address'], $params['use_microdata'], $params['separator'], $section_separator)) $org .= $address;
        if ($telephone = self::contact_telephone($params['show_telephone'], $params['use_microdata'],  $item_separator)) $org .= $telephone;
        if ($email = self::contact_email($params['show_email'], $params['use_microdata'], $item_separator)) $org .= $email;
        if  ($org && $params['use_microdata'])
            return sprintf('<span itemscope="itemscope" itemtype="http://schema.org/Organization">%1$s</span>', $org);
        else
            return $org;
    }

    private static function contact_telephone($show_telephone, $microdata, $prefix) {
      if  ($show_telephone && ($telephone = self::get_term('telephone')))
        if ($microdata)
            return sprintf('%1$s<span itemprop="telephone" class="telephone">%2$s</span>', $prefix, $telephone) ;
        else
            return sprintf('%1$s<span class="telephone">%2$s</span>', $prefix, $telephone) ;
      else
            return '';
    }

    private static function contact_email($show_email, $microdata, $prefix) {
      if  ($show_email && ($email = self::get_term('email')))
            return sprintf('%1$s<a href="mailto:%2$s" class="email"%3$s>%2$s</a>', $prefix, $email, $microdata ? ' itemprop="email"' : '') ;
      else
            return '';
    }

    private static function contact_address($show_address, $microdata, $separator, $prefix) {
      if  ($show_address)
        if ($microdata) {
            return self::org_location($separator, $prefix);
        } elseif ($address = self::get_term('address'))
            return sprintf('%1$s<span class="address">%2$s%3$s</span>', $prefix, self::format_address($address, $separator), self::get_term('country'));
      return '';
    }

    private static function org_location($separator, $prefix) {
        $location = '';
        if ($loc_address = self::location_address( $separator)) $location .=  $loc_address;
        if ($loc_geo = self::location_geo()) $location .= $loc_geo;
        if ($loc_map = self::location_map()) $location .= $loc_map;
        if ($location)
            return sprintf('%1$s<span itemprop="location" itemscope="itemscope" itemtype="http://schema.org/Place">%2$s</span>', $prefix, $location) ;
        else
            return '';
    }

    private static function location_address($separator) {
        $address = '';
        if ( $street_address = self::get_term('street_address'))
            $address .=  sprintf('<span itemprop="streetAddress">%1$s</span>', self::format_address($street_address, $separator)) ;
        if ( $locality = self::get_term('locality'))
                $address .=  sprintf('<span itemprop="addressLocality">%1$s</span>', self::format_address($locality, $separator)) ;
        if ( $region = self::get_term('region'))
                $address .=  sprintf('<span itemprop="addressRegion">%1$s</span>', self::format_address($region, $separator)) ;
        if ( $postal_code = self::get_term('postal_code'))
                $address .=  sprintf('<span itemprop="postalCode">%1$s</span>', self::format_address($postal_code, $separator)) ;
        if ( $country = self::get_term('country'))
                $address .=  sprintf('<span itemprop="addressCountry">%1$s</span>', $country) ;

        if ($address)
            return sprintf('<span class="address" itemprop="address" itemscope="itemscope" itemtype="http://schema.org/PostalAddress">%1$s</span>',$address) ;
        else
            return '';
    }

    private static function location_geo() {
        $geo = '';
        if ( $latitude = self::get_term('latitude')) $geo .=  sprintf('<meta itemprop="latitude" content="%1$s" />', $latitude) ;
        if ( $longitude = self::get_term('longitude')) $geo .=  sprintf('<meta itemprop="longitude" content="%1$s" />', $longitude) ;
        return $geo ? sprintf('<span itemprop="geo" itemscope="itemscope" itemtype="http://schema.org/GeoCoordinates">%1$s</span>', $geo) : '';
    }

    private static function location_map() {
        if ( $map = self::get_term('map'))
            return sprintf('<a rel="nofollow external" target="_blank" class="map" itemprop="map" href="%1$s">%2$s</a>', $map, __('Map')) ;
        else
            return '';
    }

	public static function footer($atts = array()) {
  		$params = shortcode_atts( self::get_options(), $atts ); //apply plugin defaults

		if ($params['center']) {
			$item_separator = '&nbsp;';
			$section_separator = $params['two_lines'] ? '<br/>' : $params['separator'];
			$params['return_class'] .= ' return-center';
			$params['footer_class'] .= ' footer-center';
			$clear = '';
		} else {
			$item_separator = $params['two_lines'] ? $params['separator'] : '<br/>' ;
			$section_separator = '<br/>';
			$params['return_class'] .= ' return-left';
			$params['footer_class'] .= ' footer-right';
			$clear = '<div class="clear"></div>';
		}	
		return (empty($params['show_return']) ? '' :
			self::return_to_top($params['return_text'], $params['return_class'])) . 
			sprintf('<div id="%1$s" class="%2$s">%3$s%4$s%5$s</div>%6$s<!-- end #%1$s -->',
				self::CODE,
				$params['footer_class'], 	
				(empty($params['nav_menu']) ? '' : self::footer_menu($params['nav_menu'])),
				(empty($params['show_copyright']) ? '' : sprintf('%1$s%2$s', $item_separator, self::copyright_owner(self::get_terms()))),
				self::contact_info($params, $item_separator, $section_separator),
				$clear
			);				
	}

	public static function terms_filter($content) {
		if ($terms = self::get_terms()) {
			$from = array();
			$to = array();
			foreach ($terms as $term => $value) {
				$from[] = '%%'.$term.'%%';
				$to[] = $value;
			}
			return str_replace($from,$to,$content);
		}
		return $content;
	}

	public static function custom_footer() {
		if ( is_active_sidebar( self::SIDEBAR_ID) ) {
			if (self::is_html5()) {
				echo '<footer class="custom-footer" role="contentinfo" itemscope="itemscope" itemtype="http://schema.org/WPFooter">';
				dynamic_sidebar( self::SIDEBAR_ID );
				echo '</footer><!-- end .custom-footer -->';
			} else {
				echo '<div class="custom-footer">';
				dynamic_sidebar( self::SIDEBAR_ID );
				echo '</div><!-- end .custom-footer -->';
			}
		}
	}

    public static function no_footer($content) { return ''; }  		

	public static function is_terms_key($key) {
		return array_key_exists($key, self::$defaults['terms']);
	}
	
	private static function theme_specific_defaults() {
		switch (basename( TEMPLATEPATH ) ) {  
			case 'twentyten': 
				self::$defaults['footer_hook'] = 'twentyten_credits'; break;
			case 'twentyeleven': 
				self::$defaults['footer_hook'] = 'twentyeleven_credits'; break;
			case 'twentytwelve': 
				self::$defaults['footer_hook'] = 'twentytwelve_credits'; break;
			case 'twentythirteen': 
				self::$defaults['footer_hook'] = 'twentythirteen_credits'; break;
			case 'twentyfourteen': 
				self::$defaults['footer_hook'] = 'twentyfourteen_credits'; break;
			case 'delicate': 
				self::$defaults['footer_hook'] = 'get_footer'; break;
			case 'genesis': 
				self::$defaults['footer_hook'] = 'genesis_footer';
				self::$defaults['footer_filter_hook'] = 'genesis_footer_output';
				break;
			case 'graphene': 
				self::$defaults['footer_hook'] = 'graphene_footer'; break;
			case 'pagelines': 
				self::$defaults['footer_hook'] = 'pagelines_leaf'; break;
			default: 
				self::$defaults['footer_hook'] = 'wp_footer';
				self::$defaults['footer_remove'] = false;				
				break;
		}
	}

	public static function add_privacy_footer($content) {
		$email = self::get_term('email');	
		$address = self::get_term('address');
		$country = self::get_term('country');
		$owner = self::get_term('owner');
		$contact = <<< PRIVACY
<h2>How to Contact Us</h2> 
<p>Questions about this statement or about our handling of your information may be sent by email to <a href="mailto:{$email}">{$email}</a>, or by post to {$owner} Privacy Office, {$address} {$country}. </p>
PRIVACY;
		return (strpos($content,'%%') == FALSE) ? ($content . $contact) : $content;
	}

	public static function add_terms_footer($content) {
		$email = self::get_term('email');	
		$address = self::get_term('address');
		$country = self::get_term('country');
		$courts = self::get_term('courts');
		$owner = self::get_term('owner');
		$copyright = self::get_term('copyright');
		$updated = self::get_term('updated');
		$terms_contact = self::get_term('terms_contact');
		$disputes = <<< DISPUTES
<h2>Dispute Resolution</h2>
<p>These terms, and any dispute arising from the use of this site, will be governed by {$courts} without regard to its conflicts of laws provisions.</p>
DISPUTES;
		$terms = <<< TERMS
<h2>Feedback And Information</h2> 
<p>Any feedback you provide at this site shall be deemed to be non-confidential. {$owner} shall be free to use such information on an unrestricted basis.</p>
<p>The terms and conditions for this web site are subject to change without notice.<p>
<p>{$copyright} {$owner} All rights reserved.<br/> {$owner}, {$address} {$country}</p>
<p>Updated by The {$owner} Legal Team on {$updated}</p>
TERMS;
		if (strpos($content,'%%') == FALSE) {
			$content .= $courts ? $disputes : '';
			$content .= $address ? $terms : '';
		}
		return $content ;
	}

	public static function hide_widget($instance) {
		$hide = false;
		if (array_key_exists('visibility',$instance))
			switch ($instance['visibility']) {
				case 'hide_landing' : $hide = self::$is_landing; break; //hide only on landing pages
				case 'show_landing' : $hide = ! self::$is_landing; break; //hide except on landing pages
			}
		return $hide;
	}

    public static function get_visibility_options(){
		return array(
			'' => 'Show on all pages', 
			'hide_landing' => 'Hide on landing pages', 
			'show_landing' => 'Show only on landing pages');
	}
	
	public static function form_field($fld_id, $fld_name, $label, $value, $type, $options = array(), $args = array()) {
		if ($args) extract($args);
		$input = '';
		$label = sprintf('<label for="%1$s">%2$s</label>', $fld_id, __($label));
		switch ($type) {
			case 'text':
				$input .= sprintf('<input type="text" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s %6$s/> %7$s',
					$fld_id, $fld_name, $value, 
					isset($size) ? ('size="'.$size.'"') : '', isset($maxlength) ? ('maxlength="'.$maxlength.'"') : '',
					isset($class) ? ('class="'.$class.'"') : '', isset($suffix) ? $suffix : '');
				return sprintf('<p>%1$s: %2$s</p>', $label, $input);
				break;
			case 'textarea':
				$input .= sprintf('<textarea id="%1$s" name="%2$s"%3$s%4$s%5$s>%6$s</textarea>',
					$fld_id, $fld_name, 
					isset($rows) ? (' rows="'.$rows.'"') : '', isset($cols) ? (' cols="'.$cols.'"') : '',
					isset($class) ? (' class="'.$class.'"') : '', $value);
				return sprintf('<p>%1$s: %2$s</p>', $label, $input);
				break;
			case 'checkbox':
				$input .= sprintf('<input type="checkbox" class="checkbox" id="%1$s" name="%2$s" %3$svalue="1"/>',
					$fld_id, $fld_name, checked($value, '1', false));
				return sprintf('%1$s%2$s<br/>', $input, $label);
				break;
			case 'radio': 
				$sep = (is_array($args) && array_key_exists('separator', $args)) ? $args['separator'] : '&nbsp;&nbsp;';
				if (is_array($options)) 
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<input type="radio" id="%1$s" name="%2$s" %3$s value="%4$s" />&nbsp;%5$s%6$s',
							$fld_id, $fld_name, checked($optkey, $value, false), $optkey, $optlabel, $sep); 
				return sprintf('<p>%1$s%2$s</p>', $label, $input);							
				break;		
			case 'select': 
				if (is_array($options)) 
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<option%1$s value="%2$s">%3$s</option>',
							selected($optkey, $value, false), $optkey, $optlabel); 
				return sprintf('<p>%1$s: %2$s</p>', $label, self::selector($fld_id, $fld_name, $value, $options));							
				break;		
		}
	}

	private static function selector($fld_id, $fld_name,  $value, $options) {
		$input = '';
		if (is_array($options)) 
			foreach ($options as $optkey => $optlabel)
				$input .= sprintf('<option%1$s value="%2$s">%3$s</option>',
					selected($optkey, $value, false), $optkey, $optlabel); 
		return sprintf('<select id="%1$s" name="%2$s">%3$s</select>', $fld_id, $fld_name, $input);							
	}
	
	public static function is_landing_page() {
		if (is_page()) {
			global $post;
			$page_template_file = get_post_meta($post->ID,'_wp_page_template',true);
			// you can add your own landing page templates to the array using the filter
			$landing_page_templates = (array) apply_filters('footer_putter_landing_page_templates', self::$landing_page_templates);
			return in_array($page_template_file, $landing_page_templates );
		} else {
			return false;
		}	
	}	
  }
}
