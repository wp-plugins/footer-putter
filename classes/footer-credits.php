<?php
class FooterCredits {
    const CLASSNAME = 'FooterCredits'; //name of class - must be same as line above - hard code for performance
    const DOMAIN = 'FooterCredits'; //text domain for translation
    const CODE = 'footer-credits'; //element prefix
	const OPTIONS_NAME = 'footer_credits_options'; 
	const SIDEBAR_ID = 'last-footer';
	const VERSION = '1.5';
    private static $version;
	protected static $options  = array();
	protected static $defaults  = array(
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
			'updated' => ''),
		'nav_menu' => 0,
		'separator' => '&nbsp;&middot;&nbsp;',
		'center' => true,
		'two_lines' => true,
		'show_copyright' => true,
		'show_telephone' => true,
		'show_address' => true,
		'show_return' => true,
		'return_text' => 'Return To Top',
		'return_class' => '',
		'footer_class' => '',			
		'footer_hook' => '',
		'footer_remove' => true,
 		'footer_filter_hook' => '',
 		'enable_html5' => false
	);

    private static function get_version(){
		return self::$version;
	}
	
	static function init() {
		self::$version = self::VERSION;
		self::theme_specific_defaults();
		add_action('widgets_init',array(self::CLASSNAME,'register'),20);		
		add_filter( 'wp_nav_menu_items', array(self::CLASSNAME, 'fix_home_link'), 10, 2 );
		if (!is_admin()) add_action('wp',array(self::CLASSNAME,'prepare'));	
	}

	static function register() {
		self::register_sidebars();
		self::register_widgets();
	}

	static function prepare() {
		if (!is_admin()) {
			add_shortcode(self::CODE.'-copyright', array(self::CLASSNAME, 'copyright_owner' ) );
			add_shortcode(self::CODE.'-menu', array(self::CLASSNAME, 'footer_menu' ) );
			add_shortcode(self::CODE, array(self::CLASSNAME, 'footer' ) );

			add_filter('widget_text', 'do_shortcode', 11);		

			add_action('wp_enqueue_scripts',array(self::CLASSNAME, 'add_styles' ));
			
			//insert custom footer at specified hook
			if ($footer_hook = self::get_option('footer_hook'))  {
				if (self::get_option('footer_remove')) remove_all_actions( $footer_hook); 
				add_action( $footer_hook, array(self::CLASSNAME, 'custom_footer')); 
			}
	
 			//suppress footer output
 			if ($ffs = self::get_option('footer_filter_hook')) 
 				add_filter($ffs, array(self::CLASSNAME, 'no_footer'),100); 

			if (is_page('terms') || is_page('privacy') || is_page('affiliates') || is_page('disclaimer'))
				add_filter('the_content', array(self::CLASSNAME, 'terms_filter') );	
		}
	}

    static function register_sidebars() {
    	if (self::get_option('footer_hook')) {
			$tag = self::get_option('enable_html5') ? 'section' : 'div';
			register_sidebar( array(
				'id' => self::SIDEBAR_ID,
				'name'	=> __( 'Credibility Footer', self::CLASSNAME ),
				'description' => __( 'Custom footer section for copyright, trademarks, etc', self::CLASSNAME),
				'before_widget' => '<'.$tag.' id="%1$s" class="widget %2$s"><div class="widget-wrap">',
				'after_widget'  => '</div></'.$tag.'>'				
			) );
		}
    }
	
	static function register_widgets() {
		register_widget( 'Footer_Putter_Copyright_Widget' );
		register_widget( 'Footer_Putter_TradeMark_Widget' );
	}	
	
	
	static function add_styles() {
		wp_enqueue_style(self::CLASSNAME, plugins_url('styles/footer-credits.css',dirname(__FILE__)), array(), self::get_version());
    }

	static function fix_home_link( $content, $args) {
		$class =  is_front_page()? ' class="current_page_item"' : '';
		$home_linktexts = array('Home','<span>Home</span>');
		foreach ($home_linktexts as $home_linktext) {
			$home_link = sprintf('<a>%1$s</a>',$home_linktext);
			if (strpos($content, $home_link) !== FALSE) 
				$content = str_replace ($home_link,sprintf('<a href="%1$s"%2$s>%3$s</a>',home_url(),$class,$home_linktext),$content); 
		} 
		return $content;
	}

	static function sanitize_terms($new_terms) {
		$new_terms = wp_parse_args($new_terms, self::$defaults['terms']); //ensure terms are complete		
		$new_terms['site'] = self::get_default_site();
		$new_terms['copyright'] = self::get_copyright($new_terms['copyright_start_year']); //generate copyright
		return $new_terms;
	}

	static function save($new_options) {
		$options = self::get_options(false);
		$new_options = wp_parse_args( $new_options, $options);
		$new_options['terms'] = self::sanitize_terms($new_options['terms']);
		$updated = update_option(self::OPTIONS_NAME,$new_options);
		if ($updated) self::get_options(false);
		return $updated;
	}	

	static function get_options ($cache = true) {
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
	
	static function get_option($option_name) {
    	$options = self::get_options();
    	if ($option_name && $options && array_key_exists($option_name,$options))
        	return $options[$option_name];
    	else
        	return false;
    }
    
 	static function get_terms() {
    	return self::get_option('terms');
    }   
	
	static function get_term($term_name) {
    	$options = self::get_options();
    	$terms = is_array($options) && array_key_exists('terms',$options) ? $options['terms'] : false;
    	if ($term_name && $terms && array_key_exists($term_name,$terms) && $terms[$term_name])
        	return $terms[$term_name];
    	else
        	return self::get_default_term($term_name);    		
    }	
	
    static function get_default_term($key) {
		$default='';
    	switch ($key) {
   			case 'owner' : $default = self::get_term('site'); break;
   			case 'copyright' : $default = self::get_copyright(self::get_term('copyright_start_year')); break;
   			case 'copyright_start_year': $default = date('Y'); break;
   			case 'country' : $default = 'The United States'; break;
   			case 'courts' : $default = ucwords(sprintf('the courts of %1$s',self::get_option('country'))); break;
   			case 'email' : $default = 'privacy@'.strtolower(self::get_term('site')); break;
   			case 'site' : $default = self::get_default_site(); break;
   			case 'updated' : $default = date('d M Y'); break;
 			default: $default='';  //default is blank for others
   		}
   		return $default;
    }
	
	static function get_default_site() { 
		$domain = strtolower(parse_url(site_url(),PHP_URL_HOST));
		$p = strpos($domain,'www.') ;
		if (($p !== FALSE) && ($p == 0)) $domain = substr($domain,4);
		return $domain; 
	}
	
	static function get_copyright($startyear){
  		$thisyear = date("Y");
		if(empty( $startyear)) $startyear = $thisyear;
  		return sprintf('Copyright &copy; %1$s%2$s', $startyear, $thisyear == $startyear ? '' : ("-".$thisyear));
	}

	static function copyright_owner($attr){
		$defaults['owner'] = self::get_term('owner');
		$defaults['copyright_start_year'] = self::get_term('copyright_start_year');		
  		$params = shortcode_atts( $defaults, $attr ); //apply plugin defaults  		
  		return sprintf('<span class="copyright">%1$s %2$s</span>', 
  			self::get_copyright($params['copyright_start_year']), $params['owner']);
	}	
	
    static function format_address ($address, $separator) {
		$s='';
		$addlines = explode(',', trim($address));
		foreach ($addlines as $a) {
			$a = trim($a);
			if (!empty($a)) $s .= $a . $separator;
		}
		return $s;
    }	
	
	static function footer_menu($menu) {
        return self::filter_links(wp_nav_menu(array('menu' => $menu, 'echo' => false, 'container' => false)));
	}

	static function return_to_top( $text, $class) {
		return sprintf( '<div class="%1$s"><a rel="nofollow" href="#" onclick="window.scrollTo(0,0); return false;" >%2$s</a></div>', trim($class), $text);
	}


	static function footer($atts = array()) {
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
		$address = self::get_term('address');
		return (empty($params['show_return']) ? '' :
			self::return_to_top($params['return_text'], $params['return_class'])) . 
			sprintf('<div id="%1$s" class="%2$s">%3$s%4$s%5$s%6$s</div>%7$s<!-- end #%1$s -->', 
				self::CODE,
				$params['footer_class'], 	
				(empty($params['nav_menu']) ? '' : self::footer_menu($params['nav_menu'])), 
				(empty($params['show_copyright']) ? '' : sprintf('%1$s%2$s', $section_separator, $copyright)),
				((empty($address) || empty($params['show_address'])) ? '' : sprintf('%1$s<span class="address">%2$s%3$s</span>', $item_separator, self::format_address($address, $params['separator']), self::get_term('country')) ),
				((empty($telephone) || empty($params['show_telephone'])) ? '' : sprintf('%1$s<span class="telephone">%2$s</span>', $section_separator, $telephone) ),
				$clear				
			);				
	}

	static function terms_filter($content) {
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

	static function custom_footer() {
		if ( is_active_sidebar( self::SIDEBAR_ID) ) {
			if (self::get_option('enable_html5')) {
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

    static function no_footer($content) { return ''; }
         
    static function add_footer_filter() {
 		add_filter('wp_list_bookmarks', array(self::CLASSNAME,'filter_links'),20); //nofollow links in custom footer widgets
    }    
         
    static function filter_links( $content) {
		return preg_replace_callback( '/<a([^>]*)>(.*?)<\/a[^>]*>/is', array( self::CLASSNAME, 'nofollow_link' ), $content );
    }		

    static function nofollow_link($matches) { //make link nofollow
		$attrs = shortcode_parse_atts( stripslashes ($matches[ 1 ]) );
		if (isset($attrs['rel'])) return $matches[ 0 ];  //skip if already has a rel attribute
		$atts='';
		foreach ( $attrs AS $key => $value ) $atts .= sprintf('%1$s="%2$s" ', $key, $value);
		$atts = substr( $atts, 0, -1 );
		return sprintf('<a rel="nofollow" %1$s>%2$s</a>', $atts, $matches[ 2 ]);
	}

	static function is_terms_key($key) {
		return array_key_exists($key, self::$defaults['terms']);
	}
	
	static function theme_specific_defaults() {
		switch (basename( TEMPLATEPATH ) ) {  
			case 'twentyten': 
				self::$defaults['footer_hook'] = 'twentyten_credits'; break;
			case 'twentyeleven': 
				self::$defaults['footer_hook'] = 'twentyeleven_credits'; break;
			case 'twentytwelve': 
				self::$defaults['footer_hook'] = 'twentytwelve_credits'; break;
			case 'delicate': 
				self::$defaults['footer_hook'] = 'get_footer'; break;
			case 'genesis': 
				self::$defaults['footer_hook'] = 'genesis_footer';
				self::$defaults['footer_filter_hook'] = 'genesis_footer_output';
				self::$defaults['enable_html5'] = function_exists('genesis_html5') && genesis_html5();
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
}

class Footer_Putter_Copyright_Widget extends WP_Widget {

	const DOMAIN = 'FooterCredits';

	function __construct() {
		$widget_ops = array( 'description' => __( "A widget displaying menu links, copyright and company details" ) );
		parent::__construct('footer_copyright', __('Footer Copyright Widget'), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		extract( $args );		
		$footer_args=array();
		echo $before_widget;
		echo FooterCredits::footer($instance);
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['nav_menu'] = !empty($new_instance['nav_menu']) ? $new_instance['nav_menu'] : 0;
		$instance['show_copyright'] = !empty($new_instance['show_copyright']) ? 1 : 0;
		$instance['show_telephone'] = !empty($new_instance['show_telephone']) ? 1 : 0;	
		$instance['show_address'] = !empty($new_instance['show_address']) ? 1 : 0;	
		$instance['center'] = !empty($new_instance['center']) ? 1 : 0;
		$instance['two_lines'] = !empty($new_instance['two_lines']) ? 1 : 0;	
		$instance['show_return'] = !empty($new_instance['show_return']) ? 1 : 0;
		$instance['return_class'] = trim($new_instance['return_class']);
		$instance['footer_class'] = trim($new_instance['footer_class']);
		return $instance;
	}

	function form( $instance ) {
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
		if ( !$menus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.', self::DOMAIN ), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		$instance = wp_parse_args( (array) $instance, 
			array( 'nav_menu' => 0, 'center' => true, 'two_lines' => true,  'show_copyright' => true, 'show_address' => true, 'show_telephone' => true, 'show_return' => true ) );
		$nav_menu = isset( $instance['nav_menu'] ) ? (int) $instance['nav_menu'] : 0;
		$center = isset( $instance['center'] ) ? (bool) $instance['center'] : false;
		$two_lines = isset( $instance['two_lines'] ) ? (bool) $instance['two_lines'] : false;
		$show_copyright = isset( $instance['show_copyright'] ) ? (bool) $instance['show_copyright'] : false;
		$show_address = isset( $instance['show_address'] ) ? (bool) $instance['show_address'] : false;
		$show_telephone = isset( $instance['show_telephone'] ) ? (bool) $instance['show_telephone'] : false;
		$show_return = isset( $instance['show_return'] ) ?  (bool) $instance['show_return'] : false;
		$return_class = isset( $instance['return_class'] ) ? $instance['return_class'] : '';		
		$footer_class = isset( $instance['footer_class'] ) ? $instance['footer_class'] : '';	
		?>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Footer Menu:', self::DOMAIN ); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
		<?php 
			$selected = empty($nav_menu) ? ' selected="selected"' : '';
			echo ('<option'.$selected.' value="0">Do not show a menu</option>');
			foreach ( $menus as $menu ) {
				$selected = $nav_menu == $menu->term_id ? ' selected="selected"' : '';
				echo '<option'. $selected .' value="'. $menu->term_id .'">'. $menu->name .'</option>';
			}
		?>
			</select>
		</p>
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('center', self::DOMAIN ); ?>" name="<?php echo $this->get_field_name('center'); ?>"<?php checked( $center ); ?> />
		<label for="<?php echo $this->get_field_id('center'); ?>"><?php _e( 'Center Menu',self::DOMAIN ); ?></label><br/>
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('two_lines', self::DOMAIN ); ?>" name="<?php echo $this->get_field_name('two_lines'); ?>"<?php checked( $two_lines ); ?> />
		<label for="<?php echo $this->get_field_id('two_lines'); ?>"><?php _e( 'Spread Over Two Lines',self::DOMAIN ); ?></label><br/>
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_copyright', self::DOMAIN ); ?>" name="<?php echo $this->get_field_name('show_copyright'); ?>"<?php checked( $show_copyright ); ?> />
		<label for="<?php echo $this->get_field_id('show_copyright'); ?>"><?php _e( 'Show Copyright' ); ?></label><br />
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_address', self::DOMAIN ); ?>" name="<?php echo $this->get_field_name('show_address'); ?>"<?php checked( $show_address ); ?> />
		<label for="<?php echo $this->get_field_id('show_address'); ?>"><?php _e( 'Show Address', self::DOMAIN  ); ?></label><br />
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_telephone', self::DOMAIN ); ?>" name="<?php echo $this->get_field_name('show_telephone'); ?>"<?php checked( $show_telephone ); ?> />
		<label for="<?php echo $this->get_field_id('show_telephone'); ?>"><?php _e( 'Show Telephone number', self::DOMAIN  ); ?></label><br />
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_return', self::DOMAIN ); ?>" name="<?php echo $this->get_field_name('show_return'); ?>"<?php checked( $show_return ); ?> />
		<label for="<?php echo $this->get_field_id('show_return'); ?>"><?php _e( 'Show Return To Top Link' ); ?></label><br />
		<h4>Custom Classes (Optional)</h4>
		<p>Add any custom CSS classes you want apply to the footer section content to change the font color and size.</p>
		<p>For your convenience we have defined 3 classes <i>dark</i>, <i>light</i> and <i>white</i> but feel free
		to define and use your own custom CSS classes.</p>
		<label for="<?php echo $this->get_field_id('return_class'); ?>"><?php _e( 'Return To Top:', self::DOMAIN ); ?></label>
		<input id="<?php echo $this->get_field_id('return_class'); ?>" name="<?php echo $this->get_field_name('return_class'); ?>" type="text" value="<?php echo $return_class; ?>" size="10" /><br/>
		<label for="<?php echo $this->get_field_id('footer_class'); ?>"><?php _e( 'Footer Credits:', self::DOMAIN ); ?></label>
		<input id="<?php echo $this->get_field_id('footer_class'); ?>" name="<?php echo $this->get_field_name('footer_class'); ?>" type="text" value="<?php echo $footer_class; ?>" size="10" />
<?php
	}
}