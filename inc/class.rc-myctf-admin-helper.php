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
        
        /* Button to fetch API keys */
        $rc_myctf_action_token = 'fetch_access_token';
        $rc_myctf_fetch_token_url = add_query_arg( array( 'rc_myctf_action_token' => $rc_myctf_action_token ), RC_MYCTF_ADMIN_URL );
        $nonced_fetch_token_url = wp_nonce_url( $rc_myctf_fetch_token_url, 'rc_myctf-' . $rc_myctf_action_token . '_fetch-token' );
        
        $html = '<div id="fetch-access-token-div">';
        $html .= '<p><br><a href="' . $nonced_fetch_token_url . '" id="rc_myctf_fetch_token">Generate Access Token &amp; Secret</a></p>';
        $html .= '</div>';
        
        /* Creating the button & link for cached tweets deletion */
        $rc_myctf_action_cache = 'delete_cached_tweets';
        $delete_cache_url = add_query_arg( array( 'rc_myctf_action_cache' => $rc_myctf_action_cache ), RC_MYCTF_ADMIN_URL );
        $nonced_delete_cache_url = wp_nonce_url( $delete_cache_url, 'rc_myctf-' . $rc_myctf_action_cache . '_cache' );
        
        $html .= '<div id="delete_cached_tweets-div">';
        $html .= '<p><br><a href="' . $nonced_delete_cache_url . '" id="rc_myctf_delete_cache">Delete Cached Tweets</a></p>';
        $html .= '</div>';
        
        $html .= '<div id="consumer-key-info-div"><p><br><hr>';
        $html .= '<strong>Note (optional)</strong>: Want to use your existing API Credentials? Feel free to enter them in the fields below. '
                . 'See how to <a href="https://www.raycreations.net/generating-twitter-api-keys/" title="create a Twitter app" target="_blank" rel="noopener">create your own Twitter app</a><br><br>';
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
        
        /* hidden field value  */
        $app_consumer_key = isset( $options[ 'app_consumer_key' ] ) ? sanitize_text_field( $options[ 'app_consumer_key' ] ) : '';

        
        $html = "<input type='text' id='consumer_key' name='rc_myctf_settings_options[consumer_key]' value='$consumer_key' />";
        $html .= "<label for='consumer_key'> &nbsp;&nbsp;" . $args[0] . "</label>";
        
        /* hidden field ( don't want to create "add_settings_field" as it needs to stay hidden ) */
        $html .= "<input type='hidden' id='app_consumer_key' name='rc_myctf_settings_options[app_consumer_key]' value='$app_consumer_key' />";
        
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
        /* hidden field value */
        $app_consumer_secret = isset( $options[ 'app_consumer_key' ] ) ? sanitize_text_field( $options[ 'app_consumer_secret' ] ) : '';

        
        $html = "<input type='text' id='consumer_secret' name='rc_myctf_settings_options[consumer_secret]' value='$consumer_secret' />";
        $html .= "<label for='consumer_secret'> &nbsp;&nbsp;" . $args[0] . "</label>";
        
        /* hidden field ( don't want to create "add_settings_field" as it needs to stay hidden ) */
        $html .= "<input type='hidden' id='app_consumer_secret' name='rc_myctf_settings_options[app_consumer_secret]' value='$app_consumer_secret' />";
        
        echo $html;
    }
    
    
    /** 
     * Function to output Access Token content
     * 
     * @since 1.0
     */
    public static function rc_myctf_access_token_callback( $args ){
        
        $options = get_option( 'rc_myctf_settings_options' );
        $access_token = isset( $options[ 'access_token' ] ) ? sanitize_text_field( $options[ 'access_token' ] ) : '';
        
        $html = "<input type='text' id='access_token' name='rc_myctf_settings_options[access_token]' value='$access_token' />";
        $html .= "<label for='access_token'> &nbsp;&nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /** 
     * Function to output Access Token Secret content
     * 
     * @since 1.0
     */
    public static function rc_myctf_access_token_secret_callback( $args ){
        
        $options = get_option( 'rc_myctf_settings_options' );
        $access_token_secret = isset( $options[ 'access_token_secret' ] ) ? sanitize_text_field( $options[ 'access_token_secret' ] ) : '';
        
        $html = "<input type='text' id='access_token_secret' name='rc_myctf_settings_options[access_token_secret]' value='$access_token_secret' />";
        $html .= "<label for='access_token_secret'> &nbsp;&nbsp;" . $args[0] . "</label>";
        
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
        $html .= "<input type='radio' id='mentions_timeline' name='rc_myctf_customize_options[feed_type]'" . checked( 'mentions_timeline', $feed_type, false)  . "value='mentions_timeline'> Mentions </input><br>";
        $html .= "<input type='radio' id='hashtags_timeline' name='rc_myctf_customize_options[feed_type]'" . checked( 'hashtags_timeline', $feed_type, false)  . "value='hashtags_timeline'> Hashtags  </input><span class='rc_myctf_tip'>(Available in Pro)</span><br>";
        $html .= "<input type='radio' id='search_timeline' name='rc_myctf_customize_options[feed_type]'" . checked( 'search_timeline', $feed_type, false)  . "value='search_timeline'> Search </input><span class='rc_myctf_tip'>(Available in Pro)</span>";
               
        $html .= "<p><label for='search_timeline'> &nbsp;&nbsp;" . $args[0] . "</label></p>";
        echo $html;   
    }
    
    /* Function to output the HTML for screen name textbox  */
    public static function rc_myctf_screen_name_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $screen_name = isset( $options[ 'screen_name' ] ) ? sanitize_text_field( $options[ 'screen_name' ] ) : 'raycreations';
        
        $html = "<input type='text' id='rc_myctf_customize_options[screen_name]' name='rc_myctf_customize_options[screen_name]' value='$screen_name' />";
        $html .= "<label for='screen_name'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    /* Function to output the HTML for Hashtags field */
    public static function rc_myctf_hashtags_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $hashtags = isset( $options[ 'hashtags' ] ) ? sanitize_text_field( $options[ 'hashtags' ] ) : 'mountain clouds';
        
        $html = "<input type='text' class='rc-myctf-pro-feature' id='rc_myctf_customize_options[hashtags]' name='rc_myctf_customize_options[hashtags]' value='$hashtags' />";
        $html .= "<label for='rc_myctf_customize_options[hashtags]'> &nbsp; &nbsp;" . $args[0] . "<span class='rc_myctf_tip'>(Available in Pro)</span></label>";
        
        echo $html;
        
    }
    
    /* Function to output the HTML for search_string field */
    public static function rc_myctf_search_string_callback( $args ) {
        
        $options = get_option( 'rc_myctf_customize_options' );
        $search_string = isset( $options[ 'search_string' ] ) ? sanitize_text_field( $options[ 'search_string' ] ) : 'fog sunrise';
        
        $html = "<input type='text' class='rc-myctf-pro-feature' id='rc_myctf_customize_options[search_string]' name='rc_myctf_customize_options[search_string]' value='$search_string' />";
        $html .= "<label for='rc_myctf_customize_options[search_string]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
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
        $html .= "<input type='radio' id='display_masonry' name='rc_myctf_customize_options[display_style]'" . checked( 'display_masonry', $display_style, false)  . "value='display_masonry'> Masonry </input><span class='rc_myctf_tip'>(Available in Pro)</span><br>";
        $html .= "<input type='radio' id='display_slider_1_col' name='rc_myctf_customize_options[display_style]'" . checked( 'display_slider_1_col', $display_style, false)  . "value='display_slider_1_col'> Slider 1 Column </input><br>";
        $html .= "<input type='radio' id='display_slider_2_col' name='rc_myctf_customize_options[display_style]'" . checked( 'display_slider_2_col', $display_style, false)  . "value='display_slider_2_col'> Slider 2 Column </input><span class='rc_myctf_tip'>(Available in Pro)</span>";
        
        $html .= "<p><label for='rc_myctf_customize_options[display_style]'> &nbsp;&nbsp;" . $args[0] . "</label></p>";
        echo $html;
        
    }

    /* Function to output the HTML for Hide Media checkbox field */
    public static function rc_myctf_hide_media_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $hide_media = isset( $options[ 'hide_media' ] ) ? strip_tags( $options[ 'hide_media' ] ) : 0;
        
        $html = '<input type="checkbox" id="hide_media" class="hide_media_chk" name="rc_myctf_customize_options[hide_media]" value="1" ' . checked( 1, $hide_media, false ) . '>';
        $html .= '<label for="hide_media"> ' . $args[0] . '</label>';
        
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
        
        $html = "<input type='text' id='number_of_tweets_in_row' name='rc_myctf_customize_options[number_of_tweets_in_row]' value='$number_of_tweets_in_row' />";
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
     * Functions & callbacks for the Customize Links section
     */
    public static function rc_myctf_customize_links_section_callback(){
        echo "Link related settings";
    }
    
    
    /* Function to output the HTML for 'remove external links' checkbox field */
    public static function rc_myctf_customize_links_hashtags_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $remove_links_hashtags = isset( $options[ 'remove_links_hashtags' ] ) ? strip_tags( $options[ 'remove_links_hashtags' ] ) : 0;
        
        $html = '<input type="checkbox" id="remove_links_hashtags" class="remove_links_hashtags_chk" name="rc_myctf_customize_options[remove_links_hashtags]" value="1" ' . checked( 1, $remove_links_hashtags, false ) . '>';
        $html .= '<label for="remove_links_hashtags"> ' . $args[0] . '<span class="rc_myctf_tip">(Available in Pro)</span></label>';
        
        echo $html;
    }
    
    
    /* Function to output the HTML for 'remove external links' checkbox field */
    public static function rc_myctf_customize_links_mentions_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $remove_links_mentions = isset( $options[ 'remove_links_mentions' ] ) ? strip_tags( $options[ 'remove_links_mentions' ] ) : 0;
        
        $html = '<input type="checkbox" id="remove_links_mentions" class="remove_links_mentions_chk" name="rc_myctf_customize_options[remove_links_mentions]" value="1" ' . checked( 1, $remove_links_mentions, false ) . '>';
        $html .= '<label for="remove_links_mentions"> ' . $args[0] . '<span class="rc_myctf_tip">(Available in Pro)</span></label>';
        
        echo $html;
    }
    
    
    /* Function to output the HTML for 'remove external links' checkbox field */
    public static function rc_myctf_customize_remove_ext_links_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $remove_ext_links = isset( $options[ 'remove_ext_links' ] ) ? strip_tags( $options[ 'remove_ext_links' ] ) : 0;
        
        $html = '<input type="checkbox" id="remove_ext_links" class="remove_ext_links_chk" name="rc_myctf_customize_options[remove_ext_links]" value="1" ' . checked( 1, $remove_ext_links, false ) . '>';
        $html .= '<label for="remove_ext_links"> ' . $args[0] . '<span class="rc_myctf_tip">(Available in Pro)</span></label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for nofollow links checkbox field */
    public static function rc_myctf_customize_link_add_nofollow_callback( $args ){
        
        $options = get_option( 'rc_myctf_customize_options' );
        $nofollow_ext_links = isset( $options[ 'nofollow_ext_links' ] ) ? strip_tags( $options[ 'nofollow_ext_links' ] ) : 0;
        
        $html = '<input type="checkbox" id="nofollow_ext_links" name="rc_myctf_customize_options[nofollow_ext_links]" value="1" ' . checked( 1, $nofollow_ext_links, false ) . '>';
        $html .= '<label for="nofollow_ext_links"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    

    
    
    
    /*
     * Functions & callbacks for the Tweets tab - 'Tweet Header' section 
     */
    public static function rc_myctf_tweet_general_section_callback(){
        
        /* Creating the button & link to 'reset to default' option for tweet visibility option */
        $rc_myctf_action = 'reset_tweets_visibility';
        $reset_visibility_url = add_query_arg( array( 'tab' => 'tweets', 'rc_myctf_action_reset' => $rc_myctf_action ), RC_MYCTF_ADMIN_URL );
        $nonced_reset_visibility_url = wp_nonce_url( $reset_visibility_url, 'rc_myctf-' . $rc_myctf_action . '_reset' );
        
        $html = '<div id="reset-tweets-visibility-div">';
        $html .= '<p><a href="' . $nonced_reset_visibility_url . '" id="rc_myctf_reset_visibility">Reset To Default Options</a></p>';
        $html .= '</div>';
        $html .= 'This section lets you control the visibility of general Tweet components';
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Display Border field */
    public static function rc_myctf_tweet_display_border_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_tweet_border = isset( $options[ 'display_tweet_border' ] ) ? strip_tags( $options[ 'display_tweet_border' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_tweet_border" name="rc_myctf_tweets_options[display_tweet_border]" value="1" ' . checked( 1, $display_tweet_border, false ) . '>';
        $html .= '<label for="display_header"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    
    /*
     * Functions & callbacks for the Tweets tab - 'Tweet Header' section 
     */
    public static function rc_myctf_tweet_header_section_callback(){
        echo "This section lets you control the display of the Tweet Header and the visibility of its components";
    }
    
    
    /* Function to output the HTML for Display Header checkbox field */
    public static function rc_myctf_display_header_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_header = isset( $options[ 'display_header' ] ) ? strip_tags( $options[ 'display_header' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_header" name="rc_myctf_tweets_options[display_header]" value="1" ' . checked( 1, $display_header, false ) . '>';
        $html .= '<label for="display_header"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    /* Function to output the HTML for Twitter Profile image checkbox field */
    public static function rc_myctf_display_profile_img_header_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_profile_img_header = isset( $options[ 'display_profile_img_header' ] ) ? strip_tags( $options[ 'display_profile_img_header' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_profile_img_header" name="rc_myctf_tweets_options[display_profile_img_header]" value="1" ' . checked( 1, $display_profile_img_header, false ) . '>';
        $html .= '<label for="display_profile_img_header"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Twitter Display name checkbox field */
    public static function rc_myctf_display_name_header_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_name_header = isset( $options[ 'display_name_header' ] ) ? strip_tags( $options[ 'display_name_header' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_name_header" name="rc_myctf_tweets_options[display_name_header]" value="1" ' . checked( 1, $display_name_header, false ) . '>';
        $html .= '<label for="display_name_header"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Twitter Display name checkbox field */
    public static function rc_myctf_display_screen_name_header_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_screen_name_header = isset( $options[ 'display_screen_name_header' ] ) ? strip_tags( $options[ 'display_screen_name_header' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_screen_name_header" name="rc_myctf_tweets_options[display_screen_name_header]" value="1" ' . checked( 1, $display_screen_name_header, false ) . '>';
        $html .= '<label for="display_screen_name_header"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Twitter Display name checkbox field */
    public static function rc_myctf_display_date_header_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_date_header = isset( $options[ 'display_date_header' ] ) ? strip_tags( $options[ 'display_date_header' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_date_header" name="rc_myctf_tweets_options[display_date_header]" value="1" ' . checked( 1, $display_date_header, false ) . '>';
        $html .= '<label for="display_date_header"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    
    
    /*
     * Functions & callbacks for the Tweets tab - 'Tweet Footer' section 
     */
    public static function rc_myctf_tweet_footer_section_callback(){
        echo "This section lets you control the display of the Tweet Footer and the visibility of its components";
    }
    
    
    /* Function to output the HTML for Display Tweet Footer checkbox field */
    public static function rc_myctf_display_tweet_footer_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_footer = isset( $options[ 'display_footer' ] ) ? strip_tags( $options[ 'display_footer' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_footer" name="rc_myctf_tweets_options[display_footer]" value="1" ' . checked( 1, $display_footer, false ) . '>';
        $html .= '<label for="display_footer"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Display Likes checkbox field */
    public static function rc_myctf_display_likes_footer_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_likes_footer = isset( $options[ 'display_likes_footer' ] ) ? strip_tags( $options[ 'display_likes_footer' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_likes_footer" name="rc_myctf_tweets_options[display_likes_footer]" value="1" ' . checked( 1, $display_likes_footer, false ) . '>';
        $html .= '<label for="display_likes_footer"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Display Retweets checkbox field */
    public static function rc_myctf_display_retweets_footer_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_retweets_footer = isset( $options[ 'display_retweets_footer' ] ) ? strip_tags( $options[ 'display_retweets_footer' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_retweets_footer" name="rc_myctf_tweets_options[display_retweets_footer]" value="1" ' . checked( 1, $display_retweets_footer, false ) . '>';
        $html .= '<label for="display_retweets_footer"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Display Retweets checkbox field */
    public static function rc_myctf_display_screen_name_footer_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_screen_name_footer = isset( $options[ 'display_screen_name_footer' ] ) ? strip_tags( $options[ 'display_screen_name_footer' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_screen_name_footer" name="rc_myctf_tweets_options[display_screen_name_footer]" value="1" ' . checked( 1, $display_screen_name_footer, false ) . '>';
        $html .= '<label for="display_screen_name_footer"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Display Retweets checkbox field */
    public static function rc_myctf_display_date_footer_callback( $args ){
        
        $options = get_option( 'rc_myctf_tweets_options' );
        $display_date_footer = isset( $options[ 'display_date_footer' ] ) ? strip_tags( $options[ 'display_date_footer' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="display_date_footer" name="rc_myctf_tweets_options[display_date_footer]" value="1" ' . checked( 1, $display_date_footer, false ) . '>';
        $html .= '<label for="display_date_footer"> ' . $args[0] . '</label>';
        
        echo $html;
        
    }
    
    
    
    
    
    /*
     * Functions & callbacks for the Style tab - 'Style General' section 
     */
    public static function rc_myctf_style_general_section_callback(){
        
        /* Creating the button & link to 'reset to default' option for tweet visibility option */
        $rc_myctf_action = 'reset_style';
        $reset_style_url = add_query_arg( array( 'tab' => 'style', 'rc_myctf_action_reset' => $rc_myctf_action ), RC_MYCTF_ADMIN_URL );
        $nonced_reset_style_url = wp_nonce_url( $reset_style_url, 'rc_myctf-' . $rc_myctf_action . '_reset' );
        
        $html = '<div id="reset-style-div">';
        $html .= '<p><a href="' . $nonced_reset_style_url . '" id="rc_myctf_reset_style">Reset To Default Options</a></p>';
        $html .= '</div>';
        $html .= 'This section lets you control the General overall styling of the Tweets section.';
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for choosing general font-size */
    public static function rc_myctf_style_font_size_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $font_size = isset( $options[ 'font_size' ] ) ? sanitize_text_field( $options[ 'font_size' ] ) : 'inherit';
        
        //Get the various font sizes
        $fontSizes = Rc_Myctf_Admin::$fontSizes;
        
        $html = '';
            
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[font_size]" name="rc_myctf_style_options[font_size]">';
                foreach ( $fontSizes as $fontSize ) {
                    $html .= '<option value="' . esc_attr( $fontSize ) . '" ' . selected( $font_size, $fontSize, false ) . '>' . sanitize_text_field( $fontSize ) . '</option>' ;
                }
            $html .= '</select>';
        
        $html .= "<label for='rc_myctf_style_options[font_size]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing font color */
    public static function rc_myctf_style_font_color_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $font_color = isset( $options[ 'font_color' ] ) ? sanitize_text_field( $options[ 'font_color' ] ) : '';
        
        $html = "<input type='text' id='font_color' name='rc_myctf_style_options[font_color]' value='$font_color' class='rc-myctf-color-fields' />";
        $html .= "<label for='font_color'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing general link text decoration */
    public static function rc_myctf_style_link_text_decoration_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $link_text_decoration = isset( $options[ 'link_text_decoration' ] ) ? sanitize_text_field( $options[ 'link_text_decoration' ] ) : 'inherit';
 
        //Get the various font sizes
        $textDecorations = Rc_Myctf_Admin::$textDecorations;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[link_text_decoration]" name="rc_myctf_style_options[link_text_decoration]">';
                foreach ( $textDecorations as $textDecoration ) {
                    $html .= '<option value="' . esc_attr( $textDecoration ) . '" ' . selected( $link_text_decoration, $textDecoration, false ) . '>' . sanitize_text_field( $textDecoration ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[link_text_decoration]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the color picker for Display Feed Background Color in Style General section */
    /**
    public static function rc_myctf_style_feed_bg_color_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $feed_bg_color = isset( $options[ 'feed_bg_color' ] ) ? sanitize_text_field( $options[ 'feed_bg_color' ] ) : '';
        
        $html = "<input type='text' id='feed_bg_color' name='rc_myctf_style_options[feed_bg_color]' value='$feed_bg_color' class='rc-myctf-color-fields' />";
        $html .= "<label for='feed_bg_color'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    */
    
    
    /* Function to output the HTML for Feed Background color */
    public static function rc_myctf_style_tweet_bg_color_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $tweet_bg_color = isset( $options[ 'tweet_bg_color' ] ) ? sanitize_text_field( $options[ 'tweet_bg_color' ] ) : '';
        
        $html = "<input type='text' id='tweet_bg_color' name='rc_myctf_style_options[tweet_bg_color]' value='$tweet_bg_color' class='rc-myctf-color-fields' />";
        $html .= "<label for='tweet_bg_color'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing general Border Type */
    public static function rc_myctf_style_tweet_border_type_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $border_type = isset( $options[ 'border_type' ] ) ? sanitize_text_field( $options[ 'border_type' ] ) : 'shadow';
 
        //Get the various font sizes
        $borderTypes = array( 'shadow', 'line' );
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[border_type]" name="rc_myctf_style_options[border_type]">';
                foreach ( $borderTypes as $borderType ) {
                    $html .= '<option value="' . esc_attr( $borderType ) . '" ' . selected( $border_type, $borderType, false ) . '>' . sanitize_text_field( $borderType ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[border_type]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    
    /*
     * Functions & callbacks for the Style tab - 'Header' section 
     */
    public static function rc_myctf_style_header_section_callback(){
        echo "This section lets you control the Styling of the Tweet Header.<br>This section includes the profile pic, Tweeter name, screen name & date.";
    }
    
    
    /* Function to output the HTML for choosing general font-size */
    public static function rc_myctf_style_font_size_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $font_size_header = isset( $options[ 'font_size_header' ] ) ? sanitize_text_field( $options[ 'font_size_header' ] ) : '95';
        
        //Get the various font sizes
        $fontPercentages = Rc_Myctf_Admin::$fontPercents;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[font_size_header]" name="rc_myctf_style_options[font_size_header]">';
                foreach ( $fontPercentages as $fontPercentage ) {
                    $html .= '<option value="' . esc_attr( $fontPercentage ) . '" ' . selected( $font_size_header, $fontPercentage, false ) . '>' . sanitize_text_field( $fontPercentage ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[font_size_header]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Header Name Font color */
    public static function rc_myctf_style_name_font_color_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $name_font_color_header = isset( $options[ 'name_font_color_header' ] ) ? sanitize_text_field( $options[ 'name_font_color_header' ] ) : '#000';
        
        $html = "<input type='text' id='name_font_color_header' name='rc_myctf_style_options[name_font_color_header]' value='$name_font_color_header' class='rc-myctf-color-fields' />";
        $html .= "<label for='name_font_color_header'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing Name Font Weight in header section */
    public static function rc_myctf_style_name_font_weight_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $name_font_weight_header = isset( $options[ 'name_font_weight_header' ] ) ? sanitize_text_field( $options[ 'name_font_weight_header' ] ) : 'bold';
        
        //Get the various font sizes
        $fontWeights = Rc_Myctf_Admin::$fontWeights;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[name_font_weight_header]" name="rc_myctf_style_options[name_font_weight_header]">';
                foreach ( $fontWeights as $fontWeight ) {
                    $html .= '<option value="' . esc_attr( $fontWeight ) . '" ' . selected( $name_font_weight_header, $fontWeight, false ) . '>' . sanitize_text_field( $fontWeight ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[name_font_weight_header]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for screen name font size for 'Style' head section */
    public static function rc_myctf_style_screen_name_font_size_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $screen_name_font_size_header = isset( $options[ 'screen_name_font_size_header' ] ) ? sanitize_text_field( $options[ 'screen_name_font_size_header' ] ) : '85';
        
        //Get the various font sizes
        $fontPercentages = Rc_Myctf_Admin::$fontPercents;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[screen_name_font_size_header]" name="rc_myctf_style_options[screen_name_font_size_header]">';
                foreach ( $fontPercentages as $fontPercentage ) {
                    $html .= '<option value="' . esc_attr( $fontPercentage ) . '" ' . selected( $screen_name_font_size_header, $fontPercentage, false ) . '>' . sanitize_text_field( $fontPercentage ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[screen_name_font_size_header]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Header "Screen Name" Font color */
    public static function rc_myctf_style_screen_name_font_color_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $screen_name_font_color_header = isset( $options[ 'screen_name_font_color_header' ] ) ? sanitize_text_field( $options[ 'screen_name_font_color_header' ] ) : '#999';
        
        $html = "<input type='text' id='screen_name_font_color_header' name='rc_myctf_style_options[screen_name_font_color_header]' value='$screen_name_font_color_header' class='rc-myctf-color-fields' />";
        $html .= "<label for='screen_name_font_color_header'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing "Screen Name" Font Weight in header section */
    public static function rc_myctf_style_screen_name_font_weight_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $screen_name_font_weight_header = isset( $options[ 'screen_name_font_weight_header' ] ) ? sanitize_text_field( $options[ 'screen_name_font_weight_header' ] ) : 'normal';
        
        //Get the various font sizes
        $fontWeights = Rc_Myctf_Admin::$fontWeights;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[screen_name_font_weight_header]" name="rc_myctf_style_options[screen_name_font_weight_header]">';
                foreach ( $fontWeights as $fontWeight ) {
                    $html .= '<option value="' . esc_attr( $fontWeight ) . '" ' . selected( $screen_name_font_weight_header, $fontWeight, false ) . '>' . sanitize_text_field( $fontWeight ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[screen_name_font_weight_header]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Date font size for 'Style' head section */
    public static function rc_myctf_style_date_font_size_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $date_font_size_header = isset( $options[ 'date_font_size_header' ] ) ? sanitize_text_field( $options[ 'date_font_size_header' ] ) : '85';
        
        //Get the various font sizes
        $fontPercentages = Rc_Myctf_Admin::$fontPercents;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[date_font_size_header]" name="rc_myctf_style_options[date_font_size_header]">';
                foreach ( $fontPercentages as $fontPercentage ) {
                    $html .= '<option value="' . esc_attr( $fontPercentage ) . '" ' . selected( $date_font_size_header, $fontPercentage, false ) . '>' . sanitize_text_field( $fontPercentage ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[date_font_size_header]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
        
    }
    
    
    /* Function to output the HTML for Header "Screen Name" Font color */
    public static function rc_myctf_style_date_font_color_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $date_font_color_header = isset( $options[ 'date_font_color_header' ] ) ? sanitize_text_field( $options[ 'date_font_color_header' ] ) : '#999';
        
        $html = "<input type='text' id='date_font_color_header' name='rc_myctf_style_options[date_font_color_header]' value='$date_font_color_header' class='rc-myctf-color-fields' />";
        $html .= "<label for='date_font_color_header'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing "Date" Font Weight in header section */
    public static function rc_myctf_style_date_font_weight_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $date_font_weight_header = isset( $options[ 'date_font_weight_header' ] ) ? sanitize_text_field( $options[ 'date_font_weight_header' ] ) : 'normal';
        $fontWeights = Rc_Myctf_Admin::$fontWeights;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[date_font_weight_header]" name="rc_myctf_style_options[date_font_weight_header]">';
                foreach ( $fontWeights as $fontWeight ) {
                    $html .= '<option value="' . esc_attr( $fontWeight ) . '" ' . selected( $date_font_weight_header, $fontWeight, false ) . '>' . sanitize_text_field( $fontWeight ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[date_font_weight_header]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing link text decoration for the Header section under 'Style' tab */
    public static function rc_myctf_style_link_text_decoration_header_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $link_text_decoration_header = isset( $options[ 'link_text_decoration_header' ] ) ? sanitize_text_field( $options[ 'link_text_decoration_header' ] ) : 'inherit';
        $textDecorations = Rc_Myctf_Admin::$textDecorations;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[link_text_decoration_header]" name="rc_myctf_style_options[link_text_decoration_header]">';
                foreach ( $textDecorations as $textDecoration ) {
                    $html .= '<option value="' . esc_attr( $textDecoration ) . '" ' . selected( $link_text_decoration_header, $textDecoration, false ) . '>' . sanitize_text_field( $textDecoration ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[link_text_decoration_header]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    
    
    
    /*
     * Functions & callbacks for the Style tab - 'Tweet' section 
     */
    public static function rc_myctf_style_tweet_section_callback(){
        echo "This section lets you control the Styling of the Tweet itself";
    }
    
    /* Function to output the HTML for choosing general font-size */
    public static function rc_myctf_style_font_size_tweet_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $font_size_tweet = isset( $options[ 'font_size_tweet' ] ) ? sanitize_text_field( $options[ 'font_size_tweet' ] ) : 'inherit';
        
        //Get the various font sizes
        $fontPercentages = Rc_Myctf_Admin::$fontPercents;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[font_size_tweet]" name="rc_myctf_style_options[font_size_tweet]">';
                foreach ( $fontPercentages as $fontPercentage ) {
                    $html .= '<option value="' . esc_attr( $fontPercentage ) . '" ' . selected( $font_size_tweet, $fontPercentage, false ) . '>' . sanitize_text_field( $fontPercentage ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[font_size_tweet]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Header "Screen Name" Font color */
    public static function rc_myctf_style_font_color_tweet_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $font_color_tweet = isset( $options[ 'font_color_tweet' ] ) ? sanitize_text_field( $options[ 'font_color_tweet' ] ) : '';
        
        $html = "<input type='text' id='font_color_tweet' name='rc_myctf_style_options[font_color_tweet]' value='$font_color_tweet' class='rc-myctf-color-fields' />";
        $html .= "<label for='font_color_tweet'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing "Date" Font Weight in header section */
    public static function rc_myctf_style_font_weight_tweet_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $font_weight_tweet = isset( $options[ 'font_weight_tweet' ] ) ? sanitize_text_field( $options[ 'font_weight_tweet' ] ) : 'normal';
        $fontWeights = Rc_Myctf_Admin::$fontWeights;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[font_weight_tweet]" name="rc_myctf_style_options[font_weight_tweet]">';
                foreach ( $fontWeights as $fontWeight ) {
                    $html .= '<option value="' . esc_attr( $fontWeight ) . '" ' . selected( $font_weight_tweet, $fontWeight, false ) . '>' . sanitize_text_field( $fontWeight ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[font_weight_tweet]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Link Color for the Tweet section */
    public static function rc_myctf_style_link_color_tweet_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $link_color_tweet = isset( $options[ 'link_color_tweet' ] ) ? sanitize_text_field( $options[ 'link_color_tweet' ] ) : '';
        
        $html = "<input type='text' id='font_color_tweet' name='rc_myctf_style_options[link_color_tweet]' value='$link_color_tweet' class='rc-myctf-color-fields' />";
        $html .= "<label for='link_color_tweet'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing link text decoration for the Tweet section */
    public static function rc_myctf_style_link_text_decoration_tweet_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $link_text_decoration_tweet = isset( $options[ 'link_text_decoration_tweet' ] ) ? sanitize_text_field( $options[ 'link_text_decoration_tweet' ] ) : 'inherit';
        $textDecorations = Rc_Myctf_Admin::$textDecorations;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[link_text_decoration_tweet]" name="rc_myctf_style_options[link_text_decoration_tweet]">';
                foreach ( $textDecorations as $textDecoration ) {
                    $html .= '<option value="' . esc_attr( $textDecoration ) . '" ' . selected( $link_text_decoration_tweet, $textDecoration, false ) . '>' . sanitize_text_field( $textDecoration ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[link_text_decoration_tweet]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    
    
    
    /*
     * Functions & callbacks for the Style tab - 'Footer' section 
     */
    public static function rc_myctf_style_footer_section_callback(){
        echo "This section lets you control the Styling of the Tweet Footer.<br>This section includes the Like icon and count, Retweet icon and count, twitter screen name & date.";
    }
    
    
    /* Function to output the HTML for choosing general font-size */
    public static function rc_myctf_style_font_size_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $font_size_footer = isset( $options[ 'font_size_footer' ] ) ? sanitize_text_field( $options[ 'font_size_footer' ] ) : '95';
        
        //Get the various font sizes
        $fontPercentages = Rc_Myctf_Admin::$fontPercents;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[font_size_footer]" name="rc_myctf_style_options[font_size_footer]">';
                foreach ( $fontPercentages as $fontPercentage ) {
                    $html .= '<option value="' . esc_attr( $fontPercentage ) . '" ' . selected( $font_size_footer, $fontPercentage, false ) . '>' . sanitize_text_field( $fontPercentage ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[font_size_footer]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Like icon color for the Style Footer section */
    public static function rc_myctf_style_like_icon_color_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $like_icon_color_footer = isset( $options[ 'like_icon_color_footer' ] ) ? sanitize_text_field( $options[ 'like_icon_color_footer' ] ) : '';
        
        $html = "<input type='text' id='like_icon_color_footer' name='rc_myctf_style_options[like_icon_color_footer]' value='$like_icon_color_footer' class='rc-myctf-color-fields' />";
        $html .= "<label for='like_icon_color_footer'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Like count color for the Style Footer section */
    public static function rc_myctf_style_like_count_color_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $like_count_color_footer = isset( $options[ 'like_count_color_footer' ] ) ? sanitize_text_field( $options[ 'like_count_color_footer' ] ) : '';
        
        $html = "<input type='text' id='like_count_color_footer' name='rc_myctf_style_options[like_count_color_footer]' value='$like_count_color_footer' class='rc-myctf-color-fields' />";
        $html .= "<label for='like_count_color_footer'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Retweet icon color for the Style Footer section */
    public static function rc_myctf_style_retweet_icon_color_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $retweet_icon_color_footer = isset( $options[ 'retweet_icon_color_footer' ] ) ? sanitize_text_field( $options[ 'retweet_icon_color_footer' ] ) : '';
        
        $html = "<input type='text' id='retweet_icon_color_footer' name='rc_myctf_style_options[retweet_icon_color_footer]' value='$retweet_icon_color_footer' class='rc-myctf-color-fields' />";
        $html .= "<label for='retweet_icon_color_footer'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Like count color for the Style Footer section */
    public static function rc_myctf_style_retweet_count_color_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $retweet_count_color_footer = isset( $options[ 'retweet_count_color_footer' ] ) ? sanitize_text_field( $options[ 'retweet_count_color_footer' ] ) : '';
        
        $html = "<input type='text' id='retweet_count_color_footer' name='rc_myctf_style_options[retweet_count_color_footer]' value='$retweet_count_color_footer' class='rc-myctf-color-fields' />";
        $html .= "<label for='retweet_count_color_footer'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Screen Name Font Color for the Style Footer section */
    public static function rc_myctf_style_screen_name_font_color_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $screen_name_font_color_footer = isset( $options[ 'screen_name_font_color_footer' ] ) ? sanitize_text_field( $options[ 'screen_name_font_color_footer' ] ) : '';
        
        $html = "<input type='text' id='screen_name_font_color_footer' name='rc_myctf_style_options[screen_name_font_color_footer]' value='$screen_name_font_color_footer' class='rc-myctf-color-fields' />";
        $html .= "<label for='screen_name_font_color_footer'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing Screen Name Font Weight in Style Footer section */
    public static function rc_myctf_style_screen_name_font_weight_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $screen_name_font_weight_footer = isset( $options[ 'screen_name_font_weight_footer' ] ) ? sanitize_text_field( $options[ 'screen_name_font_weight_footer' ] ) : 'normal';
        $fontWeights = Rc_Myctf_Admin::$fontWeights;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[screen_name_font_weight_footer]" name="rc_myctf_style_options[screen_name_font_weight_footer]">';
                foreach ( $fontWeights as $fontWeight ) {
                    $html .= '<option value="' . esc_attr( $fontWeight ) . '" ' . selected( $screen_name_font_weight_footer, $fontWeight, false ) . '>' . sanitize_text_field( $fontWeight ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[screen_name_font_weight_footer]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for Screen Name Font Color for the Style Footer section */
    public static function rc_myctf_style_date_font_color_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $date_font_color_footer = isset( $options[ 'date_font_color_footer' ] ) ? sanitize_text_field( $options[ 'date_font_color_footer' ] ) : '';
        
        $html = "<input type='text' id='date_font_color_footer' name='rc_myctf_style_options[date_font_color_footer]' value='$date_font_color_footer' class='rc-myctf-color-fields' />";
        $html .= "<label for='date_font_color_footer'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing Date Font Weight in Style Footer section */
    public static function rc_myctf_style_date_font_weight_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $date_font_weight_footer = isset( $options[ 'date_font_weight_footer' ] ) ? sanitize_text_field( $options[ 'date_font_weight_footer' ] ) : 'normal';
        $fontWeights = Rc_Myctf_Admin::$fontWeights;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[date_font_weight_footer]" name="rc_myctf_style_options[date_font_weight_footer]">';
                foreach ( $fontWeights as $fontWeight ) {
                    $html .= '<option value="' . esc_attr( $fontWeight ) . '" ' . selected( $date_font_weight_footer, $fontWeight, false ) . '>' . sanitize_text_field( $fontWeight ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[date_font_weight_footer]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the HTML for choosing link text decoration for the Footer section under 'Style' tab */
    public static function rc_myctf_style_link_text_decoration_footer_callback( $args ) {
        
        $options = get_option( 'rc_myctf_style_options' );
        $link_text_decoration_footer = isset( $options[ 'link_text_decoration_footer' ] ) ? sanitize_text_field( $options[ 'link_text_decoration_footer' ] ) : 'inherit';
        $textDecorations = Rc_Myctf_Admin::$textDecorations;
        
        $html = '';
            //Loop through all the font sizes and display them in select control
            $html .= '<select id="rc_myctf_style_options[link_text_decoration_footer]" name="rc_myctf_style_options[link_text_decoration_footer]">';
                foreach ( $textDecorations as $textDecoration ) {
                    $html .= '<option value="' . esc_attr( $textDecoration ) . '" ' . selected( $link_text_decoration_footer, $textDecoration, false ) . '>' . sanitize_text_field( $textDecoration ) . '</option>' ;
                }
            $html .= '</select>';
        $html .= "<label for='rc_myctf_style_options[link_text_decoration_footer]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    
    
    
    
    /*
     * Functions & callbacks for the Slider/Carousel tab - 'General' section 
     */
    public static function rc_myctf_slider_carousel_general_section_callback(){
        
        /* Creating the button & link to 'reset to default' option for tweet visibility option */
        $rc_myctf_action = 'reset_slider_options';
        $reset_slider_options_url = add_query_arg( array( 'tab' => 'slider', 'rc_myctf_action_reset' => $rc_myctf_action ), RC_MYCTF_ADMIN_URL );
        $nonced_reset_slider_options_url = wp_nonce_url( $reset_slider_options_url, 'rc_myctf-' . $rc_myctf_action . '_reset' );
        
        $html = '<div id="reset-slider-div">';
        $html .= '<p><a href="' . $nonced_reset_slider_options_url . '" id="rc_myctf_reset_slider">Reset To Default Options</a></p>';
        $html .= '</div>';
        $html .= 'This section lets you control the overall General Settings for Sliders & Carousels.';
        
        echo $html;
        
    }
    
    
    /*
     * Adding the checkbox field for Navigation Arrows under "Slider/Carousel" General tab
     */
    public static function rc_myctf_slider_carousel_nav_arrows_callback( $args ){
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $nav_arrows = isset( $options[ 'nav_arrows' ] ) ? strip_tags( $options[ 'nav_arrows' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="nav_arrows" name="rc_myctf_slider_carousel_options[nav_arrows]" value="1" ' . checked( 1, $nav_arrows, false ) . '>';
        $html .= '<label for="nav_arrows"> ' . $args[0] . '</label>';
        
        echo $html;
    }
    
    
    /*
     * Adding the checkbox field for Navigation Dots under "Slider/Carousel" General tab
     */
    public static function rc_myctf_slider_carousel_nav_dots_callback( $args ){
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $nav_dots = isset( $options[ 'nav_dots' ] ) ? strip_tags( $options[ 'nav_dots' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="nav_dots" name="rc_myctf_slider_carousel_options[nav_dots]" value="1" ' . checked( 1, $nav_dots, false ) . '>';
        $html .= '<label for="nav_dots"> ' . $args[0] . '</label>';
        
        echo $html;
    }
    
    
    /*
     * Adding the checkbox field for Navigation Dots under "Slider/Carousel" General tab
     */
    public static function rc_myctf_slider_carousel_autoplay_callback( $args ){
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $autoplay = isset( $options[ 'autoplay' ] ) ? strip_tags( $options[ 'autoplay' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="autoplay" name="rc_myctf_slider_carousel_options[autoplay]" value="1" ' . checked( 1, $autoplay, false ) . '>';
        $html .= '<label for="autoplay"> ' . $args[0] . '</label>';
        
        echo $html;
    }
    
    
    /* Function to output the text field for Transition Interval under "Slider/Carousel" General tab */
    public static function rc_myctf_slider_carousel_transition_interval_callback( $args ) {
        
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $transition_interval = isset( $options[ 'transition_interval' ] ) ? sanitize_text_field( $options[ 'transition_interval' ] ) : '7';
        
        $html = "<input type='text' id='transition_interval' name='rc_myctf_slider_carousel_options[transition_interval]' value='$transition_interval' />";
        $html .= "<label for='transition_interval'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /* Function to output the text field for Transition Speed under "Slider/Carousel" General tab */
    public static function rc_myctf_slider_carousel_transition_speed_callback( $args ) {
        
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $transition_speed = isset( $options[ 'transition_speed' ] ) ? sanitize_text_field( $options[ 'transition_speed' ] ) : '3';
        
        $html = "<input type='text' id='transition_speed' name='rc_myctf_slider_carousel_options[transition_speed]' value='$transition_speed' />";
        $html .= "<label for='transition_speed'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
        echo $html;
    }
    
    
    /*
     * Function to output the checkbox field for 'Pause on Hover' under "Slider/Carousel" General tab
     */
    public static function rc_myctf_slider_carousel_pause_on_hover_callback( $args ){
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $pause_on_hover = isset( $options[ 'pause_on_hover' ] ) ? strip_tags( $options[ 'pause_on_hover' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="pause_on_hover" name="rc_myctf_slider_carousel_options[pause_on_hover]" value="1" ' . checked( 1, $pause_on_hover, false ) . '>';
        $html .= '<label for="pause_on_hover"> ' . $args[0] . '</label>';
        
        echo $html;
    }
    
    
    /*
     * Function to output the checkbox field for 'Pause on Hover' under "Slider/Carousel" General tab
     */
    public static function rc_myctf_slider_carousel_loop_callback( $args ){
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $loop = isset( $options[ 'loop' ] ) ? strip_tags( $options[ 'loop' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="loop" name="rc_myctf_slider_carousel_options[loop]" value="1" ' . checked( 1, $loop, false ) . '>';
        $html .= '<label for="loop"> ' . $args[0] . '</label>';
        
        echo $html;
    }
    
    
    
    
    /*
     * Functions & callbacks for the Slider/Carousel tab - 'Slider' section 
     */
    public static function rc_myctf_slider_carousel_slider_section_callback(){
        echo "This section lets you control the settings for Slider.";
    }
    
    
    /*
     * Function to output the checkbox field for 'Auto Height' under "Slider/Carousel" Slider tab
     */
    public static function rc_myctf_slider_carousel_auto_height_callback( $args ){
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $auto_height = isset( $options[ 'auto_height' ] ) ? strip_tags( $options[ 'auto_height' ] ) : FALSE;
        
        $html = '<input type="checkbox" id="auto_height" name="rc_myctf_slider_carousel_options[auto_height]" value="1" ' . checked( 1, $auto_height, false ) . '>';
        $html .= '<label for="auto_height"> ' . $args[0] . '</label>';
        
        echo $html;
    }
    
    
    
    
    
    /*
     * Functions & callbacks for the Slider/Carousel tab - 'Carousel' section 
     */
    public static function rc_myctf_slider_carousel_carousel_section_callback(){
        echo "This section lets you control the settings for Carousels.";
    }
    
    
    /*
     * Function to output the checkbox field for 'Items On Screen' under "Slider/Carousel" Carousel tab
     */
    public static function rc_myctf_slider_carousel_items_on_screen_callback( $args ){
        $options = get_option( 'rc_myctf_slider_carousel_options' );
        $items_on_screen = isset( $options[ 'items_on_screen' ] ) ? sanitize_text_field( $options[ 'items_on_screen' ] ) : '3';
        
        //Get the various font sizes
        $itemsOnScreenValues = array( 2,3,4,5,6 );
        
        $html = '';
            
            //Loop through all the font sizes and display them in select control
            $html .= '<select disabled id="rc_myctf_slider_carousel_options[items_on_screen]" name="rc_myctf_slider_carousel_options[items_on_screen]">';
                foreach ( $itemsOnScreenValues as $itemsOnScreenValue ) {
                    $html .= '<option value="' . esc_attr( $itemsOnScreenValue ) . '" ' . selected( $items_on_screen, $itemsOnScreenValue, false ) . '>' . sanitize_text_field( $itemsOnScreenValue ) . '</option>' ;
                }
            $html .= '</select>';
        
        $html .= "<label for='rc_myctf_slider_carousel_options[items_on_screen]'> &nbsp; &nbsp;" . $args[0] . "</label>";
        
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
        $html .= '[my_custom_tweets count="3" exclude_replies="true" include_rts="false"]';
        
        $html .= '<table class="widefat rc-myctf-scode-table">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th>Shortcode Options</th>';
                    $html .= '<th>Example</th>';
                    $html .= '<th>Description</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            
            $html .= '<tfoot><tr>';
                $html .= '<th>Shortcode Options</th>';
                $html .= '<th>Example</th>';
                $html .= '<th>Description</th>';
            $html .= '</tr></tfoot>';
            
            $html .= '<tbody>';
            
                $html .= '<tr class="rc-myctf-theading">';
                    $html .= '<th colspan="3">Customize Feed</th>';
                $html .= '</tr>';
            
                $html .= '<tr>';
                    $html .= '<td>feed_type</td>';
                    $html .= '<td>[my_custom_tweets feed_type="user_timeline"]<br>'
                            . '[my_custom_tweets feed_type="mentions_timeline"]<br>'
                            . '[my_custom_tweets feed_type="hashtags_timeline"]<span class="rc_myctf_tip">(Available in Pro)</span><br>'
                            . '[my_custom_tweets feed_type="search_timeline"]<span class="rc_myctf_tip">(Available in Pro)</span><br></td>';
                    $html .= '<td>Determines the type of feed to display. Available options are user_timeline, search_timeline, hashtags_timeline, mentions_timeline</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>screen_name</td>';
                    $html .= '<td>[my_custom_tweets screen_name="raycreations"]<br>'
                            . '[my_custom_tweets feed_type="user_timeline" screen_name="raycreations"]</td>';
                    $html .= '<td>Display feed of any Twitter user, e.g. @raycreations</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>hashtags</td>';
                    $html .= '<td>[my_custom_tweets hashtags="aurora northernlights"]<br>'
                            . '[my_custom_tweets feed_type="hashtags_timeline" hashtags="aurora northernlights"]</td>';
                    $html .= '<td>Feed based on hashtags. You can mention one or more than one hashtags</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>search_string</td>';
                    $html .= '<td>[my_custom_tweets search_string="fog sunrise"]<br>'
                            . '[my_custom_tweets feed_type="search_timeline" search_string="fog sunrise"]</td>';
                    $html .= '<td>Feed based on a search string. You can mention one or more than one search terms.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_style</td>';
                    $html .= '<td>[my_custom_tweets display_style="display_list"]<br>'
                            . '[my_custom_tweets display_style="display_masonry"]<span class="rc_myctf_tip">(Available in Pro)</span><br>'
                            . '[my_custom_tweets display_style="display_slider_1_col"]<br>'
                            . '[my_custom_tweets display_style="display_slider_2_col"]<span class="rc_myctf_tip">(Available in Pro)</span><br></td>';
                    $html .= '<td>Determines the type of layout for your feed. Available options are<br>'
                            . 'display_list : List style feed.<br>'
                            . 'display_masonry : Display a Masonry of the tweets.<br> '
                            . 'display_slider_1_col : Displays a 1 column slider.<br> '
                            . 'display_slider_2_col : Display a 2 column slider.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>hide_media</td>';
                    $html .= '<td>[my_custom_tweets hide_media="1"]<span class="rc_myctf_tip">(Available in Pro)</span></td>';
                    $html .= '<td>Displays without any images/videos</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>count</td>';
                    $html .= '<td>[my_custom_tweets count="10"]</td>';
                    $html .= '<td>Specify the number of tweets to fetch from Twitter</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>number_of_tweets_in_row</td>';
                    $html .= '<td>[my_custom_tweets number_of_tweets_in_row="3"]<br>'
                            . '[my_custom_tweets display_style="display_masonry" number_of_tweets_in_row="3"]</td>';
                    $html .= '<td>Specifies the number of columns for masonry. However, it may automatiacally adjust to a '
                            . 'different value if space is not sufficient.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>exclude_replies</td>';
                    $html .= '<td>[my_custom_tweets exclude_replies="0"]</td>';
                    $html .= '<td>"0" is false. And "1" is true.<br>'
                            . 'By default "exclude_replies" is set to true.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>include_rts</td>';
                    $html .= '<td>[my_custom_tweets include_rts="1"]</td>';
                    $html .= '<td>Include retweets? By default it is set to false.<br>'
                            . '"0" is false. And "1" is true.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>include_photos</td>';
                    $html .= '<td>[my_custom_tweets include_photos="1"]</td>';
                    $html .= '<td>Indicates your preference of Tweets with photos<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>include_videos</td>';
                    $html .= '<td>[my_custom_tweets include_videos="1"]</td>';
                    $html .= '<td>Indicates your preference of Tweets with videos<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                
                $html .= '<tr class="rc-myctf-theading">';
                    $html .= '<th colspan="3">Link Settings</th>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>remove_links_hashtags</td>';
                    $html .= '<td>[my_custom_tweets remove_links_hashtags="1"]<span class="rc_myctf_tip">(Available in Pro)</span></td>';
                    $html .= '<td>Remove linking from Hashtags<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>remove_links_mentions</td>';
                    $html .= '<td>[my_custom_tweets remove_links_mentions="1"]<span class="rc_myctf_tip">(Available in Pro)</span></td>';
                    $html .= '<td>Remove linking from Mentions<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>remove_ext_links</td>';
                    $html .= '<td>[my_custom_tweets remove_ext_links="1"]<span class="rc_myctf_tip">(Available in Pro)</span></td>';
                    $html .= '<td>Removes linking from the external link at the end of the Tweet<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>nofollow_ext_links</td>';
                    $html .= '<td>[my_custom_tweets nofollow_ext_links="1"]</td>';
                    $html .= '<td>Adds a "nofollow" attribute to all external links within the feed.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                
                
                $html .= '<tr class="rc-myctf-theading">';
                    $html .= '<th colspan="3">Tweets (Show/Hide)</th>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_tweet_border</td>';
                    $html .= '<td>[my_custom_tweets display_tweet_border="1"]</td>';
                    $html .= '<td>Toggles visibility of the border/shadow around tweets<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_header</td>';
                    $html .= '<td>[my_custom_tweets display_header="1"]</td>';
                    $html .= '<td>Toggles visibility of the Tweet header. This is the section above the Tweet that encompases the profile images, '
                            . 'Name, Screen Name, and Date.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_profile_img_header</td>';
                    $html .= '<td>[my_custom_tweets display_profile_img_header="1"]</td>';
                    $html .= '<td>Toggles visibility of the profile image in Tweet header section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_name_header</td>';
                    $html .= '<td>[my_custom_tweets display_name_header="1"]</td>';
                    $html .= '<td>Toggles visibility of the "Name" in Tweet header section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_screen_name_header</td>';
                    $html .= '<td>[my_custom_tweets display_screen_name_header="1"]</td>';
                    $html .= '<td>Toggles visibility of the "Name" in Tweet header section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_date_header</td>';
                    $html .= '<td>[my_custom_tweets display_date_header="1"]</td>';
                    $html .= '<td>Toggles visibility of the "Date" in Tweet header section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_footer</td>';
                    $html .= '<td>[my_custom_tweets display_footer="1"]</td>';
                    $html .= '<td>Toggles visibility of the Tweet footer. This is the section below the Tweet that encompases the, '
                            . 'Like icon and Like count, Retweet icon and retweet count, Screen Name, and Date.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_likes_footer</td>';
                    $html .= '<td>[my_custom_tweets display_likes_footer="1"]</td>';
                    $html .= '<td>Toggles visibility of the "Like icon & counter" in Tweet footer section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_retweets_footer</td>';
                    $html .= '<td>[my_custom_tweets display_retweets_footer="1"]</td>';
                    $html .= '<td>Toggles visibility of the "Retweet icon & counter" in Tweet footer section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_screen_name_footer</td>';
                    $html .= '<td>[my_custom_tweets display_screen_name_footer="0"]</td>';
                    $html .= '<td>Toggles visibility of the "@screenname" in Tweet footer section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>display_date_footer</td>';
                    $html .= '<td>[my_custom_tweets display_date_footer="0"]</td>';
                    $html .= '<td>Toggles visibility of the "Date" in Tweet footer section.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                
                $html .= '<tr class="rc-myctf-theading">';
                    $html .= '<th colspan="3">Style</th>';
                $html .= '</tr>';
                
                $html .= '<td>border_type</td>';
                    $html .= '<td>[my_custom_tweets border_type="shadow"]<br>'
                            . '[my_custom_tweets border_type="line"]<br>'
                            . '[my_custom_tweets display_tweet_border="1" border_type="line"]<br></td>';
                    $html .= '<td>Determines the border type. Available options are <i>shadow</i>, and <i>line</i></td>';
                $html .= '</tr>';
                
                $html .= '<tr class="rc-myctf-theading">';
                    $html .= '<th colspan="3">Slider/Carousel</th>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>nav_arrows</td>';
                    $html .= '<td>[my_custom_tweets nav_arrows="1"]<br>'
                            . '[my_custom_tweets display_style="display_slider_1_col" nav_arrows="1"]</td>';
                    $html .= '<td>Toggles visibility of the "Navigation arrows" in sliders & carousels.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>nav_dots</td>';
                    $html .= '<td>[my_custom_tweets nav_dots="1"]<br>'
                            . '[my_custom_tweets display_style="display_slider_2_col" nav_dots="1"]</td>';
                    $html .= '<td>Toggles visibility of the "Navigation Dots" in sliders & carousels.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>autoplay</td>';
                    $html .= '<td>[my_custom_tweets autoplay="1"]<br>'
                            . '[my_custom_tweets display_style="display_slider_2_col" autoplay="1"]</td>';
                    $html .= '<td>Enables autoplay in sliders & carousels.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>transition_interval</td>';
                    $html .= '<td>[my_custom_tweets transition_interval="8"]<br>'
                            . '[my_custom_tweets display_style="display_slider_1_col" transition_interval="7"]</td>';
                    $html .= '<td>Time duration in seconds. Controls how frequently slides change.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>transition_speed</td>';
                    $html .= '<td>[my_custom_tweets transition_speed="3"]<br>'
                            . '[my_custom_tweets display_style="display_slider_1_col" transition_interval="3"]</td>';
                    $html .= '<td>Time duration in seconds. Controls the speed of completion of the transition itself.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>pause_on_hover</td>';
                    $html .= '<td>[my_custom_tweets pause_on_hover="1"]<br>'
                            . '[my_custom_tweets display_style="display_slider_2_col" pause_on_hover="1"]</td>';
                    $html .= '<td>Pauses on hover<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>loop</td>';
                    $html .= '<td>[my_custom_tweets loop="1"]<br>'
                            . '[my_custom_tweets display_style="display_slider_2_col" loop="1"]</td>';
                    $html .= '<td>Plays the tweets in a continuous loop<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
                $html .= '<tr>';
                    $html .= '<td>auto_height</td>';
                    $html .= '<td>[my_custom_tweets auto_height="1"]<br>'
                            . '[my_custom_tweets display_style="display_slider_1_col" auto_height="1"]</td>';
                    $html .= '<td>Only for Sliders. Automatically adjusts height according to each slide.<br>'
                            . '"1" is true. And "0" is false.</td>';
                $html .= '</tr>';
                
            $html .= '</tbody>';
        
        $html .= '</table>';
        
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
        
    }//ends rc_myctf_settings_validate_options
    
    
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
        }elseif ( $valid[ 'number_of_tweets' ] > 50 ){
            $valid[ 'number_of_tweets' ] = 50;
        }
        
        //Return the array processing any additional functions filtered by this action
        return apply_filters( 'rc_myctf_customize_validate_options', $valid, $input );
        
        
    }//ends rc_myctf_customize_validate_options
    
    
    
    /*
     * Validates the inputs & fields of the Tweets Options tab
     * 
     * @since 1.2.1
     * @access public
     * 
     * @param   $input  array   An array of settings options to be validated
     * @return  $valid  array   An array of validated inputs
     */
    public static function rc_myctf_tweets_validate_options( $input ) {
        
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
        return apply_filters( 'rc_myctf_tweets_validate_options', $valid, $input );
        
    }//ends rc_myctf_tweets_validate_options
    
    
    
    /*
     * Validates the inputs & fields of the Style Options tab
     * 
     * @since 1.2.1
     * @access public
     * 
     * @param   $input  array   An array of settings options to be validated
     * @return  $valid  array   An array of validated inputs
     */
    public static function rc_myctf_style_validate_options( $input ) {
        
        //Create an array for storing the validated options
        $valid = array();
        
        //Loop through each of the incoming options
        foreach ( $input as $key => $value ) {
            
            //Check to see if the input option has a value. If so, process it.
            if ( isset( $input[ $key ] ) ) {
                $valid[ $key ] = strip_tags( stripslashes( $input[ $key ] ) );
            }// end if
            
        }// end foreach
        
        $color_options = array(
            'font_color',
            'feed_bg_color',
            'tweet_bg_color',
            'name_font_color_header',
            'screen_name_font_color_header',
            'date_font_color_header',
            'font_color_tweet',
            'link_color_tweet',
            'like_icon_color_footer',
            'like_count_color_footer',
            'retweet_icon_color_footer',
            'retweet_count_color_footer',
            'screen_name_font_color_footer',
            'date_font_color_footer'
        );
        
        
        //check if $color_options array has valid hex color
        foreach ( $color_options as $color_option ) {
            $valid[ $color_option ] = Rc_Myctf_Admin_Helper::rc_myctf_validate_hex_color_code( $valid[ $color_option ], $color_option );
        }
        
        //Return the array processing any additional functions filtered by this action
        return apply_filters( 'rc_myctf_style_validate_options', $valid, $input );
        
    }//ends rc_myctf_style_validate_options
    
    
    
    /*
     * Validates hexadecial color
     * 
     * @since 1.2.1
     * @access public
     * 
     * @param   string $hexcolor  Hexadecimal color code
     * @return  boolean TRUE|False   Either true or false
     */
    public static function rc_myctf_validate_hex_color_code( $hexcolor, $option ) {
        
        /* 
         * if the $hexcolor is empty, simply return true
         * because if someone doesn't want to choose a value
         * we want to save that too
         */
        if ( empty( $hexcolor ) ) {
            return $hexcolor;
        }
        
        if ( preg_match( '/^#[a-f0-9]{6}$/i', $hexcolor ) ) { // if user insert a HEX color with #     
            return $hexcolor;
        } else {
            
            add_settings_error( 'rc_myctf_style_options', 'rc_myctf_font_color_error', 'Invalid ' . $option . ' entered...', 'error' );
            
            /* get the originally saved font_color and assign it back to options */
            $options = get_option( 'rc_myctf_style_options' );
            $org_val = isset( $options[ $option ] ) ? sanitize_text_field( $options[ $option ] ) : '';
            return $org_val;
        }
        
        
    }//ends rc_myctf_validate_hex_color_code
    
    
    
    /*
     * Validates the inputs & fields of the Slider/Carousel Options tab
     * 
     * @since 1.2.1
     * @access public
     * 
     * @param   $input  array   An array of settings options to be validated
     * @return  $valid  array   An array of validated inputs
     */
    public static function rc_myctf_slider_carousel_validate_options( $input ) {
        
        //Create an array for storing the validated options
        $valid = array();
        
        //Loop through each of the incoming options
        foreach ( $input as $key => $value ) {
            
            //Check to see if the input option has a value. If so, process it.
            if ( isset( $input[ $key ] ) ) {
                $valid[ $key ] = strip_tags( stripslashes( $input[ $key ] ) );
            }// end if
            
        }// end foreach
        
        $valid[ 'transition_interval' ] = ( $valid[ 'transition_interval' ] < 1 || $valid[ 'transition_interval' ] > 20 ) ? 7 : intval( $valid[ 'transition_interval' ] );
        $valid[ 'transition_speed' ] = ( $valid[ 'transition_speed' ] < 1 || $valid[ 'transition_speed' ] > 10 ) ? 3 : intval( $valid[ 'transition_speed' ] );
        
        //Return the array processing any additional functions filtered by this action
        return apply_filters( 'rc_myctf_slider_carousel_validate_options', $valid, $input );
        
    }//ends rc_myctf_style_validate_options
    
    
    
    /*
     * Function to reset Tweets visibility options
     * 
     * @param   string  $action     options to reset
     * @since 1.2.1
     */
    public static function rc_myctf_reset_options( $action ){
        
        //set initial status as FALSE
        $status = FALSE;
        
        if ( $action == 'reset_tweets_visibility' ) {
            
            //default tweet options
            $tweet_show_hide_args_default = array(
                'display_tweet_border' => 1, 'display_header' => 1, 'display_profile_img_header' => 1,
                'display_name_header' => 1, 'display_screen_name_header' => 1, 'display_date_header' => 1,
                'display_footer' => 1, 'display_likes_footer' => 1, 'display_retweets_footer' => 1,
                'display_screen_name_footer' => 0, 'display_date_footer' => 0
            );
            
            //reset the values with new options
            update_option( 'rc_myctf_tweets_options', $tweet_show_hide_args_default );
            $status = TRUE;
            
        } else if ( $action == 'reset_style' ) {
            
            //default style options
            $style_args = array(
                'font_size' => 'inherit', 'font_color' => '', 'link_text_decoration' => 'inherit', 'feed_bg_color' => '',
                'tweet_bg_color' => '', 'border_style' => 'shadow', 'font_size_header' => '100', 'name_font_color_header' => '', 'name_font_weight_header' => 'bold',
                'screen_name_font_size_header' => '75', 'screen_name_font_color_header' => '#666666', 'screen_name_font_weight_header' => 'inherit',
                'date_font_size_header' => '75', 'date_font_color_header' => '#666666', 'date_font_weight_header' => 'inherit',
                'link_text_decoration_header' => 'inherit', 'font_size_tweet' => 'inherit', 'font_color_tweet' => '',
                'font_weight_tweet' => 'inherit', 'link_color_tweet' => '', 'link_text_decoration_tweet' => 'inherit', 'font_size_footer' => '75',
                'like_icon_color_footer' => '#999999', 'like_count_color_footer' => '#999999', 'retweet_icon_color_footer' => '#999999',
                'retweet_count_color_footer' => '#999999', 'screen_name_font_color_footer' => '#999999', 'screen_name_font_weight_footer' => 'inherit',
                'date_font_color_footer' => '#999999', 'date_font_weight_footer' => 'normal', 'link_text_decoration_footer' => 'none'
            );
            
            //reset the values with new options
            update_option( 'rc_myctf_style_options', $style_args );
            $status = TRUE;
            
        } else if ( $action == 'reset_slider_options' ) {
            
            //default slider/carousel options
            $slider_args = array(
                'nav_arrows' => 0, 'nav_dots' => 1, 'autoplay' => 1, 'transition_interval' => 7, 'transition_speed' => 3,
                'pause_on_hover' => 1, 'loop' => 1
            );
            
            //reset the values with new options
            update_option( 'rc_myctf_slider_carousel_options', $slider_args );
            $status = TRUE;
            
        }//end if
        
        
        return $status;
        
    }//ends rc_myctf_reset_options
    
    
}//ends class
