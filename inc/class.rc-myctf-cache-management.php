<?php

/*
 * Manages tweets retrieval and storage from Transient
 *
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


class Rc_Myctf_Cache {
    
    /**
     * Indicates whether the class has been initialized or not.
     * 
     * @since 1.0
     * @access private
     * @var boolean
     */
    private static $instance = false;
    
    
    /**
     * Name of the transient to look up or save
     * 
     * @since 1.0
     * @access private
     * @var object
     */
    private $transient_name;
    
    
    /**
    * construct function.
    *
    * @since  1.0.0
    * @access public
    * @return void
    */
    public function __construct() {
        
        $this->transient_name = '';
        
    }
    
    
    /*
     * Fetches Tweets from Transient if available
     * Otherwise returns false
     * 
     * Id format: id="post_id_53_1" (post_id + post_id + unique shortcode postion on page)
     * 
     * @since 1.0
     * @access public
     * 
     * @param $id       string  id of the shortcode
     * @return $tweets  object  Returns tweets as object
     */
    public function rc_myctf_get_cache( $id ) {
        
        /* if id of the shortcode is not provided, return false */
        if ( empty( $id ) ) {
            return FALSE;
        }
        
        /* 
         * set the transient name according to $id
         * so each shortcode has a different $transient_name
         */
        $this->transient_name = 'rc_myctf_cached_tweets_' . $id;
        
        $rc_myctf_cached_tweets = get_transient( $this->transient_name );
        
        //echo 'Fetched Tweets with Transient id: ' . $this->transient_name;
        
        if ( FALSE === $rc_myctf_cached_tweets ) {
            return FALSE;
        } else {
            return $rc_myctf_cached_tweets;
        }
        
        return FALSE;
        
    }//ends function rc_myctf_get_cache
    
    
    /*
     * Stores tweets in Transient
     * 
     * Id format: id="post_id_53_1" (post_id + post_id + unique shortcode postion on page)
     * 
     * @since 1.0
     * @access public
     * 
     * @param   $id     string      id of the shortcode
     * @param   $tweets object      tweets retrieved from Twitter
     * @return  $status bool        Either true or false 
     */
    public function rc_myctf_set_cache( $id, $tweets ) {
        
        /* if id of the shortcode is not provided, return false */
        if ( empty( $id ) ) {
            return FALSE;
        }
        
        /* Check if $tweet is empty. If so return false */
        if ( empty( (array)$tweets ) ) {
            return FALSE;
        }
        
        /* 
         * set the transient name according to $id
         * so each shortcode has a different $transient_name
         */
        $this->transient_name = 'rc_myctf_cached_tweets_' . $id;
        
        
        /* Get cache duration set by the user in settings page */
        $duration = Rc_Myctf::rc_myctf_get_tweet_cache_duration();
        //echo 'Cache duration is: ' . $duation;
        
        if ( $duration !== FALSE ) {
            /* Store the freshly fetched tweets in Transient */
            $status = set_transient( $this->transient_name, $tweets, $duration );
        
        } else {
            $status = FALSE;
        }
        
        /* either true of false, depending on whether the transient was set or not */
        return $status;
    }
    
    
    
    /*
     * Deletes stored tweets transients
     * If $id is supplied, it will delete only that particular transient
     * If $id is empty, it will delete all tweet transients 
     * 
     * Id format: id="post_id_53_1" (post_id + post_id + unique shortcode postion on page)
     * 
     * @since 1.0
     * @access public
     * 
     * @param   $id     string      id of the shortcode
     * @param   $tweets object      tweets retrieved from Twitter
     * @return  $status bool        Either true or false 
     */
    public function rc_myctf_delete_tweets_transient( $id = '' ) {
        
        if ( !empty( $id ) ) {
            
            $this->transient_name = 'rc_myctf_cached_tweets_' . $id;
            delete_transient( $this->transient_name );
            //echo 'Deleted transient: ' . $this->transient_name;
            
        } else {
            
            /* delete all transients */
            $rc_myctf_scodes_transients = get_option( 'rc_myctf_scodes_transients' );
            
            foreach ( $rc_myctf_scodes_transients as $shortcode_id => $transient_name ) {
                
                /* Check to see that $shortcode_id has a value. If yes, delete it. */
                if ( isset( $rc_myctf_scodes_transients[ $shortcode_id ] ) ) {
                    delete_transient( $transient_name );
                }
                
            }//ends foreach
            
            return true;
            
        }//end if
        
    }//ends rc_myctf_delete_tweets_transient
    
    
    
    /*
     * This function adds the '$shortcode_id' as the key
     * And '$transient_name' as its value.
     * 
     * This option keeps a record of all the shortcodes and 
     * their corresponding transients where the tweets for those 
     * shortcodes are stored.
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   $shortcode_id       string      id of the shortcode
     * 
     * @return  $status bool        Either true or false 
     */
    public function rc_myctf_add_update_transient_name_to_options( $shortcode_id  ) {
        
        $transient_name = 'rc_myctf_cached_tweets_' . $shortcode_id;
        
        $options = get_option( 'rc_myctf_scodes_transients' );
        $options[ $shortcode_id ] = $transient_name;
        update_option( 'rc_myctf_scodes_transients', $options );
        
        //print_r($options);
        
    }//ends function rc_myctf_add_update_transient_name_to_options


    
    /**
    * Returns the instance.
    *
    * @since  1.0.0
    * @access public
    * @return object
    */
   public static function get_instance() {

           if ( !self::$instance ) {
               self::$instance = new self;
           }
           return self::$instance;
   }
    
}//ends class

Rc_Myctf_Cache::get_instance();