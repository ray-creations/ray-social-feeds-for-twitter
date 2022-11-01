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


class Rsfft_Cache {
    
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
    
    /**
     * Adds action hooks & filters needed for this class.
     * 
     * @since 1.2.3
     */
    public function rsfft_cache_hooks() {
        
        /* Deletes the "tweets cache" stored in transient for only those shortcodes that are present on the saved post/page.
         * @since 1.2.3
         */
         add_action( 'save_post', array( $this, 'rsfft_delete_cached_tweets_for_scodes_on_saved_page' ) );
         
    }//ends rsfft_cache_hooks
    
    
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
    public function rsfft_get_cache( $id ) {
        
        /* if id of the shortcode is not provided, return false */
        if ( empty( $id ) ) {
            return FALSE;
        }
        
        /* 
         * set the transient name according to $id
         * so each shortcode has a different $transient_name
         */
        $this->transient_name = 'rsfft_cached_tweets_' . $id;
        
        $rsfft_cached_tweets = get_transient( $this->transient_name );
        
        //echo 'Fetched Tweets with Transient id: ' . $this->transient_name;
        
        if ( FALSE === $rsfft_cached_tweets ) {
            return FALSE;
        } else {
            return $rsfft_cached_tweets;
        }
        
        return FALSE;
        
    }//ends function rsfft_get_cache
    
    
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
    public function rsfft_set_cache( $id, $tweets ) {
        
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
        $this->transient_name = 'rsfft_cached_tweets_' . $id;
        
        
        /* Get cache duration set by the user in settings page */
        $duration = Rsfft::rsfft_get_tweet_cache_duration();
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
    public function rsfft_delete_tweets_transient( $id = '' ) {
        
        if ( !empty( $id ) ) {
            
            $this->transient_name = 'rsfft_cached_tweets_' . $id;
            $deleted = delete_transient( $this->transient_name );
            
            //if cache transient has been successfully deleted, also remove it from the 'rsfft_scodes_trans' option
            if ( $deleted ) {
                $this->rsfft_delete_transient_from_options( $id );
            }
            
        } else {
            
            /* 
             * Delete all transients 
             * Transient details are noted in the option 'rsfft_scodes_trans' in this format:
             *  {
             *      0: '',
             *      post_id_1872_1: "rsfft_cached_tweets_post_id_1872_1",
             *      post_id_1874_1: "rsfft_cached_tweets_post_id_1874_1",
             *      post_id_1876_1: "rsfft_cached_tweets_post_id_1876_1",
             *  }
             * 
             */
            $rsfft_scodes_trans = get_option( 'rsfft_scodes_trans' );
            
            foreach ( $rsfft_scodes_trans as $shortcode_id => $transient_name ) {
                
                /* Check to see that $shortcode_id has a value. If yes, delete it. */
                if ( isset( $rsfft_scodes_trans[ $shortcode_id ] ) ) {
                    delete_transient( $transient_name );
                }
                
            }//ends foreach
            
            //now delete all transient records from the option 'rsfft_scodes_trans'
            $this->rsfft_delete_transient_from_options();
            
            return true;
            
        }//end if
        
    }//ends rsfft_delete_tweets_transient
    
    
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
    public function rsfft_add_update_transient_name_to_options( $shortcode_id  ) {
        
        //return if $shortcode_id is empty
        if ( empty( $shortcode_id ) ) {
            return;
        }
        
        $transient_name = 'rsfft_cached_tweets_' . $shortcode_id;
        
        $options = (array)get_option( 'rsfft_scodes_trans' );
        
        if ( $options == FALSE ) {
            add_option( 'rsfft_scodes_trans' );
        }
        
        $options[ $shortcode_id ] = $transient_name;
        update_option( 'rsfft_scodes_trans', $options );
        
        
    }//ends function rsfft_add_update_transient_name_to_options
    
    
    
    /**
     * This function deletes the cache transient from the option 'rsfft_scodes_trans'
     * It has the following structure:
     * {
     *      0: '',
     *      post_id_1872_1: "rsfft_cached_tweets_post_id_1872_1",
     *      post_id_1874_1: "rsfft_cached_tweets_post_id_1874_1",
     *      post_id_1876_1: "rsfft_cached_tweets_post_id_1876_1",
     *  }
     * 
     * If you supply the individual id of the shortcode, which is the key of the record, 
     * then only that particualar record will be deleted.
     * 
     * Otherwise, all the records in the option will be deleted.
     * 
     * @since 1.2.3
     * @access public
     * 
     * @param   string      $shortcode_id   key of the record that needs to be deleted
     * @return  boolean                     Returns either true or false.
     */
    public function rsfft_delete_transient_from_options( $shortcode_id = '' ) {
        
        //retrieve the options
        $scodes_trans = (array)get_option( 'rsfft_scodes_trans' );
        
        /*
         * if key is provided, delete that particular record.
         * Otherwise delete all.
         */
        if ( !empty( $shortcode_id ) ) {
            unset( $scodes_trans[ $shortcode_id ] );
        } else {
            
            //iterate through the option array and delete each record one by one
            foreach ( $scodes_trans as $scode_id => $trans_name ) {
                unset( $scodes_trans[ $scode_id ] );
            }//ends foreach
            
        }//ends if
        
        //now update the manipulated value back to the options 'rsfft_scodes_trans'
        $updation_status = update_option( 'rsfft_scodes_trans', $scodes_trans );
        
        //returns either true of false.
        return $updation_status;
        
    }//ends rsfft_delete_transient_from_options
    
    
    
    
    /**
     * Deletes the "tweets cache" stored in transient for only those 
     * shortcodes that are present on the page or post that have been saved.
     * 
     * This function is called on the 'save_post' hook, which passes the "post_id" 
     * of the saved post/page. 
     * 
     */
    function rsfft_delete_cached_tweets_for_scodes_on_saved_page( $post_id ) {
        
            $post_obj = get_post( $post_id );
            $content = $post_obj->post_content;
            
            /*
             * fetch list of shortcodes present in the above page/post content
             * returns false if no shortcode found.
             */
            $rsfft_scodes = $this->rsfft_get_shortcodes_on_page( $content );
            
            if ( $rsfft_scodes === false ) {
                return;
            }
            
            //count number of shortcodes in array
            $scodes_count = count( $rsfft_scodes );
            
            /*
             * Shortcode id to be sent to the transient delete function.
             * Shortcode is should be in the below format:
             * Id format: id="post_id_53_1" (post_id + post_id + unique shortcode postion on page)
             */
            
            //construct shortcode ids the above shortcodes
            for ( $i=1; $i <= $scodes_count; $i++ ) {
                
                //creating the shortcode id
                $scode_id = 'post_id_' . $post_id . '_' . $i;
                $this->rsfft_delete_tweets_transient( $scode_id );
                
            }
            
        
    }//ends rsfft_delete_cached_tweets_for_scodes_on_saved_page
    
    
    
    
    /**
     * Get Shortcodes on page/post in an array. If $post_id is provided, will fetch 
     * shortcodes from that page only. If no shortcode is provide, the post_id will 
     * be fetched from the global $post variable.
     * 
     * Returns false if no shortcodes present.
     * Returns array in this format:
     * 
     * {
     *      0: "[my_custom_tweets feed_type="user_timeline"]",
     *      1: "[my_custom_tweets feed_type="user_timeline" screen_name="MySwitzerlandIN"]"
     *  }
     * 
     * @since 1.2.3
     * 
     * @param object $content Post/Page content
     * @return array/false Returns either an array of scodes or False
     */
    public function rsfft_get_shortcodes_on_page( $content ) {
        
        $matches = array();
        
        
        /*
         * get_shortcode_regex() provides the regular expression needed to match shortcodes on the page
         */
        preg_match_all( '/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER );
        
        
        /*
         * #matches holds the shortcodes in this form:
         * - 0: {
         *          0: "[my_custom_tweets feed_type="user_timeline"]",
         *          1: "",
         *          2: "my_custom_tweets",
         *          3: " feed_type="user_timeline"",
         *          4: "",
         *      }
         * - 1: {
         *          0: "[my_custom_tweets feed_type="user_timeline" screen_name="MySwitzerlandIN"]",
         *          1: "",
         *          2: "my_custom_tweets",
         *          3: " feed_type="user_timeline" screen_name="MySwitzerlandIN"",
         *          4: "",
         *      }
         * 
         */

        //ensure that shortcodes have been found
        if ( empty( $matches ) ) {
            return false;
        }

        
        /* 
         * $my_org_shortcodes holds the instances of our original shortcodes in this format:
         *  {
         *      0: "[my_custom_tweets feed_type="user_timeline"]",
         *      1: "[my_custom_tweets feed_type="user_timeline" screen_name="MySwitzerlandIN"]"
         *  }
         */
        $rsfft_scodes = array();

        foreach ( $matches as $shortcodes ) {

            if ( RSFFT_SCODE_STR === $shortcodes[2] ) {
                $rsfft_scodes[] = $shortcodes[0];
            }
        }
        
        //if $rsfft_scodes[] is empty, return false.
        if ( empty( $rsfft_scodes ) ) {
            return false;
        }
        
        return $rsfft_scodes;
        
    }//ends rsfft_get_shortcodes_on_page
    
    
    
    
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

$rsfft_cache = Rsfft_Cache::get_instance();
add_action( 'plugins_loaded', array( $rsfft_cache, 'rsfft_cache_hooks' ) );