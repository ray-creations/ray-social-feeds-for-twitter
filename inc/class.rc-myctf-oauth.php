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
              
    }
    
    
    
    /**
     * Fetches the 3 legged Twitter OAuth URL for user authorization
     * This URL is fetched from Ray Creations API
     * 
     * @since 1.2
     * @access public
     * 
     * @return array    $data||False   Array holding the api keys
     */
    public static function rc_myctf_fetch_3_legged_oauth_url_from_ray_creations_api() {
        
        /* prepare data to be sent along with the request */
        $request_type = 'oauth';
        $admin_email = get_option( 'admin_email' );
        //$return_url = admin_url( 'options-general.php?page=myctf-page' );
        $return_url = RC_MYCTF_ADMIN_URL;
        $oauth_token = '';
        
        
        $args = array(
                'method' => 'POST',
                'httpversion' => '1.0',
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
                ),
                'body' => array(
                    'request_type' => $request_type,
                    'admin_email' => $admin_email,
                    'return_url' => $return_url,
                    'oauth_token' => $oauth_token
                )
            );
            
        //add_filter( 'https_ssl_verify', '__return_false' );
        
        
        /* Sending the values & retrieving the response object from Twitter */
        $response = wp_remote_post( RC_MYCTF_OAUTH_URL, $args );
        //$response = wp_remote_post( 'https://api.raycreations.net/wp-json/ray-api/v1/twitter-oauth', $args );
        
        
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
            $res_request_type = sanitize_text_field( $params->request_type );
            $res_admin_email = sanitize_text_field( $params->admin_email );
            $res_return_url = esc_url( $params->return_url );
            
            //print_r( $params );
            //wp_die();
            
            /* Check if returned variables are the same as sent variables, if not return error */
            if ( ( $res_request_type != $request_type ) || ( $res_admin_email != $admin_email ) 
                    || ( $res_return_url != $return_url ) ) {
                return FALSE;
            }

            $res_oauth_token = sanitize_text_field( $params->oauth_token );
            $res_oauth_url = esc_url( $params->oauth_url );
            
            if ( !empty( $res_oauth_token ) && !empty( $res_oauth_url ) )  {
                /*
                 * Let us first save the temporary oauth token i.e. $res_oauth_token in transient.
                 * We will need to send this back for fetching the saved access token & secret later
                 */
                set_transient( 'temp_oauth_token', $res_oauth_token, 600 );
                
                /* now return the oauth_url where the client would be sent for authorization */
                return $res_oauth_url;
                
            } else {
                
                return FALSE;
            }
            
        }
    }// rc_myctf_fetch_3_legged_oauth_url_from_ray_creations_api
    
    
    
    /*
     * Fetches the saved access tokens from api.raycreations.net through its available API
     * 
     * @since 1.0
     * @access public
     * 
     * @return array    $data||False   Array holding the api keys
     */
    public static function rc_myctf_fetch_saved_tokens_from_ray_creations_api() {
        
        /* prepare data to be sent along with the request */
        $request_type = 'access_token';
        $admin_email = sanitize_text_field( get_option( 'admin_email' ) );
        $return_url = esc_url( admin_url( 'options-general.php?page=myctf-page' ) );
        $oauth_token = wp_strip_all_tags( get_transient( 'temp_oauth_token' ) );
        
        $args = array(
                'method' => 'POST',
                'httpversion' => '1.0',
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8'
                ),
                'body' => array(
                    'request_type' => $request_type,
                    'admin_email' => $admin_email,
                    'return_url' => $return_url,
                    'oauth_token' => $oauth_token
                )
            );
            
        //add_filter( 'https_ssl_verify', '__return_false' );
        
        /* Sending the values & retrieving the response object from Twitter */
        $response = wp_remote_post( RC_MYCTF_OAUTH_URL, $args );
        //$response = wp_remote_post( 'https://api.raycreations.net/wp-json/ray-api/v1/twitter-oauth', $args );
        
        //print_r($response);
        //wp_die();
        
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
            //$res_request_type = sanitize_text_field( $params->request_type );
            $res_admin_email = sanitize_text_field( $params->admin_email );
            $res_return_url = esc_url( $params->return_url );
            $res_oauth_token = sanitize_text_field( $params->oauth_token );
            
            
            
            /* Check if returned variables are the same as sent variables, if not return error */
            if ( ( $res_admin_email != $admin_email ) || ( $res_return_url != $return_url ) 
                    || ( $res_oauth_token != $oauth_token )  ) {
                return FALSE;
            }
            
            
            
            $res_consumer_key = sanitize_text_field( $params->consumer_key );
            $res_consumer_secret = sanitize_text_field( $params->consumer_secret );
            $res_access_token = sanitize_text_field( $params->access_token );
            $res_access_secret = sanitize_text_field( $params->access_secret );
            
            /* retrieve saved options 'rc_myctf_settings_options' and update the above values */
            $options = get_option( 'rc_myctf_settings_options' );
            
            
            
            /* update the values */
            $options[ 'app_consumer_key' ] = $res_consumer_key;
            $options[ 'app_consumer_secret' ] = $res_consumer_secret;
            $options[ 'access_token' ] = $res_access_token;
            $options[ 'access_token_secret' ] = $res_access_secret;
            
            
            /* ensure access_token values are not empty */
            if ( !empty( $res_access_token ) && !empty( $res_access_secret ) ) {
                
                /*
                 *  now save the retrieved options
                 *  if values don't change, then update command does not execute
                 */
                update_option( 'rc_myctf_settings_options', $options );
            }
            
            /* we will return true because all code have executed successfully */
            return TRUE; 
            
        }
    }// rc_myctf_fetch_saved_tokens_from_ray_creations_api
    
    
}//end class