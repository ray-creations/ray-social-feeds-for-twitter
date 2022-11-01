<?php

/* 
 * Plugin Name: Ray Social Feeds For Twitter
 * Plugin URI: https://www.raycreations.net/my-custom-twitter-feed/
 * Description: Display beautiful twitter feeds on your website.
 * Version: 1.1.2
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


/**
 * Enqueue style sheet & scripts
 */
add_action( 'wp_enqueue_scripts', 'rc_myctf_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'rc_myctf_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'rc_myctf_enqueue_admin_scripts' );

/* Enqueue function to load plugin textdomain */
add_action( 'init', 'rc_myctf_load_textdomain' );

/* Function to store plugin data in transient */
add_action( 'admin_init', 'rc_myctf_store_plugin_data_in_transient' );
add_action( 'admin_enqueue_scripts', 'rc_myctf_store_plugin_base_in_plugin_data_array' );

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
        'consumer_key' => '',
        'consumer_secret' => '',
        'bearer_token' =>'',
        'token_last_invalidated' => time(),         //Stores the current Unix timestap 
        'preserve_settings' => false                //Whether to preserve settings when plugin removed. [default: false]
    );

    $customize_args = array(
        'screen_name' => 'raycreations',
        'feed_type' => 'user_timeline',         // home_timeline, user_timeline, hashtags [default: user_timeline]                 
        'hashtags' => 'mountain clouds',        // default hashtags
        'search_string' => 'fog sunrise',       // default search string
        'feed_width_type' => 'responsive',      // Allowed values are 'responsive'
        'display_style' => 'display_list',      //display_list, display_masonry, display_slider_1_col, display_slider_2_col [default: list]
        'number_of_tweets' => 10,              //value between 1-40 [default: 10]
        'number_of_tweets_in_row' => 3,       //value between 1-5 [default:3]
        'include_photos' => 1,                   //include photos in Twitter search
        'include_videos' => 0,
        'exclude_replies' => 1,
        'include_rts' => 0,
        'check_tweets_every' => 'hour',       //Permitted values are 'hour' & 'day'. 'hour' is the default value
        'tweet_checking_interval' => 1        //how often should tweets be checked [default: 1 hour/day]
    );
   
   
    /*
     * Add the options if they don't exist
     */
        add_option( 'rc_myctf_settings_options', $api_settings_args );
        add_option( 'rc_myctf_customize_options', $customize_args );
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
    }
     
}


/**
 * Enqueue scripts
 */
function rc_myctf_enqueue_scripts(){
    
    if ( !is_admin() ){
        wp_enqueue_script( 'rc_myctf_media_buttons', RC_MYCTF_URI . 'js/rc_myctf_media_buttons.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_script( 'rc_myctf_slides', RC_MYCTF_URI . 'js/rc_myctf_image_slider.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_script( 'masonry' );
        wp_enqueue_script( 'rc_myctf_initialize', RC_MYCTF_URI . 'js/rc_myctf_initialize.js', '', '', true );
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
 * Stores plugin data in transient 'plugin_data' as an array
 */
function rc_myctf_store_plugin_data_in_transient() {
    /** Get $plugin_data array from transient */
    $plugin_data = get_transient( 'plugin_data' );
    
    /* if $plugin_data array is empty */
    if (is_admin() && $plugin_data === FALSE ) {
        $plugin_data = get_plugin_data( __FILE__ );
        set_transient( 'plugin_data', $plugin_data, 86400 );
    }
    
}//ends rc_myctf_store_plugin_data_in_transient


/*
 * Storing plugin_base (screen_name) in $plugin_data array
 * $plugin_data is stored in transient
 */
function rc_myctf_store_plugin_base_in_plugin_data_array() {
    /** Get $plugin_data array from transient */
    $plugin_data = get_transient( 'plugin_data' );
    
    if ( $plugin_data !== FALSE && !isset( $plugin_data[ 'plugin_base' ] ) ) {
        /* fetch current page name */
        $current_screen = get_current_screen();
        
        //if $current_screen has the plugin page name
        if (strpos( $current_screen->base, 'myctf-page' ) !== false ) {
            
            $plugin_data[ 'plugin_base' ] = sanitize_text_field( $current_screen->base );
           
            /* save the transient data to plugin again */
            set_transient( 'plugin_data', $plugin_data, 86400 );
        }
    }
}