<?php
class Footer_Putter_TradeMark_Widget extends WP_Widget {

    private	$defaults = array( 
    	'category' => false, 'orderby' => 'name', 'limit' => '',  'visibility' => '');
		
	function __construct() {
		$widget_ops = array('description' => __( 'Trademarks, Service Marks and Kitemarks', GENESIS_CLUB_DOMAIN) );
		parent::__construct('footer_trademarks', __('TradeMarks Widget', GENESIS_CLUB_DOMAIN), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		if (FooterCredits::hide_widget($instance)) return; //check visibility requirements

		$category = isset($instance['category']) ? $instance['category'] : false;
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'name';
		$order = $orderby == 'rating' ? 'DESC' : 'ASC';
		$limit = (isset( $instance['limit'] ) && $instance['limit']) ? $instance['limit'] : -1;

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
			'name' => __( 'Link title', GENESIS_CLUB_DOMAIN),
			'rating' => __( 'Link rating', GENESIS_CLUB_DOMAIN),
			'id' => __( 'Link ID', GENESIS_CLUB_DOMAIN),
			'rand' => __( 'Random', GENESIS_CLUB_DOMAIN)
		));
		$this->print_form_field($instance, 'limit', 'Number of links to show', 'text', array(),  array('size' => 3 ,'maxlength' => 3));
		$this->print_form_field($instance, 'visibility', '<h4>Widget Visibility</h4>', 'radio', 
			FooterCredits::get_visibility_options(), array('separator' => '<br/>'));
	}
	
	function print_form_field($instance, $fld, $label, $type, $options = array(), $args = array()) {
		$value = array_key_exists($fld,$instance) ? $instance[$fld] : false;
		print FooterCredits::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), $label, $value, $type, $options, $args);
	}
}