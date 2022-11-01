<?php

/**
 * Displays the nav arrows for Tweets in a slider
 * also display the pager section only for sliders
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

$nofollow_ext_links = Rsfft::$merged_scode_atts[ 'nofollow_ext_links' ];
$remove_ext_links = Rsfft::$merged_scode_atts[ 'remove_ext_links' ];

$nofollow = $nofollow_ext_links ? ' nofollow' : '';

/* Displaying the actual tweet */
$html .= "<div class='tweet'>";
    $html .= $text;
    
    /* 
     * Fetch the first url from the array if exists 
     * also, if nofollow external link is true, add that to the link
     * also, if remove external link is true, then show plain link
     */  
    if ( !empty( Rsfft::$external_url[0] ) && !$remove_ext_links ) {
        
        $html .= "<br><a href='" . esc_url( Rsfft::$external_url[0] ) . "' target='_blank' rel='noreferrer" . $nofollow . "'>" . esc_url( $tweet->entities->urls[0]->display_url ) . "</a>";
        
    } else if ( !empty( Rsfft::$external_url[0] ) && $remove_ext_links ) {
        
        $html .= "<br>" . esc_url( Rsfft::$external_url[0] );
        
    }//end if
    
$html .= "</div>";