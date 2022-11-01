/* 
 * Limiting functions for the free version in widgets.
 * 
 * 
 * @package Ray Social Feeds For Twitter
 * @author Ray Creations
 * @version 1.0.0
 */

jQuery(document).ready(function($){
    
    'use strict';
    
    $("body").on('DOMSubtreeModified', ".wp-block-legacy-widget__edit-form", function() {
        
        //disable the 'display_slider_2_col' option in the select dropdown
        $("select option[value='display_slider_2_col']").attr('disabled', true);
        
        //ensure that "hide_media" option is selected and disabled.
        $( ".widget_hide_media" ).prop( 'disabled', true ).prop('checked', true);
        
    });
    
}); 