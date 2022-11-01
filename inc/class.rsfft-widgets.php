<?php

/*
 * Manages the widgets for this plugin
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


class Rsfft_Widget extends WP_Widget {
    
    /**
     * Holds the id of the current widget.
     * 
     * @since 1.0
     * @access private
     * @var object
     */
    private $shortcode_id = '';
    
    
    /**
    * construct function.
    * Processes the new widget
    *
    * @since  1.0.0
    * @access public
    * @return void
    */
    public function __construct() {
    
        parent::__construct(
                'rsfft_tweets_widget',               //Base ID
                'My Twitter Feed', 
                array(
                        'classname' => 'rsfft_tweets_widget_class',
                        'description' => 'Displays your Twitter feed',
                    )
                );
        
    }//ends function construct
    
    
    
    /*
     * Build the widgets settings form
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   array   $instance   Previously saved values from database 
     * @return void
     */
    public function form( $instance ) {
        
        $defaults = array(
            'title' => 'My Tweets',
            'feed_type' => 'user_timeline',
            'screen_name' => 'raycreations',
            'hashtags' => 'nature mountain',
            'search_string' => 'desert flower',
            'display_style' => 'display_list',
            'hide_media' => 'off',
            'count' => '4',
            'number_of_tweets_in_row' => '2',
            'exclude_replies' => 'on',
            'include_rts' => 'off'
        );
        
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = sanitize_text_field( $instance[ 'title' ] );
        $screen_name = sanitize_text_field( $instance[ 'screen_name' ] );
        $display_style = wp_strip_all_tags( $instance[ 'display_style' ] );
        $hide_media = wp_strip_all_tags( $instance[ 'hide_media' ] );
        $count = intval( $instance[ 'count' ] );
        $number_of_tweets_in_row = intval( $instance[ 'number_of_tweets_in_row' ] );
        $feed_type = sanitize_text_field( $instance[ 'feed_type' ] );
        $hashtags = sanitize_text_field( $instance[ 'hashtags' ] );
        $search_string = sanitize_text_field( $instance[ 'search_string' ] );
        $exclude_replies = wp_strip_all_tags( $instance[ 'exclude_replies' ] );
        $include_rts = wp_strip_all_tags( $instance[ 'include_rts' ] );
        
        ?>
            <p>
                Title: <input class="widefat" type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                              value="<?php echo esc_attr( $title ); ?>" >
            </p>
            <p>
                Feed Type:<br>
                <input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'feed_type' ) ); ?>" 
                       value="user_timeline" <?php checked( $feed_type, 'user_timeline' ); ?>> user_timeline </input><br>
                <input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'feed_type' ) ); ?>" 
                       value="mentions_timeline" <?php checked( $feed_type, 'mentions_timeline' ); ?>> mentions_timeline </input><br>
                <input type="radio" disabled name="<?php echo esc_attr( $this->get_field_name( 'feed_type' ) ); ?>"
                       value="hashtags_timeline" <?php checked( $feed_type, 'hashtags_timeline' ); ?>> hashtags_timeline </input><br>
                <input type="radio" disabled name="<?php echo esc_attr( $this->get_field_name( 'feed_type' ) ); ?>"
                       value="search_timeline" <?php checked( $feed_type, 'search_timeline'); ?>> search_timeline </input>
                
            </p>
            <p>
                Screen Name: <input class="widefat" type="text" name="<?php echo esc_attr( $this->get_field_name( 'screen_name' ) ); ?>" 
                                    value="<?php echo esc_attr( $screen_name ); ?>" >
            </p>
            <p>
                Hashtags: <input class="widefat" disabled type="text" name="<?php echo esc_attr( $this->get_field_name( 'hashtags' ) ); ?>"
                                 value="<?php echo esc_attr( $hashtags ); ?>" >
            </p>
            <p>
                Search Term: <input class="widefat" disabled type="text" name="<?php echo esc_attr( $this->get_field_name( 'search_string' ) ); ?>"
                                    value="<?php echo esc_attr( $search_string ); ?>" >
            </p>
            <p>
                Display Style:
                <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>" >
                    <option value="display_list" <?php selected( $display_style, 'display_list' ); ?>><?php echo "List" ?></option>
                    <option value="display_slider_1_col" <?php selected( $display_style, 'display_slider_1_col' ); ?>><?php echo "Slider 1 Column" ?></option>
                </select>
            </p>
            <p>
                Hide Media: <input type="checkbox" disabled name="<?php echo esc_attr( $this->get_field_name( 'hide_media' ) ); ?>"
                                        <?php checked( $hide_media, 'on' ); ?>>
            </p>
            <p>
                Number of Tweets: 
                <select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" >
                    <?php 
                    for ( $i=1; $i <= 20; $i++ ) { 
                    ?>
                        <option value="<?php echo $i ?>" <?php selected( $count, $i ); ?>><?php echo $i ?></option>
                        
                    <?php
                    } 
                    ?>
                    
                </select>
            </p>
            <p>
                Number of Tweets in row:
                <select class="widefat" disabled name="<?php echo esc_attr( $this->get_field_name( 'number_of_tweets_in_row' ) ); ?>">
                    <?php
                    for ( $i=1; $i <=5; $i++ ) {
                    ?>
                        <option value="<?php echo $i ?>" <?php selected( $number_of_tweets_in_row, $i ); ?>><?php echo $i ?></option>
                    <?php    
                    }
                    ?>
                </select>
            </p>
            <p>
                Exclude Replies: <input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'exclude_replies' ) ); ?>"
                                        <?php checked( $exclude_replies, 'on' ); ?>>
            </p>
            <p>
                Include Retweets: <input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'include_rts' ) ); ?>"
                                         <?php checked( $include_rts, 'on' ); ?>>
            </p>
        
        <?php
        
    }//ends function form
    
    
    
    /*
     * Save the options
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   array   $new_instance   Values just sent to be saved
     * @param   array   $old_instance   Previously saved values from database
     * 
     * @return  array   Updated safe values to be saved
     */
    public function update($new_instance, $old_instance) {
        
        /* Extra processing for hashtags */
        //$new_instance[ 'hashtags' ] = trim( strip_tags( stripslashes( $new_instance[ 'hashtags' ] ) ) );
        //$new_instance[ 'hashtags' ] = preg_replace( '/[^a-zA-Z0-9\s]/', '', $new_instance[ 'hashtags' ] );
        
        /* Extra processing for screen_name */
        //$new_instance[ 'screen_name' ] = trim( strip_tags( stripslashes( $new_instance[ 'screen_name' ] ) ) );
        //$new_instance[ 'screen_name' ] = preg_replace( '/[^a-zA-Z0-9_]/', '', $new_instance[ 'screen_name' ]);
        
        $instance = $old_instance;
        $instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
        $instance[ 'screen_name' ] = sanitize_text_field( $new_instance[ 'screen_name' ] );
        $instance[ 'display_style' ] = sanitize_text_field( $new_instance[ 'display_style' ] );
        $instance[ 'hide_media' ] = wp_strip_all_tags( $new_instance[ 'hide_media' ] );
        $instance[ 'count' ] = intval( $new_instance[ 'count' ] );
        $instance[ 'number_of_tweets_in_row' ] = intval( $new_instance[ 'number_of_tweets_in_row' ] );
        $instance[ 'feed_type' ] = sanitize_text_field( $new_instance[ 'feed_type' ] );
        $instance[ 'hashtags' ] = sanitize_text_field( $new_instance[ 'hashtags' ] );
        $instance[ 'search_string' ] = sanitize_text_field( $new_instance[ 'search_string' ] );
        $instance[ 'exclude_replies' ] = wp_strip_all_tags( $new_instance[ 'exclude_replies' ] );
        $instance[ 'include_rts' ] = wp_strip_all_tags( $new_instance[ 'include_rts' ] );
        
        /*
         * This widget has been updated. So we will clear its cache
         * so it can load fresh tweets as per the new options saved.
         */
        $rsfft_cache= new Rsfft_Cache();
        $rsfft_cache->rsfft_delete_tweets_transient( $this->shortcode_id );
        
        return $instance;
        
    }
    
    
    
    
    /*
     * Build the widgets settings form
     * 
     * 
     * @since 1.0
     * @access public
     * 
     * @param   array   $new_instance   Values just sent to be saved
     * @param   array   $old_instance   Previously saved values from database
     * 
     * @return  array   Updated safe values to be saved
     */
    public function widget($args, $instance) {
        
        /* get title from $instance */
        $title = apply_filters( 'widget_title', $instance[ 'title' ] );
        
        echo $args[ 'before_widget' ];
        
        if ( !empty( $title ) ) {
            echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
        }
        
        $shortcode = $this->rsfft_construct_shortcode_for_widget( $instance );
        echo do_shortcode( $shortcode );
        //echo $shortcode;
        
        echo $args[ 'after_widget' ];
        
    }//ends function widget
    
    
    
    /*
     * Constructs the shortcode according to the widget user settings
     * 
     * 
     * @since 1.0
     * @access private
     * 
     * @param   array   $new_instance   Saved user options from the database
     * 
     * @return  string  $shortcode      Constructed shortcode string
     */
    private function rsfft_construct_shortcode_for_widget( $instance ) {
        
        /* adding widget's id with the page id to make the shortcode_id unique on the page */
        global $post;
        $this->shortcode_id = $this->id . $post->ID;
        //$shortcode_id = $this->id . $post->ID;
        
        //load the widget settings
        $screen_name = !empty( $instance[ 'screen_name' ] ) ? wp_strip_all_tags( $instance[ 'screen_name' ] ) : 'raycreations';
        $display_style = !empty( $instance[ 'display_style' ] ) ? wp_strip_all_tags( $instance[ 'display_style' ] ) : 'display_list';
        $hide_media = !empty( $instance[ 'hide_media' ] ) ? wp_strip_all_tags( $instance[ 'hide_media' ] ) : '0';
        $count = !empty( $instance[ 'count' ] ) ? intval( $instance[ 'count' ] ) : '4' ;
        $number_of_tweets_in_row = !empty( $instance[ 'number_of_tweets_in_row' ] ) ? intval( $instance[ 'number_of_tweets_in_row' ] ) : '1';
        $feed_type = !empty( $instance[ 'feed_type' ] ) ? wp_strip_all_tags( $instance[ 'feed_type' ] ) : 'user_timeline';
        $hashtags = !empty( $instance[ 'hashtags' ] ) ? wp_strip_all_tags( $instance[ 'hashtags' ] ) : 'nature mountain';
        $search_string = !empty( $instance[ 'search_string' ] ) ? wp_strip_all_tags( $instance[ 'search_string' ] ) : 'desert flower';
        $exclude_replies = !empty( $instance[ 'exclude_replies' ] ) ? wp_strip_all_tags( $instance[ 'exclude_replies' ] ) : '1';
        $include_rts = !empty( $instance[ 'include_rts' ] ) ? wp_strip_all_tags( $instance[ 'include_rts' ] ) : '0';
        
        //$shortcode = '[my_custom_tweets id=' . $shortcode_id . ' feed_type="hashtags_timeline" count="4" hashtags="photography"]';
        
        $shortcode = '[my_custom_tweets id="' . $this->shortcode_id . '"';
        
        if ( $feed_type == 'hashtags_timeline' ) {
            $shortcode .= ' feed_type="hashtags_timeline" hashtags="' . $hashtags . '"';
        } else if ( $feed_type == 'search_timeline' ) {
            $shortcode .= ' feed_type="search_timeline" search_string="' . $search_string . '"';
        } else if ( $feed_type == 'user_timeline' ) {
            $shortcode .= ' feed_type="user_timeline" screen_name="' . $screen_name . '"';
        } else if ( $feed_type == 'mentions_timeline' ) {
            $shortcode .= ' feed_type="mentions_timeline"';
        }
        
        $shortcode .= ' display_style="' . $display_style . '"';
        $shortcode .= ' hide_media="' . $hide_media . '"';
        $shortcode .= ' count="' . $count . '"';
        $shortcode .= ' exclude_replies="' . $exclude_replies . '"';
        $shortcode .= ' include_rts="' . $include_rts . '"';
        $shortcode .= ' number_of_tweets_in_row="' . $number_of_tweets_in_row . '"';
        $shortcode .= ']';
        return $shortcode;
            
    }//ends function rsfft_construct_shortcode_for_widget


    
}//ends class

/* use widgets_init action hook to register our widgets register function */
add_action( 'widgets_init', 'rsfft_register_widgets' );

/* function to register the widget */
function rsfft_register_widgets() {
    register_widget( 'Rsfft_Widget' );
}

/* allow shortcode in widgets */
add_filter( 'widget_text', 'do_shortcode' );
