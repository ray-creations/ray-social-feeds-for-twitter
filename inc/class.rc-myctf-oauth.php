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
        
        /* get bearer token from options */
        $bearer_token = isset( $options[ 'bearer_token' ] ) ? sanitize_text_field( $options[ 'bearer_token' ] ) : '';
        
        /* if bearer token is not empty. Or $force is not true */
        if ( !empty( $bearer_token ) ) {
            return;
        }
        
        /** check if client has his own consumer_key and consumer_secret */
        $consumer_key = isset( $options[ 'consumer_key' ] ) ? sanitize_text_field( $options[ 'consumer_key' ] ) : '' ;
        $consumer_secret = isset( $options[ 'consumer_secret' ] ) ? sanitize_text_field( $options[ 'consumer_secret' ] ) : '';
        
        if ( empty( $consumer_key ) && empty( $consumer_secret ) ) {
            
            /* The plugin doesn't have its own keys, therefore, retrieve them from Ray Creations API */
            $api_keys = Rc_Myctf_OAuth::rc_myctf_fetch_token_from_ray_creations_api();
            $consumer_key = $api_keys[ 'res_consumer_key' ];
            $consumer_secret = $api_keys[ 'res_consumer_secret' ];
            
        }
        
        
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
    
    
    
    
    /**
     * Function to fetch tokens from Ray Creations API
     * 
     * @since 1.0
     * @access public
     * 
     * @param array $plugin_data Holds information taken from plugin declaration from main page header
     * @return array    $api_keys||False   Array holding the api keys
     */
    public static function rc_myctf_fetch_token_from_ray_creations_api() {
        
        /* retrieve $plugin_data from transient */
        $plugin_data = get_transient( 'plugin_data' ); //array holding info on plugin data
        
        /* prepare data to be sent along with the request */
        $token_type = 'api_keys';
        $plugin_name = sanitize_text_field( $plugin_data['Name'] );
        $plugin_text_domain = sanitize_text_field( $plugin_data['TextDomain'] );
        $plugin_base = sanitize_text_field( $plugin_data[ 'plugin_base' ] );
        
        $parameters = array(
            'token_type' => $token_type,
            'plugin_name' => $plugin_name,
            'plugin_text_domain' => $plugin_text_domain,
            'plugin_base' => $plugin_base,
            'plugin_data' => $plugin_data
            
        );
        
        
        $args = array(
                'method' => 'POST',
                'httpversion' => '1.0',
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
                ),
                'body' => array(
                    'token_type' => $token_type,
                    'plugin_name' => $plugin_name,
                    'plugin_text_domain' => $plugin_text_domain,
                    'plugin_base' => $plugin_base
                )
            );
            
        //add_filter( 'https_ssl_verify', '__return_false' );
        
        /* Sending the values & retrieving the response object from Twitter */
        //$response = wp_remote_post( 'http://localhost/wp-json/ray-api/v1/token', $args );
        $response = wp_remote_post( 'https://api.raycreations.net/wp-json/ray-api/v1/token', $args );
        
        if ( is_wp_error( $response ) ){
            return FALSE;
        }
        
        /* reading the response received from Ray Creations using json */
        $body = json_decode( $response['body'] );
        
        
        if($body){
            
            /* Extract data from JSON */
            $res_code = sanitize_text_field( $body->code );
            $res_status = $body->data->status;

            if ( $res_code != 'success' && $res_status != '200' ) {
                return FALSE;
            }

            /* Extract all variable obtained from JSON response */
            $params = $body->data->params;
            $res_token_type = sanitize_text_field( $params->token_type );
            $res_plugin_name = sanitize_text_field( $params->plugin_name );
            $res_plugin_text_domain = sanitize_text_field( $params->plugin_text_domain );
            $res_plugin_base = sanitize_text_field( $params->plugin_base );
            
            
            /* Check if returned variables are the same as sent variables, if not return error */
            if ( ( $res_token_type != $token_type ) && ( $res_plugin_name != $plugin_name ) 
                    && ( $res_plugin_text_domain != $plugin_text_domain ) && ( $res_plugin_base != $plugin_base ) ) {
                return FALSE;
            }

            $api_keys = array(
                'res_consumer_key' => wp_strip_all_tags( $params->consumer_key ),
                'res_consumer_secret' => wp_strip_all_tags( $params->consumer_secret )
            );
            
            if ( !empty( $api_keys[ 'res_consumer_key' ] ) && !empty( $api_keys[ 'res_consumer_secret' ] ) )  {
                
                return $api_keys;
                
            } else {
                
                return FALSE;
            }
            
        }
    }// rc_myctf_fetch_token_from_ray_creations_api
    
    
}//end class
