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


class Rc_Myctf {
    
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
        add_shortcode( 'my_custom_tweets', array( 'Rc_Myctf', 'rc_myctf_render_shortcode' ) );
        
        /* Check to see if shortcode exists for this page or post */
        //add_action( 'wp', array( 'Rc_Myctf', 'rc_myctf_check_if_shortcode_exists' ) );
        
        /* Check to see if shortcode exists for this page or post */
        add_filter( 'the_content', array( 'Rc_Myctf', 'rc_myctf_add_unique_ids_to_shortcodes' ) );
        
        /* Needed a hook that executes after shortcodes are processed so we can get parent ids of the divs to localize */
        add_action( 'wp_footer', array( 'Rc_Myctf', 'rc_myctf_slides_localize_script' ) );
        
        
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
    public static function rc_myctf_render_shortcode( $atts ){
        
        /* Before any action takes place we need to check if  */
        //$key_status = Rc_Myctf::rc_myctf_check_api_keys();
        $key_status = TRUE;
        
        if ( $key_status === FALSE ) {
            
            /* If no keys exist, return appropriate HTML indicating what needs to be done */
            $error_html = Rc_Myctf::rc_myctf_get_tweets_error_html( $error_type = 'keys' );
            return $error_html;
            
        }
        
        
        /* 
         * Unique id being automatically generated and added to $atts 
         * This will help to uniquely identify the shortcodes
         */
        $id = (isset( $atts['id'] )) ? strip_tags( $atts['id'] ) : '';
        
        /* get the display options: either default or the one set by the user */
        $merged_atts_options = Rc_Myctf::rc_myctf_fetch_merged_atts_options( $atts );
        //$display_style = $merged_atts_options[ 'display_style' ];
        $display_style = 'display_list';
        $number_of_tweets_in_row = $merged_atts_options[ 'number_of_tweets_in_row' ];
        
        
        /*
         * If $id is not empty, then add it to the
         * 'rc_myctf_scodes_transients' options
         * and add the transient_name as its value
         */
        if ( !empty( $id ) ) {
            
            $shortcode_id = $id;
            $rc_myctf_cache = new Rc_Myctf_Cache();
            $rc_myctf_cache->rc_myctf_add_update_transient_name_to_options( $shortcode_id );
            
        }
        
        /* Fetch Tweets */
        $tweets = Rc_Myctf_Tweets::rc_myctf_fetch_tweets( $atts );
        
        /* If error fetching tweets, display the error html box instead of the tweets */
        if ( $tweets === false ) {
            $error_html_box = Rc_Myctf::rc_myctf_get_tweets_error_html( $error_type = 'tweets' );
            return $error_html_box;
        }
        
        /*
         * For slider we need special classes to be added to "outer wrap div" & the "container wrap div" elements
         * Add an 'id' element to outer 'div'. This is unique for sliders. Other display types don't have id in outer div.
         * We will add class="slider" in outer div too.
         * We will add 'slides-container' in "container wrap div"
         * And 'slide' in "tweet item div"
         */
        $slider_div_id = '';
        $slider_div_class = '';
        $slider_container_class = '';
        $slider_item_class = '';
        
        if ( $display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col' ) {
            $slider_div_id = ' id="slider_' . $id . '"';
            $slider_div_class = ' slider';
            $slider_container_class = ' slides-container';
            $slider_item_class = 'slide ';
            
            /* add the id of the outer div that is present only in 'display_style' => 'display_slider_1_col' or 'display_style => 'display_slider_2_col'  */
            Rc_Myctf::$scodes_slides[] = 'slider_' . $id;
            //print_r( Rc_Myctf::$scodes_slides );
            //Rc_Myctf::rc_myctf_slides_localize_script();
        }
        
        //print_r($tweets);
        
        /* Starts tweets outer wrap */
        $html = "<div" . $slider_div_id . " ";
        $html .= "class='rc_myctf_tweets_wrap ";
        $html .= $display_style;
        $html .= $slider_div_class . " ";
        $html .= "cols_" . $number_of_tweets_in_row . "'>";
        
        /* starts tweets container wrap */
        $html .= "<div id='listing_tweets_" . $id . " ";
        $html .= "'class='listing_tweets_" . $id;
        $html .= $slider_container_class . " ";
        $html .= "tweets_container'>";
        foreach( $tweets as $i => $tweet ){
            //print_r($tweet);
            
            /* Get the Tweet text from the Tweet Object */
            //$text_with_url = $tweet->{ 'text' };
            $text_with_url = $tweet->{ 'full_text' };
            
            /* Sometimes, Tweets include links in the text part of the tweet object itself. Remove those URLs */
            $text_without_url = Rc_Myctf::rc_myctf_remove_url_in_tweet_sent_as_text( $text_with_url );
            
            /* Tweets are received without any links. This functions relinks them */
            $text = Rc_Myctf::rc_myctf_add_links_to_tweet_entities( $tweet, $text_without_url );
            
            /*
             * tweet list items starts from here
             * it has a class of "tweet_item"
             */
            $html .= "<div class='" . esc_attr( $slider_item_class ) . "tweet_item'>";
                
                /*
                 * Fetches either native media (images or videos) 
                 * or external url om:image or the largest image on the page.
                 * return value is false is there is an error
                 */
                $media_html = Rc_Myctf::rc_myctf_get_media_display_html( $tweet );
                
                
                /*
                 * Meida is being displayed before tweet only for sliders. This is only when
                 * $display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col'
                 */
                 if ( ($display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col') && $media_html !== FALSE ) {
                    
                    /* HTML for media starts here */
                    $html .= "<div class='tweet-media'>";
                        $html .= $media_html;
                    $html .= "</div>";
                    
                 }
            
                /* Wraps both the header and tweet */
                $html .= "<div class='tweet_wrapper'>";
                
                    /* Get the formatted Twitter header for display */
                    $html .= Rc_Myctf::rc_myctf_get_twitter_header( $tweet );

                    /* Displaying the actual tweet */
                    $html .= "<div class='tweet'>";
                        $html .= $text;

                        /* Fetch the first url from the array if exists */                   
                        if ( !empty( Rc_Myctf::$external_url[0] ) ) {
                            $html .= "<br><a href='" . esc_url( Rc_Myctf::$external_url[0] ) . "' target='_blank' rel='noopener'>" . esc_url( $tweet->entities->urls[0]->display_url ) . "</a>";
                        }

                    $html .= "</div>";

                $html .= "</div>"; //ends tweet_wrapper
                    
                
                /*
                 * Media is being displayed after tweet when not a slider. This is only when
                 * $display_style != 'display_slider_1_col' && $display_style != 'display_slider_2_col'
                 */
                if ( $display_style != 'display_slider_1_col' && $display_style != 'display_slider_2_col' && $media_html !== FALSE ) {
                    
                    /* HTML for media starts here */
                    $html .= "<div class='tweet-media'>";
                        $html .= $media_html;
                    $html .= "</div>";
                    
                }//ends if
                
            $html .= "</div>"; //class .tweet_item ends here
            
        }//ends foreach
        
        $html .= '</div>'; //ends tweet container wrap
        
        /*
         * display nav arrows if display_style is display_slider_1_col or display_slider_2_col
         * also display the pager section only for sliders
         */
        if ( $display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col' ) {
            
            $html .= '<div class="arrows">';
                $html .= '<a class="prev" href="#"> <img src="' . RC_MYCTF_URI . 'images/prev_arrow.png" alt="previous arrow"> </a>';
                $html .= '<a class="next" href="#"> <img src="' . RC_MYCTF_URI . 'images/next_arrow.png" alt="next arrow"> </a>';
            $html .= '</div>';

            $html .= '<div class="pager-wrap">';
                $html .= '<span class="pager">';
                    $html .= '1';
                $html .= '</span>';
            $html .= '</div>';
        
        }//ends if
        
        
        $html .= '</div>';
        
        return $html;
            
    }// Ends Shortcode rc_myctf_render_shortcode
    
    
    
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
    public static function rc_myctf_add_links_to_tweet_entities( $tweet, $text ) {
        
        /* Make sure the external links array is always empty when the loop starts for each tweet */
        Rc_Myctf::$external_url = array();
        //$external_url = array();

        foreach( $tweet->{ 'entities' } as $type => $entity ){
            if( $type == 'urls' ){
                foreach( $entity as $j => $url ){
                    $update_with = '<a href="' . $url->{ 'url' } . '" target="_blank" title="' . $url->{ 'expanded_url' } . '">' . $url->{ 'display_url' } . '</a>';
                    $text = str_replace( $url->{ 'url' }, $update_with, $text );

                    /* Store the URLs in an array */
                    Rc_Myctf::$external_url[] = $url->{ 'expanded_url' };
                    //$external_url[] = $url->{ 'expanded_url' };

                } 
            } else if( $type == 'hashtags' ){
                foreach( $entity as $j => $hashtag ){
                    $update_with = '<a href="https://twitter.com/search?q=%23' . $hashtag->{ 'text' } . '&src=hash" target="_blank" title="' . $hashtag->{ 'text' } . '">#' . $hashtag->{ 'text' } . '</a>';
                    $text = str_replace( '#' . $hashtag->{'text'}, $update_with, $text );
                }
            } else if( $type == 'user_mentions' ){
                foreach( $entity as $j => $user ){
                    $update_with = '<a href="https://twitter.com/' . $user->{ 'screen_name' } . '" target="_blank" title="' . $user->{ 'name' } . '">@' . $user->{ 'screen_name' } . '</a>';
                    $text = str_replace( '@' . $user->{ 'screen_name' }, $update_with, $text );
                }
            }
        }
        
        return $text;
        
    } //ends rc_myctf_add_links_to_tweet_entities
    
    
    
    /*
     * Returns the HTML for display of media in tweets
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public static function rc_myctf_get_media_display_html( $tweet ){
              
        /* define $html variable to hold the output tweet */
        $html = '';
        
        /* Get the string id of the tweet. Will be used as the id of the div */
        $tweet_id = strip_tags( $tweet->id_str );
        
        /* 
         * Check if the Tweet has an uploaded photo or video 
         * by checking if "extended_entities" object in the tweet has been set.
         * images & videos exist in the extended_entities object.
         */
        if ( isset( $tweet->extended_entities->media[0] ) ) {
            
            $extended_entities = $tweet->{ 'extended_entities' };
            $first_media = $extended_entities->media[0];

            

            if ( $first_media ){

                if ( $first_media->type == "video" ){
                    //$html = "This is the URL of the video: " . $first_media->video_info->variants[0]->url;
                    $video_url = esc_url( $first_media->video_info->variants[0]->url );
                    $html .= '<div class="link_details_wrap my-tweet_video">';
                        $html .= '<video id="' . esc_attr( $tweet_id ) . '" class="tweet-video" controls><source src="' . esc_attr( $video_url ) . '" type="video/mp4"></video>';
                    $html .= '</div>';

                } else if ( $first_media->type == "photo" ){
                    //$html = "This is the URL of the photo:  " . $first_media->media_url_https;
                    $image_url = esc_url( $first_media->media_url_https );
                    $html .= '<div id="' . esc_attr( $tweet_id ) . '" class="link_details_wrap my-tweet_photo">';
                    $html .= '<img alt="image" src="' . esc_attr( $image_url ) . '">';
                    $html .='</div>';
                }

            }
            
        /* If native photos & videos don't exist. Check for external urls for title, desc, og:image or 'largest image' */
        } else if ( !empty ( Rc_Myctf::$external_url[0] ) ) {
            
            $external_url_image_html = Rc_Myctf::rc_myctf_fetch_external_url_details();
            
            if ( FALSE === $external_url_image_html ) {
                return FALSE;
            }
            
            $html .= $external_url_image_html;
        } //end if
        
        return $html;
        
    }//ends rc_myctf_get_media_display_html
    
    
    
    /*
     * Tweets that do not have an external URL.
     * Tweets that contains Photo or Video as part of the tweet 
     * also contains a Twitter URL as part of the Tweet text
     * 
     * It is of the format: https://t.co/D4GwXqZTMF
     * It is not needed. So this function removed this URL from the text
     * and returns the tweet.
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public static function rc_myctf_remove_url_in_tweet_sent_as_text( $text_with_url ){
        
        /* 
        * Filter out URL like (https://t.co/D4GwXqZTMF) in Tweet text 
        * I am matching 'http or https', followed by '://' and then 't.co/' 
        * and then any characters and numbers including '-' and '.'
        */
        $regex_url ='/(http|https):\/\/t\.co\/[a-zA-Z0-9\-\.]+/';
        $text = preg_replace( $regex_url, '', $text_with_url );
        
        return $text;
        
    }

    
    
    
    /* Function to fetch tweets either live from Twitter or from Transient
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public static function rc_myctf_fetch_external_url_details() {
        
        $external_url = esc_url( Rc_Myctf::$external_url[0] );
        
        /* Declare $html variable */
        $html = '';
        
        /* Initialize empty array to hold the website details */
        $websiteDetails = array();

        /* check to see if $websiteDetails array is stored in the Transient */
        if ( false === ( $websiteDetails = get_transient( $external_url ) ) ) {

            /* Instantiate the Rc_Myctf_Url_Preview with data as $obj1 */
            $obj1 = new Rc_Myctf_Url_Preview( $external_url );
            
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
             
        }

        
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
            if ( Rc_Myctf::rc_myctf_check_if_title_desc_should_be_fetched( $external_url ) === TRUE ) {
                
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
                $html .= '<img src="' . esc_attr( $websiteDetails[ "OgImage" ] ) . '" alt="og:image">';

            } else if ( !empty ( $websiteDetails[ "LargestImgDetails" ][ "src" ] ) ) {
                $html .= '<img src="'. esc_attr( $websiteDetails[ "LargestImgDetails" ][ "src" ] ) . '" alt="Largest image">';
            }

            $html .= '</div>';

        }
                
        return $html;
        
    }//Ends function rc_myctf_fetch_external_url_details
    
    
    
    
    /* Returns the usernmae along with the verification badge if verfied.
     * 
     * @since 1.0
     * @access public
     * 
     * @param $user     object  Twitter user object
     * @return string
     */
    public static function rc_myctf_get_twitter_header( $tweet ){
        
        /* $user is assigned the Twitter User object */
        $user = $tweet->{ 'user' };
        
        /* this is the profile image */
        $html = "<img src='" . $user->{ 'profile_image_url_https' } . "' alt='" . $user->{ 'name' } . "' class='twitter_profile_img' />";
        
        /* the remaining header starts here */
        $html .= "<div class='twitter_header_meta'>";
        $html .= "<span class='name-of-tweeter'> ";
            $html .= $user->{ 'name' };
        
            if ( $user->verified == 1 ) {           
                $svg_image ='<svg viewBox="0 0 24 24" aria-label="Verified account" class="verified_badge"><g><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.998-3.818-3.998-.47 0-.92.084-1.336.25C14.818 2.415 13.51 1.5 12 1.5s-2.816.917-3.437 2.25c-.415-.165-.866-.25-1.336-.25-2.11 0-3.818 1.79-3.818 4 0 .494.083.964.237 1.4-1.272.65-2.147 2.018-2.147 3.6 0 1.495.782 2.798 1.942 3.486-.02.17-.032.34-.032.514 0 2.21 1.708 4 3.818 4 .47 0 .92-.086 1.335-.25.62 1.334 1.926 2.25 3.437 2.25 1.512 0 2.818-.916 3.437-2.25.415.163.865.248 1.336.248 2.11 0 3.818-1.79 3.818-4 0-.174-.012-.344-.033-.513 1.158-.687 1.943-1.99 1.943-3.484zm-6.616-3.334l-4.334 6.5c-.145.217-.382.334-.625.334-.143 0-.288-.04-.416-.126l-.115-.094-2.415-2.415c-.293-.293-.293-.768 0-1.06s.768-.294 1.06 0l1.77 1.767 3.825-5.74c.23-.345.696-.436 1.04-.207.346.23.44.696.21 1.04z"></path></g></svg>';
                $html .= '<span class="css-901oao">' . $svg_image . '</span>';  
            }
        $html .= "&nbsp;&nbsp;</span>";

        $html .= "<span class='screen_name'><a href='https://twitter.com/" . esc_attr( $user->{ 'screen_name' } ) . "' target='_blank' rel='noopener'>@" . esc_attr( $user->{ 'screen_name' } ) . "</a></span>";
        $html .= "<span class='rc_myctf_dot_spacer'> &nbsp;&nbsp;.&nbsp;&nbsp; </span>";
        $html .= "<span class='tweet-date'><a href='https://twitter.com/" . esc_attr( $user->{ 'screen_name' } ) . "/status/" . esc_attr( $tweet->id_str ) . "' target='_blank' rel='noopener'>" . esc_attr( Rc_Myctf::rc_myctf_format_date( $tweet->{ 'created_at' } ) ) . "</a></span>";
        $html .= "</div>";

        return $html;
        
    }
    
    
    
    /* Returns a formatted date
     * 
     * @since 1.0
     * @access public
     * 
     * @param $date     string
     * @return DateTime object
     */
    public static function rc_myctf_format_date( $time_stamp_utc ) {
        
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
            $formatted_time = date( "M j", $tweet_unix_timestamp );
            
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
    public static function rc_myctf_check_if_url_should_be_fetched( $raw_external_url ) {
        
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
        
    } //ends rc_myctf_check_if_url_should_be_fetched


    
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
    public static function rc_myctf_check_if_title_desc_should_be_fetched( $raw_external_url ) {
        
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
        
    }//ends rc_myctf_check_if_title_desc_should_be_fetched
    
    
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
    public static function rc_myctf_get_tweet_cache_duration() {
        
        /* set default value of $duration */
        $duration = FALSE;
        
        /* Retrieve customization options from options */
        $options_customize = get_option( 'rc_myctf_customize_options' );
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
        
    } //ends rc_myctf_get_tweet_cache_duration

    
    
    /* 
     * Checks if shortcode exists for a page
     * It only checks the page content
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   null
     * @return  bool
     */
    public static function rc_myctf_check_if_shortcode_exists( $content ) {
        
      
        $tag = 'my_custom_tweets';
        
        if ( false === strpos( $content, '[' ) ) {
            return FALSE;
        }
        
        if (shortcode_exists( $tag ) ) {
            
            preg_match_all( '/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER );
            
            if ( empty( $matches ) ) {
                return FALSE;
            }
            
            /* this will hold the instances of our shortcode */
            $my_shortcodes = array();
            
            foreach ( $matches as $shortcode ) {
                if ( $tag === $shortcode[2] ) {
                    $my_shortcodes[] = $shortcode;
                } 
            } 
            
            $i = 1;
            foreach ( $my_shortcodes as $my_shortcode ) {
                
                $string = explode( ']', $my_shortcode[0] );
                echo 'Value of exploded string: ' . $string[0] . '<br><br>';
                $my_shortcode[0] = $string[0] . ' id="' . $i . '"]';
                
                $i++;
            }
        }
        
        return FALSE;
        
    } //ends function 
    //
    
    
    
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
    public static function rc_myctf_add_unique_ids_to_shortcodes( $content ) {
        
        global $post;
        $post_id = $post->ID;
        
        /* shortcode tag to search */
        $tag = 'my_custom_tweets';
        
        if ( false === strpos( $content, '[' ) ) {
            return $content;
        }
        
        if (shortcode_exists( $tag ) ) {
            
            preg_match_all( '/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER );
            
            if ( empty( $matches ) ) {
                return $content;
            }
            
            /* this will hold the instances of our original shortcode */
            $my_org_shortcodes = array();
            
            foreach ( $matches as $shortcode ) {
                if ( $tag === $shortcode[2] ) {
                    
                    /* we are retrieving only the shortcode part of the array */
                    $my_org_shortcodes[] = $shortcode[0];
                    
                } 
            } 
            
            $my_new_shortcodes = array();
            $i = 1;
            foreach ( $my_org_shortcodes as $my_org_shortcode ) {
                
                /* 
                 * explode removes the last ']' string from the shortcode.
                 * so it is like "[my_custom_tweets "
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
        }
               
        return $content;
        
        
    }//ends function rc_myctf_add_unique_ids_to_shortcodes


    
    
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
    public static function rc_myctf_fetch_merged_atts_options( $atts ) {
        
        /* Retrieve customization options from options */
        $options_customize = get_option( 'rc_myctf_customize_options' );
        
        if ( $options_customize === false ) {
            add_option( 'rc_myctf_customize_options' );
        }
        
        $feed_type = isset( $options_customize[ 'feed_type' ] ) ? wp_strip_all_tags( $options_customize[ 'feed_type' ] ) : 'user_timeline';
        $screen_name = isset( $options_customize[ 'screen_name' ] ) ? wp_strip_all_tags( $options_customize[ 'screen_name' ] ) : 'raycreations';
        $hashtags = isset( $options_customize[ 'hashtags' ] ) ? wp_strip_all_tags( $options_customize[ 'hashtags' ] ) : 'mountain clouds';
        $search_string = isset( $options_customize[ 'search_string' ] ) ? wp_strip_all_tags( $options_customize[ 'search_string' ] ) : 'fog sunrise';
        $feed_width_type = isset( $options_customize[ 'feed_width_type' ] ) ? wp_strip_all_tags( $options_customize[ 'feed_width_type' ] ) : 'responsive';
        $display_style = isset( $options_customize[ 'display_style' ] ) ? wp_strip_all_tags( $options_customize[ 'display_style' ] ) : 'display_list';
        $number_of_tweets = isset( $options_customize[ 'number_of_tweets' ] ) ? intval( $options_customize[ 'number_of_tweets' ] ) : 10;
        $number_of_tweets_in_row = isset( $options_customize[ 'number_of_tweets_in_row' ] ) ? intval( $options_customize[ 'number_of_tweets_in_row' ] ) : '3';
        $exclude_replies = isset( $options_customize[ 'exclude_replies' ] ) ? wp_strip_all_tags( $options_customize[ 'exclude_replies' ] ) : 0;
        $include_rts = isset( $options_customize[ 'include_rts' ] ) ? wp_strip_all_tags( $options_customize[ 'include_rts' ] ) : 0 ;
        $include_photos = isset( $options_customize[ 'include_photos' ] ) ? wp_strip_all_tags( $options_customize[ 'include_photos' ] ) : FALSE;
        $include_videos = isset( $options_customize[ 'include_videos' ] ) ? wp_strip_all_tags( $options_customize[ 'include_videos' ] ) : FALSE;
        
        $merged_atts_options = shortcode_atts( array(
            'id' => '',
            'feed_type' => $feed_type,
            'screen_name' => $screen_name,
            'hashtags' => $hashtags,
            'search_string' => $search_string,
            'feed_width_type' => $feed_width_type,
            'display_style' => $display_style,
            'count' => $number_of_tweets,
            'number_of_tweets_in_row' => $number_of_tweets_in_row,
            'exclude_replies' => $exclude_replies,
            'include_rts' => $include_rts,
            'include_photos' => $include_photos,
            'include_videos' => $include_videos,
        ), $atts);
        
        return $merged_atts_options;
        
    }//ends function rc_myctf_fetch_merged_atts_options
    
    
    
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
    public static function rc_myctf_get_tweets_error_html( $error_type ) {
        
        $html = '';
        
        if ( $error_type == 'tweets' ) {
            
            /* HTML to show instead of the tweets */
            $html .= '<div class="rc_myctf_tweets_wrap_error">';
            $html .= '<p>Oops! Something went wrong. Please try again after some time.</p>';
            $html .= '</div>';
            
        } elseif ( $error_type == 'keys' ) {
            
            /* HTML to show instead of the tweets */
            $html .= '<div class="rc_myctf_tweets_wrap_error">';
            $html .= '<p>You need to add your Consumer Key & Consumer Secret in the <a href="' . site_url() . '/wp-admin/options-general.php?page=myctf-page&tab=settings"> API Settings page</a> '
                    . 'to start displaying tweets on your website.</p>';
            $html .= "</div>";
            
        }
        
        return $html;
    }
    
    
    /* 
     * Checks whether API keys have been added to the plugin or not 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   string  $error_type Indicates the type of error and the corresponding HTML to return
     * 
     * @return  string  $html       Error message html
     */
    public static function rc_myctf_check_api_keys() {
        
        $options_settings = get_option( 'rc_myctf_settings_options' );
        $consumer_key = isset( $options_settings[ 'consumer_key' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_key' ] ) : '';
        $consumer_secret = isset( $options_settings[ 'consumer_secret' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_secret' ] ) : '';
        $bearer_token = isset( $options_settings[ 'bearer_token' ] ) ? sanitize_text_field( $options_settings[ 'bearer_token' ] ) : '';
        
        /* If bearer token is already generated, return true */
        if ( $bearer_token ) {
            return TRUE;
        }
        
        /* if consumer key & consumer secret is not set return false */
        if ( !$consumer_key || !$consumer_secret ) {
            return FALSE;
        }
        
    }//ends function rc_myctf_check_api_keys
    
    
    
    /* 
     * Localizes the js file with handle 'rc_myctf_slides'
     * This function sends the ids of the outer div of all shortcodes 
     * that are sliders to the js
     * 
     * All such sliders are stored in the array Rc_Myctf::$scodes_slides
     * 
     * @since 1.0
     * @access public
     * 
     */
    public static function rc_myctf_slides_localize_script() {
        
        /* holds the ids of the parent divs of each of the shortcodes on page that are sliders */
        $scodes_slides = Rc_Myctf::$scodes_slides;
        
        /*
         * count the total sliders
         * we sent it to js, to loop through it and get the ids of the
         * parent divs
         */
        $total_scode_sliders = count( $scodes_slides );
        
        $total_sliders_args = array(
            'total' => $total_scode_sliders
        );
        
        wp_localize_script( 'rc_myctf_slides', 'rc_myctf_slides_args', $scodes_slides );
        wp_localize_script( 'rc_myctf_slides', 'rc_myctf_total_sliders', $total_sliders_args );
        
    }//ends rc_myctf_slides_localize_script

    
}//ends class
