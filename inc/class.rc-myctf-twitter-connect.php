<?php

/*
 * This class handles connections to Twitter API and fetching Tweets
 * using access tokens
 *
 * @since 1.2
 * @author Ray Creations
 */

/**
 * Ensures the page is not accessed directly
 */
if ( !defined( 'ABSPATH' ) ){
    exit;
}


class Rc_Myctf_Twitter_Connect {
    
    /**
     *  @var string Oauth access token 
     */
    private $rc_myctf_oauth_access_token;
    
    
    /** 
     * @var string Oauth access token secret 
     */
    private $rc_myctf_oauth_access_token_secret;
    
    
    /** 
     * @var string Consumer key 
     */
    private $rc_myctf_consumer_key;
    
    
    /** 
     * @var string Consumer Secret 
     */
    private $rc_myctf_consumer_secret;
    
    
    /**
     * @var array POST parameters
     */
    private $rc_myctf_post_fields;
    
    
    /**
     * @var string GET parameters
     */
    private $rc_myctf_get_fields;
    
    
    /**
     * @var array OAuth Credentials
     */
    private $rc_myctf_oauth_details;
    
    
    /**
     * @var string Twitter's request URL
     */
    private $rc_myctf_request_url;
    
    
    /**
     * @var string Request method
     */
    private $rc_myctf_request_method;
    
    
    /*
     * @var string feed_type [user_timeline, search_timeline, hashtags_timeline]
     */
    private $rc_myctf_feed_type;
    
    
    
    /**
     * Class constructor function
     * 
     * @since 1.2
     */
    public function __construct( $settings ) {
        
        if ( !isset( $settings[ 'oauth_access_token' ] ) 
                || !isset( $settings[ 'oauth_access_token_secret' ] ) 
                || !isset( $settings[ 'consumer_key' ] ) 
                || !isset( $settings[ 'consumer_secret' ] ) ) {
            
            return new WP_Error( 'twitter_param_incomplete', 'Make sure you are passing in all the parameters...' );
        }
        
        /* set the class properties variables now */
        $this->rc_myctf_oauth_access_token = sanitize_text_field( $settings[ 'oauth_access_token' ] );
        $this->rc_myctf_oauth_access_token_secret = sanitize_text_field( $settings[ 'oauth_access_token_secret' ] );
        $this->rc_myctf_consumer_key = sanitize_text_field( $settings[ 'consumer_key' ] );
        $this->rc_myctf_consumer_secret = sanitize_text_field( $settings[ 'consumer_secret' ] );
        
    }//ends constructor
    
    
    
    
    /**
     * Build, generate and include the OAuth Signature to the OAuth credentials
     * 
     * @since 1.2
     * 
     * @param string $request_url Twitter endpoint where request needs to be sent
     * @param string $request_method Either POST or GET
     * 
     * @return $this
     */
    public function rc_myctf_build_oauth( $feed_type, $request_method ) {
        
        /** Make sure the request is either POST or GET */
        if ( !in_array( strtolower( $request_method ), array( 'post', 'get' ) ) ) {
            return new WP_Error( 'invalid_request', 'Request method must be either POST or GET' );
        }
        
        /* sanitize $feed_type input */
        $san_feed_type = wp_strip_all_tags( $feed_type );
        
        /* set $request_url class property according based on $feed_type */
        $this->rc_myctf_request_url = $this->rc_myctf_get_base_url_based_on_feed_type( $san_feed_type );
        
        
        /* building the $oauth_credentials value */
        $oauth_credentials = array(
            'oauth_consumer_key' => $this->rc_myctf_consumer_key,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $this->rc_myctf_oauth_access_token,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );
        
        if ( !is_null( $this->rc_myctf_get_fields ) ) {
            //removing question mark and at the same time converting to an array with key-value pairs
            $get_fields = str_replace( '?', '', explode( '&', $this->rc_myctf_get_fields ) );
            
            foreach( $get_fields as $field ) {
                
                /*
                 * splitting the query variables into key-value pair
                 * and adding them back to the $oauth_credentials variable
                 */
                $split = explode( '=', $field );
                $oauth_credentials[ $split[0] ] = $split[1];
            }
            
            
            
        }//edns if
        
        /* Generate the signature base string */
        $signature_base_string = $this->rc_myctf_build_signature_base_string( $request_method, $oauth_credentials );
        
        /* Finally create the OAuth Signature and add it to the $oauth_credentials array */
        $oauth_credentials[ 'oauth_signature' ] = $this->rc_myctf_generate_oauth_signature( $signature_base_string );
        
        
        //save the request_url for use by HTTP API
        //$this->rc_myctf_request_url = $request_url;
        
        //save the OAuth Details
        $this->rc_myctf_oauth_details = $oauth_credentials;
        $this->rc_myctf_request_method = $request_method;
        
        return $this;
        
    }//ends rc_myctf_build_oauth

    

    /**
     * Stores the POST parameters
     * 
     * @since 1.2
     * 
     * @param array $post_fields array of POST parameters
     * @return $this
     */
    public function rc_myctf_set_post_fields( array $post_fields ) {
        
        $this->rc_myctf_post_fields = $post_fields;
        return $this;
        
    }//ends rc_myctf_set_post_fields
    
    
    
    /**
     * Store the GET Parameters
     * 
     * @since 1.2
     * 
     * @param string $get_field
     * @return $this
     */
    public function rc_myctf_set_get_field( $get_fields ) {
        
        $this->rc_myctf_get_fields = strip_tags( $get_fields );
        
        return $this;
        
    }//ends rc_myctf_set_get_field
    
    
    
    /**
     * Create a signature base string from the list of arguments
     * Signature base string requires these 3 parts
     * request URL, request method, and OAuth parameters.
     * 
     * @since 1.2
     * 
     * @param string $request_url request URL or endpoint
     * @param string $request_method Request method GET or POST
     * @param array $oauth_params Twitter's OAuth parameters
     * 
     * @return string
     */
    private function rc_myctf_build_signature_base_string( $request_method, $oauth_params ) {
        
        
        //retrieve the $request_property of the class
        $request_url = esc_url( $this->rc_myctf_request_url );
        
        //save the parameters as key value pair bounded together with '&'
        $string_params = array();
        
        /* sorting the keys in alphabetical order */
        ksort( $oauth_params );
        
        foreach ( $oauth_params as $key => $value ) {
            //convert to oauth parameters to key-value pair
            $string_params[] = "$key=$value";
        }
        
        return "$request_method&" . rawurlencode( $request_url ) . '&' . rawurlencode(implode( '&', $string_params) );
        
    }//ends rc_myctf_build_signature_base_string
    
    
    
    /*
     * Accepts the signature base string and generates the OAuth Signature
     * 
     * @since 1.2
     * 
     * @param string $signature_base_string
     * @return string $oauth_signature
     */
    private function rc_myctf_generate_oauth_signature( $signature_base_string ) {
        
        //generating the signing key required for the OAuth Signature
        $signing_key = rawurlencode( $this->rc_myctf_consumer_secret ) . '&' . rawurlencode( $this->rc_myctf_oauth_access_token_secret );
        
        /* Creating the OAuth Signature */
        $oauth_signature = base64_encode( hash_hmac( 'sha1', $signature_base_string, $signing_key, true ) );
        
        return $oauth_signature;
        
    }//ends rc_myctf_generate_oauth_signature
    
    
    
    /*
     * Generate the authorization HTTP header
     * 
     * @return string
     * @since 1.2
     */
    public function rc_myctf_authorization_header() {
        
        $header = 'OAuth ';
        
        $oauth_params = array();
        foreach ( $this->rc_myctf_oauth_details as $key => $value ) {
            
            $oauth_params[] = "$key=\"" . rawurlencode( $value ) . '"';
        }
        
        $header .= implode( ', ', $oauth_params );
        
        return $header;
        
    }//ends rc_myctf_authorization_header
    
    
    
    /*
     * Process the return the JSON result
     * 
     * @since 1.2
     * @return string
     */
    public function rc_myctf_process_request() {
        
        $header = $this->rc_myctf_authorization_header();
        
        $args = array(
            'headers' => array( 'Authorization' => $header ),
            'timeout' => 45,
            'sslverify' => FALSE
        );
        
        if ( !is_null( $this->rc_myctf_post_fields ) ) {
            
            $args[ 'body' ] = $this->rc_myctf_post_fields;
            
            $response = wp_remote_post( $this->rc_myctf_request_url, $args );
            
            return $response;
            //return wp_remote_retrieve_body( $response );
        } else {
            
            //add the GET parameters to the Twitter request url or endpoint
            $url = $this->rc_myctf_request_url . $this->rc_myctf_get_fields;
            
            $response = wp_remote_get( $url, $args );
            return $response;
            
            //return wp_remote_retrieve_body( $response );
            
        }//ends if
        
    }//ends rc_myctf_process_request
    
    
    
    /**
     * This function returns the relevant $request_url based on the $feed_type
     * 
     * @since 1.2
     * @access private
     * 
     * @param string $feed_type Current feed type for the shortcode
     * @return string Twitter base URL where request needs to be sent
     */
    private function rc_myctf_get_base_url_based_on_feed_type( $feed_type ) {
        
        /* set request_url as empty */
        $request_url = '';
        
        /* select Twitter base URL based on feed_type */
        if ( $feed_type == 'user_timeline' ) {
            
            $request_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
            
        } else if( $feed_type == 'hashtags_timeline' || $feed_type == 'search_timeline' ) {
            
            $request_url = 'https://api.twitter.com/1.1/search/tweets.json';
            
        }//ends if
        
        return $request_url;
        
    }//ends rc_myctf_get_base_url_based_on_feed_type
    
    
}//ends Rc_Myctf_Twitter_Connect
