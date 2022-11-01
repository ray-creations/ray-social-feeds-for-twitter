/* 
 * Admin JS scripts
 * 
 * 
 * @package Ray Social Feeds For Twitter
 * @author Ray Creations
 */

jQuery(document).ready(function($){

    // Add Color Picker to all inputs that have 'rc-myctf-color-fields' class
    $( '.rc-myctf-color-fields' ).wpColorPicker();
    
});//ends main function



jQuery(document).ready(function($){
    
    /**
     * Functions for the free version
     * Disable elements for the free version below
     */
    
    //if Masonry, or display_slider_2_col radio box is selected, switch the selection to "display_list"
    if ( $( "#display_masonry" ).is( ':checked' ) || $( "#display_slider_2_col" ).is( ':checked' ) ) {
        $( '#display_list' ).prop( 'checked', true );
    }
    
    //disable the unavailbale display_style
    $( '#display_masonry' ).prop( 'disabled', true );
    $( '#display_slider_2_col' ).prop( 'disabled', true );
    
    //make sure hide_media is always checked.
    if ( !$( "#hide_media" ).is( ':checked' ) ) {
        $( '#hide_media' ).prop( 'checked', true );
    }
    //disable hide_media button
    $( '.hide_media_chk' ).prop( 'disabled', true );
    
    //Handle Feed Type radio button options
    if ( $( '#hashtags_timeline' ).is( ':checked' ) || $( "#search_timeline" ).is( ':checked' ) ) {
        $( '#user_timeline' ).prop( 'checked', true );
    }
    //disable unavailable options
    $( '#hashtags_timeline' ).prop( 'disabled', true );
    $( '#search_timeline' ).prop( 'disabled', true );
    
    
    //Handle Links Settings Section (check boxes)
    if ( $( '#remove_links_hashtags' ).is( ':checked' ) ) {
        $( '#remove_links_hashtags' ).prop( 'checked', false );
    }
    if ( $( '#remove_links_mentions' ).is( ':checked' ) ) {
        $( '#remove_links_mentions' ).prop( 'checked', false );
    }
    if ( $( '#remove_ext_links' ).is( ':checked' ) ) {
        $( '#remove_ext_links' ).prop( 'checked', false );
    }
    //disable links sections checkboxes for fee version
    $( '.remove_links_hashtags_chk' ).prop( 'disabled', true );
    $( '.remove_links_mentions_chk' ).prop( 'disabled', true );
    $( '.remove_ext_links_chk' ).prop( 'disabled', true );
    
    
    //disable Hashtags & Search String text field
    $( '.rc-myctf-pro-feature' ).attr( 'disabled', true );
    
    /**
     * for the widget section
     */
    
    
});//ends main function