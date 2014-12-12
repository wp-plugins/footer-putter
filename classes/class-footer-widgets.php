<?php
class Footer_Putter_Copyright_Widget extends WP_Widget {

	private $instance;
	private $tooltips;	
    private	$defaults = array( 
    	'nav_menu' => 0, 'center' => true, 'two_lines' => true,  
		'show_copyright' => true, 'show_address' => true, 'show_telephone' => true, 'show_email' => false,
		'show_return' => true, 'return_class' => '', 'footer_class' => '', 'visibility' => '');

	private $tips = array(
		'nav_menu' => array('heading' => 'Footer Menu', 'tip' => 'Choose the menu to display in the footer'),
		'center' => array('heading' => 'Center Menu', 'tip' => 'Center the footer horizontally'),
		'two_lines' => array('heading' => 'Spread Over Two Lines', 'tip' => 'Place the menu and copyright on one line and the contact details on the other'),
		'show_copyright' => array('heading' => 'Show Copyright', 'tip' => 'Show copyright holder an year range'),
		'show_address' => array('heading' => 'Show Address', 'tip' => 'Show contact address'),
		'show_telephone' => array('heading' => 'Show Telephone Number', 'tip' => 'Show telephone number(s)'),
		'show_email' => array('heading' => 'Show Email Address', 'tip' => 'Show email'),
		'use_microdata' => array('heading' => 'Use HTML5 Microdata', 'tip' => 'Express organization, contact and any geo-coordinates using HTML5 microdata'),
		'show_return' => array('heading' => 'Show Return To Top Links', 'tip' => 'Show link to return to the top of the page'),
		'return_class' => array('heading' => 'Return To Top', 'tip' => 'Add custom classes to apply to the return to top links'),
		'footer_class' => array('heading' => 'Footer Credits', 'tip' => 'Add custom classes to apply to the footer menu, copyright and contact information'),
		'visibility' => array('heading' => 'Widget Visibility', 'tip' => 'Determine on which pages the footer widget is displayed'),
	);

	function get_tips() {
		return $this->tips;
	}
	
	function get_defaults() {
		return $this->defaults;
	}

	function __construct() {
		$widget_ops = array( 'description' => __( "A widget displaying menu links, copyright and company details" ) );
		parent::__construct('footer_copyright', __('Footer Copyright Widget'), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		extract( $args );		
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		if (Footer_Putter_Utils::hide_widget($instance['visibility'])) return; //check visibility requirements

		if ($footer = Footer_Credits::footer($instance)) 
			printf ('%1$s%2$s%3$s', $before_widget, $footer, $after_widget);
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( (array) $old_instance, $this->defaults );
		$instance['nav_menu'] = !empty($new_instance['nav_menu']) ? $new_instance['nav_menu'] : 0;
		$instance['show_copyright'] = !empty($new_instance['show_copyright']) ? 1 : 0;
		$instance['show_telephone'] = !empty($new_instance['show_telephone']) ? 1 : 0;	
		$instance['show_email'] = !empty($new_instance['show_email']) ? 1 : 0;	
		$instance['show_address'] = !empty($new_instance['show_address']) ? 1 : 0;	
		$instance['use_microdata'] = !empty($new_instance['use_microdata']);
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
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.' ), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		$menus = array();
		$menus[0] = 'No menu';
		foreach ( $menu_terms as $term ) $menus[ $term->term_id ] = $term->name;
		
		$this->instance = wp_parse_args( (array) $instance, $this->get_defaults() );
		$this->tooltips = new Footer_Putter_Tooltip($this->get_tips());

		$this->print_form_field('nav_menu',  'select', $menus);
		$this->print_form_field('center',  'checkbox');
		$this->print_form_field('two_lines', 'checkbox');
		$this->print_form_field('show_copyright', 'checkbox');
		$this->print_form_field('show_address', 'checkbox');
		$this->print_form_field('show_telephone', 'checkbox');
		$this->print_form_field('show_email', 'checkbox');
		$this->print_form_field('show_return', 'checkbox');
		if (Footer_Putter_Utils::is_html5()) $this->print_form_field('use_microdata', 'checkbox');

		print <<< CUSTOM_CLASSES
<h4>Custom Classes (Optional)</h4>
<p>Add any custom CSS classes you want apply to the footer section content to change the font color and size.</p>
<p>For your convenience we have defined 3 color classes <i>dark</i>, <i>light</i> and <i>white</i>, and 2 size classes, 
<i>small</i> and <i>tiny</i>. Feel free to use these alongside your own custom CSS classes.</p>
CUSTOM_CLASSES;

		$this->print_form_field('return_class', 'text', array(), array('size' => 10));
		$this->print_form_field('footer_class', 'text', array(), array('size' => 10));
		$this->print_form_field('visibility',  'radio', Footer_Putter_Utils::get_visibility_options(), array('separator' => '<br />'));
	}

	function print_form_field($fld, $type, $options = array(), $args = array()) {
		$value = array_key_exists($fld,$this->instance) ? $this->instance[$fld] : false;
		print Footer_Putter_Utils::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), $this->tooltips->tip($fld), $value, $type, $options, $args, 'br');
	}
}

class Footer_Putter_Trademark_Widget extends WP_Widget {

	private $instance;
	private $tooltips;

	private $tips = array(
			'title' => array('heading' => 'Title', 'tip' => 'Widget Title'),
			'category' => array('heading' => 'Category', 'tip' => 'Select Link Category for Your Trademarks'),
			'limit' => array('heading' => 'Number of links', 'tip' => 'Number of trademarks to show'),
			'orderby' => array('heading' => 'Order By', 'tip' => 'Sort by name, rating, ID or random'),
			'nofollow' => array('heading' => 'Make Links Nofollow', 'tip' => 'Mark the links with rel=nofollow'),
			'visibility' => array('heading' => 'Widget Visibility', 'tip' => 'Determine on which pages the footer widget is displayed'),
			);

    private	$defaults = array( 'title' => '',
    	'category' => false, 'limit' => '', 'orderby' => 'name', 'nofollow' => false, 'visibility' => '');

	function get_tips() {
		return $this->tips;
	}
	
	function get_defaults() {
		return $this->defaults;
	}
		
	function __construct() {
		add_filter('pre_option_link_manager_enabled', '__return_true' );
		$widget_ops = array('description' => __( 'Trademarks, Service Marks and Kitemarks') );
		parent::__construct('footer_trademarks', __('Trademarks Widget'), $widget_ops);
	}

    function nofollow_links( $content) {
	    return preg_replace_callback( '/<a([^>]*)>(.*?)<\/a[^>]*>/is', array( &$this, 'nofollow_link' ), $content ) ;
    }

    function nofollow_link($matches) { //make link nofollow
		$attrs = shortcode_parse_atts( stripslashes ($matches[ 1 ]) );
		$atts='';
        $rel = ' rel="nofollow"';
		foreach ( $attrs AS $key => $value ) {
			$key = strtolower($key);
            $nofollow = '';
			if ('rel' == $key) {
              $rel = '';
              if (strpos($value, 'follow') === FALSE) $nofollow = ' nofollow';
            }
            $atts .= sprintf(' %1$s="%2$s%3$s"', $key, $value, $nofollow);
		}
		return sprintf('<a%1$s%2$s>%3$s</a>', $rel, $atts, $matches[ 2 ]);
	}

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		if (Footer_Putter_Utils::hide_widget($instance['visibility'])) return; //check visibility requirements

		$title = apply_filters('widget_title', $instance['title'] );
		$category = isset($instance['category']) ? $instance['category'] : false;
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'name';
		$order = $orderby == 'rating' ? 'DESC' : 'ASC';
		$limit = (isset( $instance['limit'] ) && $instance['limit']) ? $instance['limit'] : -1;
		$nofollow = isset( $instance['nofollow'] ) && $instance['nofollow'];

		$links = wp_list_bookmarks(apply_filters('widget_links_args', array(
			'echo' => 0,
			'title_before' => $before_title, 'title_after' => $after_title,
			'title_li' => '', 'categorize' => false,
			'before' => '', 'after' => '',
			'category_before' => '', 'category_after' => '',
			'show_images' => true, 'show_description' => false,
			'show_name' => false, 'show_rating' => false,
			'category' => $category, 'class' => 'trademark widget',
			'orderby' => $orderby, 'order' => $order,
			'limit' => $limit,
		)));
		echo $before_widget;
 		if ($title) echo $before_title . $title . $after_title;
        if ($nofollow)
		    echo $this->nofollow_links($links);
        else
            echo $links;
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;
		$instance = wp_parse_args( (array) $old_instance, $this->defaults );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['orderby'] = 'name';
		if ( in_array( $new_instance['orderby'], array( 'name', 'rating', 'id', 'rand' ) ) )
			$instance['orderby'] = $new_instance['orderby'];
		$instance['category'] = intval( $new_instance['category'] );
		$instance['limit'] = ! empty( $new_instance['limit'] ) ? intval( $new_instance['limit'] ) : '';
		$instance['nofollow'] = !empty($new_instance['nofollow']);
		$instance['visibility'] = trim($new_instance['visibility']);
		return $instance;
	}

	function form( $instance ) {
		$this->instance = wp_parse_args( (array) $instance, $this->get_defaults() );
		$this->tooltips = new Footer_Putter_Tooltip($this->get_tips());

		$links = array();
		$link_cats = get_terms( 'link_category' );
		foreach ( $link_cats as $link_cat ) {
			$id = intval($link_cat->term_id);
			$links[$id] = $link_cat->name;
		}
		$this->print_form_field('title', 'text', array(), array('size' => 10));
		$this->print_form_field('category', 'select', $links);
		$this->print_form_field('orderby', 'select', array(
			'name' => __( 'Link title'),
			'rating' => __( 'Link rating'),
			'id' => __( 'Link ID'),
			'rand' => __( 'Random')
		));
		$this->print_form_field('limit',  'text', array(),  array('size' => 3 ,'maxlength' => 3));
		$this->print_form_field('nofollow',  'checkbox');
	    $this->print_form_field('visibility', 'radio',
			Footer_Putter_Utils::get_visibility_options(), array('separator' => '<br />'));
	}

	function print_form_field ($fld, $type, $options = array(), $args = array()) {
		$value = array_key_exists($fld,$this->instance) ? $this->instance[$fld] : false;
		print Footer_Putter_Utils::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), $this->tooltips->tip($fld), $value, $type, $options, $args,'br');
	}
}