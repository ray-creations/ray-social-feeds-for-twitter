/* 
 * Limiting functions for the free version.
 * Disable elements for the free version below.
 * 
 * 
 * @package Ray Social Feeds For Twitter
 * @author Ray Creations
 * @version 1.0.0
 * 
 */

jQuery(document).ready(function($){
    
    'use strict';
    console.log('loaded rsfft-admin-limiter.js file...');
    /**
     * Limiting functions for the free version.
     * Disable elements for the free version below.
     * 
     * Ensure "Include Photos" has "(Available in Pro)" mentioned.
     * Ensure "Include Photos" is disabled.
     * 
     * Ensure "Include Videos" has "(Available in Pro)" mentioned.
     * Ensure "Include Videos" is disabled.
     * 
     * Ensure "Slider 2 Column" has "(Available in Pro)" mentioned.
     * Ensure "Slider 2 Column" is disabled.
     * 
     * Ensure "Hide all images/videos" is always CHECKED.
     * Ensure "Hide all images/videos" is disabled.
     * 
     * Ensure "Number of Tweets" textbox does not allow more than 10.
     * 
     */
    
    
    /*
     * Append "Available in Pro" to the include_photos label
     * @since 1.2.4
     */
    var includePhotosLbl = $("label[for='include_photos']");
    includePhotosLbl.html( includePhotosLbl.text() + "<span class='rsfft_tip'>(Available in Pro)</span>" );
    
    /*
     * Append "Available in Pro" to the include_videos label
     * @since 1.2.4
     */
    var includeVideosLbl = $("label[for='include_videos']");
    includeVideosLbl.html( includeVideosLbl.text() + "<span class='rsfft_tip'>(Available in Pro)</span>" );
    
    /*
     * Add the warning "Available in Pro" next to the #display_slider_2_col radio button
     * @since 1.2.4
     */
    $("#display_slider_2_col_pro_warning").text('(Available in Pro)');
    
    
    /*
     * make sure #include_photos & #include_videos checkboxes are unchecked.
     * @since 1.2.4
     */
    $( "#include_photos" ).prop( 'checked', false );
    $( "#include_videos" ).prop( 'checked', false );
    
    
    /*
     * If display_slider_2_col radio box is selected, switch the selection to "display_list"
     * @since 1.0
     */
    if ( $( "#display_slider_2_col" ).is( ':checked' ) ) {
        $( '#display_list' ).prop( 'checked', true );
    }
    
    
    //make sure hide_media is always checked.
    if ( !$( "#hide_media" ).is( ':checked' ) ) {
        $( '#hide_media' ).prop( 'checked', true );
    }
    
    
    //Limit "Number of Tweets" to 10
    limitNumberOfTweets();
    $("#number_of_tweets").focusout(limitNumberOfTweets);
    
    function limitNumberOfTweets() {
        var noOfTweets = $("#number_of_tweets").val();
        if ( noOfTweets > 10 ) {
            $("#number_of_tweets").val(10);
        }
    }
    
    
    /*
     * disable checkboxes & radio buttons with the class="rsfft-pro-feature" attribute
     * @since 1.2.4
     */
    $( '.rsfft-pro-feature' ).prop( 'disabled', true );

        
    
});//ends main function