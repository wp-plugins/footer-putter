<?php
class Footer_Putter_TradeMark_Widget extends WP_Widget {
	const DOMAIN = 'FooterPutter';
		
	function __construct() {
		$widget_ops = array('description' => __( 'Trademarks, Service Marks and Kitemarks', self::DOMAIN) );
		parent::__construct('footer_trademarks', __('TradeMarks Widget', self::DOMAIN), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);

		$category = isset($instance['category']) ? $instance['category'] : false;
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'name';
		$order = $orderby == 'rating' ? 'DESC' : 'ASC';
		$limit = isset( $instance['limit'] ) ? $instance['limit'] : -1;

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
		echo FooterCredits::filter_links($links);
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;

		$instance['orderby'] = 'name';
		if ( in_array( $new_instance['orderby'], array( 'name', 'rating', 'id', 'rand' ) ) )
			$instance['orderby'] = $new_instance['orderby'];

		$instance['category'] = intval( $new_instance['category'] );
		$instance['limit'] = ! empty( $new_instance['limit'] ) ? intval( $new_instance['limit'] ) : -1;

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'category' => false, 'orderby' => 'name', 'limit' => -1 ) );
		$link_cats = get_terms( 'link_category' );
		if ( ! $limit = intval( $instance['limit'] ) ) { $limit = -1; } ?>
		<p>
		<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e( 'Select Link Category for Your Trademarks:', self::DOMAIN ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
		<?php
		foreach ( $link_cats as $link_cat ) {
			echo '<option value="' . intval($link_cat->term_id) . '"'
				. ( $link_cat->term_id == $instance['category'] ? ' selected="selected"' : '' )
				. '>' . $link_cat->name . "</option>\n";
		}
		?>
		</select>
		<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e( 'Sort by:', self::DOMAIN ); ?></label>
		<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat">
			<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php _e( 'Link title', self::DOMAIN); ?></option>
			<option value="rating"<?php selected( $instance['orderby'], 'rating' ); ?>><?php _e( 'Link rating', self::DOMAIN ); ?></option>
			<option value="id"<?php selected( $instance['orderby'], 'id' ); ?>><?php _e( 'Link ID', self::DOMAIN ); ?></option>
			<option value="rand"<?php selected( $instance['orderby'], 'rand' ); ?>><?php _e( 'Random', self::DOMAIN ); ?></option>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e( 'Number of links to show:', self::DOMAIN ); ?></label>
		<input id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $limit == -1 ? '' : intval( $limit ); ?>" size="3" />
		</p>
<?php
	}
}