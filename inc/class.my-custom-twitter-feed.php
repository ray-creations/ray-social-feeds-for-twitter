<?php

/**
 * Activates & Initializes the plugin
 *
 * @author Ray Creations
 */

/**
 * Ensures the page is not accessed directly
 */
if ( !defined( 'ABSPATH' ) ){
    exit;
}


class Rsfft {
    
    /**
     * Indicates whether the class has been initialized or not.
     * 
     * @since 1.0
     * @access private
     * @var boolean
     */
    private static $initiated = false;
    
    
    /**
     * Holds the external URLs found in Tweet.
     * 
     * @since 1.0
     * @access private
     * @var array
     */
    private static $external_url = array();
    
    
    /**
     * Holds the 'id's of the <divs> of the shortcodes
     * that are of display_style == 'display_slider_1_col' or display_style == 'display_slider_2_col'
     * 
     * @since 1.0
     * @access public
     * @var array
     */
    public static $scodes_slides = array();

    
    
    /**
     * Holds the particular shortcode attributes
     * So that it can be accessed across the plugin functions
     * 
     * since 1.2
     * @access public
     * @var array
     */
    public static $scode_atts = array();
    
    /**
     * Merged shortcode attributes. 
     * When attributes are mentioned in the shortcode $atts value, they take precedence.
     * Otherwise, defaults are taken from the options settings table
     * 
     * @since 1.2
     * @access public
     * @var array
     */
    public static $merged_scode_atts = array();
    
    
    /*
     * Array containing Tweet details like, is_retweet, retweet_user user object
     * 
     * It could be used to hold other items if required later on.
     * 
     * @since 1.2.4
     * @access public
     */
    public static $tweet_details = array();
    
    
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
        
        /* Add Shortcode for Tweets */
        add_shortcode( 'my_custom_tweets', array( 'Rsfft', 'rsfft_render_shortcode' ) );
        
        /* Check to see if shortcode exists for this page or post */
        add_filter( 'the_content', array( 'Rsfft', 'rsfft_add_unique_ids_to_shortcodes' ) );
        
        /* Localize the options needed during initialization of the owl slider & carousel */
        add_action( 'wp_footer', array( 'Rsfft', 'rsfft_owl_slides_carousel_localize_script' ) );
        
        
    } 
    
    
    /*
     * Function to render the tweet shortcode with Tweets
     * 
     * @since 1.0
     * @access public
     * 
     * @param array     $atts   Attributes added to shortcode by user
     * @return string
     */
    public static function rsfft_render_shortcode( $atts ){
        
        /* Before any action takes place we need to check if api keys are added. Else display appropriate message  */
        if ( ( $key_status = Rsfft::rsfft_check_api_keys() ) === FALSE ) {
            $error_html = Rsfft::rsfft_get_tweets_error_html( $error_type = 'keys' );
            return $error_html;
        }
        
        Rsfft::$scode_atts = Rsfft::rsfft_sanitize_atts($atts);                                     //save sanitized & validated $atts
        Rsfft::$merged_scode_atts = Rsfft::rsfft_fetch_merged_atts_options( Rsfft::$scode_atts );         //merged options
        if ( !RSFFT_IS_PRO ) {
            Rsfft::rsfft_reset_settings_reserved_for_pro();                                               //resets options reserved for pro in shortcode
        }                                                
        $display_style = Rsfft::$merged_scode_atts[ 'display_style' ];                                    //display_style for this shortcode
        

        /*
         * add/update the $shortcode_id and its corresponding $transient_name 
         * to the option 'rsfft_scodes_transients' options as key => value pair
         */
        $scode_id = strip_tags( Rsfft::$merged_scode_atts[ 'id' ] );
        $rsfft_cache = new Rsfft_Cache();
        $rsfft_cache->rsfft_add_update_transient_name_to_options( $scode_id );
        
        /* Fetch Tweets */
        $tweets = Rsfft_Tweets::rsfft_fetch_tweets();
        
//        print_r( $tweets );
//        wp_die();
        
        /* If error fetching tweets, display the error html box instead of the tweets */
        if ( $tweets === false ) {
            $error_html_box = Rsfft::rsfft_get_tweets_error_html( $error_type = 'tweets' );
            return $error_html_box;
        }
        
        /* if display_style is slider */
        if ( ($display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col') ) {
            require( RSFFT_DIR . 'views/display-style/slider.php' );
        }
        
        /* if display_style is either list or masonry  */
        if ( ($display_style == 'display_list' || $display_style == 'display_masonry' ) ) {
            require( RSFFT_DIR . 'views/display-style/list-masonry.php' );
        }
        
        return $html;
        
    }// Ends Shortcode rsfft_render_shortcode
    
    
    
    /* Twitter API sends Tweets without any links in them.
     * This function adds the links back to the entities retuns them as text
     * 
     * @since 1.0
     * @access public
     * 
     * @param $tweet    object  An individual tweet received from Twitter API
     * @param $text     string  The Tweet in text format.
     * @return $text    string  Returns the tweet in text format after adding links
     */
    public static function rsfft_add_links_to_tweet_entities( $tweet, $text ) {
        
        
        
        //check if links needs to be removed from mentions & hashtags
        $remove_links_hashtags = Rsfft::$merged_scode_atts[ 'remove_links_hashtags' ];
        $remove_links_mentions = Rsfft::$merged_scode_atts[ 'remove_links_mentions' ];
        $remove_ext_links = Rsfft::$merged_scode_atts[ 'remove_ext_links' ];
        $nofollow_ext_links = Rsfft::$merged_scode_atts[ 'nofollow_ext_links' ];
        $nofollow = $nofollow_ext_links ? ' nofollow' : '';
        
        
        foreach( $tweet->{ 'entities' } as $type => $entity ){
            if( $type == 'urls' && !$remove_ext_links ){
                foreach( $entity as $j => $url ){
                    $update_with = '<a href="' . $url->{ 'url' } . '" target="_blank" rel="noreferrer' . $nofollow . '" title="' . $url->{ 'expanded_url' } . '">' . $url->{ 'display_url' } . '</a>';
                    $text = str_replace( $url->{ 'display_url' }, $update_with, $text );
                } 
            } else if( $type == 'hashtags' && !$remove_links_hashtags ){
                foreach( $entity as $j => $hashtag ){
                    $update_with = '<a href="https://twitter.com/search?q=%23' . $hashtag->{ 'text' } . '&src=hash" target="_blank" title="' . $hashtag->{ 'text' } . '">#' . $hashtag->{ 'text' } . '</a>';
                    $text = str_replace( '#' . $hashtag->{'text'}, $update_with, $text );
                }
            } else if( $type == 'user_mentions' & !$remove_links_mentions ){
                foreach( $entity as $j => $user ){
                    $update_with = '<a href="https://twitter.com/' . $user->{ 'screen_name' } . '" target="_blank" title="' . $user->{ 'name' } . '">@' . $user->{ 'screen_name' } . '</a>';
                    $text = str_replace( '@' . $user->{ 'screen_name' }, $update_with, $text );
                }
            }
        }
        
        return $text;
        
    } //ends rsfft_add_links_to_tweet_entities
    
    
    
    /*
     * Returns the HTML for display of media in tweets
     * 
     * Fetches either native media (images or videos) 
     * or external url og:image or the largest image on the page.
     * return value is false if there is an error
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public static function rsfft_get_media_display_html( $tweet ){
        
        // check whether user has opted to hide media or not, if TRUE, then simply return
        if ( Rsfft::$merged_scode_atts[ 'hide_media' ] ) { return FALSE; }

        $has_media = isset( $tweet->extended_entities ) ? true : false;    //$tweet->extended_entities object is set only when you have media
        $tweet_id = strip_tags( $tweet->id_str );                          //Tweet id. Will be used as the id of the div
        $tweetMedia = $has_media ? $tweet->extended_entities->media : '';  //Shortcut for Media object on set only when it exists.
        
        $media_count = '';                                                  //Keeps a track of the number of actual images generated for each tweet.
        $html = '';                                                         //$html to hold the media html
        
        //run this block only if tweet has images/videos attached.
        if ( !empty( $tweetMedia ) ) {
            
            //loop through the $tweetsMedia object to fetch multiple images where applicable
            foreach ( $tweetMedia as $i => $media ) {
                
                //if media is a video.
                if ( $media->type == 'video' ) {

                    //$video_url = esc_url( $media->video_info->variants[0]->url );
                    $video_url = Rsfft::rsfft_get_highest_bitrate_video( $media->video_info->variants );
                    
                    if ( $video_url ) {
                        $html .= '<div class="link_details_wrap my-tweet-video-' . ($i +1) . '">';
                            $html .= '<video id="' . esc_attr( $tweet_id ) . '" class="tweet-video" controls><source src="' . esc_attr( $video_url ) . '" type="video/mp4"></video>';
                        $html .= '</div>';
                        
                        //increment the $media_count variable. It gets added to the '.tweet-media' class like '.tweet-media-2'.
                        $media_count = '-' . ($i +1);  
                    }

                }//ends if

                //if media has images.
                if ( $media->type == 'photo' ){
                    
                    $image_url = esc_url( $media->media_url_https );
                    if ( wp_http_validate_url( $image_url ) ) {
                        $html .= '<div id="' . esc_attr( $tweet_id ) . '-' . ($i + 1) . '" class="link_details_wrap my-tweet-photo-' . ($i + 1) . '">';
                            $html .= '<img alt="image" src="' . esc_attr( $image_url ) . '">';
                        $html .='</div>';
                        
                        //increment the $media_count variable. It gets added to the '.tweet-media' class like '.tweet-media-2'
                        $media_count = $media_count = '-' . ($i +1);;
                    }

                } //ends if

            }//ends foreach
        
        }//ends if
        
        //If native photos & videos don't exist. Check for external urls for title, desc, og:image or 'largest image'
        if ( !$has_media && !empty ( Rsfft::$external_url[0] ) ) {
            
            $external_url_image_html = Rsfft::rsfft_fetch_external_url_details();
            
            if ( !empty( $external_url_image_html ) ) {
                $html .= $external_url_image_html;
            }
            
        } //end if
        
        
        
        if ( !empty( $html )  ) {
            $returnHtml = "<div class='tweet-media tweet-media" . $media_count . "'>";     //opening outer div for media
                $returnHtml .= $html;                                           //html returned for the media.
            $returnHtml .= '</div>';                                            //closing outer div for media
            
            return $returnHtml;
        }
        
        return false;
        
        
    }//ends rsfft_get_media_display_html
    
    
    
    
    
    /**
     * Fetches the highest rate video from the video variants in a tweet
     * 
     * @since 1.2.4
     * @access public
     * 
     * @param obj $videoVariants Contains an array of videos with varying bitrate
     * @return string URL of the highest bitrate video
     */
    public static function rsfft_get_highest_bitrate_video( $videoVariants ) {

        $hightest_bitrate = 0;
        $highest_bitrate_video_url = '';
        
        foreach ( $videoVariants as $i => $video ) {
            
            if ( isset( $video->bitrate ) && $video->bitrate > $hightest_bitrate  ) {
                
                $hightest_bitrate = $video->{ 'bitrate' };
                $highest_bitrate_video_url = $video->{ 'url' };
                
            }//ends if
            
        }//ends foreach
        
        //returns either URL or FALSE on failure
        $video_url = wp_http_validate_url( $highest_bitrate_video_url );
        
        return $video_url;
        
    }//ends rsfft_get_highest_bitrate_video




    /**
     * Tweets often contain URLs that are not menant to be displayed, but are 
     * sent as part of the tweet. Here we are matching the URLs contained in the body 
     * of the tweet with the URLs present in "$tweet->entities->urls" object.
     * 
     * If a particular URL is not present in the "$tweet->entities->urls" object, we 
     * remove it from the tweet text. And return the text.
     * 
     * 
     * @since 1.0       Delete all URLs in the Tweet text
     * @since 1.2.4     Delete only URLs that don't exist in "$tweet->entities->urls" object
     * @access public
     * @param   obj     $tweet          An individual tweet object
     * @param   string  $text_with_url  Tweet text with unwanted URLs
     * @return string
     */
    public static function rsfft_remove_url_in_tweet_sent_as_text( $tweet, $text_with_url ){
        
        //$regex_url ='/(http|https):\/\/t\.co\/[a-zA-Z0-9\-\.]+/';
        //$text = preg_replace( $regex_url, '', $text_with_url );
        
        $text = $text_with_url;
        $regex_url = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
        $urlsInText = array();
        $urlsInEntities = array();
        Rsfft::$external_url = array();         //Make sure the external links array is always empty when the loop starts for each tweet
        
        
        if ( preg_match_all( $regex_url, $text, $urlsInText ) ) {
            
            /*
             * Loop through all the URLs sent in the entities->urls object 
             * and create an array of all URLs in "$tweet->entities->urls" object
             */
            foreach ( $tweet->entities->{ 'urls' } as $i => $urlDetails ) {
                $urlsInEntities[] = $urlDetails->{ 'url' };
                
                /* Store the URLs in an array */
                Rsfft::$external_url[] = $urlDetails->{ 'expanded_url' };
                
            }
            
            /**
             * $urlsInText[0] has URLs in the following format: 
             * {
             *      0: "https://t.co/lunlz72TRp",
             *      1: "https://t.co/STdzLjn5wO"
             *  }
             */
            
            //$urlsInText[0] contains all the URLs in this Tweet text
            foreach ( $urlsInText[0] as $urlInText ) {
                if ( !in_array( $urlInText, $urlsInEntities ) ) {
                    $text = str_replace( $urlInText, '', $text );
                }
            }//ends foreach
            
            /**
             * Now you have the same URLs in the "$tweet->entities->urls" object 
             * and in the tweet text.
             * 
             * Now replace all URLs in Tweet text with the 'display_url'
             */
            foreach ( $tweet->entities->{ 'urls' } as $i => $urlDetails ) {
                $text = str_replace( $urlDetails->{ 'url' }, $urlDetails->{ 'display_url' }, $text );
            }
            
        }//ends if
        
        return $text;
        
    }//ends rsfft_remove_url_in_tweet_sent_as_text

    
    
    
    /* Function to fetch relevant information from given external URL 
     * so as to create a preview of the URL page.
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public static function rsfft_fetch_external_url_details() {
        
        $external_url = esc_url( Rsfft::$external_url[0] );
        $img_status = FALSE;
        
        /* Declare $html variable */
        $html = '';
        
        /* Initialize empty array to hold the website details */
        $websiteDetails = array();

        /* check to see if $websiteDetails array is stored in the Transient */
        if ( false === ( $websiteDetails = get_transient( $external_url ) ) ) {

            /* Instantiate the Rsfft_Url_Preview with data as $obj1 */
            $obj1 = new Rsfft_Url_Preview( $external_url );
            
            /* if $obj1 === false, return false */
            if ( $obj1 === FALSE ) {
                return FALSE;
            }

            /*
             * Fill the array with with freshly filled url details 
             * It is empty if details could not be fetched for any reason or error.
             * If empty, return false.
             */
            $websiteDetails = $obj1->listWebsiteDetails();
            if ( empty( $websiteDetails ) ) {
                return FALSE;
            }
            

        } else {

            /* Retrieve the stored URL details from Transient */
            $websiteDetails = get_transient( $external_url );
            //echo 'fetched external image details from transient. External img url: ' . $external_url . '<br>';
        }//ends if

        
        /* 
         * Make sure that the external link details were fetched properly before displaying them
         * If $websiteDetails[ "Title" ] is empty, it means that the URLs details don't exist
         * for this link.
         * Therefore, hide the div with class="link_details_wrap"
         */
        if ( !empty( $websiteDetails[ "Title" ] ) ) {
            
            $html = '<div class="link_details_wrap">';
            
            /*
             * Title & Description should only be fetched if the external Url is
             * not Twitter or Instagram.
             */
            if ( Rsfft::rsfft_check_if_title_desc_should_be_fetched( $external_url ) === TRUE ) {
                
                $html .= '<div class="ext_link_title_desc">';
                $html .= '<span class="ext_link_title">' . esc_attr( $websiteDetails[ "Title" ] ) . '</span>';
                $html .= '<span class="ext_link_desc">' . esc_attr( $websiteDetails[ "Description" ] ) . '</span>';
                $html .= '</div>';
            }
            
            

            /*
             * Check for og:image. If available use that.
             * Otherwise use largest image selected and stored in 
             * $websiteDetails[ "LargestImgDetails" ][ "src" ]
             * 
             * $websiteDEtails[ "LargestImgDetails" ][ "width" ] is used by other 
             * functions to seclect the largest image.
             */
            if ( !empty( $websiteDetails[ "OgImage" ] ) ) {
                $img_status = wp_http_validate_url( $websiteDetails[ "OgImage" ] ) ? TRUE : FALSE;
                $html .= '<img src="' . esc_attr( $websiteDetails[ "OgImage" ] ) . '" alt="og:image">';

            } else if ( !empty ( $websiteDetails[ "LargestImgDetails" ][ "src" ] ) ) {
                $img_status = wp_http_validate_url( $websiteDetails[ "LargestImgDetails" ][ "src" ] ) ? TRUE : FALSE;
                $html .= '<img src="'. esc_attr( $websiteDetails[ "LargestImgDetails" ][ "src" ] ) . '" alt="Largest image">';
            }

            $html .= '</div>';

        }
        
        if ( $img_status ) {
            return $html;
        } else {
            return FALSE;
        }
        
    }//Ends function rsfft_fetch_external_url_details
    
    
    
    
    /* Returns the usernmae along with the verification badge if verfied.
     * 
     * @since 1.0
     * @access public
     * 
     * @param $user     object  Twitter user object
     * @return string
     */
    public static function rsfft_get_tweet_header( $tweet ){
        
        /* get the options */
        $display_style = Rsfft::$merged_scode_atts[ 'display_style' ];
        $display_profile_img_header = Rsfft::$merged_scode_atts[ 'display_profile_img_header' ];
        $display_name_header = Rsfft::$merged_scode_atts[ 'display_name_header' ];
        $display_screen_name_header = Rsfft::$merged_scode_atts[ 'display_screen_name_header' ];
        $display_date_header = Rsfft::$merged_scode_atts[ 'display_date_header' ];
        
        /* $user is assigned the Twitter User object */
        $user = $tweet->{ 'user' };
        
        /* $html variable to store header */
        $html = '';
        
        /**
         * This block handles the retweeted by section. This block only shows 
         * when the tweet is a retweet.
         */
        if ( Rsfft::$tweet_details[ 'is_retweet' ] ) {
            
            //fetch the retweet user object.
            $retweet_user = Rsfft::$tweet_details[ 'retweet_user' ];
            $retweeter_name = sanitize_text_field( $retweet_user->{ 'name' } );
            $retweeter_screen_name = sanitize_text_field( $retweet_user->{ 'screen_name' } );
            
            $html .= "<div class='rsfft-retweet-div'>";
                $retweet_svg_img = '<svg viewBox="0 0 24 24" aria-hidden="true" class="rsftt-retweet-sign-top"><g><path d="M23.615 15.477c-.47-.47-1.23-.47-1.697 0l-1.326 1.326V7.4c0-2.178-1.772-3.95-3.95-3.95h-5.2c-.663 0-1.2.538-1.2 1.2s.537 1.2 1.2 1.2h5.2c.854 0 1.55.695 1.55 1.55v9.403l-1.326-1.326c-.47-.47-1.23-.47-1.697 0s-.47 1.23 0 1.697l3.374 3.375c.234.233.542.35.85.35s.613-.116.848-.35l3.375-3.376c.467-.47.467-1.23-.002-1.697zM12.562 18.5h-5.2c-.854 0-1.55-.695-1.55-1.55V7.547l1.326 1.326c.234.235.542.352.848.352s.614-.117.85-.352c.468-.47.468-1.23 0-1.697L5.46 3.8c-.47-.468-1.23-.468-1.697 0L.388 7.177c-.47.47-.47 1.23 0 1.697s1.23.47 1.697 0L3.41 7.547v9.403c0 2.178 1.773 3.95 3.95 3.95h5.2c.664 0 1.2-.538 1.2-1.2s-.535-1.2-1.198-1.2z"></path></g></svg>';
                $html .= "<span>" . $retweet_svg_img . "</span>";
                $html .= "<span class='rsfft-retweeter-screenname'><a href='https://twitter.com/" . esc_attr( strtolower( $retweeter_screen_name ) ) . "' target='_blank' rel='noopener'>" . esc_attr( $retweeter_name ) . " Retweeted</a></span>";
            $html .= "</div>";
        }
        
        
        /* 
         * this is the profile image only for List type display style
         * profile image for other type of $display_style would be included
         * inside the header div (twitter_header_meta)
         */
        if ( $display_profile_img_header && $display_style == 'display_list' ) {
            $html .= "<img src='" . esc_url( $user->{ 'profile_image_url_https' } ) . "' alt='" . esc_attr( $user->{ 'name' } ) . "' class='twitter_profile_img' />";
        }//ends if
        
        /* the remaining header starts here */
        $html .= "<div class='twitter_header_meta'>";
        
            /**
             * Profile image where display style is not "display_list"
             */
            if ( $display_profile_img_header && $display_style != 'display_list' ) {
                $html .= "<img src='" . esc_url( $user->{ 'profile_image_url_https' } ) . "' alt='" . esc_attr( $user->{ 'name' } ) . "' class='twitter_inner_profile_img' />";
            }//ends if
        
        
            if ( $display_name_header ) {
                $html .= "<span class='name-of-tweeter'> ";
                    $html .= $user->{ 'name' };

                    if ( $user->verified == 1 ) {           
                        $svg_image ='<svg viewBox="0 0 24 24" aria-label="Verified account" class="verified_badge"><g><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.998-3.818-3.998-.47 0-.92.084-1.336.25C14.818 2.415 13.51 1.5 12 1.5s-2.816.917-3.437 2.25c-.415-.165-.866-.25-1.336-.25-2.11 0-3.818 1.79-3.818 4 0 .494.083.964.237 1.4-1.272.65-2.147 2.018-2.147 3.6 0 1.495.782 2.798 1.942 3.486-.02.17-.032.34-.032.514 0 2.21 1.708 4 3.818 4 .47 0 .92-.086 1.335-.25.62 1.334 1.926 2.25 3.437 2.25 1.512 0 2.818-.916 3.437-2.25.415.163.865.248 1.336.248 2.11 0 3.818-1.79 3.818-4 0-.174-.012-.344-.033-.513 1.158-.687 1.943-1.99 1.943-3.484zm-6.616-3.334l-4.334 6.5c-.145.217-.382.334-.625.334-.143 0-.288-.04-.416-.126l-.115-.094-2.415-2.415c-.293-.293-.293-.768 0-1.06s.768-.294 1.06 0l1.77 1.767 3.825-5.74c.23-.345.696-.436 1.04-.207.346.23.44.696.21 1.04z"></path></g></svg>';
                        $html .= '<span>' . $svg_image . '</span>';  
                    }
                $html .= "&nbsp;&nbsp;</span>";
            }//end if
        
            /* display screen name */
            if ( $display_screen_name_header ) {
                $html .= "<span class='screen_name'><a href='https://twitter.com/" . esc_attr( $user->{ 'screen_name' } ) . "' target='_blank' rel='noopener'>@" . esc_attr( $user->{ 'screen_name' } ) . "</a></span>";
            }
            
            /* display date */
            if ( $display_date_header ) {
                $html .= "<span class='tweet_date'><a href='https://twitter.com/" . esc_attr( $user->{ 'screen_name' } ) . "/status/" . esc_attr( $tweet->id_str ) . "' target='_blank' rel='noopener'>" . esc_attr( Rsfft::rsfft_format_date( $tweet->{ 'created_at' } ) ) . "</a></span>";
            }
            
        $html .= "</div>";

        return $html;
        
    }
    
    
    /* Returns the formatted twitter footer with like and share count.
     * 
     * @since 1.0
     * @access public
     * 
     * @param $tweet     object  Twitter user object
     * @return string
     */
    public static function rsfft_get_tweet_footer( $tweet ){
        
        /* get the options */
        $display_likes_footer = Rsfft::$merged_scode_atts[ 'display_likes_footer' ];
        $display_retweets_footer = Rsfft::$merged_scode_atts[ 'display_retweets_footer' ];
        $display_screen_name_footer = Rsfft::$merged_scode_atts[ 'display_screen_name_footer' ];
        $display_date_footer = Rsfft::$merged_scode_atts[ 'display_date_footer' ];
        
        /* $user is assigned the Twitter User object */
        $user = $tweet->{ 'user' };
        
        /* get the values of likes and retweets */
        $favorite_count = $tweet->{ 'favorite_count' };
        $retweet_count = $tweet->{ 'retweet_count' };
        
        /* $html variable to store header */
        $html = '';
        
        /* opening div */
        $html .= '<div class="rsfft_tweet_footer">';
        
        /* displaying heart */
        if ( $display_likes_footer ) {
            $heart_svg_img = '<svg viewBox="0 0 24 24" aria-label="Tweet favorite count" class="rsfft_twitter_heart"><g><path d="M12 21.638h-.014C9.403 21.59 1.95 14.856 1.95 8.478c0-3.064 2.525-5.754 5.403-5.754 2.29 0 3.83 1.58 4.646 2.73.814-1.148 2.354-2.73 4.645-2.73 2.88 0 5.404 2.69 5.404 5.755 0 6.376-7.454 13.11-10.037 13.157H12zM7.354 4.225c-2.08 0-3.903 1.988-3.903 4.255 0 5.74 7.034 11.596 8.55 11.658 1.518-.062 8.55-5.917 8.55-11.658 0-2.267-1.823-4.255-3.903-4.255-2.528 0-3.94 2.936-3.952 2.965-.23.562-1.156.562-1.387 0-.014-.03-1.425-2.965-3.954-2.965z"></path></g></svg>';
            $html .= '<span class="rsfft_like_wrap">';
            $html .= '<span>' . $heart_svg_img . '</span>';
            $html .= '<span class="rsfft_favorite_count">' . $favorite_count . '</span>';
            $html .= '</span>';
        }
        
        
        /* displaying retweet & count */
        if ( $display_retweets_footer ) {
            $retweet_svg_img = '<svg viewBox="0 0 24 24" aria-label="Retweet count" class="rsfft_retweet_sign"><g><path d="M23.77 15.67c-.292-.293-.767-.293-1.06 0l-2.22 2.22V7.65c0-2.068-1.683-3.75-3.75-3.75h-5.85c-.414 0-.75.336-.75.75s.336.75.75.75h5.85c1.24 0 2.25 1.01 2.25 2.25v10.24l-2.22-2.22c-.293-.293-.768-.293-1.06 0s-.294.768 0 1.06l3.5 3.5c.145.147.337.22.53.22s.383-.072.53-.22l3.5-3.5c.294-.292.294-.767 0-1.06zm-10.66 3.28H7.26c-1.24 0-2.25-1.01-2.25-2.25V6.46l2.22 2.22c.148.147.34.22.532.22s.384-.073.53-.22c.293-.293.293-.768 0-1.06l-3.5-3.5c-.293-.294-.768-.294-1.06 0l-3.5 3.5c-.294.292-.294.767 0 1.06s.767.293 1.06 0l2.22-2.22V16.7c0 2.068 1.683 3.75 3.75 3.75h5.85c.414 0 .75-.336.75-.75s-.337-.75-.75-.75z"></path></g></svg>';
            $html .= '<span class="rsfft_retweet_wrap">';
            $html .= '<span>' . $retweet_svg_img . '</span>';
            $html .= '<span class="rsfft_retweet_count">' . $retweet_count . '</span>';
            $html .= '</span>';
        }
        
        
        /* displaying footer screen name */
        if ( $display_screen_name_footer ) {
            $html .= "<span class='screen_name_footer'><a href='https://twitter.com/" . esc_attr( $user->{ 'screen_name' } ) . "' target='_blank' rel='noopener'>@" . esc_attr( $user->{ 'screen_name' } ) . "</a></span>";
        }
        
        /* displaying footer date */
        if ( $display_date_footer ) {
            $html .= "<span class='tweet_date_footer'><a href='https://twitter.com/" . esc_attr( $user->{ 'screen_name' } ) . "/status/" . esc_attr( $tweet->id_str ) . "' target='_blank' rel='noopener'>" . esc_attr( Rsfft::rsfft_format_date( $tweet->{ 'created_at' } ) ) . "</a></span>";
        }
        
        /* closing footer div */
        $html .= "</div>";

        return $html;
        
    }//ends rsfft_get_tweet_footer
    
    
    
    /* Returns a formatted date
     * 
     * @since 1.0
     * @access public
     * 
     * @param $date     string
     * @return DateTime object
     */
    public static function rsfft_format_date( $time_stamp_utc ) {
        
        $formatted_time = '';
        
        /* convert the Twitter tweet time to from Coordinated Universal Time (UTC) to DateTime object */
        $tweet_time = new DateTime( $time_stamp_utc );
        
        /* Get Unix Timestamp from DateTime object */
        $tweet_unix_timestamp = $tweet_time->getTimestamp();
        
        /* Get current time in Unix Timestamp */
        $now_unix_timestamp = time();
        
        /* Calculate the time difference between Tweet time and Now */
        $time_diff = $now_unix_timestamp - $tweet_unix_timestamp;
        
        
        if ( $time_diff >= 86400 ) {         
            
            /*
             * Checking if time difference is greater than or equal to 24 hours.
             */
            $formatted_time = date( "M j, Y", $tweet_unix_timestamp );
            
            //$formatted_time = $tweet_time->format('Y m d H:i:s');
            
        } else if ( $time_diff < 86400 && $time_diff >= 3600 ) {
            
            /*
             * Checking if less than 24 hours and more than or equal to 1 hour
             * in such a case we only want to show hours lapsed
             */
            $hours = intval( $time_diff /( 60*60 ) );
            $formatted_time = $hours . 'h';
            
        } else if ( $time_diff < 3600 && $time_diff >= 60 ) {
            
            /*
             * Checking if less than 1 hour and more than 60 seconds
             * in such a case we want to show minutes lapsed
             */
            $minutes = intval ( $time_diff / 60 );
            $formatted_time = $minutes . 'm';
            
        } else if ( $time_diff < 60 ) {
            
            /*
             * If less than 1 minute or 60 seconds. We will display seconds
             */
            $formatted_time = $time_diff . 's';
            
        }
        
        return $formatted_time;
    }
    
    
    /* Checks if URL is of a particular format like Twitter URL
     * In that case the URL details should not be fetched.
     * 
     * @since 1.0
     * @access public
     * 
     * @param   $external_url   string  A URL string
     * @return  $fetch          Boolean True or False value
     */
    public static function rsfft_check_if_url_should_be_fetched( $raw_external_url ) {
        
        $external_url = esc_url( $raw_external_url );
        
        //$needle = 'twitter.com';
        //$exist = strpos( $external_url, $needle );
        
        //if ( $exist === false ) {
          //  return TRUE;
        //}
        
        $needle_arr = array(
            'twitter.com',
            'youtu.be',
            'youtube.com'
        );
        
        $should_url_be_fetched = TRUE;
        
        foreach ( $needle_arr as $needle ) {
            $exist = strpos( $external_url, $needle );
            
            if ( $exist !== FALSE) {
                
                /* returning false meaning URL should not be fetched. */
                $should_url_be_fetched = FALSE;
                return $should_url_be_fetched;
            }
        }
        
        return $should_url_be_fetched;
        
    } //ends rsfft_check_if_url_should_be_fetched


    
    /* Checks if URL is of a particular format like Twitter or Instagram
     * In that case we will not display Title or Desc.
     * We will only display the image.
     * 
     * @since 1.0
     * @access public
     * 
     * @param   $external_url   string  A URL string
     * @return  $display        Boolean True or False value
     */
    public static function rsfft_check_if_title_desc_should_be_fetched( $raw_external_url ) {
        
        /* sanitize the received url */
        $external_url = esc_url( $raw_external_url );
        
        $exist = false;
        
        //Array of string URLs to match with external_url
        $needles = array(
            'twitter.com',
            'instagram.com'
        );
        
        foreach ( $needles as $needle ) {
            
            //Check if current needle matches with URL
            $exist = strpos( $external_url, $needle );
            
            /*
             * Must match with False, as it returns either False or position number.
             * strpos does not return True value.
             */
            if ( $exist !== FALSE ) {
                break;
            }
        }
        
        if ( $exist !== FALSE ) {
            return FALSE;
        } else {
            return TRUE;
        }
        
    }//ends rsfft_check_if_title_desc_should_be_fetched
    
    
    /* Calculates the time in seconds to store the retrieved tweets in Transient
     * This value is decided by user in the settings section
     * which are then stored in options.
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   null
     * @return  $duration       integer value
     */
    public static function rsfft_get_tweet_cache_duration() {
        
        /* set default value of $duration */
        $duration = FALSE;
        
        /* Retrieve customization options from options */
        $options_customize = get_option( 'rsfft_customize_options' );
        $check_tweets_every = isset( $options_customize[ 'check_tweets_every' ] ) ? wp_strip_all_tags( $options_customize[ 'check_tweets_every' ] ) : '';
        $tweet_checking_interval = isset( $options_customize[ 'tweet_checking_interval' ] ) ? intval( $options_customize[ 'tweet_checking_interval' ] ) : '';
        
        /*
         * $check_tweets_every is either day or hour
         * Depending on the value. Calculate the seconds.
         */
        if ( !empty( $check_tweets_every ) ) {
            
            /* Calculate "day" or "hour" in seconds */
            if ( $check_tweets_every == 'day' ) {
                
                $day_or_hour_in_seconds = 60*60*24;
                
            } else if ( $check_tweets_every == 'hour' ) {
                
                $day_or_hour_in_seconds = 60*60;
            }
             
        } // ends if
        
        /*
         * $tweet_checking_interval is an integer number
         * multiply it with $every_in_seconds to get the total seconds for caching
         */
        if ( !empty( $tweet_checking_interval ) ) {
            $duration = intval( $day_or_hour_in_seconds * $tweet_checking_interval ); 
         
        }
        
        return $duration;
        
    } //ends rsfft_get_tweet_cache_duration

    
    
    /* 
     * Add unique ids to shortcodes on each page
     * Searches automatically for our shortcode in page & post content
     * Add an unique id to all our shortcodes.
     * Id format: id="post_id_53_1" (post_id + post_id + unique shortcode postion on page)
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   string  $content    Post content
     * @return  string  $content    Post content
     */
    public static function rsfft_add_unique_ids_to_shortcodes( $content ) {
        
        global $post;
        $post_id = $post->ID;
                
        if ( false === strpos( $content, '[' ) ) {
            return $content;
        }
        
        if ( ! shortcode_exists( RSFFT_SCODE_STR ) ) {
            return $content;
        }
        
        
        //fetch the original shortcodes on page.
        $rsfft_cache = new Rsfft_Cache;
        $my_org_shortcodes = $rsfft_cache->rsfft_get_shortcodes_on_page( $content );
        
        //if no shortcodes returned, return back the original $content
        if ($my_org_shortcodes === false) {
            return $content;
        }
 
        $my_new_shortcodes = array();
        $i = 1;
        foreach ( $my_org_shortcodes as $my_org_shortcode ) {

            /* 
             * explode removes the last ']' string from the shortcode.
             * so it is like '[my_custom_tweets feed_type="user_timeline '
             */
            $string = explode( ']', $my_org_shortcode );
            //echo 'Value of exploded string: ' . $string[0] . '<br><br>';
            $my_new_shortcodes[] = $string[0] . ' id="post_id_' . $post_id . '_' . $i . '"]';

            $i++;
        }


        /* Search in $content and replace with new shortcode corresponding value */
        $j = 0;
        foreach ( $my_org_shortcodes as $my_org_shortcode ) {

            $content = str_replace( $my_org_shortcode, $my_new_shortcodes[ $j ], $content );
            $j++;
        }

        return $content;

   
    }//ends function rsfft_add_unique_ids_to_shortcodes


    
    
    /* 
     * Function returns the merged attributes & options after comparing 
     * with the shortcode attributes (atts). 
     * Attributes (atts) takes precedence over options 
     * saved for the plugin
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   array   $atts                   Shortcode attributes
     * @return  string  $merged_atts_options    Value of the option requested
     */
    public static function rsfft_fetch_merged_atts_options( $atts ) {
        
        /* Retrieve customization options from options */
        $options_customize = get_option( 'rsfft_customize_options' );
        
        /* Retrieve Tweet header & footer options */
        $options_tweets = get_option( 'rsfft_tweets_options' );
        
        /* Retrieve Style tab options */
        $options_style = get_option( 'rsfft_style_options' );
        
        /* Retrieve Slider/Carousel options */
        $options_slider = get_option( 'rsfft_slider_carousel_options' );
        
        if ( $options_customize === false ) {
            add_option( 'rsfft_customize_options' );
        }
        
        $feed_type = isset( $options_customize[ 'feed_type' ] ) ? wp_strip_all_tags( $options_customize[ 'feed_type' ] ) : 'user_timeline';
        $screen_name = isset( $options_customize[ 'screen_name' ] ) ? wp_strip_all_tags( $options_customize[ 'screen_name' ] ) : 'raycreations';
        $hashtags = isset( $options_customize[ 'hashtags' ] ) ? wp_strip_all_tags( $options_customize[ 'hashtags' ] ) : 'mountain clouds';
        $search_string = isset( $options_customize[ 'search_string' ] ) ? wp_strip_all_tags( $options_customize[ 'search_string' ] ) : 'fog sunrise';
        $feed_width_type = isset( $options_customize[ 'feed_width_type' ] ) ? wp_strip_all_tags( $options_customize[ 'feed_width_type' ] ) : 'responsive';
        $display_style = isset( $options_customize[ 'display_style' ] ) ? wp_strip_all_tags( $options_customize[ 'display_style' ] ) : 'display_list';
        $hide_media = isset( $options_customize[ 'hide_media' ] ) ? wp_strip_all_tags( $options_customize[ 'hide_media' ] ) : 0;
        $number_of_tweets = isset( $options_customize[ 'number_of_tweets' ] ) ? intval( $options_customize[ 'number_of_tweets' ] ) : 10;
        $number_of_tweets_in_row = isset( $options_customize[ 'number_of_tweets_in_row' ] ) ? intval( $options_customize[ 'number_of_tweets_in_row' ] ) : '3';
        $exclude_replies = isset( $options_customize[ 'exclude_replies' ] ) ? wp_strip_all_tags( $options_customize[ 'exclude_replies' ] ) : 0;
        $include_rts = isset( $options_customize[ 'include_rts' ] ) ? wp_strip_all_tags( $options_customize[ 'include_rts' ] ) : 0 ;
        $include_photos = isset( $options_customize[ 'include_photos' ] ) ? wp_strip_all_tags( $options_customize[ 'include_photos' ] ) : 0;
        $include_videos = isset( $options_customize[ 'include_videos' ] ) ? wp_strip_all_tags( $options_customize[ 'include_videos' ] ) : 0;
        
        $remove_links_hashtags = isset( $options_customize[ 'remove_links_hashtags' ] ) ? wp_strip_all_tags( $options_customize[ 'remove_links_hashtags' ] ) : 0;
        $remove_links_mentions = isset( $options_customize[ 'remove_links_mentions' ] ) ? wp_strip_all_tags( $options_customize[ 'remove_links_mentions' ] ) : 0;
        $remove_ext_links = isset( $options_customize[ 'remove_ext_links' ] ) ? wp_strip_all_tags( $options_customize[ 'remove_ext_links' ] ) : 0;
        $nofollow_ext_links = isset( $options_customize[ 'nofollow_ext_links' ] ) ? wp_strip_all_tags( $options_customize[ 'nofollow_ext_links' ] ) : 0;
        
        $display_tweet_border = isset( $options_tweets[ 'display_tweet_border' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_tweet_border' ] ) : 0;
        
        $display_header = isset( $options_tweets[ 'display_header' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_header' ] ) : 0;
        $display_profile_img_header = isset( $options_tweets[ 'display_profile_img_header' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_profile_img_header' ] ) : 0;
        $display_name_header = isset( $options_tweets[ 'display_name_header' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_name_header' ] ) : 0;
        $display_screen_name_header = isset( $options_tweets[ 'display_screen_name_header' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_screen_name_header' ] ) : 0;
        $display_date_header = isset( $options_tweets[ 'display_date_header' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_date_header' ] ) : 0;
        
        $display_footer = isset( $options_tweets[ 'display_footer' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_footer' ] ) : 0;
        $display_likes_footer = isset( $options_tweets[ 'display_likes_footer' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_likes_footer' ] ) : 0;
        $display_retweets_footer = isset( $options_tweets[ 'display_retweets_footer' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_retweets_footer' ] ) : 0;
        $display_screen_name_footer = isset( $options_tweets[ 'display_screen_name_footer' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_screen_name_footer' ] ) : 0;
        $display_date_footer = isset( $options_tweets[ 'display_date_footer' ] ) ? wp_strip_all_tags( $options_tweets[ 'display_date_footer' ] ) : 0;
        
        /* Style Tab */
        $border_type = isset( $options_style[ 'border_type' ] ) ? sanitize_text_field( $options_style[ 'border_type' ] ) : 'shadow';
        
        /* options for slider & carousel */
        $nav_arrows = isset( $options_slider[ 'nav_arrows' ] ) ? strip_tags( $options_slider[ 'nav_arrows' ] ) : 0;
        $nav_dots = isset( $options_slider[ 'nav_dots' ] ) ? strip_tags( $options_slider[ 'nav_dots' ] ) : 0;
        $autoplay = isset( $options_slider[ 'autoplay' ] ) ? strip_tags( $options_slider[ 'autoplay' ] ) : 0;
        $transition_interval = isset( $options_slider[ 'transition_interval' ] ) ? sanitize_text_field( $options_slider[ 'transition_interval' ] ) : '7';
        $transition_speed = isset( $options_slider[ 'transition_speed' ] ) ? sanitize_text_field( $options_slider[ 'transition_speed' ] ) : '3';
        $pause_on_hover = isset( $options_slider[ 'pause_on_hover' ] ) ? strip_tags( $options_slider[ 'pause_on_hover' ] ) : 0;
        $loop = isset( $options_slider[ 'loop' ] ) ? strip_tags( $options_slider[ 'loop' ] ) : 0;
        $auto_height = isset( $options_slider[ 'auto_height' ] ) ? strip_tags( $options_slider[ 'auto_height' ] ) : 0;
        $items = isset( $options_slider[ 'items' ] ) ? sanitize_text_field( $options_slider[ 'items' ] ) : '3';
        
        /*
         * Get the current post id
         * @since 1.2.3
         */
        $post_id = is_numeric( get_the_ID() ) ? sanitize_text_field( get_the_ID() ) : '';
        
        $merged_atts_options = shortcode_atts( array(
            'id' => '',
            'post_id' => $post_id,
            'feed_type' => $feed_type,
            'screen_name' => $screen_name,
            'hashtags' => $hashtags,
            'search_string' => $search_string,
            'feed_width_type' => $feed_width_type,
            'display_style' => $display_style,
            'hide_media' => $hide_media,
            'count' => $number_of_tweets,
            'number_of_tweets_in_row' => $number_of_tweets_in_row,
            'exclude_replies' => $exclude_replies,
            'include_rts' => $include_rts,
            'include_photos' => $include_photos,
            'include_videos' => $include_videos,
            'remove_links_hashtags' => $remove_links_hashtags,
            'remove_links_mentions' => $remove_links_mentions,
            'remove_ext_links' => $remove_ext_links,
            'nofollow_ext_links' => $nofollow_ext_links,
            'display_tweet_border' => $display_tweet_border,
            'display_header' => $display_header,
            'display_profile_img_header' => $display_profile_img_header,
            'display_name_header' => $display_name_header,
            'display_screen_name_header' => $display_screen_name_header,
            'display_date_header' => $display_date_header,
            'display_footer' => $display_footer,
            'display_likes_footer' => $display_likes_footer,
            'display_retweets_footer' => $display_retweets_footer,
            'display_screen_name_footer' => $display_screen_name_footer,
            'display_date_footer' => $display_date_footer,
            'border_type' => $border_type,
            'nav_arrows' => $nav_arrows,
            'nav_dots' => $nav_dots,
            'autoplay' => $autoplay,
            'transition_interval' => $transition_interval,
            'transition_speed' => $transition_speed,
            'pause_on_hover' => $pause_on_hover,
            'loop' => $loop,
            'auto_height' => $auto_height,
            'items' => $items
            
        ), $atts);
        
        return $merged_atts_options;
        
    }//ends function rsfft_fetch_merged_atts_options
    
    
    
    /* 
     * This function resets back the options which are reserved for pro.
     * If the user chooses options in shortcode which are only for pro, this 
     * fucntion resets them.
     * 
     * Following are the limits:
     * Images are not available.
     * Videos are not available.
     * Tweets are limited to 10 only.
     * 2 column slider, 'display_slider_2_col' is not available. As it needs images.
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   array   $atts                   Shortcode attributes
     * @return  string  $merged_atts_options    Value of the option requested
     */
    public static function rsfft_reset_settings_reserved_for_pro() {
        
        //check if $display_style is 'display_slider_2_col' reset it to 'display_list'
        $display_style = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'display_style' ] );
        if ( $display_style == 'display_slider_2_col' ) {
            Rsfft::$merged_scode_atts[ 'display_style' ] = 'display_list';
        }
        
        
        //Check if hide_media is set to false. If yes, set it back to true
        $hide_media = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'hide_media' ] );
        if ( $hide_media == 0 ) {
            Rsfft::$merged_scode_atts[ 'hide_media' ] = 1;
        }
        
        
        /*
         * Ensure number of tweets ('count') are not more than 10
         * If less than 1, set it back to 5
         */
        $count = sanitize_text_field( Rsfft::$merged_scode_atts[ 'count' ] );
        if ( $count > 10 ) {
            Rsfft::$merged_scode_atts[ 'count' ] = 10;
        } else if ( $count < 1 ) {
            Rsfft::$merged_scode_atts[ 'count' ] = 5;
        }
        
        
    }//ends rsfft_reset_settings_reserved_for_pro


    /* 
     * If there is an error in fetching Tweets either from Twitter or Transient,
     * this html box would be displayed instead.
     * 
     * @since 1.0
     * @access public
     * 
     * @param   string  $error_type Indicates the type of error and the corresponding HTML to return
     * 
     * @return  string  $html       Error message html
     */
    public static function rsfft_get_tweets_error_html( $error_type ) {
        
        $html = '';
        
        if ( $error_type == 'tweets' ) {
            
            /* HTML to show instead of the tweets */
            $html .= '<div class="rsfft_tweets_wrap_error">';
            $html .= '<p>Oops! Something went wrong. Please try again after some time.</p>';
            $html .= '</div>';
            
        } elseif ( $error_type == 'keys' ) {
            
            /* HTML to show instead of the tweets */
            $html .= '<div class="rsfft_tweets_wrap_error">';
            $html .= '<p>Generate your access key & secret in the plugin <a href="' . RSFFT_ADMIN_URL . '&tab=settings"> API Settings page</a> '
                    . 'to display tweets.</p>';
            $html .= "</div>";
            
        }
        
        return $html;
    }
    
    
    /* 
     * Generates the div HTML for the Tweet outer wrap and the container wrap
     * 
     * For slider we need special classes to be added to "outer wrap div" & the "container wrap div" elements
     * Add an 'id' element to outer 'div'. This is unique for sliders. Other display types don't have id in outer div.
     * We will add class="slider" in outer div too.
     * We will add 'slides-container' in "container wrap div"
     * And 'slide' in "tweet item div"
     * 
     * @since 1.2.1
     * @access public
     * 
     * @param   string  $display_style  Display format
     * @return  string  $html       HTML for the tweet header divs
     */
    public static function rsfft_get_tweet_opening_div_wraps_html( $disp_style ) {
        
        /*
         * We need the divs in the following format.
         * 
         * //For Listing
         * <div class="rsfft_tweets_wrap display_list cols_1">
         * <div id="listing_tweets_post_id_5_1 " class= "listing_tweets_post_id_5_1 tweets_container">
         * 
         * //For 2 column Slider
         * <div id="slider_post_id_6029_2" class="rsfft_tweets_wrap display_slider_2_col slider cols_3">
         * <div id="listing_tweets_post_id_6029_2" class="listing_tweets_post_id_6029_2 slides-container tweets_container">
         * 
         * //For 1 column Slider
         * <div id="slider_post_id_6052_2" class="rsfft_tweets_wrap display_slider_1_col slider cols_3">
         * <div id="listing_tweets_post_id_6052_2 " class="listing_tweets_post_id_6052_2 slides-container tweets_container">
         * 
         * //For Masonry
         * <div class="rsfft_tweets_wrap display_masonry cols_3">
         * <div id="listing_tweets_post_id_6029_1 " class="listing_tweets_post_id_6029_1 tweets_container">
         * 
         */
        
        
        $display_style = sanitize_text_field( $disp_style ); 
        $number_of_tweets_in_row = intval( Rsfft::$merged_scode_atts[ 'number_of_tweets_in_row' ] );     
        $id = sanitize_text_field( Rsfft::$merged_scode_atts[ 'id' ] );
        
        $slider_div_id = '';
        $slider_div_id_class = '';
        $slider_div_class = '';
        $slider_container_class = '';
        
        if ( $display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col' ) {
            $slider_div_id = ' id="slider_' . $id . '"';
            $slider_div_id_class = ' slider_' . $id;
            $slider_div_class = ' slider';
            $slider_container_class = ' slides-container owl-carousel owl-theme';
            
            
            /*
             *  Store inidvidual sliders/carousels and their settings as key => value pair
             * 'slider_' . $id  =>  $settings
             */
            Rsfft::rsfft_store_slider_n_carousel_settings_in_array( $id );
            
        }
        
        //print_r($tweets);
        
        /* Starts tweets outer wrap */
        $html = "<div" . $slider_div_id . " ";
        $html .= "class='rsfft_tweets_wrap ";
        $html .= $display_style;
        $html .= $slider_div_class . " ";
        $html .= "cols_" . $number_of_tweets_in_row . "'>";
        
        /* starts tweets container wrap */
        $html .= "<div id='listing_tweets_" . $id . " ";
        $html .= "'class='listing_tweets_" . $id;
        $html .= $slider_div_id_class;
        $html .= $slider_container_class . " ";
        $html .= "tweets_container'>";
        
        return $html;
        
    }//ends rsfft_get_tweet_opening_div_wraps_html




    /* 
     * Checks whether necessary API keys have been added to the plugin or not 
     * 
     * @since 1.0
     * @access public
     * 
     * @return  Boolen  TRUE | False
     */
    public static function rsfft_check_api_keys() {
        
        $options_settings = get_option( 'rsfft_settings_options' );
        $app_consumer_key = isset( $options_settings[ 'app_consumer_key' ] ) ? wp_strip_all_tags( $options_settings[ 'app_consumer_key' ] ) : '';
        $app_consumer_secret = isset( $options_settings[ 'app_consumer_secret' ] ) ? wp_strip_all_tags( $options_settings[ 'app_consumer_secret' ] ) : '';
        
        $consumer_key = isset( $options_settings[ 'consumer_key' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_key' ] ) : '';
        $consumer_secret = isset( $options_settings[ 'consumer_secret' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_secret' ] ) : '';
        
        $access_token = isset( $options_settings[ 'access_token' ] ) ? wp_strip_all_tags( $options_settings[ 'access_token' ] ) : '';
        $access_token_secret = isset( $options_settings[ 'access_token_secret' ] ) ? wp_strip_all_tags( $options_settings[ 'access_token_secret' ] ) : '';
        
        /* if access_token OR access_token_secret is not set return false */
        if ( !$access_token || !$access_token_secret ) {
            return FALSE;
        }
        
        /* 
         * either the app_consumer_key & app_consumer_secret 
         * or consumer_key & consumer_secret should be present
         * or both should be present
         */
        if ( ( $app_consumer_key && $app_consumer_secret ) || ( $consumer_key && $consumer_secret )  ) {
            return TRUE;
        }
        
        /* since conditions are not met */
        return FALSE;
        
    }//ends function rsfft_check_api_keys
    
    
    
    /* 
     * Localizes the js file with handle 'rsfft_slides'
     * This function sends the ids of the outer div of all shortcodes 
     * that are sliders to the js
     * 
     * All such sliders are stored in the array Rsfft::$scodes_slides
     * 
     * @since 1.0
     * @access public
     * 
     */
    public static function rsfft_slides_localize_script() {

        /* holds the ids of the parent divs of each of the shortcodes on page that are sliders */
        $scodes_slides = Rsfft::$scodes_slides;
        
        /*
         * count the total sliders
         * we sent it to js, to loop through it and get the ids of the
         * parent divs
         */
        $total_scode_sliders = count( $scodes_slides );
        
        $total_sliders_args = array(
            'total' => $total_scode_sliders
        );
        
        //wp_localize_script( 'rsfft_slides', 'rsfft_slides_args', $scodes_slides );
        //wp_localize_script( 'rsfft_slides', 'rsfft_total_sliders', $total_sliders_args );
        
    }//ends rsfft_slides_localize_script

    
    
    /* 
     * Localizes the main frontend js file with handle 'rsfft_scripts'
     * This function sends the parameters needed by the owl initialize function
     * to control the different aspects of owl sliders and owl carousels
     * 
     * 
     * @since 1.2.1
     * @access public
     * 
     */
    public static function rsfft_owl_slides_carousel_localize_script() {
        
        /**
         * When the page is rendered, all sliders/carousels with their individual settings
         * were stored in Rsfft::$scodes_slides as key => value pair.
         * 
         * We will extract the slider ids and corresponding settings value and pass them to the 
         * JS that is initializing each one of them.
         */      
        
        //if no sliders or carousels, then simply return
        if ( count( Rsfft::$scodes_slides ) < 1 ) { return; }
        
        //extract the slider details in a variable
        $scodes_slides = Rsfft::$scodes_slides;
        
        //print_r($scodes_slides);
        //wp_die();
        
        //setup a counter
        $i = 0;
        
        //loop thorugh each of the slides/carousels
        foreach ( $scodes_slides as $slider_id => $settings ) {
            //increment counter by 1
            $i++;
            
            //ensure the options $owl_slider_options is set to empty in the beginning
            $owl_slider_options[] = '';
            
            $items = intval( $settings[ 'items' ] );
            $auto_height = wp_strip_all_tags( $settings[ 'auto_height' ] );
            $nav_dots = wp_strip_all_tags( $settings[ 'nav_dots' ] );
            $nav_arrows = wp_strip_all_tags( $settings[ 'nav_arrows' ] );
            $autoplay = wp_strip_all_tags( $settings[ 'autoplay' ] );
            $transition_interval_miliseconds = 1000 * intval( $settings[ 'transition_interval' ] );
            $transition_speed_miliseconds = 1000 * intval( $settings[ 'transition_speed' ] );
            $pause_on_hover = wp_strip_all_tags( $settings[ 'pause_on_hover' ] );
            $loop = wp_strip_all_tags( $settings[ 'loop' ] );
            
            $display_style = wp_strip_all_tags( $settings[ 'display_style' ] );
            $slider_id = wp_strip_all_tags( $slider_id );
            
            
            /* If not slider & 'autoHeight' is true, then change it to false */
            if ( ( $display_style != 'display_slider_1_col' && $display_style != 'display_slider_2_col' ) && $auto_height == true ) {
                $auto_height = 0;
            }
            
            /* if dislay_style is slider, then ensure that items on screen is set to 1 */
            if ( ( $display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col' ) ) {
                $items = 1;
            }
            
            
            /*
             * arrange all the options in an array
             */
            $owl_slider_options = array(
                'items' => $items,
                'autoHeight' => $auto_height,
                'dots'=> $nav_dots,
                'nav' => $nav_arrows,
                'autoplay' => $autoplay,
                'autoplayTimeout' => $transition_interval_miliseconds,
                'autoplaySpeed' => $transition_speed_miliseconds,
                'autoplayHoverPause'=> $pause_on_hover,
                'loop' => $loop,
                'display_style' => $display_style,
                'slider_id' => $slider_id,
                    
            );

            wp_localize_script( 'rsfft_scripts', 'rsfft_owl_options_' . $i, $owl_slider_options );
            
        }//ends foreach
        
        $total_sliders_args = array(
            'total' => $i
        );
        
        //make the total slider number value available to the main JS
        wp_localize_script( 'rsfft_scripts', 'rsfft_total_owl_sliders', $total_sliders_args );  
        
    }//ends rsfft_slides_localize_script
    
    
    /**
     * Check if the tweet is a retweet. If yes, then save is_retweet, and retweet_user 
     * details in the global array Rsfft::$tweet_details.
     * 
     * @since 1.2.4
     * @param obj $tweet The tweet object.
     * @return NULL Returns nothing.
     */
    public static function rsfft_check_if_retweet( $tweet ) {
        
        //check if retweeted_status for the tweet is set. If yes, then it is a retweet.
        if ( !isset( $tweet->retweeted_status ) ) {
            
            Rsfft::$tweet_details[ 'is_retweet' ] = false;
            Rsfft::$tweet_details[ 'retweet_user' ] = '';
            Rsfft::$tweet_details[ 'tweet' ] = $tweet;
            
        } else {
            
            Rsfft::$tweet_details[ 'is_retweet' ] = true;
            Rsfft::$tweet_details[ 'retweet_user' ] = $tweet->user;             //holds the complete user obj of the retwitter user
            
            /**
             * now set the tweet to $tweet->retweeted_status object. 
             * So that every tweet element is fetched from the retweet object.
             */
            Rsfft::$tweet_details[ 'tweet' ] = $tweet->retweeted_status;
            
        }//end if
        
        return;
        
    }//rsfft_check_if_retweet



    /*
     * Get displayable Tweet text from a single tweet
     * It also removes unnecessary links
     * And relinks all links, hashtags, mentions etc.
     * 
     * @since 1.21
     * @param   string      $tweet          Raw tweet as fetched from Twitter
     * @param   Boolean     $full_text      Whether to fetch full_text of truncated text
     * 
     * @return string   Displayable tweet
     */
    public static function rsfft_get_displayable_tweet( $tweet ) {
        
        //declare variable to hold the tweet text
        $text_with_urls = $tweet->{ 'full_text' };
        
        /* Sometimes, Tweets include extra links in the text part of the tweet object. Remove those URLs */
        $text_without_url = Rsfft::rsfft_remove_url_in_tweet_sent_as_text( $tweet, $text_with_urls );
        
        /* Tweets are received without any links. This functions relinks them */
        $text = Rsfft::rsfft_add_links_to_tweet_entities( $tweet, $text_without_url );
        
        return $text;
        
    }//ends rsfft_get_displayable_tweet
    
    
    
    /*
     * Store inidvidual sliders/carousels ids and their settings as key => value pair
     * And relinks all links, hashtags, mentions etc.
     * 
     * @since 1.21
     * @param   string      $tweet          Raw tweet as fetched from Twitter
     * @param   Boolean     $full_text      Whether to fetch full_text of truncated text
     * 
     * @return string   Displayable tweet
     */
    public static function rsfft_store_slider_n_carousel_settings_in_array( $id ){
        
        //Create the slider id
        $slider_id = 'slider_' . $id;
        
        //holds the individual slider/carousel settings
        $settings[] = '';
        
        //Extract the merged slider/carousel settings value
        $items = intval( Rsfft::$merged_scode_atts[ 'items' ] );
        $auto_height = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'auto_height' ] );
        $nav_dots = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'nav_dots' ] );
        $nav_arrows = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'nav_arrows' ] );
        $autoplay = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'autoplay' ] );
        $transition_interval = intval( Rsfft::$merged_scode_atts[ 'transition_interval' ] );
        $transition_speed = intval( Rsfft::$merged_scode_atts[ 'transition_speed' ] );
        $pause_on_hover = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'pause_on_hover' ] );
        $loop = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'loop' ] );
        
        $display_style = wp_strip_all_tags( Rsfft::$merged_scode_atts[ 'display_style' ] );
        
        $settings = array(
            'items' => $items,
            'auto_height' => $auto_height,
            'nav_dots'=> $nav_dots,
            'nav_arrows' => $nav_arrows,
            'autoplay' => $autoplay,
            'transition_interval' => $transition_interval,
            'transition_speed' => $transition_speed,
            'pause_on_hover'=> $pause_on_hover,
            'loop' => $loop, 
            'display_style' => $display_style
        );
        
        //store the values now
        Rsfft::$scodes_slides[ $slider_id ] = $settings;
        
        
    }//ends rsfft_store_slider_n_carousel_settings_in_array
    
    
    /**
     * Function to sanitize and validate attributes passed by users.
     * 
     * @param   array     $atts     String of Hashtags enter by user separated by space
     * @return  array               Sanitizes & validated hashtags
     * 
     * @since 1.2.4
     */
    public static function rsfft_sanitize_atts( $atts ) {
        
        //ensure $atts array is empty
        if ( empty( $atts ) ) {
            return '';
        }
        
        //create an empty array.
        $valid = array();
        
        //Loop through each of the incoming options
        foreach ( $atts as $key => $value ) {
            
            //Check to see if the input option has a value. If so, process it.
            if ( isset( $atts[ $key ] ) ) {
                $valid[ $key ] = trim( strip_tags( stripslashes( $atts[ $key ] ) ) );
            }// end if

        }// ends foreach
        
        //if user has set hashtags attribute, sanitize and validate them
        if ( isset( $valid[ 'hashtags' ] ) ) {
            $valid[ 'hashtags' ] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $valid[ 'hashtags' ] );
        }
        
        //if search_string attribute is set, additional sanitizatio for it.
        if ( isset( $valid[ 'search_string' ] ) ) {
            $valid[ 'search_string' ] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $valid[ 'search_string' ] );
        }
        
        //validate Twitter screen_name if set
        if ( isset( $valid[ 'screen_name' ] ) ) {
            $valid[ 'screen_name' ] = preg_replace( '/[^a-zA-Z0-9_]/', '', $valid[ 'screen_name' ]);
        }
        
        
        //if "count" is set, ensure "count" value is numeric, otherwise set it to 10
        if ( isset( $valid[ 'count' ] ) && !is_numeric( $valid[ 'count' ] ) ) {
            $valid[ 'count' ] = '10';
        }
        
        //if "number_of_tweets_in_row" is set, ensure it a number
        if ( isset( $valid[ 'number_of_tweets_in_row' ] ) && !is_numeric( $valid[ 'number_of_tweets_in_row' ] ) ) {
            $valid[ 'number_of_tweets_in_row' ] = '3';
        }
        
        //if "transition_interval" is set, ensure it a number. Otherwise set it back to the default value '7'
        if ( isset( $valid[ 'transition_interval' ] ) && !is_numeric( $valid[ 'transition_interval' ] ) ) {
            $valid[ 'transition_interval' ] = '7';
        }
        
        //if "transition_speed" is set, ensure "transition_speed" value is numeric, otherwise set it to 3
        if ( isset( $valid[ 'transition_speed' ] ) && !is_numeric( $valid[ 'transition_speed' ] ) ) {
            $valid[ 'transition_speed' ] = '3';
        }
        
        /**
         * Processing array of attributes that either needs to be '1' or '0;
         * If they have any value other than '0' or '1', change it back to their default.
         */
        
        //creating an array of attributes whose default value is '0'
        $default_zero_attrs = [ 'hide_media', 'include_rts', 'remove_links_mentions', 'remove_ext_links', 'display_screen_name_footer', 
            'display_date_footer', 'nav_arrows', 'auto_height' ];
        
        //loop through each to ensure they have a numeric value set
        foreach ( $default_zero_attrs as $default_zero_attr ) {
            if ( isset( $valid[ $default_zero_attr ] ) && !is_numeric( $valid[ $default_zero_attr ] ) ) {
                $valid[ $default_zero_attr ] = '0';
            }
        }
        
        //now creating an array of attributes whose default value is '1'
        $default_one_attrs = [ 'exclude_replies', 'include_photos', 'include_videos', 'remove_links_hashtags', 'nofollow_ext_links', 
            'display_tweet_border', 'display_header', 'display_profile_img_header', 
            'display_name_header', 'display_screen_name_header', 'display_date_header', 'display_footer', 
            'display_likes_footer', 'display_retweets_footer', 'nav_dots', 'autoplay', 'pause_on_hover', 'loop',  ];
        
        //loop through each to ensure that a numeric value is set.
        foreach ( $default_one_attrs as $default_one_attr ) {
            if ( isset( $valid[ $default_one_attr ] ) && !is_numeric( $valid[ $default_one_attr ] ) ) {
                $valid[ $default_one_attr ] = '1';
            }
        }
        
        return $valid;
        
        
    }//ends rsfft_process_htags_sstrings
    
    
}//ends class
