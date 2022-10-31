<?php

/**
 * This class handles generating of Bearer token by authenticating with Twitter API
 *
 * @author Ray Creations
 */

/**
 * Ensures the page is not accessed directly
 */
if ( !defined( 'ABSPATH' ) ){
    exit;
}


class Rc_Myctf_OAuth {

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
              
            add_action( 'template_redirect', array( 'Rc_Myctf_OAuth', 'rc_myctf_get_bearer_token' ) );
            
            
    }
    
    
    /**
     * Function to fetch bearer token from Twitter if not stored in Options
     * 
     * @since 1.0
     * @access public
     * 
     * @param $force    boolean Force to fetch bearer token afresh
     * @return  void    
     */
    public static function rc_myctf_get_bearer_token( $force ){
               
        /** Get the required keys from Options table */
        $options = get_option( 'rc_myctf_settings_options' );
        
        /** Retrieve the keys from $options array */
        $consumer_key = isset( $options[ 'consumer_key' ] ) ? sanitize_text_field( $options[ 'consumer_key' ] ) : '' ;
        $consumer_secret = isset( $options[ 'consumer_secret' ] ) ? sanitize_text_field( $options[ 'consumer_secret' ] ) : '';
        $bearer_token = isset( $options[ 'bearer_token' ] ) ? sanitize_text_field( $options[ 'bearer_token' ] ) : '';
        
        
        /* 
         * If bearer token is not already stored in options, fetch new ones from Twitter.
         * Alternatively, also fetch from Twitter if $force = true
         */
        if( $consumer_key && $consumer_secret && ( !$bearer_token || $force ) ){

            $url_encoded_consumer_key = urlencode( $consumer_key );
            $url_encoded_consumer_secret = urlencode( $consumer_secret );
            $bearer_token_credentials = $url_encoded_consumer_key . ':' . $url_encoded_consumer_secret;
            $base64_encoded_bearer_token_credentials = base64_encode( $bearer_token_credentials );
            
            
            $args = array(
                'method' => 'POST',
                'httpversion' => '1.1',
                'blocking' => true,
                'headers' => array(
                    'Authorization' => 'Basic ' . $base64_encoded_bearer_token_credentials,
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
                ),
                'body' => array(
                    'grant_type' => 'client_credentials'
                )
            );
            
            //add_filter( 'https_ssl_verify', '__return_false' );
            
            /** Sending the values & retrieving the response object from Twitter */
            $response = wp_remote_post( 'https://api.twitter.com/oauth2/token', $args );
                       
            if ( is_wp_error( $response ) ){
                echo $response->get_error_message();
            }
            
            /** reading the response received from Twitter using json */
            $body = json_decode( $response['body'] );
            
            if($body){
                $keys = $body->{ 'access_token' };
                $options[ 'bearer_token' ] = $keys;
                update_option( 'rc_myctf_settings_options', $options );
            }
            
        }
    }//ends function rc_myctf_get_bearer_token
    
    
    
    /**
     * Function to invalidate current bearer token
     * 
     * @since 1.0
     * @access public
     */
    public static function rc_myctf_invalidate_bearer_token() {
        
        
        /** Get the required keys from Options table */
        $options = get_option( 'rc_myctf_settings_options' );
        
        /** Retrieve the keys from $options array */
        $consumer_key = isset( $options[ 'consumer_key' ] ) ? sanitize_text_field( $options[ 'consumer_key' ] ) : '' ;
        $consumer_secret = isset( $options[ 'consumer_secret' ] ) ? sanitize_text_field( $options[ 'consumer_secret' ] ) : '';
        $bearer_token = isset( $options[ 'bearer_token' ] ) ? sanitize_text_field( $options[ 'bearer_token' ] ) : '';
        
        
        $url_encoded_consumer_key = urlencode( $consumer_key );
        $url_encoded_consumer_secret = urlencode( $consumer_secret );
        $basic_auth_string = $url_encoded_consumer_key . ':' . $url_encoded_consumer_secret;
        $base64_encoded_basic_auth_string = base64_encode( $basic_auth_string );

        $args = array(
                'method' => 'POST',
                'httpversion' => '1.1',
                'headers' => array(
                    'authorization' => 'Basic ' . $base64_encoded_basic_auth_string,
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
                ),
                'body' => array(
                    'access_token' => $bearer_token
                )
            );
            
        //add_filter( 'https_ssl_verify', '__return_false' );
        
        /* Sending the values & retrieving the response object from Twitter */
        $response = wp_remote_post( 'https://api.twitter.com/oauth2/invalidate_token', $args );
         
        if ( is_wp_error( $response ) ){
            
            echo $response->get_error_message();
            $options[ 'error_message' ] = $response->get_error_message();
            update_option( 'rc_myctf_settings_options', $options );
            return FALSE;
        }

        /* reading the response received from Twitter using json */
        $body = json_decode( $response['body'] );
        
        if($body){
            
            $invalidated_token = $body->{ 'access_token' };
            
            if ( $invalidated_token == $bearer_token ) {
                
                /* Set bearer_token as empty. So automatically new token would be generated next time Twitter is queried */
                $options[ 'bearer_token' ] = '';
                
                /* Stores the current Unix timestap */
                $options[ 'token_last_invalidated' ] = time();
                update_option( 'rc_myctf_settings_options', $options );
                
                return TRUE;
                
            } else {
                
                return FALSE;
            }
            
        }
    }// ends function rc_myctf_invalidate_bearer_token
    
    
    
    
}//end class
