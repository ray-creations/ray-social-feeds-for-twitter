<?php

/**
 * Fetches Tweet from Twitter and manages its storage
 * and retrieval from Transient.
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


class Rsfft_Tweets {
    
    
    /*
     * Constructor Function
     * 
     * @since 1.0
     * @access public
     * @return void
     */
    public function __construct() {
        
    }
    
    
    /* Fetches Tweets either directly from Twitter or from Transient
     * 
     * @since 1.0
     * @access public
     * 
     * @return $tweets  object  Returns tweets as object
     */
    public static function rsfft_fetch_tweets() {
        
        /* 
         * check to see if Tweets are stored in the Transient 
         * if not fetch live
         */

        $scode_id = !empty( Rsfft::$merged_scode_atts[ 'id' ] ) ? wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'id' ] ) : '';
        $rsfft_cache = new Rsfft_Cache();
        
        if ( false === ( $rsfft_cache->rsfft_get_cache( $scode_id ) ) ) {
            
            //Fetch fresh Tweets as stored tweets have expired for this shortcode id
            $raw_tweets = Rsfft_Tweets::rsfft_fetch_live_tweets();
            
        } else {
            
            /* Retrieve the stored tweets or "false" if error */
            $raw_tweets = $rsfft_cache->rsfft_get_cache( $scode_id );

        }

        /*
         * Check if $raw_tweets === false, then return false
         * Or check whether $raw_tweets is an object of WP Error class
         */
        if ( $raw_tweets === FALSE || is_wp_error( $raw_tweets ) || Rsfft_Tweets::rsfft_tweets_contain_error_message( $raw_tweets ) ) {
            return FALSE;
        }
        
        
        /*
         * Check the feed_type from $merged_atts_options and return the tweet accordingly
         * For Tweet Searches/Hashtags the Tweets are wrapped in the 
         * object "statuses"
         */
        
        //$merged_atts_options = Rsfft::rsfft_fetch_merged_atts_options( $atts );
        $merged_atts_options = Rsfft::$merged_scode_atts;
        $feed_type = wp_strip_all_tags( $merged_atts_options[ 'feed_type' ] );
        
        if ( $feed_type == 'hashtags_timeline' || $feed_type == 'search_timeline' ) {
            $tweets = $raw_tweets->statuses;
        } else {
            $tweets = $raw_tweets;
        }
        
        
        return $tweets;
        
    } //ends rsfft_fetch_tweets
    
    
    
    
    /*
     * Function to fetch fresh tweets from Twitter
     * 
     * @since 1.0
     * @access public
     * 
     * @return $tweets  object  List of tweets fetched from Twitter
     */
    public static function rsfft_fetch_live_tweets() {
        
        $atts = Rsfft::$scode_atts;
        $id = !empty( $atts[ 'id' ] ) ? strip_tags( $atts[ 'id' ] ) : '';
        
        /* if there is no "id" associated with a shortcode, return "false" */
        if ( empty( $id ) ) {
            return FALSE;
        }
        
        $settings_option = get_option( 'rsfft_settings_options' );
        $oauth_access_token = isset( $settings_option[ 'access_token' ] ) ? sanitize_text_field( $settings_option[ 'access_token' ] ) : '';
        $oauth_access_token_secret = isset( $settings_option[ 'access_token_secret' ] ) ? sanitize_text_field( $settings_option[ 'access_token_secret' ] ) : '';
        $consumer_key = isset( $settings_option[ 'app_consumer_key' ] ) ? sanitize_text_field( $settings_option[ 'app_consumer_key' ] ) : '';
        $consumer_secret = isset( $settings_option[ 'app_consumer_secret' ] ) ? sanitize_text_field( $settings_option[ 'app_consumer_secret' ] ) : '';
        
        $settings = array(
            'oauth_access_token' => $oauth_access_token,
            'oauth_access_token_secret' => $oauth_access_token_secret,
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret
        );
        
        /* This function constructs and retuns the appropriate GET fields to query Twiiter */
        $get_fields_string = strip_tags( Rsfft_Tweets::rsfft_construct_get_fields_string() );
        
        $request_method = 'GET';
        
        /* get feed_type */
        $feed_type = strip_tags( Rsfft::$merged_scode_atts[ 'feed_type' ] );
        
        
        include_once( RSFFT_DIR . 'inc/class.rsfft-twitter-connect.php' );
        $twitter_instance = new Rsfft_Twitter_Connect( $settings );
        
        /*
         * $get_field_format:
         * ?screen_name=raycreations&count=10&exclude_replies=1&include_rts=0&tweet_mode=extended
         */
        
        $response = $twitter_instance
                ->rsfft_set_get_field( $get_fields_string )
                ->rsfft_build_oauth( $feed_type, $request_method )
                ->rsfft_process_request();
        
        
        if ( $response === FALSE || is_wp_error( $response ) ) {
            return FALSE;
        }
                
        $tweets = json_decode( $response['body'] );

        
        /*
         * Check if $tweets === false Or if $raw_tweets is an object of WP Error class
         * Also, check that $tweets itself does not contain error message from Twitter
         * if not, then save it to transient (cache)
         */
        if ( $tweets === FALSE || is_wp_error( $tweets ) || Rsfft_Tweets::rsfft_tweets_contain_error_message( $tweets ) ) {
            return FALSE;
        } else {
            
            $rsfft_cache = new Rsfft_Cache();
            $status = $rsfft_cache->rsfft_set_cache( $id, $tweets );
            
            /* if $status is false, meaning cache could not be set. Send error.  */
            if ( $status === FALSE ) {
                return new WP_Error( 'cache_could_not_be_set', 'Could not store the fetched live tweets to cache...' );
            }
            
        }//ends if
        
        return $tweets;
        
        
    }//ends rsfft_fetch_live_tweets_from_twitter

    
    
    
    /*
     * Constructs a string of the GET fields that will be sent to the 
     * class.rsfft-twitter-connect.php class.
     * 
     * @since 1.2
     * @access public
     * 
     * @return string   A string of the GET fields
     */
    public static function rsfft_construct_get_fields_string() {
        
        /* merged attributes and option values */
        $merged_atts_options = Rsfft::$merged_scode_atts;
        
        /* If there was an error, return false */
        if ( $merged_atts_options == FALSE ) {
            return FALSE;
        }
        
        
        /* Retrieve options from merged attributes & options variable */
        //$id = esc_attr( $merged_atts_options[ 'id' ] );
        $screen_name = sanitize_text_field( $merged_atts_options[ 'screen_name' ] );
        $count = (int) $merged_atts_options[ 'count' ];
        $exclude_replies = strip_tags( $merged_atts_options[ 'exclude_replies' ] );
        $include_rts = strip_tags( $merged_atts_options[ 'include_rts' ] );
        $feed_type = sanitize_text_field( $merged_atts_options[ 'feed_type' ] );
        $include_photos = strip_tags( $merged_atts_options[ 'include_photos' ] );
        $include_videos = strip_tags( $merged_atts_options[ 'include_videos' ] );
        
        $hashtags_str = sanitize_text_field( $merged_atts_options[ 'hashtags' ] );
        $search_string = sanitize_text_field( $merged_atts_options[ 'search_string' ] );
        
                
        /* if feed_type is 'user_timeline' */
        if ( $feed_type == 'user_timeline' ) {
            
            /* prefix a question mark (?) to the $get_fields string, as required by the Rsfft_Twitter_Connect class  */
            $get_fields = '?';
            
            $get_fields .= 'screen_name=' . rawurlencode( $screen_name );
            $get_fields .= '&count=' . $count;
            $get_fields .= '&exclude_replies=' . $exclude_replies;
            $get_fields .= '&include_rts=' . $include_rts;
            $get_fields .= '&tweet_mode=extended';
 
            
        } else if ( $feed_type == 'mentions_timeline' ) {
            
            /* prefix a question mark (?) to the $get_fields string, as required by the Rsfft_Twitter_Connect class  */
            $get_fields = '?';
            
            $get_fields .= 'count=' . $count;
            $get_fields .= '&include_entities=true';
            $get_fields .= '&tweet_mode=extended';
            
        } else if ( $feed_type == 'hashtags_timeline' || $feed_type == 'search_timeline' ) {
            
            /* prefix a question mark (?) to the $get_fields string, as required by the Rsfft_Twitter_Connect class  */
            $get_fields = '?';
            
            if ( $feed_type == 'hashtags_timeline' ) {
                
                /* Function to return a formatted string of Hashtags to be used in Twitter URL */
                $hashtags = Rsfft_Tweets::rsfft_get_hashtags_formatted_for_twitter_url( $hashtags_str );
                
                $get_fields .= 'q=' . rawurlencode( $hashtags );
                   
            } else {
                
                $get_fields .= 'q=' . rawurlencode( $search_string );
                                
            }
            
            if ( $include_photos && !$include_videos ) {
                $get_fields .= rawurlencode( ' AND filter:images' );
            }
            
            if ( $include_videos && !$include_photos ) {
                $get_fields .= rawurlencode( ' AND filter:native_video' );
            }
            
            if ( $include_photos && $include_videos ) {
                $get_fields .= rawurlencode( ' AND filter:media' );
            }
            
            if ( $exclude_replies == TRUE ) {
                $get_fields .= rawurlencode( ' AND -filter:replies' );
            }
            
            if ( $include_rts == FALSE ) {
                $get_fields .= rawurlencode( ' AND -filter:retweets' );
            }
            
            $get_fields .= '&count=' . $count;
            $get_fields .= '&include_entities=true';
            $get_fields .= '&result_type=mix';
            $get_fields .= '&tweet_mode=extended';
            
            
        }

        return $get_fields;
        
        
    }//ends rsfft_construct_get_fields_string



    /*
     * Returns hashtags formatted for use in Twitter URL
     * 
     * @since 1.0
     * @access public
     * 
     * @param $hashtags_str string  List of hashtags in string format
     * @return $hashtags    string  String formatted for Twitter URL
     */
    public static function rsfft_get_hashtags_formatted_for_twitter_url( $hashtags_str ) {
        
        /* Strip all characters except alphabets, digits, and whitespaces  */
        $hashtags_stripped = preg_replace( '/[^a-zA-Z0-9\s]/', '', $hashtags_str );
        $hashtags_stripped_sanitized = trim( strip_tags( stripslashes( $hashtags_stripped ) ) );
        
        //echo 'Special Characters Stripped Hashtags String: ' . $hashtags_stripped_sanitized . '<br><br>';
        
        /* convert to array by breaking up the string using space ' ' as delimiter */
        $hashtags_array = explode( ' ', $hashtags_stripped_sanitized );
        
        $counter = 1;
        $hashtags = '';
        foreach ( $hashtags_array as $hashtag ) {

            if ( $counter == 1 ) {

                // for first hashtag, we don't need the 'OR' added
                $hashtags .= '#' . $hashtag;
                $counter++;
            } else {
                $hashtags .= ' OR #' . $hashtag;
            }

        }// ends foreach

        return $hashtags;
    }
    
    
    
    /*
     * Ensures received Tweets do no contain any error messages.
     * Returns false on error
     * 
     * @since 1.0
     * @access public
     * 
     * @param object    @tweets object  Tweet object
     * @return boolean    True | False  Returns either true or false
     */
    public static function rsfft_tweets_contain_error_message( $tweets ) {
        if ( isset( $tweets->errors ) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }//ends rsfft_ensure_tweets_do_not_contain_error_message
    
    
}//ends class
