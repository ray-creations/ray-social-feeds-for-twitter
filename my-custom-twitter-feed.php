<?php

/* 
 * Plugin Name: Ray Social Feeds For Twitter
 * Plugin URI: https://www.raycreations.net/my-custom-twitter-feed/
 * Description: Display beautiful twitter feeds on your website.
 * Version: 1.2.2
 * Author: Ray Creations
 * Author URI: https://www.raycreations.net
 * License: GPLv2 or later
 * Text Domain: my-custom-twitter-feed
 * Domain Path: Languages
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2019 Ray Creations
*/


/**
 * Ensures the page is not accessed directly
 */
if ( !defined( 'ABSPATH' ) ){
    exit;
}

/**
 * Define Global constants
 * 
 * @since 1.0
 */
define( 'RC_MYCTF_VERSION', '1.0' );
define( 'RC_MYCTF_DIR', plugin_dir_path( __FILE__ ) );
define( 'RC_MYCTF_URI', plugin_dir_url( __FILE__ ) );
define( 'RC_MYCTF_OAUTH_URL', 'https://api.raycreations.net/wp-json/ray-api/v1/twitter-oauth' );
define( 'RC_MYCTF_ADMIN_URL', admin_url( 'options-general.php?page=myctf-page' ) );


/**
 * Enqueue style sheet & scripts
 */
add_action( 'wp_enqueue_scripts', 'rc_myctf_enqueue_styles', 50 );                                  //plugin front facing main stylesheet
add_action( 'wp_enqueue_scripts', 'rc_myctf_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'rc_myctf_enqueue_admin_scripts' );

/* Enqueue function to load plugin textdomain */
add_action( 'init', 'rc_myctf_load_textdomain' );

/* Function to store plugin data in transient */
//add_action( 'admin_init', 'rc_myctf_store_plugin_data_in_transient' );
//add_action( 'admin_enqueue_scripts', 'rc_myctf_store_plugin_base_in_plugin_data_array' );

/**
 * Include classes required by this plugin
 */
require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-oauth.php' );
require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-cache-management.php' );
require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-widgets.php' );

if ( !is_admin() ) {
    require_once( RC_MYCTF_DIR . 'inc/class.my-custom-twitter-feed.php' );
    require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-url-preview.php' );
    require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-get-tweets.php' );
}

/**
 * Initialize the included pages wherever required
 */

add_action( 'init', array( 'Rc_Myctf_OAuth', 'init' ) );

if ( !is_admin() ) {
    add_action( 'init', array( 'Rc_Myctf', 'init' ) );
}



/* When user is in the Admin Dashboard */
if( is_admin() ){
    require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-admin.php' );
    add_action( 'init', array( 'Rc_Myctf_Admin', 'init' ) );
    
    /*
     * Include the admin helper class that houses many of the 
     * functions from the admin page above  
     */
    require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-admin-helper.php' );
    
    /* include the class for handling admin notices */
    require_once( RC_MYCTF_DIR . 'inc/class.rc-myctf-notices.php' );
    add_action( 'init', array( 'Rc_Myctf_Notices', 'init' ) );
    
}



/**
 * Register activation hook
 */
register_activation_hook( __FILE__, 'rc_myctf_plugin_activation' );


/**
 * Register deactivation hook
 */
register_deactivation_hook( __FILE__, 'rc_myctf_plugin_deactivation' );





/*
* Plugin activation function called from the main file
* 
* @since 1.0
* @access public static
* @return void
*/
function rc_myctf_plugin_activation(){

  /**
  * Register uninstall hook in the plugin activation function
  */
  register_uninstall_hook( __FILE__, 'rc_myctf_plugin_uninstall' );

   /** 
    * If the plugin options don't exist, create them
    * 
    */
  
    // create the "rc_myctf_api_options" option
    $api_settings_args = array(
        'app_consumer_key' => '',
        'app_consumer_secret' => '',
        'consumer_key' => '',
        'consumer_secret' => '',
        'access_token' => '',
        'access_token_secret' => '',
        'preserve_settings' => false                //Whether to preserve settings when plugin removed. [default: false]
    );

    $customize_args = array(
        'screen_name' => 'raycreations',
        'feed_type' => 'user_timeline',         // home_timeline, user_timeline, hashtags [default: user_timeline]                 
        'hashtags' => 'mountain clouds',        // default hashtags
        'search_string' => 'fog sunrise',       // default search string
        'feed_width_type' => 'responsive',      // Allowed values are 'responsive'
        'hide_media' => 1,                      // default True for Free version
        'display_style' => 'display_list',      //display_list, display_masonry, display_slider_1_col, display_slider_2_col [default: list]
        'number_of_tweets' => 10,              //value between 1-40 [default: 10]
        'number_of_tweets_in_row' => 3,       //value between 1-5 [default:3]
        'include_photos' => 1,                   //include photos in Twitter search
        'include_videos' => 0,
        'exclude_replies' => 1,
        'include_rts' => 0,
        'check_tweets_every' => 'hour',       //Permitted values are 'hour' & 'day'. 'hour' is the default value
        'tweet_checking_interval' => 1,        //how often should tweets be checked [default: 1 hour/day]
        'remove_links_hashtags' => 0,
        'remove_links_mentions' => 0,
        'remove_ext_links' => 0,
        'nofollow_ext_links' => 1
    );
   
    $tweets_args = array(
        'display_tweet_border' => 1,
        'display_header' => 1,
        'display_profile_img_header' => 1,
        'display_name_header' => 1,
        'display_screen_name_header' => 1,
        'display_date_header' => 1,
        'display_footer' => 1,
        'display_likes_footer' => 1,
        'display_retweets_footer' => 1,
        'display_screen_name_footer' => 0,
        'display_date_footer' => 0, 
    );
    
    $slider_args = array(
        'nav_arrows' => 0,
        'nav_dots' => 1,
        'autoplay' => 1,
        'transition_interval' => 7,
        'transition_speed' => 3,
        'pause_on_hover' => 1,
        'loop' => 1
    );
    
    $style_args = array(
        'font_size' => 'inherit',
        'font_color' => '',
        'link_text_decoration' => 'inherit',
        'feed_bg_color' => '',
        'tweet_bg_color' => '',
        'border_style' => 'shadow',
        'font_size_header' => '100',
        'name_font_color_header' => '',
        'name_font_weight_header' => 'bold',
        'screen_name_font_size_header' => '75',
        'screen_name_font_color_header' => '#666666',
        'screen_name_font_weight_header' => 'inherit',
        'date_font_size_header' => '75',
        'date_font_color_header' => '#666666',
        'date_font_weight_header' => 'inherit',
        'link_text_decoration_header' => 'inherit',
        'font_size_tweet' => 'inherit',
        'font_color_tweet' => '',
        'font_weight_tweet' => 'inherit',
        'link_color_tweet' => '',
        'link_text_decoration_tweet' => 'inherit',
        'font_size_footer' => '75',
        'like_icon_color_footer' => '#999999',
        'like_count_color_footer' => '#999999',
        'retweet_icon_color_footer' => '#999999',
        'retweet_count_color_footer' => '#999999',
        'screen_name_font_color_footer' => '#999999',
        'screen_name_font_weight_footer' => 'inherit',
        'date_font_color_footer' => '#999999',
        'date_font_weight_footer' => 'normal',
        'link_text_decoration_footer' => 'none'
    );
   
    $tweet_show_hide_args = array(
        'display_tweet_border' => 1,
        'display_header' => 1,
        'display_profile_img_header' => 1,
        'display_name_header' => 1,
        'display_screen_name_header' => 1,
        'display_date_header' => 1,
        'display_footer' => 1,
        'display_likes_footer' => 1,
        'display_retweets_footer' => 1,
        'display_screen_name_footer' => 0,
        'display_date_footer' => 0
    );
    
    /*
     * Add the options if they don't exist
     */
        add_option( 'rc_myctf_settings_options', $api_settings_args );
        add_option( 'rc_myctf_customize_options', $customize_args );
        add_option( 'rc_myctf_tweets_options', $tweets_args );
        add_option( 'rc_myctf_slider_carousel_options', $slider_args );
        add_option( 'rc_myctf_style_options', $style_args );
        add_option( 'rc_myctf_tweets_options', $tweet_show_hide_args );
        add_option( 'rc_myctf_support_options' );
        add_option( 'rc_myctf_scodes_trans' );

} //ends plugin activation function
    


/*
* Functions to be performed on plugin deactivaton. Called from the main file
* 
* @since 1.0
* @access public static
* @return void
*/
function rc_myctf_plugin_deactivation(){
 
}


/*
* Perform clean up function on plugin uninstall/removal
*/
function rc_myctf_plugin_uninstall(){

   /*
    * Remove options if "rc_myctf_settings_options['preserve_settings'] 
    */
   $options = get_option( 'rc_myctf_settings_options' );
   $preserve_settings = $options[ 'preserve_settings' ];

   //Do not delete options if $preserve_settings value is true
   if( $preserve_settings == FALSE ){

           //delete plugin options
           delete_option( 'rc_myctf_settings_options' );
           delete_option( 'rc_myctf_customize_options' );
           delete_option( 'rc_myctf_tweets_options' );
           delete_option( 'rc_myctf_slider_carousel_options' );
           delete_option( 'rc_myctf_style_options' );
           delete_option( 'rc_myctf_tweets_options' );
           delete_option( 'rc_myctf_support_options' );
           delete_option( 'rc_myctf_scodes_trans' );
           
   }
}



/**
 * Enqueue style sheet
 */
function rc_myctf_enqueue_styles(){
    
    /* Enqueue the front-end plugin css */
    wp_enqueue_style( 'rc_myctf_style', RC_MYCTF_URI . 'css/rc-myctf.css', '', '1.0' );
    
    /* owl carousel stylesheets */
    wp_enqueue_style( 'rc_myctf_owl_carousel', RC_MYCTF_URI . 'css/owl.carousel.min.css' );
    
    /* generating custom css based on user chosen options */
    $rc_myctf_custom_css = rc_myctf_generate_custom_css();
    wp_add_inline_style( 'rc_myctf_style', $rc_myctf_custom_css );
    
}

/**
 * Enqueues admin style sheet
 */
function rc_myctf_enqueue_admin_scripts(){
    
    /* fetch current page name */
    $current_screen = get_current_screen();
    
    /*
     *  if $current_screen has the plugin page name, then enqueue the admin style sheet
     *  this will ensure that the stylesheet is enqueued for our plugin pages only
     */
    if (strpos( $current_screen->base, 'myctf-page' ) !== false ) {
        /* Load admin styles */
        wp_enqueue_style( 'rc_myctf_admin_style', RC_MYCTF_URI . 'css/rc-myctf-admin.css', '', '1.0' );
        
        /* Add the color picker css file */
        wp_enqueue_style( 'wp-color-picker' );
        
        /* Include admin js file for our plugin */
        wp_enqueue_script( 'rc_myctf_admin_scripts', RC_MYCTF_URI . 'js/rc_myctf_admin_scripts.js', array( 'wp-color-picker' ), '1.0', true );
        
    }//ends if
     
}//ends rc_myctf_enqueue_admin_scripts


/**
 * Enqueue scripts
 */
function rc_myctf_enqueue_scripts(){
    
    if ( !is_admin() ){
        wp_enqueue_script( 'rc_myctf_scripts', RC_MYCTF_URI . 'js/rc-myctf-scripts.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_script( 'masonry' );
        wp_enqueue_script( 'rc_myctf_owl_scripts', RC_MYCTF_URI . 'js/owl.carousel.min.js', array( 'jquery' ), true );
    }
    
}

/*
 * Load plugin textdomain
 */
function rc_myctf_load_textdomain() {
    
    /* Loads the translation for the plugin. */
    if ( !is_admin() ) {
        load_plugin_textdomain( 'my-custom-twitter-feed', false, RC_MYCTF_DIR . 'languages' );
    }
}


/*
 * Generate custom styles based on the saved options
 * 
 * @since   1.2.1
 * @return  string  styles in string format.
 */
function rc_myctf_generate_custom_css() {
    
    /* Extraction various options */
    $options = get_option( 'rc_myctf_style_options' );

    /*
     * Tweet General Options
     */
    
    //extract font size and then convert it to rem
    $font_size = isset( $options[ 'font_size' ] ) ? sanitize_text_field( $options[ 'font_size' ] ) : 'inherit';
    $font_size_rem = $font_size != 'inherit' ? sanitize_text_field( $font_size / 16 . 'rem' ) : 'inherit';
    //$font_size_rem = $font_size != 'inherit' ? sanitize_text_field( $font_size . 'px' ) : 'inherit';
    
    
    $font_color = !isset( $options[ 'font_color' ] ) || empty( $options[ 'font_color' ] ) ? 'inherit' : sanitize_text_field( $options[ 'font_color' ] );
    $link_text_decoration = isset( $options[ 'link_text_decoration' ] ) ? sanitize_text_field( $options[ 'link_text_decoration' ] ) : 'inherit';
    $feed_bg_color = !isset( $options[ 'feed_bg_color' ] ) || empty( $options[ 'feed_bg_color' ] ) ? 'inherit' : sanitize_text_field( $options[ 'feed_bg_color' ] );
    $tweet_bg_color = !isset( $options[ 'tweet_bg_color' ] ) || empty( $options[ 'tweet_bg_color' ] ) ? 'inherit' : sanitize_text_field( $options[ 'tweet_bg_color' ] );
    
    //adding to css
    $rc_myctf_custom_css = ".rc_myctf_tweets_wrap{ font-size: $font_size_rem; color: $font_color; }";
    $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap a{ color: $font_color; }";

    if ( $link_text_decoration != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap a{ text-decoration: $link_text_decoration; box-shadow: none; border: 0; }";
        $rc_myctf_custom_css .= " .widget .rc_myctf_tweets_wrap a{ text-decoration: $link_text_decoration; box-shadow: none; border: 0; }";
    }
    
    if ( $tweet_bg_color != 'inherit' ) {
        $rc_myctf_custom_css .= " .tweet_item{ background-color: $tweet_bg_color; }";
    }
    
    if ( $feed_bg_color != 'inherit' ) {
        $rc_myctf_custom_css .= " #content{ background-color: $feed_bg_color; )";
    }
    
    
    /*
     * Tweet Header Options
     */
    $font_size_header = isset( $options[ 'font_size_header' ] ) ? sanitize_text_field( $options[ 'font_size_header' ] ) : 'inherit';
    $font_size_header_percent = $font_size_header != 'inherit' ? sanitize_text_field( $font_size_header . '%' ) : 'inherit';
    
    $name_font_color_header = !isset( $options[ 'name_font_color_header' ] ) || empty( $options[ 'name_font_color_header' ] ) ? 'inherit' : sanitize_text_field( $options[ 'name_font_color_header' ] );
    $name_font_weight_header = isset( $options[ 'name_font_weight_header' ] ) ? sanitize_text_field( $options[ 'name_font_weight_header' ] ) : 'inherit';
    
    //extract screen_name_font_size_header and then convert it to percentage
    $screen_name_font_size_header = isset( $options[ 'screen_name_font_size_header' ] ) ? sanitize_text_field( $options[ 'screen_name_font_size_header' ] ) : 'inherit';
    $screen_name_font_size_header_percent = $font_size_header != 'inherit' ? sanitize_text_field( $screen_name_font_size_header . '%' ) : 'inherit';
    
    $screen_name_font_color_header = !isset( $options[ 'screen_name_font_color_header' ] ) || empty( $options[ 'screen_name_font_color_header' ] ) ? 'inherit' : sanitize_text_field( $options[ 'screen_name_font_color_header' ] );
    $screen_name_font_weight_header = isset( $options[ 'screen_name_font_weight_header' ] ) ? sanitize_text_field( $options[ 'screen_name_font_weight_header' ] ) : 'inherit';
    
    //extract date_font_size_header and then convert it to percentage
    $date_font_size_header = isset( $options[ 'date_font_size_header' ] ) ? sanitize_text_field( $options[ 'date_font_size_header' ] ) : 'inherit';
    $date_font_size_header_percent = $font_size_header != 'inherit' ? sanitize_text_field( $date_font_size_header . '%' ) : 'inherit';
    
    $date_font_color_header = !isset( $options[ 'date_font_color_header' ] ) || empty( $options[ 'date_font_color_header' ] ) ? 'inherit' : sanitize_text_field( $options[ 'date_font_color_header' ] );
    $date_font_weight_header = isset( $options[ 'date_font_weight_header' ] ) ? sanitize_text_field( $options[ 'date_font_weight_header' ] ) : 'inherit';
    $link_text_decoration_header = isset( $options[ 'link_text_decoration_header' ] ) ? sanitize_text_field( $options[ 'link_text_decoration_header' ] ) : 'inherit';
    
    
    
    //adding to css
    $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .twitter_header_meta{ font-size: $font_size_header_percent; }";
    
    //when not inherit, it should fetch from plugin stylesheet. And not inherit from parent.
    if ( $name_font_color_header != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .name-of-tweeter{ color: $name_font_color_header; }";
    }
    
    //when inherit, it should inherit from default theme
    if ( $name_font_weight_header != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .name-of-tweeter{ font-weight: $name_font_weight_header; }";
    }
    
    //inheriting from parent
    if ( $screen_name_font_size_header_percent != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .screen_name{ font-size: $screen_name_font_size_header_percent; }";
    }
    
    //inheriting from parent
    if ( $screen_name_font_color_header != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .screen_name{ color: $screen_name_font_color_header; }";
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .screen_name a{ color: $screen_name_font_color_header; border-bottom: 0; box-shadow: none; }";
    }
    
    //Inheriting from parent.
    if ( $screen_name_font_weight_header != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .screen_name{ font-weight: $screen_name_font_weight_header; }";
    }
    
    //inheriting from parent
    if ( $date_font_size_header_percent != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet_date{ font-size: $date_font_size_header_percent; }";
    }
    
    //when inherit, it should fetch from plugin stylesheet. And not inherit from parent.
    if ( $date_font_color_header != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet_date{ color: $date_font_color_header; }";
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet_date a{ color: $date_font_color_header; border-bottom: 0; box-shadow: none; }";
    }
    
    //Inheriting from parent
    if ( $date_font_weight_header != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet_date{ font-weight: $date_font_weight_header; }";
    }
    
    //inheriting from parent
    if ( $link_text_decoration_header != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .twitter_header_meta a{ text-decoration: $link_text_decoration_header; box-shadow: none; border: 0; }";
        $rc_myctf_custom_css .= " .widget .rc_myctf_tweets_wrap .twitter_header_meta a{ text-decoration: $link_text_decoration_header; box-shadow: none; border: 0; }";
    }
    
    
    /*
     * Tweet Section
     */
    $font_size_tweet = isset( $options[ 'font_size_tweet' ] ) ? sanitize_text_field( $options[ 'font_size_tweet' ] ) : 'inherit';
    $font_size_tweet_percent = $font_size_tweet != 'inherit' ? sanitize_text_field( $font_size_tweet . '%' ) : 'inherit';
    
    $font_color_tweet = !isset( $options[ 'font_color_tweet' ] ) || empty( $options[ 'font_color_tweet' ] ) ? 'inherit' : sanitize_text_field( $options[ 'font_color_tweet' ] );
    $font_weight_tweet = isset( $options[ 'font_weight_tweet' ] ) ? sanitize_text_field( $options[ 'font_weight_tweet' ] ) : 'inherit';
    $link_color_tweet = !isset( $options[ 'link_color_tweet' ] ) || empty( $options[ 'link_color_tweet' ] ) ? 'inherit' : sanitize_text_field( $options[ 'link_color_tweet' ] );
    $link_text_decoration_tweet = isset( $options[ 'link_text_decoration_tweet' ] ) ? sanitize_text_field( $options[ 'link_text_decoration_tweet' ] ) : 'inherit';
     
    $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet{ font-size: $font_size_tweet_percent; color: $font_color_tweet; font-weight: $font_weight_tweet; }";
    if ( $link_text_decoration_tweet != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet a{ color: $link_color_tweet; text-decoration: $link_text_decoration_tweet; border-bottom: none;  }";
    }
    
    /*
     * Tweet Footer
     */
    $font_size_footer = isset( $options[ 'font_size_footer' ] ) ? sanitize_text_field( $options[ 'font_size_footer' ] ) : 'inherit';
    $font_size_footer_percent = $font_size_footer != 'inherit' ? sanitize_text_field( $font_size_footer . '%' ) : 'inherit';
    
    $like_icon_color_footer = !isset( $options[ 'like_icon_color_footer' ] ) || empty( $options[ 'like_icon_color_footer' ] ) ? 'inherit' : sanitize_text_field( $options[ 'like_icon_color_footer' ] );
    $like_count_color_footer = !isset( $options[ 'like_count_color_footer' ] ) || empty( $options[ 'like_count_color_footer' ] ) ? 'inherit' : sanitize_text_field( $options[ 'like_count_color_footer' ] );
    $retweet_icon_color_footer = !isset( $options[ 'retweet_icon_color_footer' ] ) || empty( $options[ 'retweet_icon_color_footer' ] ) ? 'inherit' : sanitize_text_field( $options[ 'retweet_icon_color_footer' ] );
    $retweet_count_color_footer = !isset( $options[ 'retweet_count_color_footer' ] ) || empty( $options[ 'retweet_count_color_footer' ] ) ? 'inherit' : sanitize_text_field( $options[ 'retweet_count_color_footer' ] );
    $screen_name_font_color_footer = !isset( $options[ 'screen_name_font_color_footer' ] ) || empty( $options[ 'screen_name_font_color_footer' ] ) ? 'inherit' : sanitize_text_field( $options[ 'screen_name_font_color_footer' ] );
    $screen_name_font_weight_footer = isset( $options[ 'screen_name_font_weight_footer' ] ) ? sanitize_text_field( $options[ 'screen_name_font_weight_footer' ] ) : 'inherit';
    $date_font_color_footer = !isset( $options[ 'date_font_color_footer' ] ) || empty( $options[ 'date_font_color_footer' ] ) ? 'inherit' : sanitize_text_field( $options[ 'date_font_color_footer' ] );
    $date_font_weight_footer = isset( $options[ 'date_font_weight_footer' ] ) ? sanitize_text_field( $options[ 'date_font_weight_footer' ] ) : 'inherit';
    $link_text_decoration_footer = isset( $options[ 'link_text_decoration_footer' ] ) ? sanitize_text_field( $options[ 'link_text_decoration_footer' ] ) : 'inherit';
    
    
    //adding to css
    $rc_myctf_custom_css .= " .rc_myctf_tweet_footer{ font-size: $font_size_footer_percent; }";
    
    //when not inherit, it should fetch from plugin stylesheet. And not inherit from parent.
    if ( $like_icon_color_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_twitter_heart{ color: $like_icon_color_footer; }";
    }
    
    //Inheriting from parent.
    if ( $like_count_color_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_favorite_count{ color: $like_count_color_footer; }";
    }
    
    //when not inherit, it should fetch from plugin stylesheet. And not inherit from parent.
    if ( $retweet_icon_color_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_retweet_sign{ color: $retweet_icon_color_footer; }";
    }
    
    //Inheriting from parent.
    if ( $retweet_count_color_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_retweet_count{ color: $retweet_count_color_footer; }";
    }
    
    //when not inherit, it should fetch from plugin stylesheet. And not inherit from parent.
    if ( $screen_name_font_color_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .screen_name_footer{ color: $screen_name_font_color_footer; }";
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .screen_name_footer a{ color: $screen_name_font_color_footer; border-bottom: 0; box-shadow: none; }";
    }
    
    //Inheriting from parent.
    if ( $screen_name_font_weight_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .screen_name_footer{ font-weight: $screen_name_font_weight_footer; }";
    }
    
    //when not inherit, it should fetch from plugin stylesheet. And not inherit from parent.
    if ( $date_font_color_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet_date_footer{ color: $date_font_color_footer; }";
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet_date_footer a{ color: $date_font_color_footer; border-bottom:0; box-shadow: none }";
    }
    
    //Inheriting from parent.
    if ( $date_font_weight_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .tweet_date_footer{ font-weight: $date_font_weight_footer; }";
    }
    
    //inheriting from parent
    if ( $link_text_decoration_footer != 'inherit' ) {
        $rc_myctf_custom_css .= " .rc_myctf_tweets_wrap .rc_myctf_tweet_footer a{ text-decoration: $link_text_decoration_footer; box-shadow: none; border: 0; }";
        $rc_myctf_custom_css .= " .widget .rc_myctf_tweets_wrap .rc_myctf_tweet_footer a{ text-decoration: $link_text_decoration_footer; box-shadow: none; border: 0; }";
    }
        
    return $rc_myctf_custom_css;
    
}