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


class Rc_Myctf_Tweets {
    
    
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
     * @param $atts     array   Attributes sent as part of the shortcode
     * @return $tweets  object  Returns tweets as object
     */
    public static function rc_myctf_fetch_tweets( $atts ) {
        
        /* 
         * check to see if Tweets are stored in the Transient 
         * if not fetch live
         */
        
        $id = !empty( $atts[ 'id' ] ) ? wp_strip_all_tags( $atts[ 'id' ] ) : '';
        $rc_myctf_cache = new Rc_Myctf_Cache();
        
        if ( false === ( $rc_myctf_cache->rc_myctf_get_cache( $id ) ) ) {
            
            /* Fetch fresh Tweets as stored tweets have expired */
            $raw_tweets = Rc_Myctf_Tweets::rc_myctf_fetch_live_tweets( $atts );
            
        } else {
            
            /* Retrieve the stored tweets or "false" if error */
            $raw_tweets = $rc_myctf_cache->rc_myctf_get_cache( $id );

        }
        //print_r($raw_tweets);
        //wp_die();
        /*
         * Check if $raw_tweets === false, then return false
         * Or check whether $raw_tweets is an object of WP Error class
         */
        if ( $raw_tweets === FALSE || is_wp_error( $raw_tweets ) || Rc_Myctf_Tweets::rc_myctf_tweets_contain_error_message( $raw_tweets ) ) {
            return FALSE;
        }
        
        
        /*
         * Check the feed_type from $merged_atts_options and return the tweet accordingly
         * For Tweet Searches/Hashtags the Tweets are wrapped in the 
         * object "statuses"
         */
        
        $merged_atts_options = Rc_Myctf::rc_myctf_fetch_merged_atts_options( $atts );
        $feed_type = wp_strip_all_tags( $merged_atts_options[ 'feed_type' ] );
        
        if ( $feed_type == 'hashtags_timeline' || $feed_type == 'search_timeline' ) {
            $tweets = $raw_tweets->statuses;
        } else {
            $tweets = $raw_tweets;
        }
        
        
        return $tweets;
        
    } //ends rc_myctf_fetch_tweets
    
    
    
    
    /*
     * Function to fetch tweets either live from Twitter or from Transient
     * 
     * @since 1.0
     * @access public
     * 
     * @param $atts array   Attributes as set in the shortcode by user
     * @return $tweets  object  List of tweets fetched from Twitter
     */
    public static function rc_myctf_fetch_live_tweets( $atts ){
        
        $id = !empty( $atts[ 'id' ] ) ? strip_tags( $atts[ 'id' ] ) : '';
        
        /* if there is no "id" associated with a shortcode, return "false" */
        if ( empty( $id ) ) {
            return FALSE;
        }
        
        /** get bearer token stored in options */
        $options_api = get_option( 'rc_myctf_settings_options' );
        $bearer_token = strip_tags( $options_api[ 'bearer_token' ] );
                
        $args = array(
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(
                'Authorization' => "Bearer $bearer_token"
            )
        );
        
        //add_filter( 'https_ssl_verify', '__return_false' );
        
        /* This function constructs and retuns the appropriate URL to query Twiiter */
        $api_url = Rc_Myctf_Tweets::rc_myctf_construct_twitter_api_url( $atts );
        
        $response = wp_remote_get( $api_url, $args );

        //echo 'Fetched URL: ' . $api_url . '<br>';
        
        if ( is_wp_error($response) ){
            //echo 'This is the error msg: ' . $response->get_error_message();
            return FALSE;
        }
                
        $tweets = json_decode( $response['body'] );
        
        /*
         *  sets the transient and returns the status as either true or false 
         * $status value can be used to 
         */
        $rc_myctf_cache = new Rc_Myctf_Cache();
        $status = $rc_myctf_cache->rc_myctf_set_cache( $id, $tweets );
        if ( $status == FALSE ) {
            //echo 'The transient for shortcode_id . "' . $id . '" could not be set.';
            //return FALSE;
        }
        
        return $tweets;
        
    }//Ends function rc_myctf_fetch_live_tweets
    
    
    
    
    
    
    /*
     * Constructs the Twitter API Url according to settings by the user
     * to fetch Tweets from Twitter
     * 
     * @since 1.0
     * @access public
     * 
     * @return $api_url string  URL string
     */
    public static function rc_myctf_construct_twitter_api_url( $atts ) {
        
        /* merged attributes and option values */
        $merged_atts_options = Rc_Myctf::rc_myctf_fetch_merged_atts_options( $atts );
        
        /* If there was an error, return false */
        if ( $merged_atts_options == FALSE ) {
            return FALSE;
        }
        
        /* Restricting count to 10 only for Free version */
        if ( $merged_atts_options[ 'count' ] > 10 ) {
            $merged_atts_options[ 'count' ] = 10;
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
        
        
        //echo 'Echoed feed type: ' . $feed_type . '<br><br>';
        
        /* if feed_type is 'user_timeline' */
        if ( $feed_type == 'user_timeline' ) {
            
            $api_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?';
            $api_url .= 'screen_name=' . urlencode( $screen_name );
            $api_url .= '&count=' . $count;
            $api_url .= '&exclude_replies=' . $exclude_replies;
            $api_url .= '&include_rts=' . $include_rts;
            $api_url .= '&tweet_mode=extended';
 
            
        } else if ( $feed_type == 'hashtags_timeline' || $feed_type == 'search_timeline' ) {
            
            $api_url = 'https://api.twitter.com/1.1/search/tweets.json?';
            
            if ( $feed_type == 'hashtags_timeline' ) {
                
                /* get the hashtags from options as string */
                //$hashtags_str = isset( $options_customize[ 'hashtags' ] ) ? $options_customize[ 'hashtags' ] : '';
                
                /* Function to return a formatted string of Hashtags to be used in Twitter URL */
                $hashtags = Rc_Myctf_Tweets::rc_myctf_get_hashtags_formatted_for_twitter_url( $hashtags_str );
                
                $api_url .= 'q=' . urlencode( $hashtags );
                
                //echo 'Fetched Hashtags Tweets: ' . $hashtags . '<br><br>';
                    
            } else {
                
                $api_url .= 'q=' . urlencode( $search_string );
                
                //echo 'Fetched Search Tweets: ' . $search_string . '<br><br>';
                
            }
            
            if ( $include_photos && !$include_videos ) {
                $api_url .= urlencode( ' AND filter:images' );
            }
            
            if ( $include_videos && !$include_photos ) {
                $api_url .= urlencode( ' AND filter:native_video' );
            }
            
            if ( $include_photos && $include_videos ) {
                $api_url .= urlencode( ' AND filter:media' );
            }
            
            if ( $exclude_replies == TRUE ) {
                $api_url .= urlencode( ' AND -filter:replies' );
            }
            
            if ( $include_rts == FALSE ) {
                $api_url .= urlencode( ' AND -filter:retweets' );
            }
            
            $api_url .= '&count=' . $count;
            $api_url .= '&include_entities=true';
            $api_url .= '&result_type=mix';
            $api_url .= '&tweet_mode=extended';
            
            
        }

        return $api_url;
        
    }//ends function rc_myctf_construct_twitter_api_url
    
    
    
    
    /*
     * Returns hashtags formatted for use in Twitter URL
     * 
     * @since 1.0
     * @access public
     * 
     * @param $hashtags_str string  List of hashtags in string format
     * @return $hashtags    string  String formatted for Twitter URL
     */
    public static function rc_myctf_get_hashtags_formatted_for_twitter_url( $hashtags_str ) {
        
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
    public static function rc_myctf_tweets_contain_error_message( $tweets ) {
        if ( isset( $tweets->errors ) ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }//ends rc_myctf_ensure_tweets_do_not_contain_error_message
    
}//ends class
