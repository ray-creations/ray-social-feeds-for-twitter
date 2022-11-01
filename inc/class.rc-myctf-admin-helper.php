<?php
/**
 * This class houses the functions and callbacks needed on the admin main page
 * to render the admin settings page.
 * 
 * This is done to make the code on the rc-myctf-admin.php class more organized,
 * manageable, and readable. 
 *
 * @author Ray Creations
 */

/**
 * Ensures the page is not accessed directly
 */
if ( !defined( 'ABSPATH' ) ){
    exit;
}


class Rc_Myctf_Admin_Helper {
    
    /** 
     * Function for displaying content in the API settings section
     * 
     * @since   1.0
     */
    public static function rc_myctf_api_section_callback(){
        
        /* Get options to check if bearer token exists */
        //$settings_options = get_option( 'rc_myctf_settings_options' );
        //$bearer_token = isset( $settings_options[ 'bearer_token' ] ) ? sanitize_text_field( $settings_options[ 'bearer_token' ] ) : '';
        
        /* url of our plugin page */
        $admin_url = admin_url( 'options-general.php?page=myctf-page' );
        
        $html = '';
        
        //if ( !empty( $bearer_token ) ) {
            
            /* Creating the button & link for bearer token deletion */
            //$rc_myctf_action_bearer = 'delete_bearer_token';
            
            /* construct url with query strings */
            //$delete_token_url = add_query_arg( array( 'rc_myctf_action_bearer' => $rc_myctf_action_bearer ), $admin_url );
            //$nonced_delete_bearer_token_url = wp_nonce_url( $delete_token_url, 'rc_myctf-' . $rc_myctf_action_bearer . '_bearer-token');
            
            //$html .= '<div id="bearer-token-delete-div">';
            //$html .= '<p><br><a href="' . $nonced_delete_bearer_token_url . '" id="rc_myctf_delete_token">Delete Current Bearer Token</a></p>';
            //$html .= '</div>';
        //}
        
        /* Creating the button & link for cached tweets deletion */
        $rc_myctf_action_cache = 'delete_cached_tweets';
        $delete_cache_url = add_query_arg( array( 'rc_myctf_action_cache' => $rc_myctf_action_cache ), $admin_url );
        $nonced_delete_cache_url = wp_nonce_url( $delete_cache_url, 'rc_myctf-' . $rc_myctf_action_cache . '_cache' );
        
        $html .= '<div id="bearer-token-delete-div">';
        $html .= '<p><br><a href="' . $nonced_delete_cache_url . '" id="rc_myctf_delete_cache">Delete Cached Tweets</a></p>';
        $html .= '</div>';
        
        $html .= '<div id="consumer-key-info-div"><p><br><hr>';
        $html .= '<strong>Note (optional)</strong>: You can <a href="https://www.raycreations.net/generating-twitter-api-keys/" title="create a Twitter app" target="_blank" rel="noopener">create your own Twitter app</a> to obtain your own API keys & secret needed for this plugin to fetch tweets &#128522;<br><br>';
        $html .= 'Please enter your consumer key and consumer secret below:<br><br>';
        $html .= '</p></div>';
        echo $html;
    }
    
    
    /**
     * Function to output Consumer Key field content 
     * 
     * @since 1.0
     */
    public static function rc_myctf_consumer_key_callback( $args ){
        
        $options = get_option( 'rc_myctf_settings_options' );
        $consumer_key = isset( $options[ 'consumer_key' ] ) ? sanitize_text_field( $options[ 'consumer_key' ] ) : '';

        $html = "<input type='text' id='consumer_key' name='rc_myctf_settings_options[consumer_key]' value='$consumer_key' />";
        $html .= "<label for='consumer_key'> &nbsp;&nbsp;" . $args[0] . "</label>";
        echo $html;
        
    }
    
    
    /** 
     * Function to output Consumer Secret field content
     * 
     * @since 1.0
     */
    public static function rc_myctf_consumer_secret_callback( $args ){
        
        $options = get_option( 'rc_myctf_settings_options' );
        $consumer_secret = isset( $options[ 'consumer_secret' ] ) ? sanitize_text_field( $options[ 'consumer_secret' ] ) : '';

        
        $html = "<input type='text' id='consumer_secret' name='rc_myctf_settings_options[consumer_secret]' value='$consumer_secret' />";
        $html .= "<label for='consumer_secret'> &nbsp;&nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    
    /*
     * Functions & callbacks for the Setting tab 'Preserve Settings' section 
     */
    public static function rc_myctf_api_plugin_settings_section_callback(){
        echo "Find your plugin specific sections here.";
    }
    
    /*
     * Function to output the preserve settings checkbox
     */
    public static function rc_myctf_preserve_settings_callback( $args ){
        $options = get_option( 'rc_myctf_settings_options' );
        $preserve_settings = isset( $options[ 'preserve_settings' ] ) ? strip_tags( $options[ 'preserve_settings' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="preserve_settings" name="rc_myctf_settings_options[preserve_settings]" value="1" ' . checked( 1, $preserve_settings, false ) . '>';
        $html .= '<label for="preserve_settings"> ' . $args[0] . '</label>';
        
        echo $html;
    }
    
    
    
    
    /*
     * Functions & callbacks for Invalidate Bearer Token section
     */
    public static function rc_myctf_api_invalidate_token_section_callback() {
        $invalidate_token_url = add_query_arg( 
                array( 
                    'rc_myctf_action' => 'invalidate_token',
                ));
        
        $nonced_invalidate_url = wp_nonce_url( $invalidate_token_url, 'rc_myctf-invalidate_token_bearer-token');
        //admin_url( 'admin.php?page=myctf-page' );
        
        $html = 'Click on link below to invalidate your current bearer token. '
                . 'This will also generate a new bearer token for you. You need it to fetch Twitter data &#128522;<br><br>';
        $html .= '<p>Note: Only do this if you feel your <strong>bearer token</strong> has been compromised and can be misused.</p>';
        $html .= '<p><a href="' . $nonced_invalidate_url . '" id="rc_myctf_invalidate_token"> Click To Invalidate & Generate New Bearer Token</a></p>';
        $html .= '<br><br><br>';
        echo $html;
    }










    /*
     * Functions & callbacks for the Customize 'Feed Settings' section 
     */
    public static function rc_myctf_feed_settings_section_callback(){
        echo "Settings for the type of feed you want to display on your site. These would be your default settings. "
        . "You would be able to set different options using shortcodes. ";
    }
    
    /* Function to output the html for feed type radio buttons  */
    public static function rc_myctf_feed_type_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $feed_type = isset( $options[ 'feed_type' ] ) ? sanitize_text_field( $options[ 'feed_type' ] ) : 'user_timeline';
        
        
        // Values are: fixed, percentage
        $html = "<input type='radio' id='user_timeline' name='rc_myctf_customize_options[feed_type]'" . checked( 'user_timeline', $feed_type, false)  . "value='user_timeline'> User Timeline </input><br>";
        $html .= "<input type='radio' id='hashtags_timeline' name='rc_myctf_customize_options[feed_type]'" . checked( 'hashtags_timeline', $feed_type, false)  . "value='hashtags_timeline'> Hashtags </input><br>";
        $html .= "<input disabled type='radio' id='search_timeline' name='rc_myctf_customize_options[feed_type]'" . checked( 'search_timeline', $feed_type, false)  . "value='search_timeline'> Search </input>";
               
        $html .= "<p><label for='search_timeline'> &nbsp;&nbsp;" . $args[0] . "</label></p>";
        echo $html;   
    }
    
    /* Function to output the HTML for screen name textbox  */
    public static function rc_myctf_screen_name_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $screen_name = isset( $options[ 'screen_name' ] ) ? sanitize_text_field( $options[ 'screen_name' ] ) : 'raycreations';
        
        $html = "<input type='text' id='screen_name' name='rc_myctf_customize_options[screen_name]' value='$screen_name' />";
        $html .= "<label for='screen_name'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    /* Function to output the HTML for Hashtags field */
    public static function rc_myctf_hashtags_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $hashtags = isset( $options[ 'hashtags' ] ) ? sanitize_text_field( $options[ 'hashtags' ] ) : 'mountain clouds';
        
        $html = "<input type='text' id='hashtags' name='rc_myctf_customize_options[hashtags]' value='$hashtags' />";
        $html .= "<label for='hashtags'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
        
    }
    
    /* Function to output the HTML for search_string field */
    public static function rc_myctf_search_string_callback( $args ) {
        
        $options = get_option( 'rc_myctf_customize_options' );
        $search_string = isset( $options[ 'search_string' ] ) ? sanitize_text_field( $options[ 'search_string' ] ) : 'fog sunrise';
        
        $html = "<input type='text' disabled id='search_string' name='rc_myctf_customize_options[search_string]' value='$search_string' />";
        $html .= "<label for='search_string'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    /* Function to "include_meida_type" in tweet & hashtags serach results */
    public static function rc_myctf_include_media_type_callback( $args ) {
        
        $options = get_option( 'rc_myctf_customize_options' );
        $include_photos = isset( $options[ 'include_photos' ] ) ? strip_tags( $options[ 'include_photos' ] ) : FALSE;
        $include_videos = isset( $options[ 'include_videos' ] ) ? strip_tags( $options[ 'include_videos' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="include_photos" name="rc_myctf_customize_options[include_photos]" value="1" ' . checked( 1, $include_photos, false ) . '>';
        $html .= '<label for="include_photos"> &nbsp;&nbsp;' . $args[0] . '</label><br>';
        
        $html .= '<input type="checkbox" id="include_videos" name="rc_myctf_customize_options[include_videos]" value="1" ' . checked( 1, $include_videos, false ) . '>';
        $html .= '<label for="include_videos"> &nbsp;&nbsp;' . $args[1] . '</label>';
        echo $html;
        
    }


    /* Function to output the HTML for Exclude Replies checkbox field */
    public static function rc_myctf_exclude_replies_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $exclude_replies = isset( $options[ 'exclude_replies' ] ) ? strip_tags( $options[ 'exclude_replies' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="exclude_replies" name="rc_myctf_customize_options[exclude_replies]" value="1" ' . checked( 1, $exclude_replies, false ) . '>';
        $html .= '<label for="exclude_replies"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
            
    /* Function to output the HTML for Exclude Replies checkbox field */
    public static function rc_myctf_include_retweets_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $include_rts = isset( $options[ 'include_rts' ] ) ? strip_tags( $options[ 'include_rts' ] ) : 0;
        
        $html = '<input type="checkbox" id="include_rts" name="rc_myctf_customize_options[include_rts]" value="1" ' . checked( 1, $include_rts, false ) . '>';
        $html .= '<label for="include_rts"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
            
            

        
    /**
     * Functions & callbacks for the Customize Layout section
     */
    public static function rc_myctf_layout_section_callback(){
        echo "A few more options to customize your Twitter feed";
    }
    
    
    /**
     * Function to output "width type" radio selection html within the additional customization section
     */
    public static function rc_myctf_width_type_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $feed_width_type = isset( $options[ 'feed_width_type' ] ) ? sanitize_text_field( $options[ 'feed_width_type' ] ) : 'responsive';
        
        // Values are: fixed, percentage
        $html = "<input type='radio' id='responsive' name='rc_myctf_customize_options[feed_width_type]' checked disabled value='responsive'> Responsive </input>";
               
        $html .= "<label for='responsive'> &nbsp;&nbsp;" . $args[0] . "</label>";
        echo $html;
    }
    
    /*
     * Function to output "display_style" radio selection HTML within the Additional Customization section.
     */
    public static function rc_myctf_display_style_callback( $args ) {
        
        $options = get_option( 'rc_myctf_customize_options' );
        $display_style = isset( $options[ 'display_style' ] ) ? sanitize_text_field( $options[ 'display_style' ] ) : 'display_list';
        
        $html = "<input type='radio' id='display_list' name='rc_myctf_customize_options[display_style]'" . checked( 'display_list', $display_style, false)  . "value='display_list'> List </input><br>";
        $html .= "<input type='radio' disabled id='display_masonry' name='rc_myctf_customize_options[display_style]'" . checked( 'display_masonry', $display_style, false)  . "value='display_masonry'> Masonry <span class='rc_myctf_tip'>(Available in Pro)</span> </input><br>";
        $html .= "<input type='radio' disabled id='display_slider_1_col' name='rc_myctf_customize_options[display_style]'" . checked( 'display_slider_1_col', $display_style, false)  . "value='display_slider_1_col'> Slider 1 Column <span class='rc_myctf_tip'>(Available in Pro)</span> </input><br>";
        $html .= "<input type='radio' disabled id='display_slider_2_col' name='rc_myctf_customize_options[display_style]'" . checked( 'display_slider_2_col', $display_style, false)  . "value='display_slider_2_col'> Slider 2 Column <span class='rc_myctf_tip'>(Available in Pro)</span> </input>";
        
        $html .= "<p><label for='rc_myctf_customize_options[display_style]'> &nbsp;&nbsp;" . $args[0] . "</label></p>";
        echo $html;
        
    }




    

    /*
     * Function to output 'Number of Tweets' field html  
     */
    public static function rc_myctf_number_of_tweets_callback( $args ){
        $options = get_option( 'rc_myctf_customize_options' );
        $number_of_tweets = isset( $options[ 'number_of_tweets' ] ) ? intval( $options[ 'number_of_tweets' ] ) : '10';
        
        $html = "<input type='text' id='number_of_tweets' name='rc_myctf_customize_options[number_of_tweets]' value='$number_of_tweets' />";
        $html .= "<label for='number_of_tweets'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    /*
     * Function to output 'Tweets in row' field html  
     */
    public static function rc_myctf_number_of_tweets_in_row_callback( $args ){
        $options = get_option( 'rc_myctf_customize_options' );
        $number_of_tweets_in_row = isset( $options[ 'number_of_tweets_in_row' ] ) ? intval( $options[ 'number_of_tweets_in_row' ] ) : '3';
        
        $html = "<input disabled type='text' id='number_of_tweets_in_row' name='rc_myctf_customize_options[number_of_tweets_in_row]' value='$number_of_tweets_in_row' />";
        $html .= "<label for='number_of_tweets_in_row'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /*
     * Function to output the 'Check Tweets Every' field html
     */
    public static function rc_myctf_check_tweets_every_callback( $args ){
        $options = get_option( 'rc_myctf_customize_options' );
        $check_tweets_every = isset( $options[ 'check_tweets_every' ] ) ? sanitize_text_field( $options[ 'check_tweets_every' ] ) : 'hour';
        
        // permitted values are 'hour' & 'day'
        $html = "<input type='radio' name='rc_myctf_customize_options[check_tweets_every]'" . checked( 'hour', $check_tweets_every, false)  . " id='hour' value='hour'> hour </input>";
        $html .= "<input type='radio' name='rc_myctf_customize_options[check_tweets_every]'" . checked( 'day', $check_tweets_every, false)  . " id='day' value='day'> day </input>";
        
        //$html .= "<label for='check_tweets_every'> &nbsp; &nbsp;" . $args[0] . "</label>" ;
        
        echo $html;
    }
    
    
    /** 
     * Function to output 'tweet checking interval' field html  
     */
    public static function rc_myctf_tweet_checking_interval_callback( $args ){
        $options = get_option( 'rc_myctf_customize_options' );
        $tweet_checking_interval = isset( $options[ 'tweet_checking_interval' ] ) ? intval( $options[ 'tweet_checking_interval' ] ) : '1';
        
        $html = "<input type='text' id='tweet_checking_interval' name='rc_myctf_customize_options[tweet_checking_interval]' value='$tweet_checking_interval' />";
        $html .= "<label for='tweet_checking_interval'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    
    
    
    
    /**
     * Functions & callbacks for the "Need Help?" section of the Support tab
     */
    public static function rc_myctf_api_support_settings_section_callback(){
        $html = '';
        $html .= 'Need support with this plugin?<br>';
        $html .= 'Please email us at support[@]raycreations.net<br><br>';
        $html .= '<p>Do provide as much detail as you can so that we can resolve the issues faster.</p><br>';
        $html .= '<strong>List of example Shortcodes:</strong><br>';
        $html .= '[my_custom_tweets]<br>';
        $html .= '[my_custom_tweets count="3"]<br>';
        $html .= '[my_custom_tweets feed_type="user_timeline" screen_name="MySwitzerlandIN"]<br>';
        $html .= '[my_custom_tweets feed_type="hashtags_timeline" hashtags="nature photography"]<br>';
        $html .= '[my_custom_tweets count="3" exclude_replies="true" include_rts="false"]<br>';
        
        echo $html;
    }
    
    
    
    
    
    /*
     * Validates the inputs & fields of the Settings Options tab
     * 
     * @since 1.0
     * @access public
     * 
     * @param   $input  array   An array of settings options to be validated
     * @return  $valid  array   An array of validated inputs
     */
    public static function rc_myctf_settings_validate_options( $input ) {
        
        //Create an array for storing the validated options
        $valid = array();
        
        //Loop through each of the incoming options
        foreach ( $input as $key => $value ) {
            
            //Check to see if the input option has a value. If so, process it.
            if ( isset( $input[ $key ] ) ) {
                $valid[ $key ] = strip_tags( stripslashes( $input[ $key ] ) );
            }// end if
            
        }// end foreach
        
        //Return the array processing any additional functions filtered by this action
        return apply_filters( 'rc_myctf_settings_validate_options', $valid, $input );
        
    }
    
    
    /*
     * Validates the inputs & fields of the Customize Options tab
     * 
     * @since 1.0
     * @access public
     * 
     * @param   $input  array   An array of settings options to be validated
     * @return  $valid  array   An array of validated inputs
     */
    public static function rc_myctf_customize_validate_options( $input ) {
        
        //Create an array for storing the validated options
        $valid = array();
        
        //Loop through each of the incoming options
        foreach ( $input as $key => $value ) {
            
            //Check to see if the input option has a value. If so, process it.
            if ( isset( $input[ $key ] ) ) {
                $valid[ $key ] = trim( strip_tags( stripslashes( $input[ $key ] ) ) );
            }// end if
            
        }// ends foreach
        
        $valid[ 'hashtags' ] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $valid[ 'hashtags' ] );
        $valid[ 'screen_name' ] = preg_replace( '/[^a-zA-Z0-9_]/', '', $valid[ 'screen_name' ]);
        
        /*
         * Additional processing of 'number of tweets in a row' value.
         * Make sure it is greater than zero
         * And less than or equal to 5
         */
        if ( $valid[ 'number_of_tweets_in_row' ] < 1 ) {
            
            /*  ensure the minimum value   */
            $valid[ 'number_of_tweets_in_row' ] = 1;
            
        } elseif ( $valid[ 'number_of_tweets_in_row' ] > 5 ) {
        
            /* if the value is above 5, set it forcibly to 5 */
            $valid[ 'number_of_tweets_in_row' ] = 5;
        }
        
        
        /*
         * Additional processing for 'number_of_tweets'
         * Fixing it between 1-50
         */
        if ( $valid[ 'number_of_tweets' ] < 1 ) {
            $valid[ 'number_of_tweets' ] = 1;
        }elseif ( $valid[ 'number_of_tweets' ] > 10 ){
            $valid[ 'number_of_tweets' ] = 10;
        }
        
        //Return the array processing any additional functions filtered by this action
        return apply_filters( 'rc_myctf_customize_validate_options', $valid, $input );
        
        
    }
    
    
}//ends class
