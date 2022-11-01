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


/* Displaying the actual tweet */
$html .= "<div class='tweet'>";
    $html .= nl2br( $text );
    
$html .= "</div>";