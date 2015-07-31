<?php
/*
Plugin Name: Time Line
Plugin URI: http://wp-plugins.in/wordpress-timeline-plugin
Description: Make your timeline page easily, one shortcode only and full customize.
Version: 1.0.0
Author: Alobaidi
Author URI: http://wp-plugins.in
License: GPLv2 or later
*/

/*  Copyright 2015 Alobaidi (email: wp-plugins@outlook.com)

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


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


function alobaidi_time_line_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'time-line.php' ) !== false ) {
		
		$new_links = array(
						'<a href="http://wp-plugins.in/wordpress-timeline-plugin" target="_blank">Explanation of Use</a>',
						'<a href="http://wp-plugins.in/buy-time-line-style-extension" target="_blank">Buy Time Line Style Extension</a>',
						'<a href="http://j.mp/ET_WPTime_ref_pl" target="_blank">Elegant Themes</a>',
					);
		
		$links = array_merge( $links, $new_links );
		
	}
	
	return $links;
	
}
add_filter( 'plugin_row_meta', 'alobaidi_time_line_plugin_row_meta', 10, 2 );


function alobaidi_time_line( $atts, $content = null ){
	
	extract(
		shortcode_atts(
			array(
				"year"		=>	"2015",
				"list"		=>	"ul",
				"limit"		=>	"",
				"date"		=>	"no"
			),$atts
		)
	);
	
	if( is_single() or is_page() ){
		
		global $alobaidi_time_line_plugin_year_filter, $post;
		$alobaidi_time_line_plugin_year_filter = $year;
		
		if( !get_option('alobaidi_timeline_cache') ){
			$random = rand();
			update_option( "alobaidi_timeline_cache", "alobaidi_timeline_$random" );
		}
	
		ob_start();
		
		$postid = $post->ID;
		$get_cahce = get_option('alobaidi_timeline_cache');
		$transient_name = md5($get_cahce.$year.$list.$limit.$date.$postid);
		$get_transient = get_transient( $transient_name );
	
		$wrap_start = apply_filters('alobaidi_time_line_wrap_start', '');
		$wrap_end = apply_filters('alobaidi_time_line_wrap_end', '');
	
		if ( empty( $get_transient ) ){

			global $wpdb;
		
			$get_posts = $wpdb->get_results(" SELECT * FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = '$year' ORDER BY post_date DESC");
			$count = 1;
			$transient_output = "";
			$check = $wpdb->query(" SELECT * FROM $wpdb->posts WHERE YEAR(post_date) = '$year' ");
		
			if( $check !== 0 ){
			
				$class = ' class="alobaidi_timeline"';
			
				$transient_output .= apply_filters('alobaidi_timeline_list_start', "<$list$class>");
				foreach( $get_posts as $get_post ){

					$title = $get_post->post_title;
				
					if( $date == 'yes' ){
						$get_date_1 = ' <span>- '.get_post_time( 'Y-m-d', false, $get_post->ID, false ).'</span>';
						$get_date_2 = null;
					}else{
						$get_date_2 = ' ('.get_post_time( 'Y-m-d', false, $get_post->ID, false ).')';
						$get_date_1 = null;
					}
					
					$url = get_permalink( $get_post->ID );
					
					$link = '<a href="'.$url.'" title="'.$title.$get_date_2.'">'.$title.'</a>'.$get_date_1;
					$transient_output .= '<li>'.$link.'</li>';

					if( !empty($limit) and $count++ == $limit ){
						break;
					}
			
				}
			
				$transient_output .= apply_filters('alobaidi_timeline_list_end', "</$list>");
		
			}else{
				$transient_output .= "<p>Not found posts on $year.</p>";
			}
		
			echo $transient_output;
		
			set_transient($transient_name, $transient_output, 3600 * 12);
		
		}
	
		else{
			echo $get_transient;
		}

		return $wrap_start.ob_get_clean().$wrap_end;
	}
	
}
add_shortcode('alobaidi_time_line', 'alobaidi_time_line');

?>