<?php
class Footer_Putter_TradeMark_Widget extends WP_Widget {

    private	$defaults = array(
    	'category' => false, 'orderby' => 'name', 'limit' => '',  'visibility' => '', 'nofollow' => false);
		
	function __construct() {
		$widget_ops = array('description' => __( 'Trademarks, Service Marks and Kitemarks', GENESIS_CLUB_DOMAIN) );
		parent::__construct('footer_trademarks', __('TradeMarks Widget', GENESIS_CLUB_DOMAIN), $widget_ops);
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
		if (FooterCredits::hide_widget($instance)) return; //check visibility requirements

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
        if ($nofollow)
		    echo $this->nofollow_links($links);
        else
            echo $links;
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;
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
			FooterCredits::get_visibility_options(), array('separator' => '<br/>'));
	}

	function print_form_field($instance, $fld, $label, $type, $options = array(), $args = array()) {
		$value = array_key_exists($fld,$instance) ? $instance[$fld] : false;
		print FooterCredits::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), $label, $value, $type, $options, $args);
	}
}