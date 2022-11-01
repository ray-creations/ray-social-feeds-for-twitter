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


class Rsfft_Twitter_Connect {
    
    /**
     *  @var string Oauth access token 
     */
    private $rsfft_oauth_access_token;
    
    
    /** 
     * @var string Oauth access token secret 
     */
    private $rsfft_oauth_access_token_secret;
    
    
    /** 
     * @var string Consumer key 
     */
    private $rsfft_consumer_key;
    
    
    /** 
     * @var string Consumer Secret 
     */
    private $rsfft_consumer_secret;
    
    
    /**
     * @var array POST parameters
     */
    private $rsfft_post_fields;
    
    
    /**
     * @var string GET parameters
     */
    private $rsfft_get_fields;
    
    
    /**
     * @var array OAuth Credentials
     */
    private $rsfft_oauth_details;
    
    
    /**
     * @var string Twitter's request URL
     */
    private $rsfft_request_url;
    
    
    /**
     * @var string Request method
     */
    private $rsfft_request_method;
    
    
    /*
     * @var string feed_type [user_timeline, search_timeline, hashtags_timeline]
     */
    private $rsfft_feed_type;
    
    
    
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
        $this->rsfft_oauth_access_token = sanitize_text_field( $settings[ 'oauth_access_token' ] );
        $this->rsfft_oauth_access_token_secret = sanitize_text_field( $settings[ 'oauth_access_token_secret' ] );
        $this->rsfft_consumer_key = sanitize_text_field( $settings[ 'consumer_key' ] );
        $this->rsfft_consumer_secret = sanitize_text_field( $settings[ 'consumer_secret' ] );
        
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
    public function rsfft_build_oauth( $feed_type, $request_method ) {
        
        /** Make sure the request is either POST or GET */
        if ( !in_array( strtolower( $request_method ), array( 'post', 'get' ) ) ) {
            return new WP_Error( 'invalid_request', 'Request method must be either POST or GET' );
        }
        
        /* sanitize $feed_type input */
        $san_feed_type = wp_strip_all_tags( $feed_type );
        
        /* set $request_url class property according based on $feed_type */
        $this->rsfft_request_url = $this->rsfft_get_base_url_based_on_feed_type( $san_feed_type );
        
        
        /* building the $oauth_credentials value */
        $oauth_credentials = array(
            'oauth_consumer_key' => $this->rsfft_consumer_key,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $this->rsfft_oauth_access_token,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );
        
        if ( !is_null( $this->rsfft_get_fields ) ) {
            
            // $this->rsfft_get_fields format: ?screen_name=raycreations&count=10&exclude_replies=1&include_rts=0&tweet_mode=extended
            //removing question mark and at the same time converting to an array with key-value pairs
            $get_fields = str_replace( '?', '', explode( '&', $this->rsfft_get_fields ) );
            
            /*
             * After explode and str_replace we get this.
             * $get_fields is an array, and has a value similar to below:
             * {
             *      0: "screen_name=raycreations",
             *      1: "count=10",
             *      2: "exclude_replies=1",
             *      3: "include_rts=0",
             *      4: "tweet_mode=extended"
             *   },
             */
            
            foreach( $get_fields as $field ) {
                
                /*
                 * splitting the query variables into key-value pair
                 * and adding them back to the $oauth_credentials variable
                 * 
                 * after explode, "$split" has similar value to this:
                 * Array ( 
                 *          [0] => screen_name, 
                 *          [1] => raycreations 
                 * )
                 */
                $split = explode( '=', $field );
                $oauth_credentials[ $split[0] ] = $split[1];
            }
            
            /*
             * After the above foreach loop $oauth_credentials has value similar to below:
             * 
             * Array (
             *      "oauth_consumer_key" => "*******b2xR5***********",
             *      "oauth_nonce" => "1649754557",
             *      "oauth_signature_method" => "HMAC-SHA1",
             *      "oauth_token" => "23152579-TIIy0wjF4JqxmbvpSt6yhgteDWRcf80t6qBnHmuAt",
             *      "oauth_timestamp" => "1649754557",
             *      "oauth_version" => "1.0",
             *      "screen_name" => "raycreations",
             *      "count" => "10",
             *      "exclude_replies" => "1",
             *      "include_rts" => "0",
             *      "tweet_mode" => "extended"
             *  )
             */
            
        }//edns if
        
        /*
         * The returned URL is rawurlencode() ed.
         * But without rawurlencode() funtion, the returned '$signature_base_string' looks similar to this:
         * 
         * GET&https://api.twitter.com/1.1/statuses/user_timeline.json&count=10&exclude_replies=1&include_rts=0
         * &oauth_consumer_key=LiuyhTjtkjiuhgtfrERDsSPvKWM1Hu&oauth_nonce=1649757862&oauth_signature_method=HMAC-SHA1
         * &oauth_timestamp=1649757862&oauth_token=81418579-TIIy0wjF4JqxmbvpStQ2u3FeDWRcf80t6qBnHmuAt
         * &oauth_version=1.0&screen_name=raycreations&tweet_mode=extended
         */
        $signature_base_string = $this->rsfft_build_signature_base_string( $request_method, $oauth_credentials );
                
        /* 
         * Finally create the OAuth Signature. i.e. encodes & encrypt the '$signature_base_string'
         * and adds it to the $oauth_credentials array 
         * 
         */
        $oauth_credentials[ 'oauth_signature' ] = $this->rsfft_generate_oauth_signature( $signature_base_string );
        
        
        //save the request_url for use by HTTP API
        //$this->rsfft_request_url = $request_url;
        
        //save the OAuth Details
        $this->rsfft_oauth_details = $oauth_credentials;
        $this->rsfft_request_method = $request_method;
        
        return $this;
        
    }//ends rsfft_build_oauth

    

    /**
     * Stores the POST parameters
     * 
     * @since 1.2
     * 
     * @param array $post_fields array of POST parameters
     * @return $this
     */
    public function rsfft_set_post_fields( array $post_fields ) {
        
        $this->rsfft_post_fields = $post_fields;
        return $this;
        
    }//ends rsfft_set_post_fields
    
    
    
    /**
     * Store the GET Parameters
     * $get_field format: 
     * ?screen_name=raycreations&count=10&exclude_replies=1&include_rts=0&tweet_mode=extended
     * 
     * @since 1.2
     * 
     * @param string $get_field
     * @return $this
     */
    public function rsfft_set_get_field( $get_fields ) {
        
        $this->rsfft_get_fields = strip_tags( $get_fields );
        
        return $this;
        
    }//ends rsfft_set_get_field
    
    
    
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
    private function rsfft_build_signature_base_string( $request_method, $oauth_params ) {
        
        
        //retrieve the $request_property of the class
        $request_url = esc_url( $this->rsfft_request_url );
        
        //save the parameters as key value pair bounded together with '&'
        $string_params = array();
        
        /* sorting the keys in alphabetical order */
        ksort( $oauth_params );
        
        foreach ( $oauth_params as $key => $value ) {
            //convert to oauth parameters to key-value pair
            $string_params[] = "$key=$value";
        }
        
        /*
         * $string_params now has a value similar to below:
         * 
         * {
         *      0: "count=10",
         *      1: "exclude_replies=1",
         *      2: "include_rts=0",
         *      3: "oauth_consumer_key=Djttykb2xR5sSPvKWMs9uu1Hu",
         *      4: "oauth_nonce=1649757089",
         *      5: "oauth_signature_method=HMAC-SHA1",
         *      6: "oauth_timestamp=1649757089",
         *      7: "oauth_token=81418579-TIIy0wjF4JqxmbvpStQ2u3FeDWRcf80t6qBnHmuAt",
         *      8: "oauth_version=1.0",
         *      9: "screen_name=raycreations",
         *      10: "tweet_mode=extended"
         *  }
         */
        
        /*
         * Without rawurlencode() funtion, the returned URL looks similar to this:
         * GET&https://api.twitter.com/1.1/statuses/user_timeline.json&count=10&exclude_replies=1&include_rts=0
         * &oauth_consumer_key=LiuyhTjtkjiuhgtfrERDsSPvKWM1Hu&oauth_nonce=1649757862&oauth_signature_method=HMAC-SHA1
         * &oauth_timestamp=1649757862&oauth_token=81418579-TIIy0wjF4JqxmbvpStQ2u3FeDWRcf80t6qBnHmuAt
         * &oauth_version=1.0&screen_name=raycreations&tweet_mode=extended
         */
        
        return "$request_method&" . rawurlencode( $request_url ) . '&' . rawurlencode(implode( '&', $string_params) );

    }//ends rsfft_build_signature_base_string
    
    
    
    /*
     * Accepts the signature base string and generates the OAuth Signature
     * This function encodes & encrypts the data.
     * 
     * @since 1.2
     * 
     * @param string $signature_base_string
     * @return string $oauth_signature
     */
    private function rsfft_generate_oauth_signature( $signature_base_string ) {
        
        //generating the signing key required for the OAuth Signature
        $signing_key = rawurlencode( $this->rsfft_consumer_secret ) . '&' . rawurlencode( $this->rsfft_oauth_access_token_secret );
        
        /* Creating the OAuth Signature */
        $oauth_signature = base64_encode( hash_hmac( 'sha1', $signature_base_string, $signing_key, true ) );
        
        return $oauth_signature;
        
    }//ends rsfft_generate_oauth_signature
    
    
    
    /*
     * Generate the authorization HTTP header
     * 
     * @return string
     * @since 1.2
     */
    public function rsfft_authorization_header() {
        
        $header = 'OAuth ';
        
        $oauth_params = array();
        foreach ( $this->rsfft_oauth_details as $key => $value ) {
            
            $oauth_params[] = "$key=\"" . rawurlencode( $value ) . "\"";
        }
        
        /*
         * $oauth_params now has value similar to the this:
         * {
         *      0: "oauth_consumer_key="Mjtnjhb6Y65xR5sSPvKujhuy6hg1Hu"",
         *      1: "oauth_nonce="1649764622"",
         *      2: "oauth_signature_method="HMAC-SHA1"",
         *      3: "oauth_token="81418579-LIYy0wjF4JqmnjhgbvfdtQ2u3FeDWR98980t6qBnJmuAt"",
         *      4: "oauth_timestamp="1649764622"",
         *      5: "oauth_version="1.0"",
         *      6: "screen_name="raycreations"",
         *      7: "count="10"",
         *      8: "exclude_replies="1"",
         *      9: "include_rts="0"",
         *      10: "tweet_mode="extended"",
         *      11: "oauth_signature="jhe8GeTuvZ9nO7HAUId7EfDeB%2BU%3D""
         * }
         */
        
        $header .= implode( ', ', $oauth_params );
                
        /*
         * After implode function, $header will have a similar value as below:
         * OAuth oauth_consumer_key="Mjtnjhb6Y65xR5sSPvKujhuy6hg1Hu", oauth_nonce="1649768999", 
         * oauth_signature_method="HMAC-SHA1", oauth_token="81418579-LIYy0wjF4JqmnjhgbvfdtQ2u3FeDWR98980t6qBnJmuAt", 
         * oauth_timestamp="1649768999", oauth_version="1.0", screen_name="raycreations", count="10", 
         * exclude_replies="1", include_rts="0", tweet_mode="extended", oauth_signature="caQJr9ZKPIDX5HUSGIMM8xJgA6s%3D"
         */
        
        return $header;
        
    }//ends rsfft_authorization_header
    
    
    
    /*
     * Process the return the JSON result
     * 
     * @since 1.2
     * @return string
     */
    public function rsfft_process_request() {
        
        $header = $this->rsfft_authorization_header();
        
        $args = array(
            'headers' => array( 'Authorization' => $header ),
            'timeout' => 45,
            'sslverify' => FALSE
        );
        
        if ( !is_null( $this->rsfft_post_fields ) ) {
            
            $args[ 'body' ] = $this->rsfft_post_fields;
            
            $response = wp_remote_post( $this->rsfft_request_url, $args );
            
            return $response;
            //return wp_remote_retrieve_body( $response );
        } else {
            
            //add the GET parameters to the Twitter request url or endpoint
            $url = $this->rsfft_request_url . $this->rsfft_get_fields;
            
            $response = wp_remote_get( $url, $args );
            return $response;
            
            //return wp_remote_retrieve_body( $response );
            
        }//ends if
        
    }//ends rsfft_process_request
    
    
    
    /**
     * This function returns the relevant $request_url based on the $feed_type
     * 
     * @since 1.2
     * @access private
     * 
     * @param string $feed_type Current feed type for the shortcode
     * @return string Twitter base URL where request needs to be sent
     */
    private function rsfft_get_base_url_based_on_feed_type( $feed_type ) {
        
        /* set request_url as empty */
        $request_url = '';
        
        /* select Twitter base URL based on feed_type */
        if ( $feed_type == 'user_timeline' ) {
            
            $request_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
            
        } else if( $feed_type == 'mentions_timeline' ) {
            
            $request_url = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';
            
        } else if( $feed_type == 'hashtags_timeline' || $feed_type == 'search_timeline' ) {
            
            $request_url = 'https://api.twitter.com/1.1/search/tweets.json';
            
        }//ends if
        
        return $request_url;
        
    }//ends rsfft_get_base_url_based_on_feed_type
    
    
}//ends Rsfft_Twitter_Connect
