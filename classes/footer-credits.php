<?php
class FooterCredits {
    const CODE = 'footer-credits'; //element prefix
	const OPTIONS_NAME = 'footer_credits_options'; 
	const SIDEBAR_ID = 'last-footer';
	const VERSION = '1.7';
    private static $version;

	protected static $is_html5 = false;
	protected static $is_landing = false;
	protected static $options = array();
	protected static $defaults = array(
		'terms' => array(
			'site' => '',
			'owner' => '',
			'copyright' => '',
			'copyright_start_year' => '',
			'country' => '',
			'courts' => '',
			'email' => '',
			'telephone' => '',
			'address' => '',
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
 		'visibility' => ''
	);
	
    private static function get_version(){
		return self::$version;
	}
	
	public static function init() {
		self::$version = self::VERSION;
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

	public static function prepare() {
		add_shortcode(self::CODE.'-copyright', array(__CLASS__, 'copyright_owner' ) );
		add_shortcode(self::CODE.'-menu', array(__CLASS__, 'footer_menu' ) );
		add_shortcode(self::CODE, array(__CLASS__, 'footer' ) );
		add_filter('widget_text', 'do_shortcode', 11);
		add_action('wp_enqueue_scripts',array(__CLASS__, 'enqueue_styles' ));

		self::$is_landing = is_page_template('page_landing.php');
			
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
	
    private static function register_sidebars() {
    	if (self::get_option('footer_hook')) {
			$tag = self::$is_html5 ? 'section' : 'div';
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
		register_widget( 'Footer_Putter_Copyright_Widget' );
		register_widget( 'Footer_Putter_TradeMark_Widget' );
	}	
	
	public static function enqueue_styles() {
		wp_enqueue_style(__CLASS__, plugins_url('styles/footer-credits.css',dirname(__FILE__)), array(), self::get_version());
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

	public static function footer($atts = array()) {
  		$params = shortcode_atts( self::get_options(), $atts ); //apply plugin defaults   
		
		if ($params['center']) {
			$section_separator = '&nbsp;';
			$item_separator = $params['two_lines'] ? '<br/>' : $params['separator'];
			$params['return_class'] .= ' return-center';
			$params['footer_class'] .= ' footer-center';
			$clear = '';
		} else {
			$section_separator = $params['two_lines'] ? $params['separator'] : '<br/>' ;
			$item_separator = '<br/>';
			$params['return_class'] .= ' return-left';
			$params['footer_class'] .= ' footer-right';
			$clear = '<div class="clear"></div>';
		}	
		$copyright = self::copyright_owner(self::get_terms());
		$telephone = self::get_term('telephone');			
		$email = self::get_term('email');	
		$address = self::get_term('address');
		return (empty($params['show_return']) ? '' :
			self::return_to_top($params['return_text'], $params['return_class'])) . 
			sprintf('<div id="%1$s" class="%2$s">%3$s%4$s%5$s%6$s%7$s</div>%8$s<!-- end #%1$s -->', 
				self::CODE,
				$params['footer_class'], 	
				(empty($params['nav_menu']) ? '' : self::footer_menu($params['nav_menu'])), 
				(empty($params['show_copyright']) ? '' : sprintf('%1$s%2$s', $section_separator, $copyright)),
				((empty($address) || empty($params['show_address'])) ? '' : sprintf('%1$s<span class="address">%2$s%3$s</span>', $item_separator, self::format_address($address, $params['separator']), self::get_term('country')) ),
				((empty($telephone) || empty($params['show_telephone'])) ? '' : sprintf('%1$s<span class="telephone">%2$s</span>', $section_separator, $telephone) ),
				((empty($email) || empty($params['show_email'])) ? '' : sprintf('%1$s<span class="email"><a href="mailto:%2$s">%2$s</a></span>', $section_separator, $email) ),
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
			if (self::$is_html5) {
				echo '<footer class="custom-footer" role="contentinfo" itemscope="" itemtype="http://schema.org/WPFooter">';
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

}

class Footer_Putter_Copyright_Widget extends WP_Widget {

	const DOMAIN = 'FooterCredits';

    private	$defaults = array( 
    	'nav_menu' => 0, 'center' => true, 'two_lines' => true,  
		'show_copyright' => true, 'show_address' => true, 'show_telephone' => true, 'show_email' => false,
		'show_return' => true, 'return_class' => '', 'footer_class' => '', 'visibility' => '');

	function __construct() {
		$widget_ops = array( 'description' => __( "A widget displaying menu links, copyright and company details" ) );
		parent::__construct('footer_copyright', __('Footer Copyright Widget'), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		extract( $args );		
		if (FooterCredits::hide_widget($instance)) return; //check visibility requirements

		if ($footer = FooterCredits::footer($instance)) 
			printf ('%1$s%2$s%3$s', $before_widget, $footer, $after_widget);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['nav_menu'] = !empty($new_instance['nav_menu']) ? $new_instance['nav_menu'] : 0;
		$instance['show_copyright'] = !empty($new_instance['show_copyright']) ? 1 : 0;
		$instance['show_telephone'] = !empty($new_instance['show_telephone']) ? 1 : 0;	
		$instance['show_email'] = !empty($new_instance['show_email']) ? 1 : 0;	
		$instance['show_address'] = !empty($new_instance['show_address']) ? 1 : 0;	
		$instance['center'] = !empty($new_instance['center']) ? 1 : 0;
		$instance['two_lines'] = !empty($new_instance['two_lines']) ? 1 : 0;	
		$instance['show_return'] = !empty($new_instance['show_return']) ? 1 : 0;
		$instance['return_class'] = trim($new_instance['return_class']);
		$instance['footer_class'] = trim($new_instance['footer_class']);
		$instance['visibility'] = trim($new_instance['visibility']);
		return $instance;
	}

	function form( $instance ) {
		$menu_terms = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
		if ( !$menu_terms ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.', GENESIS_CLUB_DOMAIN ), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		$menus = array();
		$menus[0] = 'Do not show a menu';
		foreach ( $menu_terms as $term ) $menus[ $term->term_id ] = $term->name;
		
		$instance = wp_parse_args( (array) $instance, $this->defaults);
		$this->print_form_field($instance, 'nav_menu', 'Select Footer Menu', 'select', $menus);
		$this->print_form_field($instance, 'center', 'Center Menu', 'checkbox');
		$this->print_form_field($instance, 'two_lines', 'Spread Over Two Lines', 'checkbox');
		$this->print_form_field($instance, 'show_copyright', 'Show Copyright', 'checkbox');
		$this->print_form_field($instance, 'show_address', 'Show Address', 'checkbox');
		$this->print_form_field($instance, 'show_telephone', 'Show Telephone number', 'checkbox');
		$this->print_form_field($instance, 'show_email', 'Show Email Address', 'checkbox');
		$this->print_form_field($instance, 'show_return', 'Show Return To Top Links', 'checkbox');

		print <<< CUSTOM_CLASSES
<h4>Custom Classes (Optional)</h4>
<p>Add any custom CSS classes you want apply to the footer section content to change the font color and size.</p>
<p>For your convenience we have defined 3 color classes <i>dark</i>, <i>light</i> and <i>white</i>, and 2 size classes, 
<i>small</i> and <i>tiny</i>. Feel free to use these alongside your own custom CSS classes.</p>
CUSTOM_CLASSES;

		$this->print_form_field($instance, 'return_class', 'Return To Top', 'text', array(), array('size' => 10));
		$this->print_form_field($instance, 'footer_class', 'Footer Credits', 'text', array(), array('size' => 10));
		$this->print_form_field($instance, 'visibility', '<h4>Widget Visibility</h4>', 'radio',
			FooterCredits::get_visibility_options(), array('separator' => '<br/>'));
	}

	function print_form_field($instance, $fld, $label, $type, $options = array(), $args = array()) {
		$value = array_key_exists($fld,$instance) ? $instance[$fld] : false;
		print FooterCredits::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), $label, $value, $type, $options, $args);
	}

}