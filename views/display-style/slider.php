<?php

/**
 * View for tweets when display_style is set to either
 * slider 2 col or slider 2 col.
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


/* Outer opening 2 div wraps for tweets (outer & container wraps)  */
$html = Rsfft::rsfft_get_tweet_opening_div_wraps_html( $display_style );


foreach( $tweets as $i => $tweet ){
    
    //reset Rsfft::$tweet_details when looping through a new tweet
    Rsfft::$tweet_details = array();
    
    /*
     * checks if the tweet is a retweet. If no, it sets the original tweet object  
     * in Rsfft::$tweet_details[ 'tweet' ] array element.
     * If yes, then saves $tweet->retweeted_status in Rsfft::$tweet_details[ 'tweet' ] array element.
     */
    Rsfft::rsfft_check_if_retweet($tweet);
    
    $text = Rsfft::rsfft_get_displayable_tweet( Rsfft::$tweet_details[ 'tweet' ] );             //displayable tweet
    $media_html = Rsfft::rsfft_get_media_display_html( Rsfft::$tweet_details[ 'tweet' ] );      //media for tweets
    $tweet_header = Rsfft::rsfft_get_tweet_header( Rsfft::$tweet_details[ 'tweet' ] );        //formatted tweet header part
    $tweet_footer = Rsfft::rsfft_get_tweet_footer( Rsfft::$tweet_details[ 'tweet' ] );          //formatted tweet footer part
    
    $display_tweet_border = Rsfft::$merged_scode_atts[ 'display_tweet_border' ];
    $border_type = Rsfft::$merged_scode_atts[ 'border_type' ];
    $display_header = Rsfft::$merged_scode_atts[ 'display_header' ];
    $display_footer = Rsfft::$merged_scode_atts[ 'display_footer' ];
    $display_style = Rsfft::$merged_scode_atts[ 'display_style' ];
    
    //controls the display of border and border_type
    $tweet_border_css = '';
    if ( $display_tweet_border ) {
        $tweet_border_css = $border_type == 'shadow' ? '' : 'tweet_border_type_line';
    } else {
        $tweet_border_css = 'tweet_border_none';
    }
    
    //display_slider_2_col and no image, making sure the width of the .tweet_wrapper extends 100%
    $tweet_wrapper_css = !$media_html && $display_style == 'display_slider_2_col' ? 'tweet_wrapper tweet_wrapper_full' : 'tweet_wrapper';
    
    /* tweet list items starts from here it has a class of "tweet_item" */
    //$html .= '<div class="slide tweet_item" style="' . $display_tweet_border_css . '">';
    $html .= '<div class="slide tweet_item ' . $tweet_border_css . '">';

        /*
         * Meida is being displayed before tweet only for sliders. This is only when
         * $display_style == 'display_slider_1_col' || $display_style == 'display_slider_2_col'
         */
         if ( $media_html !== FALSE ) {
            $html .= $media_html;
         }

        /* Wraps both the header and tweet */
        $html .= "<div class='" . $tweet_wrapper_css . "'>";
            if ( $display_header ) { $html .= $tweet_header; }                  //Display header
            require( RSFFT_DIR . 'views/tweet-with-extn-link.php' );         //Display the actual tweet
            
            //display tweet footer
            if ( $display_footer ) { $html .= $tweet_footer; }
            
        $html .= "</div>"; //ends tweet_wrapper
          
    $html .= "</div>"; //class .tweet_item ends here

}//ends foreach

$html .= '</div>'; //ends tweet container wrap

$html .= '</div>'; //ends tweet outer wrap
