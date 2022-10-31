<?php

/**
 * Handles notices shown on the website
 *
 * @package My Custom Twitter Feed
 * @author Ray Creations
 * @copyright (c) 2019, Ray Creations
 */

/**
 * Ensures the page is not accessed directly
 */
if ( !defined( 'ABSPATH' ) ){
    exit;
}


class Rc_Myctf_Notices {
    
    /**
     * Indicates whether the class has been initialized or not.
     * 
     * @since 1.0
     * @access private
     * @var boolean
     */
    private static $initiated = false;
    
    
    /**
     * Check whether the Class has been initialized
     * 
     * @since 1.0
     * @acces public
     * @return void
     */
    public static function init(){
        if( !self::$initiated ){
            self::init_hooks();
        }
    }
    
    
    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks(){
        self::$initiated = true;
    
        
    }
    
    
    /*
     * Displays admin notice of success
     * 
     * @since 1.0
     * @access public
     */
    public static function rc_myctf_admin_notice__success() {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Done!', 'my-custom-twitter-feed' ); ?></p>
        </div>
        <?php
    }
    
    /*
     * Displays admin error notice
     * 
     * @since 1.0
     * @access public
     */
    public static function rc_myctf_admin_notice__error() {
        $class = 'notice notice-error';
	$message_str = __( 'Oops! An error has occurred.', 'my-custom-twitter-feed' );
        
        $options = get_option( 'rc_myctf_settings_options' );
        $message = isset( $options[ 'error_message' ] ) ? sanitize_text_field( $options[ 'error_message' ] ) : $message_str;

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 

    }
    
    
    public static function rc_myctf_admin_notice__no_keys() {
        
         ?>
        <div class="notice notice-info">
            <p><?php _e( 'Please add your Consumer Key & Secret to activate the plugin!', 'my-custom-twitter-feed' ); ?></p>
        </div>
        <?php
        
    }
    
}// ends class
