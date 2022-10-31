<?php

/**
 * Primary class file for the Twitter plugin
 *
 * @author Ray Creations
 */

/**
 * Ensures the page is not accessed directly
 */
if ( !defined( 'ABSPATH' ) ){
    exit;
}


class Rc_Myctf_Admin {
    
    /**
     * Indicates whether the class has been initialized or not.
     * 
     * @since 1.0
     * @access private
     * @var boolean
     */
    private static $initiated = false;
    
 
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
        
        /** This creates the main settings page  */
        add_action( 'admin_menu', array( 'Rc_Myctf_Admin', 'rc_myctf_add_settings_page' ) );
        
        /**  This creates the API Settings section & fields for the main settings page above  */
        add_action( 'admin_init', array( 'Rc_Myctf_Admin', 'rc_myctf_create_settings_sections_fields' ) );
        
        /**  This creates the Customize section & fields for the main settings page above  */
        add_action( 'admin_init', array( 'Rc_Myctf_Admin', 'rc_myctf_create_customize_sections_fields' ) );
        
        /**  This creates the Support section & fields for the main settings page above  */
        add_action( 'admin_init', array( 'Rc_Myctf_Admin', 'rc_myctf_create_support_sections_fields' ) );
        
        /* This function is to delete the Tweets saved in Transient when username in Customize tab settings is updated */
        add_action( 'update_option_rc_myctf_customize_options', array( 'Rc_Myctf_Admin', 'rc_myctf_delete_tweets_from_transient' ) );
        
        /* Upon token invalidate request. This functions checks if it should be invalidated. And then calls appropriate function to invalidate it */
        //add_action( 'admin_menu', array( 'Rc_Myctf_Admin', 'rc_myctf_check_if_token_should_be_invalidated' ) );
        
        /* Function checks if Consumer key & secret are present. If not, displays an admin notice */
        add_action( 'admin_head', array( 'Rc_Myctf_Admin', 'rc_myctf_show_admin_notice_for_no_keys' ) );
        
    }//ends init_hooks
    
    
    /**
     * Public function to add Twitter Settings Page
     */
    public static function rc_myctf_add_settings_page(){
              
        //
        add_options_page(
                'Ray Social Feeds For Twitter',                               //The text to be displayed in the title tag of the page
                'Ray Twitter Feeds',                                      //Text to be used for the menu
                'manage_options',                                       //capability
                'myctf-page',                                           //slug name to refer to this menu
                array( 'Rc_Myctf_Admin', 'rc_myctf_settings_page' )     //The callback function to output the content for this page
                );
        
    }
    
    
    /**
     * Function to output the content for the settings page
     * It also houses the tabs for the tabbed navigation
     * 
     * @since 1.0
     */
    public static function rc_myctf_settings_page(){
        
        /* Check to see if current user has sufficient permissions */
        if ( !current_user_can( 'manage_options' ) ){
            wp_die( __( 'You do not havae sufficient permissions to access this page...') );
        }
        
        
        ?>
        <div class="wrap">

            <h2>Ray Social Feeds For Twitter Settings</h2>

            <?php
            /**  Getting the active tab variable from $_GET global variable
             * Using filter_input to sanitize S_GET variable
             */

            $tab = sanitize_text_field( filter_input( INPUT_GET, 'tab' ) );
            $active_tab = !empty( $tab ) ? $tab : 'settings';
            ?>
                <h2 class="nav-tab-wrapper">
                    <a href="?page=myctf-page&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
                    <a href="?page=myctf-page&tab=customize" class="nav-tab <?php echo $active_tab == 'customize' ? 'nav-tab-active' : ''; ?>">Customize</a>
                    <a href="?page=myctf-page&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support</a>
                </h2>

            <?php //settings_errors(); ?>
            <form action="options.php" method="post">
            <?php 

            if ( $active_tab == 'settings' ){
                    settings_fields( 'rc_myctf_settings_options' );                 //should match with the option name
                    do_settings_sections( 'rc_myctf_api_settings_page' );           //page name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                    //Section for the plugin settings section
                    do_settings_sections( 'rc_myctf_api_preserve_settings_page' );         // should match the section name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                    //Section for the plugin settings section
                    do_settings_sections( 'rc_myctf_api_invalidate_token_page' );         // should match the section name
                ?>
                <hr>

                <?php

            }else if ( $active_tab == 'customize' ){

                settings_fields( 'rc_myctf_customize_options' );            // should match with the option name
                do_settings_sections( 'rc_myctf_feed_settings_page' );      // page name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>

                <?php
                do_settings_sections( 'rc_myctf_layout_section_page' );     // should match the section name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>

                <?php

            }else if ( $active_tab == 'support' ){
                settings_fields( 'rc_myctf_support_options' );              // should match with the option name
                do_settings_sections( 'rc_myctf_need_support_section_page' );  // should match with the section name
                ?>

                <!-- Disabled the Submit button as we don't really need it. There are no fields to be submitted -->
                <!--<input name="Submit" type="submit" value="Save Changes" />-->  
                <?php
            }
            ?>
            </form>
        </div>

        <?php
    }
    
    /**
     * Initialize the "API options" by registering the sections,
     * fields, and settings for the "Settings" tab
     * 
     * @since 1.0
     */
    public static function rc_myctf_create_settings_sections_fields(){
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rc_myctf_settings_options',            //option group name
                'rc_myctf_settings_options',            //option name
                array(                                  //validation function
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_settings_validate_options'
                )      
                );
        
        
        /* Registering the API Settings Section. 
         * This 'API settings' tab fields will belong to this section 
         */
        add_settings_section(
                'rc_myctf_api_settings_section',                                // ID to identify this section
                'API Settings Options',                                         // Title of the section
                array(                                                          // Function that fills the section with desired content
                    'Rc_Myctf_Admin_Helper', 
                    'rc_myctf_api_section_callback' ),
                'rc_myctf_api_settings_page'                                    // Page on which to add this section. Matches the section name
                );
        
        /** Introducing the fields for the "API settings" tab section */
        add_settings_field(
                'rc_myctf_consumer_key',                                            // ID of the field
                'Consumer Key',                                                     // Title of the field
                array( 'Rc_Myctf_Admin_Helper', 'rc_myctf_consumer_key_callback' ), // Function that fills the field with desired input 
                'rc_myctf_api_settings_page',                                             // Page on which to display this field 
                'rc_myctf_api_settings_section',                                    // Section in which to display this field 
                array( 'Your Twitter API Consumer Key' )                            // Argument passed to the callback function
                );
        
        add_settings_field(
                'rc_myctf_consumer_secret', 
                'Consumer Secret', 
                array( 'Rc_Myctf_Admin_Helper', 'rc_myctf_consumer_secret_callback' ), 
                'rc_myctf_api_settings_page', 
                'rc_myctf_api_settings_section', 
                array( 'Your Twitter API Consumer Secret' )
                );
        
        
        
       
        /* Registering the API Settings Section. 
         * This 'API settings' tab fields will belong to this section 
         */
        add_settings_section(
                'rc_myctf_api_plugin_settings_section',                                    // ID to identify this section
                'Plugin Settings Options',                                          // Title of the section
                array(                                                              // Function that fills the section with desired content
                    'Rc_Myctf_Admin_Helper', 
                    'rc_myctf_api_plugin_settings_section_callback' ),  
                'rc_myctf_api_preserve_settings_page'                               // Page on which to add this section. Matches the section name
                );
        
                add_settings_field(
                        'rc_myctf_preserve_settings', 
                        'Preserve Settings?', 
                        array(
                            'Rc_Myctf_Admin_Helper',
                            'rc_myctf_preserve_settings_callback'
                        ), 
                        'rc_myctf_api_preserve_settings_page', 
                        'rc_myctf_api_plugin_settings_section', 
                        array( 'Check to preserve your settings on plugin uninstall' )
                        );
        
                
        
        
        /* 
         * Adding the button to invalidate the bearer token 
         */
        
        /**
        add_settings_section(
                'rc_myctf_api_invalidate_token_section',                            // ID to identify this section
                'Invalidate Bearer Token',                                          // Title of the section
                array(                                                              // Function that fills the section with desired content
                    'Rc_Myctf_Admin_Helper', 
                    'rc_myctf_api_invalidate_token_section_callback' ),  
                'rc_myctf_api_invalidate_token_page'                                // Page on which to add this section. Matches the section name
                );
        */
                
    }// ends function rc_myctf_create_settings_sections_fields
    
    
    
    
    /**
     * Initialize the "Customize" options by registering the sections,
     * fields, and settings for the "Customize" tab
     * 
     * @since 1.0
     */
    public static function rc_myctf_create_customize_sections_fields(){
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rc_myctf_customize_options',           //option group name
                'rc_myctf_customize_options',           //option name
                array(
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_customize_validate_options'
                )
                );
        
        
        /**
         * Adding the section for Feed Settings under Customize tab
         */
        add_settings_section(
                'rc_myctf_feed_settings_section',                           // This is the section name
                'Feed Settings',                                            // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_feed_settings_section_callback'
                ), 
                'rc_myctf_feed_settings_page'                            // Matches the section name   
                );
        
        /** Adding the field for Feed Type  */
        add_settings_field(
                'rc_myctf_feed_type',                       // This is the section name                                                
                'Feed Type',                                // Title of the section
                array(                                      // Function that fills the field with desired input
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_feed_type_callback'
                ), 
                'rc_myctf_feed_settings_page',           // Matches the section name
                'rc_myctf_feed_settings_section',           // Matches the section name
                array('Type of feed you wish to display.')   // Argument passed to the callback function
                );
        
        /** Adding the field for Screen Name  */
        add_settings_field(
                'rc_myctf_screen_name',                                                                   
                'Screen Name',                               
                array(                                      
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_screen_name_callback'
                ), 
                'rc_myctf_feed_settings_page', 
                'rc_myctf_feed_settings_section',
                array('Twitter screen name for user timeline')   
                );
        
        /* Adding the field for Hashtags  */
        add_settings_field(
                'rc_myctf_hashtags',                                                                   
                'Hashtags',                               
                array(                                      
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_hashtags_callback'
                ), 
                'rc_myctf_feed_settings_page', 
                'rc_myctf_feed_settings_section',
                array('Tweets based on Hashtags')   
                );
        
        /** Adding the field for Search String  */
        add_settings_field(
                'rc_myctf_search_string',                                                                   
                'Search String',                               
                array(                                      
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_search_string_callback'
                ), 
                'rc_myctf_feed_settings_page', 
                'rc_myctf_feed_settings_section',
                array('Search Tweets using keywords. <span class="rc_myctf_tip">(Available in Pro)</span>')   
                );
        
        /* Adding "include_media_type" in search */
        add_settings_field(
                'rc_myctf_include_media_type',                                                                   
                'Include Media in Search',                               
                array(                                      
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_include_media_type_callback'
                ), 
                'rc_myctf_feed_settings_page', 
                'rc_myctf_feed_settings_section',
                array('Include Photos', 'Include Videos')   
                );
        
        
        /* Adding the checkbox for Exclude Replies  */
        add_settings_field(
                'rc_myctf_exclude_replies',                                                                   
                'Exclude Replies?',                               
                array(                                      
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_exclude_replies_callback'
                ), 
                'rc_myctf_feed_settings_page', 
                'rc_myctf_feed_settings_section',
                array('Exclude replies from Tweets')   
                );
        
        /* Adding the checkbox for Include Retweets  */
        add_settings_field(
                'rc_myctf_include_rts',                                                                   
                'Include Retweets?',                               
                array(                                      
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_include_retweets_callback'
                ), 
                'rc_myctf_feed_settings_page', 
                'rc_myctf_feed_settings_section',
                array('Want to display retweets?')   
                );
        
        
        
        
        
        /** 
         * Adding the section for Layout under Customize tab  
         */
        add_settings_section(
                'rc_myctf_layout_section',                          // This is the section name
                'Additional Customization',                         // Title of the section
                array(                                              // Function that fills the section with desired content
                    'Rc_Myctf_Admin_Helper', 
                    'rc_myctf_layout_section_callback' 
                    ),
                'rc_myctf_layout_section_page'                           // Matches the section name
                );                         
        
        
        /** Adding the field for width type */
        add_settings_field(
                'rc_myctf_feed_width_type',                                     // ID of the field
                'Feed Width Type',                                              // Title of the field
                array(                                                          // Function that fills the field with desired input
                    'Rc_Myctf_Admin_Helper', 
                    'rc_myctf_width_type_callback' 
                    ), 
                'rc_myctf_layout_section_page',                                      // Matches the section name
                'rc_myctf_layout_section',                                      // Matches the section name
                array( '&nbsp;&nbsp;( Responsive/Mobile Friendly layout )' )        
                );
        
        
        /* Adding the field for 'display_style' type */
        add_settings_field(
                'rc_myctf_display_style', 
                'Display Style', 
                array(
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_display_style_callback'
                ), 
                'rc_myctf_layout_section_page', 
                'rc_myctf_layout_section', 
                array( "Choose a default display style" )
                );
        
        
        /* Adding the 'number of tweets' field  */
        add_settings_field(
                'rc_myctf_number_of_tweets', 
                'Number of Tweets', 
                array(
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_number_of_tweets_callback'
                ), 
                'rc_myctf_layout_section_page', 
                'rc_myctf_layout_section', 
                array( "Default number of Tweets to fetch from Twitter (1 - 10) <span class='rc_myctf_tip'>[up to 50 in Pro]</span>" )
                );
        
        
        /** Adding the 'tweets in row' field  */
        add_settings_field(
                'rc_myctf_number_of_tweets_in_row', 
                'Tweets in a row', 
                array(
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_number_of_tweets_in_row_callback'
                ), 
                'rc_myctf_layout_section_page', 
                'rc_myctf_layout_section', 
                array( "Display the number of tweets in a row (1 - 5) <span class='rc_myctf_tip'>[Available in Pro]</span>" )
                );
        
        
        /* Adding the radio button for 'check tweets every' field   */
        add_settings_field(
                'rc_myctf_check_tweets_every', 
                'Check Tweets Every', 
                array(
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_check_tweets_every_callback'
                ), 
                'rc_myctf_layout_section_page', 
                'rc_myctf_layout_section', 
                array( '' )
                );
        
        /** Adding the field for 'tweet_checking_interval' */
        add_settings_field(
                'rc_myctf_tweet_checking_interval', 
                'Tweet Checking Interval', 
                array(
                    'Rc_Myctf_Admin_Helper',
                    'rc_myctf_tweet_checking_interval_callback'
                ), 
                'rc_myctf_layout_section_page', 
                'rc_myctf_layout_section', 
                array( 'Number of hours/days before we check for new tweets' )
                );
        
    }
    
    
    
    
    /*
     * Initializes the "Support" options by registering the sections,
     * fields, and settings for the "Support" tab
     * 
     * @since 1.0
     */
    public static function rc_myctf_create_support_sections_fields(){
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rc_myctf_support_options',         //option group name
                'rc_myctf_support_options'          //option name
                );
        
        
        /* 
         * Registering Support tab Settings Section. 
         */
        add_settings_section(
                'rc_myctf_api_support_settings_section',                            // ID to identify this section
                'Plugin Settings Options',                                          // Title of the section
                array(                                                              // Function that fills the section with desired content
                    'Rc_Myctf_Admin_Helper', 
                    'rc_myctf_api_support_settings_section_callback' ),  
                'rc_myctf_need_support_section_page'                               // Page on which to add this section. Matches the section name
                );
        
    }//Ends rc_myctf_create_support_sections_fields

        
    
    
    /*
     * Function to delete the Tweets stored in transients 
     * when "rc_myctf_customize_options" is updated.
     * 
     * Since a new "screen_name" is being updated. We need to discard old tweets and fetch
     * new ones for this new "screen_name"
     * 
     * @since 1.0
     */
    public static function rc_myctf_delete_tweets_from_transient(){
        
        //delete stored tweets from transient
        $rc_myctf_cache = new Rc_Myctf_Cache();
        $rc_myctf_cache->rc_myctf_delete_tweets_transient();
        
        
    }//ends rc_myctf_delete_tweets_from_transient
    
    
    
    
    /**
     * Upon token invalidate request, this functions checks if it should be invalidated. 
     * And then calls appropriate function to invalidate it.
     * 
     * @since 1.0
     * @access public
     */
    public static function rc_myctf_check_if_token_should_be_invalidated() {
        
        if ( !isset( $_REQUEST[ 'rc_myctf_action' ] ) ){
            return;
        }
        
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient privileges' );
        }
        
        $action = wp_strip_all_tags( $_REQUEST[ 'rc_myctf_action' ] );
        
        if ( $action == 'invalidated_token' ) {
            add_action( 'admin_notices', array( 'Rc_Myctf_Notices', 'rc_myctf_admin_notice__success' ) );
            return;
        } else if ( $action == 'error' ) {
            add_action( 'admin_notices', array( 'Rc_Myctf_Notices', 'rc_myctf_admin_notice__error' ) );
            return;
        }
        
        
        check_admin_referer( 'rc_myctf-' . $action . '_bearer-token' );
        
        $result = FALSE;
        /* check the $action parameter. And also check that at least 24 hours have elapsed since last token invalidation */
        if ( $action == 'invalidate_token' && Rc_Myctf_Admin::rc_myctf_one_day_passed_since_last_invalidation() ) {
            
            if ( function_exists( Rc_Myctf_OAuth::rc_myctf_invalidate_bearer_token() ) ) {
                $result = Rc_Myctf_OAuth::rc_myctf_invalidate_bearer_token();
                
            }
            
            
            //If $result is TRUE
            if ( $result ) {
                //wp_redirect( add_query_arg( array( 'rc_myctf_action' => 'invalidated_token' ) ) );
            } else {
                //wp_redirect( add_query_arg( array( 'rc_myctf_action' => 'error' ) ) );
            }//ends if
            
        }//ends if
        
    }
    
    
    
    /*
     * Determines whether 24 hours have passed since last invalidation
     * This is to make sure the Twitter service is not abused.
     * 
     * @since 1.0
     * @access public
     * @return  Boolean     True or False
     */
    public static function rc_myctf_one_day_passed_since_last_invalidation() {
        $options = get_option( 'rc_myctf_settings_options' );
        
        /* if variable is not defined, add todays date - 25 hours */
        $token_last_invalidated = isset( $options[ 'token_last_invalidated' ] ) ? wp_strip_all_tags( $options[ 'token_last_invalidated' ] ) : time() - 60*60*25;
        
        /*
         * check to see if 'token_last_invalidated' value is more than 24 hours
         * if yes, return true
         * otherwise, return false
         */
        
        $time_elapsed = time() - $token_last_invalidated;
        
        if ( $time_elapsed > 86400 ) {
            
            return TRUE;
            
        } else {
            
            return FALSE;
            
        }// end if
        
    }
     
    
    
    /*
     * Displays admin notice if Consumer key & Secret is not installed
     * 
     * @since 1.0
     * @access public
     */
    public static function rc_myctf_show_admin_notice_for_no_keys() {
        
        /* fetch the current_screen so that the message can only be shown on this page */
        $current_screen = get_current_screen();
        
        /*
         * $current_screen->id evalutes to 'settings_page_myctf-page' for our plugin page 
         */
        if ( $current_screen->id === 'settings_page_myctf-page' ) {
            
            /* check if consumer key & secret have been added */
            $options_settings = get_option( 'rc_myctf_settings_options' );
            $consumer_key = isset( $options_settings[ 'consumer_key' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_key' ] ) : '';
            $consumer_secret = isset( $options_settings[ 'consumer_secret' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_secret' ] ) : '';

            if ( !$consumer_key || !$consumer_secret ) {
                
                /* add notice to be shown if no keys or secret */
                add_action( 'admin_notices', array( 'Rc_Myctf_Notices', 'rc_myctf_admin_notice__no_keys' ) );
                
            }//ends if
        }
        
    }//ends rc_myctf_show_admin_notice_for_no_keys
    
    
    
}// ends class