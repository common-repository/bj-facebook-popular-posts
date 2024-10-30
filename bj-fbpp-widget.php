<?php

class BJ_FBPP_Widget extends WP_Widget {

	function BJ_FBPP_Widget() {
		parent::WP_Widget( false, $name = __( 'Popular Posts', 'bj-facebook-popular-posts') );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		if ( ! isset( $instance['title'] ) ) {
			$instance['title'] = '';
		}

		if ( ! isset( $instance['num_posts'] ) ) {
			$instance['num_posts'] = 5;
		}

		if ( ! isset( $instance['selected_post_types'] ) || ! is_array( $instance['selected_post_types'] ) || ! count( $instance['selected_post_types'] ) ) {
			$post_types = array( 'any' );
		} else {
			$post_types = $instance['selected_post_types'];
		}
		
		$num_posts = intval( $instance['num_posts'] ); 
		if ( ! $num_posts ) {
			$num_posts = 5;
		}


		echo $before_widget;
		echo $before_title . $instance['title'] . $after_title;
		
		$args = array(
			'posts_per_page' => $num_posts,
			'post_type' => $post_types,
			'meta_key' => '_bj_fb_shares',
			'orderby' => 'meta_value_num',
			'order' => 'DESC'
		);
		$query = new WP_Query( $args );

		if ( file_exists( ( $tmpl = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'bj-fbpp-widget.tmpl.php' )) ) { // child theme
			include( $tmpl );
		} elseif ( file_exists( ( $tmpl = get_template_directory() . DIRECTORY_SEPARATOR . 'bj-fbpp-widget.tmpl.php' )) ) { // parent theme
			include( $tmpl );
		} else {
			include( 'bj-fbpp-widget.tmpl.php' );
		}
		
		wp_reset_postdata();
		
		echo $after_widget;
	
	}
	
	function update($new_instance, $old_instance) {
		return $new_instance;
	}
	
	function form( $instance ) {

		if ( ! isset( $instance['title'] ) ) {
			$instance['title'] = '';
		}
		if ( ! isset( $instance['num_posts'] ) ) {
			$instance['num_posts'] = '5';
		}
		if ( ! isset( $instance['post_types'] ) || 'select' != $instance['post_types'] ) {
			$instance['post_types'] = 'any';
		}

		if ( ! isset( $instance['selected_post_types'] ) ) {
			$instance['selected_post_types'] = array();
		} 

		$available_post_types = get_post_types( array( 'public' => true ) );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title', 'bj-facebook-popular-posts' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('num_posts'); ?>">
				<?php _e( 'Number of posts', 'bj-facebook-popular-posts' ); ?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'num_posts' ); ?>" name="<?php echo $this->get_field_name( 'num_posts' ); ?>" type="text" value="<?php echo esc_attr( ( intval( $instance['num_posts'] ) ? intval($instance['num_posts']) : 5 ) ); ?>" />
			</label>
		</p>
		<p>
			<div><?php _e( 'Post types', 'bj-facebook-popular-posts' ); ?>:</div>
			<label for="<?php echo $this->get_field_id( 'post_types_any' ); ?>"><input onchange="var wrap=document.getElementById('<?php echo $this->get_field_id( 'select_post_types_wrapper' ); ?>');if ( this.checked ) { wrap.style.display='none'; } else { wrap.style.display='block'; }" type="radio" value="any" name="<?php echo $this->get_field_name( 'post_types' ); ?>" id="<?php echo $this->get_field_id( 'post_types_any' ); ?>" class="<?php echo $this->get_field_id( 'post_types_radio' ); ?>" <?php checked( $instance['post_types'], 'any' ); ?>> <?php _e( 'Any', 'bj-facebook-popular-posts' ); ?></label><br>
			<label for="<?php echo $this->get_field_id( 'post_types_select' ); ?>"><input onchange="var wrap=document.getElementById('<?php echo $this->get_field_id( 'select_post_types_wrapper' ); ?>');if ( this.checked ) { wrap.style.display='block'; } else { wrap.style.display='none'; }" type="radio" value="select" name="<?php echo $this->get_field_name( 'post_types' ); ?>" id="<?php echo $this->get_field_id( 'post_types_select' ); ?>" class="<?php echo $this->get_field_id( 'post_types_radio' ); ?>"<?php checked( $instance['post_types'], 'select' ); ?>> <?php _e( 'Select', 'bj-facebook-popular-posts' ); ?></label>
		</p>
		<div id="<?php echo $this->get_field_id( 'select_post_types_wrapper' ); ?>" <?php if ( 'select' != $instance['post_types'] ): ?> style="display: none"<?php endif; ?>>
			<?php foreach ( $available_post_types as $post_type ) : ?>
				<div><label><input type="checkbox" value="<?php echo esc_attr( $post_type ); ?>" name="<?php echo $this->get_field_name( 'selected_post_types' ); ?>[]" <?php checked( true, in_array( $post_type, $instance['selected_post_types'] ) ); ?>> <?php echo $post_type; ?></label></div>
			<?php endforeach; ?>
		</div>
		<!--script>
			(function($){
				$( '.<?php echo $this->get_field_id( 'post_types_radio' ); ?>' ).change(function(){
					var $checked = $('.<?php echo $this->get_field_id( 'post_types_radio' ); ?>:checked'),
						$wrapper = $('#<?php echo $this->get_field_id( 'select_post_types_wrapper' ); ?>');

					if ( 'select' == $checked.val() ) {
						$wrapper.slideDown(100);
					} else {
						$wrapper.slideUp(100);
					}
				});
			})(jQuery);
		</script-->
		<?php
	}
}

add_action( 'widgets_init', create_function('', 'return register_widget("BJ_FBPP_Widget");') );

