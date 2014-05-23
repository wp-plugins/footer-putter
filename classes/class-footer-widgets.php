<?php

if (!class_exists('Footer_Credits_Copyright_Widget')) {
 class Footer_Credits_Copyright_Widget extends WP_Widget {

	const DOMAIN = 'Footer_Credits';

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
		if (Footer_Credits::hide_widget($instance)) return; //check visibility requirements

		if ($footer = Footer_Credits::footer($instance)) 
			printf ('%1$s%2$s%3$s', $before_widget, $footer, $after_widget);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
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
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.', FOOTER_PUTTER_DOMAIN ), admin_url('nav-menus.php') ) .'</p>';
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
		if (Footer_Credits::is_html5()) $this->print_form_field($instance, 'use_microdata', 'Use HTML5 Microdata', 'checkbox');

		print <<< CUSTOM_CLASSES
<h4>Custom Classes (Optional)</h4>
<p>Add any custom CSS classes you want apply to the footer section content to change the font color and size.</p>
<p>For your convenience we have defined 3 color classes <i>dark</i>, <i>light</i> and <i>white</i>, and 2 size classes, 
<i>small</i> and <i>tiny</i>. Feel free to use these alongside your own custom CSS classes.</p>
CUSTOM_CLASSES;

		$this->print_form_field($instance, 'return_class', 'Return To Top', 'text', array(), array('size' => 10));
		$this->print_form_field($instance, 'footer_class', 'Footer Credits', 'text', array(), array('size' => 10));
		$this->print_form_field($instance, 'visibility', '<h4>Widget Visibility</h4>', 'radio',
			Footer_Credits::get_visibility_options(), array('separator' => '<br/>'));
	}

	function print_form_field($instance, $fld, $label, $type, $options = array(), $args = array()) {
		$value = array_key_exists($fld,$instance) ? $instance[$fld] : false;
		print Footer_Credits::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), $label, $value, $type, $options, $args);
	}
 }
}

if (!class_exists('Footer_Credits_Trademark_Widget')) {
 class Footer_Credits_Trademark_Widget extends WP_Widget {

    private	$defaults = array( 'title' => '',
    	'category' => false, 'orderby' => 'name', 'limit' => '',  'visibility' => '', 'nofollow' => false);
		
	function __construct() {
		$widget_ops = array('description' => __( 'Trademarks, Service Marks and Kitemarks', FOOTER_PUTTER_DOMAIN) );
		parent::__construct('footer_trademarks', __('Footer Trademarks Widget', FOOTER_PUTTER_DOMAIN), $widget_ops);
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
		if (Footer_Credits::hide_widget($instance)) return; //check visibility requirements
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
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$links = array();
		$link_cats = get_terms( 'link_category' );
		foreach ( $link_cats as $link_cat ) {
			$id = intval($link_cat->term_id);
			$links[$id] = $link_cat->name;
		}
		$this->print_form_field($instance, 'title', 'Title: ', 'text');
		$this->print_form_field($instance, 'category', 'Select Link Category for Your Trademarks', 'select', $links);
		$this->print_form_field($instance, 'orderby', 'Sort by', 'select', array(
			'name' => __( 'Link title'),
			'rating' => __( 'Link rating'),
			'id' => __( 'Link ID'),
			'rand' => __( 'Random')
		));
		$this->print_form_field($instance, 'limit', 'Number of links to show', 'text', array(),  array('size' => 3 ,'maxlength' => 3));
		$this->print_form_field($instance, 'nofollow', ' NoFollow links', 'checkbox');
	    $this->print_form_field($instance, 'visibility', '<h4>Widget Visibility</h4>', 'radio',
			Footer_Credits::get_visibility_options(), array('separator' => '<br/>'));
	}

	function print_form_field($instance, $fld, $label, $type, $options = array(), $args = array()) {
		$value = array_key_exists($fld,$instance) ? $instance[$fld] : false;
		print Footer_Credits::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), $label, $value, $type, $options, $args);
	}
 }
}
