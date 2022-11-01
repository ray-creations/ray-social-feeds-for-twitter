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


class Rsfft_Admin {
    
    /**
     * Indicates whether the class has been initialized or not.
     * 
     * @since 1.0
     * @access private
     * @var boolean
     */
    private static $initiated = false;
    
    
    /**
     * Values for fontSizes
     * @since 1.2.1
     * @var array
     */
    public static $fontSizes = array();
    
    
    /**
     * Values for font size in percentages
     * @since 1.2.1
     * @var array
     */
    public static $fontPercents = array();
   
    
    /**
     * Values for font weight
     * @since 1.2.1
     * @var array
     */
    public static $fontWeights = array();
   
    
    /**
     * Values for Text Decorations
     * @since 1.2.1
     * @var array
     */
    public static $textDecorations = array();
   
    
 
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
        add_action( 'admin_menu', array( 'Rsfft_Admin', 'rsfft_add_settings_page' ) );
        
        /**  This creates the API Settings section & fields for the main settings page above  */
        add_action( 'admin_init', array( 'Rsfft_Admin', 'rsfft_create_settings_sections_fields' ) );
        
        /**  This creates the Customize section & fields for the main settings page above  */
        add_action( 'admin_init', array( 'Rsfft_Admin', 'rsfft_create_customize_sections_fields' ) );
        
        /** This creates the Tweets section & fields for the Tweets tab */
        add_action( 'admin_init', array( 'Rsfft_Admin', 'rsfft_create_tweets_section_fields' ) );
        
        /** This creates the Tweets section & fields for the Tweets tab */
        add_action( 'admin_init', array( 'Rsfft_Admin', 'rsfft_create_style_section_fields' ) );
        
        /** This creates the sections & fields for the 'Slider/Carousel' tab in settings page  */
        add_action( 'admin_init', array( 'Rsfft_Admin', 'rsfft_create_slider_carousel_section_fields' ) );
        
        /**  This creates the Support section & fields for the main settings page above  */
        add_action( 'admin_init', array( 'Rsfft_Admin', 'rsfft_create_support_sections_fields' ) );
        
        /* This function is to delete the Tweets saved in Transient when username in Customize tab settings is updated */
        add_action( 'update_option_rsfft_customize_options', array( 'Rsfft_Admin', 'rsfft_delete_tweets_from_transient' ) );
        
        /* Function checks if Consumer key & secret are present. If not, displays an admin notice */
        //add_action( 'admin_head', array( 'Rsfft_Admin', 'rsfft_show_admin_notice_for_no_keys' ) );
        
        /* Resets the options to their default state */
        add_action( 'admin_menu', array( 'Rsfft_Admin', 'rsfft_handle_reset_options_request' ) );
        
        /* Deletes the tweets stored in transient */
        add_action( 'admin_menu', array( 'Rsfft_Admin', 'rsfft_delete_cached_tweets' ) );
        
        /* Fetches tokens from Twitter (Twitter OAuth) */
        add_action( 'admin_menu', array( 'Rsfft_Admin', 'rsfft_fetch_access_tokens_from_twitter' ) );
        
        /* Generates and stores the arrays needed by other functions like font_size etc. */
        add_action( 'admin_init', array( 'Rsfft_Admin', 'rsfft_generate_required_arrays' ) );
        
        
        
        
    }//ends init_hooks
    
    
    /**
     * Public function to add Twitter Settings Page
     */
    public static function rsfft_add_settings_page(){
              
        //
        add_options_page(
                'Ray Social Feeds For Twitter',                               //The text to be displayed in the title tag of the page
                'Ray Twitter Feeds',                                      //Text to be used for the menu
                'manage_options',                                       //capability
                'myctf-page',                                           //slug name to refer to this menu
                array( 'Rsfft_Admin', 'rsfft_settings_page' )     //The callback function to output the content for this page
                );
        
    }
    
    
    /**
     * Function to output the content for the settings page
     * It also houses the tabs for the tabbed navigation
     * 
     * @since 1.0
     */
    public static function rsfft_settings_page(){
        
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
                    <a href="?page=myctf-page&tab=customize" class="nav-tab <?php echo $active_tab == 'customize' ? 'nav-tab-active' : ''; ?>">Customize Feed</a>
                    <a href="?page=myctf-page&tab=tweets" class="nav-tab <?php echo $active_tab == 'tweets' ? 'nav-tab-active' : ''; ?>">Tweets (Show/Hide)</a>
                    <a href="?page=myctf-page&tab=style" class="nav-tab <?php echo $active_tab == 'style' ? 'nav-tab-active' : ''; ?>">Style</a>
                    <a href="?page=myctf-page&tab=slider" class="nav-tab <?php echo $active_tab == 'slider' ? 'nav-tab-active' : ''; ?>">Slider/Carousel</a>
                    <a href="?page=myctf-page&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support</a>
                </h2>

            <?php //settings_errors(); ?>
            <form action="options.php" method="post">
            <?php 

            if ( $active_tab == 'settings' ){
                    settings_fields( 'rsfft_settings_options' );                 //should match with the option name
                    do_settings_sections( 'rsfft_api_settings_page' );           //page name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                    //Section for the plugin settings section
                    do_settings_sections( 'rsfft_api_preserve_settings_page' );         // should match the section name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                    //Section for the plugin settings section
                    do_settings_sections( 'rsfft_api_invalidate_token_page' );         // should match the section name
                ?>
                <hr>

                <?php

            }else if ( $active_tab == 'customize' ){

                settings_fields( 'rsfft_customize_options' );            // should match with the option name
                do_settings_sections( 'rsfft_feed_settings_page' );      // page name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>

                <?php
                do_settings_sections( 'rsfft_layout_section_page' );     // should match the section name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                
                <?php
                do_settings_sections( 'rsfft_customize_links_page' );     // should match the section name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>

                <?php
            }else if ( $active_tab == 'tweets' ){
                
                settings_fields( 'rsfft_tweets_options' );            // should match with the option name
                do_settings_sections( 'rsfft_tweets_general_page' );     // should match the section name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                
                <?php
                do_settings_sections( 'rsfft_tweets_header_page' );   // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                do_settings_sections( 'rsfft_tweets_footer_page' );     // should match the section name
                ?>

                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                
                <?php
            }else if ( $active_tab == 'style' ){
                
                settings_fields( 'rsfft_style_options' );            // should match with the option name
                do_settings_sections( 'rsfft_style_general_page' );   // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                do_settings_sections( 'rsfft_style_header_page' );   // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                do_settings_sections( 'rsfft_style_tweet_page' );   // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                do_settings_sections( 'rsfft_style_footer_page' );   // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                
                <?php
            }else if ( $active_tab == 'slider' ){
                
                settings_fields( 'rsfft_slider_carousel_options' );              // should match with the option name
                do_settings_sections( 'rsfft_slider_carousel_general_page' );    // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                do_settings_sections( 'rsfft_slider_carousel_slider_page' );   // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
                do_settings_sections( 'rsfft_slider_carousel_carousel_page' );   // page name
                ?>
                
                <p class="submit">
                    <input name="Submit" class="button-primary" type="submit" value="Save Changes" />
                </p>
                <hr>
                
                <?php
            }else if ( $active_tab == 'support' ){
                settings_fields( 'rsfft_support_options' );              // should match with the option name
                do_settings_sections( 'rsfft_need_support_section_page' );  // should match with the section name
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
    public static function rsfft_create_settings_sections_fields(){
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rsfft_settings_options',            //option group name
                'rsfft_settings_options',            //option name
                array(                                  //validation function
                    'Rsfft_Admin_Helper',
                    'rsfft_settings_validate_options'
                )      
                );
        
        
        /* Registering the API Settings Section. 
         * This 'API settings' tab fields will belong to this section 
         */
        add_settings_section(
                'rsfft_api_settings_section',                                // ID to identify this section
                'API Settings Options',                                         // Title of the section
                array(                                                          // Function that fills the section with desired content
                    'Rsfft_Admin_Helper', 
                    'rsfft_api_section_callback' ),
                'rsfft_api_settings_page'                                    // Page on which to add this section. Matches the section name
                );
        
        /** Introducing the fields for the "API settings" tab section */
        add_settings_field(
                'rsfft_consumer_key',                                            // ID of the field
                'Consumer Key',                                                     // Title of the field
                array( 'Rsfft_Admin_Helper', 'rsfft_consumer_key_callback' ), // Function that fills the field with desired input 
                'rsfft_api_settings_page',                                             // Page on which to display this field 
                'rsfft_api_settings_section',                                    // Section in which to display this field 
                array( 'Your Twitter API Consumer Key' )                            // Argument passed to the callback function
                );
        
        add_settings_field(
                'rsfft_consumer_secret', 
                'Consumer Secret', 
                array( 'Rsfft_Admin_Helper', 'rsfft_consumer_secret_callback' ), 
                'rsfft_api_settings_page', 
                'rsfft_api_settings_section', 
                array( 'Your Twitter API Consumer Secret' )
                );
        
        add_settings_field(
                'rsfft_access_token', 
                'Access Token', 
                array( 'Rsfft_Admin_Helper', 'rsfft_access_token_callback' ), 
                'rsfft_api_settings_page', 
                'rsfft_api_settings_section', 
                array( 'Your Twitter API Access Token' )
                );
        
        
        add_settings_field(
                'rsfft_access_token_secret', 
                'Access Token Secret', 
                array( 'Rsfft_Admin_Helper', 'rsfft_access_token_secret_callback' ), 
                'rsfft_api_settings_page', 
                'rsfft_api_settings_section', 
                array( 'Your Twitter API Access Token Secret' )
                );
        
        
        
       
        /* Registering the API Settings Section. 
         * This 'API settings' tab fields will belong to this section 
         */
        add_settings_section(
                'rsfft_api_plugin_settings_section',                                    // ID to identify this section
                'Plugin Settings Options',                                          // Title of the section
                array(                                                              // Function that fills the section with desired content
                    'Rsfft_Admin_Helper', 
                    'rsfft_api_plugin_settings_section_callback' ),  
                'rsfft_api_preserve_settings_page'                               // Page on which to add this section. Matches the section name
                );
        
                add_settings_field(
                        'rsfft_preserve_settings', 
                        'Preserve Settings?', 
                        array(
                            'Rsfft_Admin_Helper',
                            'rsfft_preserve_settings_callback'
                        ), 
                        'rsfft_api_preserve_settings_page', 
                        'rsfft_api_plugin_settings_section', 
                        array( 'Check to preserve your settings on plugin uninstall' )
                        );
        
        
                
    }// ends function rsfft_create_settings_sections_fields
    
    
    
    
    /**
     * Initialize the "Customize" options by registering the sections,
     * fields, and settings for the "Customize" tab
     * 
     * @since 1.0
     */
    public static function rsfft_create_customize_sections_fields(){
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rsfft_customize_options',           //option group name
                'rsfft_customize_options',           //option name
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_customize_validate_options'
                )
                );
        
        
        /**
         * Adding the section for Feed Settings under Customize tab
         */
        add_settings_section(
                'rsfft_feed_settings_section',                           // This is the section name
                'Feed Settings',                                            // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_feed_settings_section_callback'
                ), 
                'rsfft_feed_settings_page'                            // Matches the section name   
                );
        
        /** Adding the field for Feed Type  */
        add_settings_field(
                'rsfft_feed_type',                       // This is the section name                                                
                'Feed Type',                                // Title of the section
                array(                                      // Function that fills the field with desired input
                    'Rsfft_Admin_Helper',
                    'rsfft_feed_type_callback'
                ), 
                'rsfft_feed_settings_page',           // Matches the section name
                'rsfft_feed_settings_section',           // Matches the section name
                array('Choose the default feed type for shortcodes.')   // Argument passed to the callback function
                );
        
        /** Adding the field for Screen Name  */
        add_settings_field(
                'rsfft_screen_name',                                                                   
                'Screen Name',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_screen_name_callback'
                ), 
                'rsfft_feed_settings_page', 
                'rsfft_feed_settings_section',
                array('Set a default Twitter screen name for your user timelines.')   
                );
        
        /* Adding the field for Hashtags  */
        add_settings_field(
                'rsfft_hashtags',                                                                   
                'Hashtags',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_hashtags_callback'
                ), 
                'rsfft_feed_settings_page', 
                'rsfft_feed_settings_section',
                array('Set default hashtags.')   
                );
        
        /** Adding the field for Search String  */
        add_settings_field(
                'rsfft_search_string',                                                                   
                'Search String',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_search_string_callback'
                ), 
                'rsfft_feed_settings_page', 
                'rsfft_feed_settings_section',
                array('Set default search terms for your Twitter search_timeline.')   
                );
        
        /* Adding "include_media_type" in search */
        add_settings_field(
                'rsfft_include_media_type',                                                                   
                'Include Media in Search',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_include_media_type_callback'
                ), 
                'rsfft_feed_settings_page', 
                'rsfft_feed_settings_section',
                array('Include Photos', 'Include Videos')   
                );
        
        
        /* Adding the checkbox for Exclude Replies  */
        add_settings_field(
                'rsfft_exclude_replies',                                                                   
                'Exclude Replies?',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_exclude_replies_callback'
                ), 
                'rsfft_feed_settings_page', 
                'rsfft_feed_settings_section',
                array('Exclude replies from Tweets')   
                );
        
        /* Adding the checkbox for Include Retweets  */
        add_settings_field(
                'rsfft_include_rts',                                                                   
                'Include Retweets?',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_include_retweets_callback'
                ), 
                'rsfft_feed_settings_page', 
                'rsfft_feed_settings_section',
                array('Want to display retweets?')   
                );
        
        
        
        
        
        /** 
         * Adding the section for Layout under Customize tab  
         */
        add_settings_section(
                'rsfft_layout_section',                          // This is the section name
                'Additional Customization',                         // Title of the section
                array(                                              // Function that fills the section with desired content
                    'Rsfft_Admin_Helper', 
                    'rsfft_layout_section_callback' 
                    ),
                'rsfft_layout_section_page'                           // Matches the section name
                );                         
        
        
        /** Adding the field for width type */
        add_settings_field(
                'rsfft_feed_width_type',                                     // ID of the field
                'Feed Width Type',                                              // Title of the field
                array(                                                          // Function that fills the field with desired input
                    'Rsfft_Admin_Helper', 
                    'rsfft_width_type_callback' 
                    ), 
                'rsfft_layout_section_page',                                      // Matches the section name
                'rsfft_layout_section',                                      // Matches the section name
                array( '&nbsp;&nbsp;( Responsive/Mobile Friendly layout )' )        
                );
        
        
        /* Adding the field for 'display_style' type */
        add_settings_field(
                'rsfft_display_style', 
                'Display Style', 
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_display_style_callback'
                ), 
                'rsfft_layout_section_page', 
                'rsfft_layout_section', 
                array( "Choose a default display style" )
                );
        
        
        /** Adding the field for hide media option */
        add_settings_field(
                'rsfft_hide_media',
                'Hide Media',     
                array(
                    'Rsfft_Admin_Helper', 
                    'rsfft_hide_media_callback' 
                    ), 
                'rsfft_layout_section_page',
                'rsfft_layout_section',
                array( "Hide all images/videos" )        
                );
        
        
        
        /* Adding the 'number of tweets' field  */
        add_settings_field(
                'rsfft_number_of_tweets', 
                'Number of Tweets', 
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_number_of_tweets_callback'
                ), 
                'rsfft_layout_section_page', 
                'rsfft_layout_section', 
                array( "Default number of Tweets to fetch from Twitter" )
                );
        
        
        /** Adding the 'tweets in row' field  */
        add_settings_field(
                'rsfft_number_of_tweets_in_row', 
                'Tweets in a row', 
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_number_of_tweets_in_row_callback'
                ), 
                'rsfft_layout_section_page', 
                'rsfft_layout_section', 
                array( "Number of tweets in a Masonry row (1 - 5)" )
                );
        
        
        /* Adding the radio button for 'check tweets every' field   */
        add_settings_field(
                'rsfft_check_tweets_every', 
                'Check Tweets Every', 
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_check_tweets_every_callback'
                ), 
                'rsfft_layout_section_page', 
                'rsfft_layout_section', 
                array( '' )
                );
        
        /** Adding the field for 'tweet_checking_interval' */
        add_settings_field(
                'rsfft_tweet_checking_interval', 
                'Tweet Checking Interval', 
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_tweet_checking_interval_callback'
                ), 
                'rsfft_layout_section_page', 
                'rsfft_layout_section', 
                array( 'Number of hours/days before we check for new tweets' )
                );
        
        
        
        /** 
         * Adding the section for Links under Customize tab  
         */
        add_settings_section(
                'rsfft_customize_links_section',                         // This is the section name
                'Links Settings',                                           // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper', 
                    'rsfft_customize_links_section_callback' 
                    ),
                'rsfft_customize_links_page'                             // Matches the section name
                );                         
        
        
        /** Adding the field for width type */
        add_settings_field(
                'rsfft_customize_remove_links_hashtags',
                'Remove Links from Hashtags', 
                array(                                                          
                    'Rsfft_Admin_Helper', 
                    'rsfft_customize_links_hashtags_callback' 
                    ), 
                'rsfft_customize_links_page',  
                'rsfft_customize_links_section', 
                array( '&nbsp;&nbsp;( Remove all links from Hashtags )' )        
                );
        
        
        /** Adding the field for width type */
        add_settings_field(
                'rsfft_customize_remove_links_mentions',
                'Remove Links from Mentions', 
                array(                                                          
                    'Rsfft_Admin_Helper', 
                    'rsfft_customize_links_mentions_callback' 
                    ), 
                'rsfft_customize_links_page',  
                'rsfft_customize_links_section', 
                array( '&nbsp;&nbsp;( Remove all links from Mentions )' )        
                );
        
        
        /** Adding the field for width type */
        add_settings_field(
                'rsfft_customize_remove_ext_links',
                'Remove External Links',                               // Title of the field
                array(                                                          // Function that fills the field with desired input
                    'Rsfft_Admin_Helper', 
                    'rsfft_customize_remove_ext_links_callback' 
                    ), 
                'rsfft_customize_links_page',                                // Matches the section name
                'rsfft_customize_links_section',                             // Matches the section name
                array( '&nbsp;&nbsp;( Remove all external links )' )        
                );
        
        
        /** Adding the field for width type */
        add_settings_field(
                'rsfft_customize_link_add_nofollow',                         // ID of the field
                'Add nofollow to External Links',                               // Title of the field
                array(                                                          // Function that fills the field with desired input
                    'Rsfft_Admin_Helper', 
                    'rsfft_customize_link_add_nofollow_callback' 
                    ), 
                'rsfft_customize_links_page',                                // Matches the section name
                'rsfft_customize_links_section',                             // Matches the section name
                array( '&nbsp;&nbsp;( Nofollow all external links )' )        
                );
        
        
    }//end rsfft_create_customize_sections_fields
    
    
    
    
    /**
     * Initialize the "Tweets" options by registering the sections,
     * fields, and settings for the "Tweets" tab
     * 
     * @since 1.2.1
     */
    public static function rsfft_create_tweets_section_fields() {
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rsfft_tweets_options',           //option group name
                'rsfft_tweets_options',           //option name
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_tweets_validate_options'
                )
                );
        
        
        
        /**
         * Adding the section for Tweet General under Tweets tab
         */
        add_settings_section(
                'rsfft_tweet_general_section',                           // This is the section name
                'Tweet General',                                            // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_tweet_general_section_callback'
                ), 
                'rsfft_tweets_general_page'                            // Matches the section name   
                );
        
        
        /* Adding the checkbox for Tweet Border Display  */
        add_settings_field(
                'rsfft_tweet_border',                                                                   
                'Display Tweet Border?',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_tweet_display_border_callback'
                ), 
                'rsfft_tweets_general_page', 
                'rsfft_tweet_general_section',
                array('Toggles the visibility of the border/shadow around each tweet')   
                );
        
        
        
        
        /**
         * Adding the section for Tweet Header under Tweets tab
         */
        add_settings_section(
                'rsfft_tweet_header_section',                           // This is the section name
                'Tweet Header',                                            // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_tweet_header_section_callback'
                ), 
                'rsfft_tweets_header_page'                            // Matches the section name   
                );
        
        
        /* Adding the checkbox for Display Header  */
        add_settings_field(
                'rsfft_display_header',                                                                   
                'Display Tweet Header?',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_display_header_callback'
                ), 
                'rsfft_tweets_header_page', 
                'rsfft_tweet_header_section',
                array('Controls the visibility of the Tweet header')   
                );
        
        
        /* Adding the checkbox for Profile Image for Header  */
        add_settings_field(
                'rsfft_display_profile_img_header',                                                                   
                'Display Profile Image',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_display_profile_img_header_callback'
                ), 
                'rsfft_tweets_header_page', 
                'rsfft_tweet_header_section',
                array('Show profile image in Tweet header')   
                );
        
        
        /* Adding the checkbox for Display Name for Header  */
        add_settings_field(
                'rsfft_display_name_header',                                                                   
                'Display Twitter Name',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_display_name_header_callback'
                ), 
                'rsfft_tweets_header_page', 
                'rsfft_tweet_header_section',
                array('Show Twitter name in the header')   
                );
        
        
        /* Adding the checkbox for Display Screen Name for Header  */
        add_settings_field(
                'rsfft_display_screen_name_header',                                                                   
                'Display Screen Name',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_display_screen_name_header_callback'
                ), 
                'rsfft_tweets_header_page', 
                'rsfft_tweet_header_section',
                array('Show Twitter screen name in the header (eg. @raycreations)')   
                );
        
        
        /* Adding the checkbox for Display Date for Tweet Header  */
        add_settings_field(
                'rsfft_display_date_header',                                                                   
                'Display Date',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_display_date_header_callback'
                ), 
                'rsfft_tweets_header_page', 
                'rsfft_tweet_header_section',
                array('Show date/time of tweet in the header')   
                );
        
        
        
        
        /** 
        * Adding the section for Layout under Customize tab  
        */
        add_settings_section(
                'rsfft_tweet_footer_section',                          // This is the section name
                'Tweet Footer',                                          // Title of the section
                array(                                              // Function that fills the section with desired content
                    'Rsfft_Admin_Helper', 
                    'rsfft_tweet_footer_section_callback' 
                    ),
                'rsfft_tweets_footer_page'                           // Matches the section name
                );                         


        /** Adding the field for Display Tweet Footer */
        add_settings_field(
                'rsfft_display_tweet_footer',                                     // ID of the field
                'Display Tweet Footer',                                         // Title of the field
                array(                                                          // Function that fills the field with desired input
                    'Rsfft_Admin_Helper', 
                    'rsfft_display_tweet_footer_callback' 
                    ), 
                'rsfft_tweets_footer_page',                                      // Matches the section name
                'rsfft_tweet_footer_section',                                      // Matches the section name
                array( 'Controls the visibility of the Tweet Footer' )        
                );
        
        
        /** Adding the field for Display Likes */
        add_settings_field(
                'rsfft_display_likes_footer',
                'Display Likes',
                array(
                    'Rsfft_Admin_Helper', 
                    'rsfft_display_likes_footer_callback' 
                    ), 
                'rsfft_tweets_footer_page',
                'rsfft_tweet_footer_section',
                array( 'Show Likes in the Tweet Footer' )        
                );
        
        
        /** Adding the field for Display Retweets */
        add_settings_field(
                'rsfft_display_retweets_footer',
                'Display Retweets',
                array(
                    'Rsfft_Admin_Helper', 
                    'rsfft_display_retweets_footer_callback' 
                    ), 
                'rsfft_tweets_footer_page',
                'rsfft_tweet_footer_section',
                array( 'Show Retweets in the Tweet Footer' )        
                );
        
        
        /** Adding the field for Display Screen Name */
        add_settings_field(
                'rsfft_display_screen_name_footer',
                'Display Screen Name',
                array(
                    'Rsfft_Admin_Helper', 
                    'rsfft_display_screen_name_footer_callback' 
                    ), 
                'rsfft_tweets_footer_page',
                'rsfft_tweet_footer_section',
                array( 'Show Screen Name in the Tweet Footer (eg. @raycreations)' )        
                );
        
        
        /** Adding the field for Display Date */
        add_settings_field(
                'rsfft_display_date_footer',
                'Display Date',
                array(
                    'Rsfft_Admin_Helper', 
                    'rsfft_display_date_footer_callback' 
                    ), 
                'rsfft_tweets_footer_page',
                'rsfft_tweet_footer_section',
                array( 'Show Date in the Tweet Footer' )        
                );
        
        
    }//ends rsfft_create_tweets_section_fields
    
    
    
    
    
    
    /**
     * Initialize the "Style" options by registering the sections,
     * fields, and settings for the "Style" tab
     * 
     * @since 1.2.1
     */
    public static function rsfft_create_style_section_fields() {
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rsfft_style_options',           //option group name
                'rsfft_style_options',           //option name
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_style_validate_options'
                )
                );
        
        
        /**
         * Adding the section for General under "Style" tab
         */
        add_settings_section(
                'rsfft_style_general_section',                           // This is the section name
                'Style General',                                            // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_style_general_section_callback'
                ), 
                'rsfft_style_general_page'                            // Matches the section name   
                );
        
        
        /* Adding the dropdown field for General Font Size under "Style" tab  */
        add_settings_field(
                'rsfft_style_font_size',                                                                   
                'Overall Font Size (px)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_font_size_callback'
                ), 
                'rsfft_style_general_page', 
                'rsfft_style_general_section',
                array('Overall font size in pixels (px)')   
                );
        
        
        /* Adding the color picker for General Font Color under "Style" tab  */
        add_settings_field(
                'rsfft_style_font_color',                                                                   
                'Choose Font Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_font_color_callback'
                ), 
                'rsfft_style_general_page', 
                'rsfft_style_general_section',
                array('Leave blank for default')   
                );
        
        
        /* Adding the checkbox for Display Header  */
        add_settings_field(
                'rsfft_style_link_text_decoration',                                                                   
                'Link Text Decoration',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_link_text_decoration_callback'
                ), 
                'rsfft_style_general_page', 
                'rsfft_style_general_section',
                array('Hyperlinked text-decoration')   
                );
        
        
        /* Adding the color picker for Display Feed Background Color in Style General section  */
        /*
        add_settings_field(
                'rsfft_style_feed_bg_color',                                                                   
                'Feed Background Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_feed_bg_color_callback'
                ), 
                'rsfft_style_general_page', 
                'rsfft_style_general_section',
                array('Feed Background color')   
                );
        */
        
        
        /* Adding the checkbox for Display Header  */
        add_settings_field(
                'rsfft_style_tweet_bg_color',                                                                   
                'Tweet Background Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_tweet_bg_color_callback'
                ), 
                'rsfft_style_general_page', 
                'rsfft_style_general_section',
                array('Tweet Background color')   
                );
        
        
        /* Adding the checkbox for Display Header  */
        add_settings_field(
                'rsfft_style_tweet_border_type',                                                                   
                'Tweet Border Type',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_tweet_border_type_callback'
                ), 
                'rsfft_style_general_page', 
                'rsfft_style_general_section',
                array('Border around tweets')   
                );
        
        
        
        /**
         * Adding the section for Header under "Style" tab
         */
        add_settings_section(
                'rsfft_style_header_section',                                // This is the section name
                'Tweet Header',                                           // Title of the section
                array(                                                          // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_style_header_section_callback'
                ), 
                'rsfft_style_header_page'                                    // Matches the section name   
                );
        
        
        /* Adding the checkbox for Display Header  */
        add_settings_field(
                'rsfft_style_font_size_header',                                                                   
                'Font Size (%)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_font_size_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Percentage (%) of the <i>Overall Font Size</i> set above')   
                );
        
        
        /* Adding the color picker for Name Font Color in Header section */
        add_settings_field(
                'rsfft_style_name_font_color_header',                                                                   
                'Name Font Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_name_font_color_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Name font color in the Twitter Head section')   
                );
        
        
        /* Adding the dropdown box for Name Font Weight in Header Section  */
        add_settings_field(
                'rsfft_style_name_font_weight_header',                                                                   
                'Name Font Weight',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_name_font_weight_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Name font weight in the Twitter Head section')   
                );
        
        
        /* Adding the color picker for Screen Name Font Size in Header section */
        add_settings_field(
                'rsfft_style_screen_name_font_size_header',                                                                   
                'Screen Name Font Size (%)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_screen_name_font_size_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Screen Name font size percentage in the Twitter Head section')   
                );
        
        
        /* Adding the color picker for Screen Name Font Color in Header section */
        add_settings_field(
                'rsfft_style_screen_name_font_color_header',                                                                   
                'Screen Name Font Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_screen_name_font_color_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Screen Name (e.g. @raycreations) font color in the Twitter Head section')   
                );
        
        
        /* Adding the dropdown box for Name Font Weight in Header Section  */
        add_settings_field(
                'rsfft_style_screen_name_font_weight_header',                                                                   
                'Screen Name Font Weight',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_screen_name_font_weight_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Screen Name font weight in the Twitter Head section')   
                );
        
        
        /* Adding the dropdown for Date Font Size in Header section */
        add_settings_field(
                'rsfft_style_date_font_size_header',                                                                   
                'Date Font Size (%)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_date_font_size_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Date font size percentage in the Twitter Head section')   
                );
        
        
        /* Adding the color picker for Date Font Color in Header section */
        add_settings_field(
                'rsfft_style_date_font_color_header',                                                                   
                'Date Font Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_date_font_color_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Date font color in the Twitter Head section')   
                );
        
        
        /* Adding the dropdown box for Date Font Weight in Header Section  */
        add_settings_field(
                'rsfft_style_date_font_weight_header',                                                                   
                'Date Font Weight',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_date_font_weight_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Date font weight in the Twitter Head section')   
                );
        
        
        /* Adding the dropdown box for Link Text Decoration in Header Section  */
        add_settings_field(
                'rsfft_style_link_text_decoration_header',                                                                   
                'Link Text Decoration',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_link_text_decoration_header_callback'
                ), 
                'rsfft_style_header_page', 
                'rsfft_style_header_section',
                array('Link text decoration styling')   
                );
        
        
        
        
        /**
         * Adding the section for Tweet under "Style" tab
         */
        add_settings_section(
                'rsfft_style_tweet_section',                                // This is the section name
                'Tweets Section',                                           // Title of the section
                array(                                                          // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_style_tweet_section_callback'
                ), 
                'rsfft_style_tweet_page'                                    // Matches the section name   
                );
        
        
        /* Adding the dropdown for Tweet Font Size  */
        add_settings_field(
                'rsfft_style_font_size_tweet',                                                                   
                'Font Size (%)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_font_size_tweet_callback'
                ), 
                'rsfft_style_tweet_page', 
                'rsfft_style_tweet_section',
                array('Percentage (%) of the <i>Overall Font Size</i> set above')   
                );
        
        
        /* Adding the color picker for Font Color in Tweet section */
        add_settings_field(
                'rsfft_style_font_color_tweet',                                                                   
                'Font Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_font_color_tweet_callback'
                ), 
                'rsfft_style_tweet_page', 
                'rsfft_style_tweet_section',
                array('Font color of the tweets')   
                );
        
        
        /* Adding the dropdown box for Date Font Weight in Header Section  */
        add_settings_field(
                'rsfft_style_font_weight_tweet',                                                                   
                'Font Weight',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_font_weight_tweet_callback'
                ), 
                'rsfft_style_tweet_page', 
                'rsfft_style_tweet_section',
                array('Font weight of the tweets')   
                );
        
        
        /* Adding the color picker for Font Color in Tweet section */
        add_settings_field(
                'rsfft_style_link_color_tweet',                                                                   
                'Link Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_link_color_tweet_callback'
                ), 
                'rsfft_style_tweet_page', 
                'rsfft_style_tweet_section',
                array('Link color of the tweets')   
                );
        
        
        /* Adding the checkbox for Display Header  */
        add_settings_field(
                'rsfft_style_link_text_decoration_tweet',                                                                   
                'Link Text Decoration',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_link_text_decoration_tweet_callback'
                ), 
                'rsfft_style_tweet_page', 
                'rsfft_style_tweet_section',
                array('Hyperlinked text-decoration')   
                );
        
        
        
        
        
        /**
         * Adding the section for Footer under "Style" tab
         */
        add_settings_section(
                'rsfft_style_footer_section',                                // This is the section name
                'Footer Section',                                           // Title of the section
                array(                                                          // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_style_footer_section_callback'
                ), 
                'rsfft_style_footer_page'                                    // Matches the section name   
                );
        
        
        /* Adding the dropdown for Style tab - Footer Font Size  */
        add_settings_field(
                'rsfft_style_font_size_footer',                                                                   
                'Font Size (%)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_font_size_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Percentage (%) of the <i>Overall Font Size</i> set above')   
                );
        
        /* Adding the color picker for Like icon Color in Style Footer section */
        add_settings_field(
                'rsfft_style_like_icon_color_footer',                                                                   
                'Like Icon Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_like_icon_color_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Like icon color in the Tweet footer')   
                );
        
        
        /* Adding the color picker for Like count in Style Footer section */
        add_settings_field(
                'rsfft_style_like_count_color_footer',                                                                   
                'Like Count Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_like_count_color_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Like count color in the Tweet footer')   
                );
        
        
        /* Adding the color picker for Retweet icon Color in Style Footer section */
        add_settings_field(
                'rsfft_style_retweet_icon_color_footer',                                                                   
                'Retweet Icon Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_retweet_icon_color_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Retweet icon color in the Tweet footer')   
                );
        
        
        /* Adding the color picker for Like count in Style Footer section */
        add_settings_field(
                'rsfft_style_retweet_count_color_footer',                                                                   
                'Retweet Count Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_retweet_count_color_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Retweet count color in the Tweet footer')   
                );
        
        
        /* Adding the color picker for Screen Name Font Color in Style Footer section */
        add_settings_field(
                'rsfft_style_screen_name_font_color_footer',                                                                   
                'Screen Name Font Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_screen_name_font_color_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Screen Name (e.g. @raycreations) font color in the Tweet footer')   
                );
        
        
        /* Adding the dropdown box for Screen Name Font Weight in Style Footer Section  */
        add_settings_field(
                'rsfft_style_screen_name_font_weight_footer',                                                                   
                'Screen Name Font Weight',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_screen_name_font_weight_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Screen Name font weight in the Tweet footer')   
                );
        
        
        /* Adding the color picker for Screen Name Font Color in Style Footer section */
        add_settings_field(
                'rsfft_style_date_font_color_footer',                                                                   
                'Date Font Color',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_date_font_color_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Date font color in the Tweet footer')   
                );
        
        
        /* Adding the dropdown box for Date Font Weight in Style Footer Section  */
        add_settings_field(
                'rsfft_style_date_font_weight_footer',                                                                   
                'Date Font Weight',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_date_font_weight_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Date font weight in the Tweet footer')   
                );
        
        
        /* Adding the dropdown box for Link Text Decoration in Footer Section under 'Style' tab  */
        add_settings_field(
                'rsfft_style_link_text_decoration_footer',                                                                   
                'Link Text Decoration',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_style_link_text_decoration_footer_callback'
                ), 
                'rsfft_style_footer_page', 
                'rsfft_style_footer_section',
                array('Link text decoration styling')   
                );
        
        
        
        
    }//ends rsfft_create_style_section_fields
    
    
    
    
    
    
    /**
     * Initialize the "Slider/Carousel" options by registering the sections,
     * fields, and settings for the "Slider/Carousel" tab
     * 
     * @since 1.2.1
     */
    public static function rsfft_create_slider_carousel_section_fields() {
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rsfft_slider_carousel_options',           //option group name
                'rsfft_slider_carousel_options',           //option name
                array(
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_validate_options'
                )
                );
        
        
        /**
         * Adding the section for General under "Slider/Carousel" tab
         */
        add_settings_section(
                'rsfft_slider_carousel_general_section',                           // This is the section name
                'General Settings',                                                  // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_general_section_callback'
                ), 
                'rsfft_slider_carousel_general_page'                            // Matches the section name   
                );
        
        
        /* Adding the checkbox field for Navigation Arrows under "Slider/Carousel" General tab  */
        add_settings_field(
                'rsfft_slider_carousel_nav_arrows',                                                                   
                'Show Nav Arrows',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_nav_arrows_callback'
                ), 
                'rsfft_slider_carousel_general_page', 
                'rsfft_slider_carousel_general_section',
                array('Display the prev/next navigation arrow')   
                );
        
        
        /* Adding the checkbox field for Navigation Dots under "Slider/Carousel" General tab  */
        add_settings_field(
                'rsfft_slider_carousel_nav_dots',                                                                   
                'Show Dots Navigation',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_nav_dots_callback'
                ), 
                'rsfft_slider_carousel_general_page', 
                'rsfft_slider_carousel_general_section',
                array('Display dots navigation')   
                );
        
        
        /* Adding the checkbox field for Navigation Dots under "Slider/Carousel" General tab  */
        add_settings_field(
                'rsfft_slider_carousel_autoplay',                                                                   
                'Autoplay',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_autoplay_callback'
                ), 
                'rsfft_slider_carousel_general_page', 
                'rsfft_slider_carousel_general_section',
                array('Automatic transition of slides')   
                );
        
        
        /* Adding the text field for Transition Interval under "Slider/Carousel" General tab  */
        add_settings_field(
                'rsfft_slider_carousel_transition_interval',                                                                   
                'Transition Interval (Sec)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_transition_interval_callback'
                ), 
                'rsfft_slider_carousel_general_page', 
                'rsfft_slider_carousel_general_section',
                array('(1-20) Automatic transition of slides in seconds')   
                );
        
        
        /* Adding the text field for Transition Speed under "Slider/Carousel" General tab  */
        add_settings_field(
                'rsfft_slider_carousel_transition_speed',                                                                   
                'Transition Speed (Sec)',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_transition_speed_callback'
                ), 
                'rsfft_slider_carousel_general_page', 
                'rsfft_slider_carousel_general_section',
                array('(1-10) Automatic speed of slides in seconds')   
                );
        
        
        /* Adding the checkbox field for 'Pause on Hover' under "Slider/Carousel" General tab  */
        add_settings_field(
                'rsfft_slider_carousel_pause_on_hover',                                                                   
                'Pause On Hover',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_pause_on_hover_callback'
                ), 
                'rsfft_slider_carousel_general_page', 
                'rsfft_slider_carousel_general_section',
                array('Pause on mouse hover')   
                );
        
        
        /* Adding the checkbox field for 'Loop' under "Slider/Carousel" General tab  */
        add_settings_field(
                'rsfft_slider_carousel_loop',                                                                   
                'Loop',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_loop_callback'
                ), 
                'rsfft_slider_carousel_general_page', 
                'rsfft_slider_carousel_general_section',
                array('Loop through the tweet items')   
                );
        
        
        
        
        
        /**
         * Adding the section for 'Slider Settings' under "Slider/Carousel" tab
         */
        add_settings_section(
                'rsfft_slider_carousel_slider_section',                           // This is the section name
                'Slider Settings',                                                  // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_slider_section_callback'
                ), 
                'rsfft_slider_carousel_slider_page'                            // Matches the section name   
                );
        
        
        /* Adding the checkbox field for 'Auto Height' under "Slider/Carousel" Slider tab  */
        add_settings_field(
                'rsfft_slider_carousel_nav_arrows',                                                                   
                'Auto Height',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_auto_height_callback'
                ), 
                'rsfft_slider_carousel_slider_page', 
                'rsfft_slider_carousel_slider_section',
                array('Automatic height adjustment')   
                );
        
        
        
        
        /**
         * Adding the section for 'Carousel Settings' under "Slider/Carousel" tab
         */
        add_settings_section(
                'rsfft_slider_carousel_carousel_section',                           // This is the section name
                'Carousel Settings',                                                  // Title of the section
                array(                                                      // Function that fills the section with desired content
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_carousel_section_callback'
                ), 
                'rsfft_slider_carousel_carousel_page'                            // Matches the section name   
                );
        
        
        /* Adding the checkbox field for 'Items On Screen' under "Slider/Carousel" Carousel tab  */
        add_settings_field(
                'rsfft_slider_carousel_items_on_screen',                                                                   
                'Items On Screen',                               
                array(                                      
                    'Rsfft_Admin_Helper',
                    'rsfft_slider_carousel_items_on_screen_callback'
                ), 
                'rsfft_slider_carousel_carousel_page', 
                'rsfft_slider_carousel_carousel_section',
                array('Number of items shown in the carousel at once')   
                );
        
        
        
        
    }//ends rsfft_create_slider_carousel_section_fields
    
    
    
    
    
    
  
    
    /*
     * Initializes the "Support" options by registering the sections,
     * fields, and settings for the "Support" tab
     * 
     * @since 1.0
     */
    public static function rsfft_create_support_sections_fields(){
        
        /* Registering the fields with WordPress  */
        register_setting(
                'rsfft_support_options',         //option group name
                'rsfft_support_options'          //option name
                );
        
        
        /* 
         * Registering Support tab Settings Section. 
         */
        add_settings_section(
                'rsfft_api_support_settings_section',                            // ID to identify this section
                'Plugin Settings Options',                                          // Title of the section
                array(                                                              // Function that fills the section with desired content
                    'Rsfft_Admin_Helper', 
                    'rsfft_api_support_settings_section_callback' ),  
                'rsfft_need_support_section_page'                               // Page on which to add this section. Matches the section name
                );
        
    }//Ends rsfft_create_support_sections_fields

        
    
    
    /*
     * Function to delete the Tweets stored in transients 
     * when "rsfft_customize_options" is updated.
     * 
     * Since a new "screen_name" is being updated. We need to discard old tweets and fetch
     * new ones for this new "screen_name"
     * 
     * @since 1.0
     */
    public static function rsfft_delete_tweets_from_transient(){
        
        //delete stored tweets from transient
        $rsfft_cache = new Rsfft_Cache();
        $status = $rsfft_cache->rsfft_delete_tweets_transient();
        
        return $status;
        
    }//ends rsfft_delete_tweets_from_transient
    
    
    
    
    
    /*
     * Determines whether 24 hours have passed since last invalidation
     * This is to make sure the Twitter service is not abused.
     * 
     * @since 1.0
     * @access public
     * @return  Boolean     True or False
     */
    public static function rsfft_one_day_passed_since_last_invalidation() {
        $options = get_option( 'rsfft_settings_options' );
        
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
    public static function rsfft_show_admin_notice_for_no_keys() {
        
        /* fetch the current_screen so that the message can only be shown on this page */
        $current_screen = get_current_screen();
        
        /*
         * $current_screen->id evalutes to 'settings_page_myctf-page' for our plugin page 
         */
        if ( $current_screen->id === 'settings_page_myctf-page' ) {
            
            /* check if consumer key & secret have been added */
            $options_settings = get_option( 'rsfft_settings_options' );
            $consumer_key = isset( $options_settings[ 'consumer_key' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_key' ] ) : '';
            $consumer_secret = isset( $options_settings[ 'consumer_secret' ] ) ? wp_strip_all_tags( $options_settings[ 'consumer_secret' ] ) : '';

            if ( !$consumer_key || !$consumer_secret ) {
                
                /* add notice to be shown if no keys or secret */
                add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__no_keys' ) );
                
            }//ends if
        }
        
    }//ends rsfft_show_admin_notice_for_no_keys
    
    
    
    
    /*
     * Deletes the cached Tweets when one clicks on the "Delete Cached Tweets" button
     * in the admin panel
     * 
     * @since 1.1
     * @access public
     */
    public static function rsfft_delete_cached_tweets() {
        
        /* check if 'rsfft_action_cache' is set, if not return */
        if ( !isset( $_REQUEST[ 'rsfft_action_cache' ] ) ){ return; }
        
        /* check if current user has sufficient privileges, otherwise, tell WordPress to die */
        if ( !current_user_can( 'manage_options' ) ) { wp_die( 'Insufficient privileges' ); }
        
        /* Extract the value of 'rsfft_action_cache' */
        $action = wp_strip_all_tags( $_REQUEST[ 'rsfft_action_cache' ] );
        
        
        if ( $action == 'deleted_cached_tweets' ) {
            add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__success' ) );
            return;
        } else if ( $action == 'error' ) {
            add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__error' ) );
            return;
        }
        
        check_admin_referer( 'rsfft-' . $action . '_cache' );
        
        $result = FALSE;
            
        //check that action equals 'delete_cached_tweets'
        if ( $action == 'delete_cached_tweets' ) {

        //delete cached tweets
        $result = Rsfft_Admin::rsfft_delete_tweets_from_transient();
        
        /* url of our plugin page */
        //$admin_url = admin_url( 'options-general.php?page=myctf-page' );
        
            //if $result is TRUE
            if ( $result ) {
                wp_redirect( add_query_arg( array( 'rsfft_action_cache' => 'deleted_cached_tweets' ), RSFFT_ADMIN_URL ) );
            } else {
                wp_redirect( add_query_arg( array( 'rsfft_action_cache' => 'error' ), RSFFT_ADMIN_URL ) );
            }
        }//end if
        
    }//ends rsfft_delete_cached_tweets
    
    
    
    
    
    
    
    /*
     * Function to handle the fetching of Acces tokens, secret,
     * consumer key, and consumer secret from Twitter API.
     * 
     * The request is sent to Ray Creations API, which in turn sends us back the 
     * authorization url.
     * 
     * @since 1.1
     * @access public
     */
    public static function rsfft_fetch_access_tokens_from_twitter() {
        
        /* check if 'rsfft_action_token' is set, if not return */
        if ( !isset( $_REQUEST[ 'rsfft_action_token' ] ) ){ return; }
        
        /* check if current user has sufficient privileges, otherwise, tell WordPress to die */
        if ( !current_user_can( 'manage_options' ) ) { wp_die( 'Insufficient privileges' ); }
        
        /* url of our plugin page */
        //$admin_url = admin_url( 'options-general.php?page=myctf-page' );
        
        /* Extract the value of 'rsfft_action_token' */
        $action = wp_strip_all_tags( $_REQUEST[ 'rsfft_action_token' ] );
        
        
        if ( $action == 'fetched_access_token' ) {
            add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__success' ) );
            return;
        } else if ( $action == 'error' ) {
            add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__error' ) );
            return;
        } else if ( $action == 'saved_tokens' ) {
            
            /* the tokens are saved temporarily with Ray Creations, which needs to be fetched using API */
            $fetch_status = Rsfft_OAuth::rsfft_fetch_saved_tokens_from_ray_creations_api();
            
            if ( $fetch_status ) {
                /* redirect so that the page can load again and show the stored keys in the admin */
                wp_redirect( add_query_arg( array( 'rsfft_action_token' => 'fetched_access_token' ), RSFFT_ADMIN_URL ) );
                exit;
            } else {
                
                add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__error' ) );
                return;
            }//ends if
            
        }//ends if
        
        check_admin_referer( 'rsfft-' . $action . '_fetch-token' );
        
        $oauth_url = '';
            
        //check that action equals 'fetch_access_token'
        if ( $action == 'fetch_access_token' ) {
        
            //delete cached tweets
            $oauth_url = Rsfft_OAuth::rsfft_fetch_3_legged_oauth_url_from_ray_creations_api();

            //if $result is TRUE
            if ( !empty( $oauth_url ) ) {
                //wp_redirect( add_query_arg( array( 'rsfft_action_token' => 'fetched_access_token' ), RSFFT_ADMIN_URL ) );
                header('Location: ' . $oauth_url );
            } else {
                wp_redirect( add_query_arg( array( 'rsfft_action_token' => 'error' ), RSFFT_ADMIN_URL ) );
            }
        }//end if
        
    }//ends rsfft_fetch_access_tokens_from_twitter

    
    
    /*
     * Generates the arrays requried by other functions like
     * $font_size needed by the Style Settings tab
     * 
     * @since 1.2.1
     */
    public static function rsfft_generate_required_arrays() {
        
        //set the values for global $fontSizes
        Rsfft_Admin::$fontSizes = array(
            'inherit', '8', '9', '10', '11', '12', '14', '16', '18', '20', '22', '24', '26', '28', '36', '48', '72'
        );
        
        //set the values for the global $fontPercents
        Rsfft_Admin::$fontPercents = array(
            'inherit', '50', '55', '60', '65', '70', '75', '80', '85', '90', 
            '95', '100', '105', '110', '115', '120', '125', '130', '135', '140', 
            '145', '150'
        );
        
        //set the values for the global $fontWeights
        Rsfft_Admin::$fontWeights = array( 'inherit', 'normal', 'bold' );
        
        //set the values for text decorations
        Rsfft_Admin::$textDecorations = array( 'inherit', 'none', 'underline' );
        
        
    }//ends rsfft_generate_required_arrays
    
    
    
    /*
     * Handles the reset options for the settings tabs
     * in the admin panel
     * 
     * @since 1.2.1
     * @access public
     */
    public static function rsfft_handle_reset_options_request() {
        
        /* check if 'rsfft_action_reset' is set, if not return */
        if ( !isset( $_REQUEST[ 'rsfft_action_reset' ] ) || !isset( $_REQUEST[ 'tab' ] ) ){ 
            return; 
        }
        
        /* check if current user has sufficient privileges, otherwise, tell WordPress to die */
        if ( !current_user_can( 'manage_options' ) ) { wp_die( 'Insufficient privileges' ); }
        
        /* Extract the value of 'rsfft_action_reset' */
        //$action = wp_strip_all_tags( $_REQUEST[ 'rsfft_action_reset' ] );
        $action = sanitize_text_field( filter_input( INPUT_GET, 'rsfft_action_reset' ) );
        $tab = sanitize_text_field( filter_input( INPUT_GET, 'tab' ) );
        
        if ( $action == 'success' ) {
            add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__success' ) );
            return;
        } else if ( $action == 'error' ) {
            add_action( 'admin_notices', array( 'Rsfft_Notices', 'rsfft_admin_notice__error' ) );
            return;
        }
        
        //verify nonce
        check_admin_referer( 'rsfft-' . $action . '_reset' );
        
        //reset tweet visibility options
        $result = Rsfft_Admin_Helper::rsfft_reset_options( $action );
            
        //check that action equals 'delete_cached_tweets'
        if ( $action == 'reset_tweets_visibility' || $action == 'reset_style' || $action == 'reset_slider_options' ) {
            //if $result is TRUE
            if ( $result ) {
                wp_redirect( add_query_arg( array( 'tab' => $tab, 'rsfft_action_reset' => 'success' ), RSFFT_ADMIN_URL ) );
            } else {
                wp_redirect( add_query_arg( array( 'tab' => $tab, 'rsfft_action_reset' => 'error' ), RSFFT_ADMIN_URL ) );
            }
        }//end if
        
    }//ends rsfft_delete_cached_tweets
    
    

}// ends class