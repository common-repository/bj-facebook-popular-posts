<?php
/*
Plugin Name: BJ Facebook Popular Posts
Plugin URI: http://wordpress.org/extend/plugins/bj-facebook-popular-posts/
Description: Finds the number of shares for each of your posts. Includes widget for displaying most popular posts.
Version: 0.2.2
Author: Bjørn Johansen
Author URI: http://twitter.com/bjornjohansen
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2012  Bjørn Johansen  (email : post@bjornjohansen.no)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// Inspired by http://www.metronet.no/sorting-posts-by-facebook-likes-in-wordpress/

include_once( 'bj-fbpp-widget.php' );


class BJ_FBPP {
	function get_fb_shares_call() {

		// Disable all output buffers so we can flush output to avoid timeouts
		while ( $ob_handlers = ob_list_handlers() ) {
			ob_end_clean();
		}
		
		// Insert the meta data field on all posts so we can sort on it later
		// There MUST be a more effective way to do this. Plz help!
		$args = array( 'posts_per_page' => '-1', 'post_type' => 'any' );
		$the_query = new WP_Query( $args );
		while ( $the_query->have_posts() ) {
			set_time_limit( 10 ); // make sure we don't timeout due to many posts
			
			$the_query->the_post();
			
			$last_checked = get_post_meta( get_the_ID(), '_bj_fb_last_checked', true );
			
			if ( ! strlen( $last_checked ) ) {
				update_post_meta( get_the_ID(), '_bj_fb_last_checked', '1970-01-01 00:00:00' );
			}

		}
		
		$bj_fb_num_posts = -1;
		$bj_fb_num_posts = apply_filters( 'bj_fb_num_posts', $bj_fb_num_posts );
		$bj_fb_post_type = 'any';
		$bj_fb_post_type = apply_filters( 'bj_fb_post_type', $bj_fb_post_type );
		
		
		// Loop Through the Posts
		$args = array( 'posts_per_page' => $bj_fb_num_posts, 'post_type' => $bj_fb_post_type, 'order' => 'ASC', 'orderby' => 'meta_value', 'meta_key' => '_bj_fb_last_checked' );
		$the_query = new WP_Query( $args );
		
		while ( $the_query->have_posts() ) {
			set_time_limit( 10 ); // make sure we don't timeout due to many posts
			
			$the_query->the_post();

			// Get Facebook Likes From FB Graph API
			/*
			$data = file_get_contents( 'http://graph.facebook.com/?id=' . get_permalink() );
			$obj = json_decode( $data );
			$num_shares = intval( $obj->{'shares'} );
			*/
			// Since version 0.2.0, use FQL instead:
			$fql  = sprintf( "SELECT url, normalized_url, share_count, like_count, comment_count, total_count, commentsbox_count, comments_fbid, click_count FROM link_stat WHERE url = '%s'", get_permalink() );
			$apifql = 'https://api.facebook.com/method/fql.query?format=json&query=' . urlencode( $fql );
			$json = file_get_contents( $apifql );
			$res = json_decode( $json );
			$num_shares = $res[0]->total_count; //not really just shares, but shares, likes and comments combined

			// Add Facebook Likes to Post Meta
			update_post_meta( get_the_ID(), '_bj_fb_shares', $num_shares );
			update_post_meta( get_the_ID(), '_bj_fb_last_checked', date('Y-m-d H:i:s') );

			echo sprintf('<p>%d: %s<br>%s<br>%s</p>', get_the_ID(), get_the_title(), get_permalink(), $num_shares );
			flush();
		}
		exit;
	}

	function parse_request() {
		if ( isset( $_GET['bj_get_fb_shares'] ) ) {
			BJ_FBPP::get_fb_shares_call();
		}
	}

	// load the url that triggers the exec
	function get_fb_shares_trigger_exec_call() {
		
		// send a non-blocking request
		$url = site_url( '/?bj_get_fb_shares' );
		
		$response = wp_remote_get( $url, array(
			'method' => 'GET',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => false,
			'headers' => array( 'Host' => parse_url( $url, PHP_URL_HOST ) ),
			'body' => array( 'bj_get_fb_shares' => 'yup' ),
			'cookies' => array()
			)
		);
		
	}
	
	// Schedule the cron job
	function get_fb_shares_cron() {
		if ( ! wp_next_scheduled( 'bj_get_fb_shares_action' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'bj_get_fb_shares_action' );
		}
	}
	
	
	// Add the column shown on the manage posts/pages screen
	function add_fb_shares_column( $columns ) {
		return array_merge( $columns, 
				  array( 'fb_shares' => __('FB Shares', 'bj-facebook-popular-posts')) );
	}
	function custom_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'fb_shares':
				echo intval( get_post_meta( $post_id , '_bj_fb_shares' , true ) ); 
				//echo sprintf( ' (%s)', get_post_meta( $post_id, '_bj_fb_last_checked', true ) );
				break;
			}
	}
	// Register the column as sortable
	function sort_column_register_sortable( $columns ) {
		$columns['fb_shares'] = array('fb_shares', 1);
		return $columns;
	}
	
	// Add the sorting SQL for the FB Shares column
	function fb_shares_column_orderby( $orderby, $wp_query ) {
		global $wpdb;

		$wp_query->query = wp_parse_args($wp_query->query);

		if ( 'fb_shares' == @$wp_query->query['orderby'] ) {
			$orderby = "((SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = '_bj_fb_shares')+0) " . $wp_query->get('order');
		}

		return $orderby;

	}
}

// Add the column shown on the manage posts/pages screen
add_filter( 'manage_posts_columns' , array( 'BJ_FBPP', 'add_fb_shares_column' ) );
add_action( 'manage_posts_custom_column' , array( 'BJ_FBPP', 'custom_columns' ), 10, 2 );
add_filter( 'manage_pages_columns' , array( 'BJ_FBPP', 'add_fb_shares_column' ) );
add_action( 'manage_pages_custom_column' , array( 'BJ_FBPP', 'custom_columns' ), 10, 2 );

// Register the column as sortable
add_filter( 'manage_edit-post_sortable_columns', array( 'BJ_FBPP', 'sort_column_register_sortable' ) );
add_filter( 'manage_edit-page_sortable_columns', array( 'BJ_FBPP', 'sort_column_register_sortable' ) );

// Add the sorting SQL for the FB Shares column
add_filter( 'posts_orderby', array( 'BJ_FBPP', 'fb_shares_column_orderby' ), 10, 2 );



// Schedule the the cron job
add_action( 'wp', array( 'BJ_FBPP', 'get_fb_shares_cron') );

// The cron event
add_action( 'bj_get_fb_shares_action', array( 'BJ_FBPP', 'get_fb_shares_trigger_exec_call' ) );

// Parse the request to see if we should do anything
add_action( 'parse_request', array( 'BJ_FBPP', 'parse_request' ) );

// Get FB Shares on plugin activation
register_activation_hook( __FILE__, array( 'BJ_FBPP', 'get_fb_shares_trigger_exec_call' ) );
