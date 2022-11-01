/* 
 * Admin JS scripts
 * 
 * 
 * @package Ray Social Feeds For Twitter
 * @author Ray Creations
 */

jQuery(document).ready(function($){
    
    'use strict';
    
    // Add Color Picker to all inputs that have 'rsfft-color-fields' class
    $( '.rsfft-color-fields' ).wpColorPicker();
    
    
    /*
     * Ensure "Number of Tweets" is not less than 0
     * If so, change it back to 5
     */
    limitNumberOfTweets();
    $("#number_of_tweets").focusout(limitNumberOfTweets);
    
    function limitNumberOfTweets() {
        var noOfTweets = $("#number_of_tweets").val();
        if ( noOfTweets < 0 ) {
            $("#number_of_tweets").val(5);
        }
    }
    
    
    /*
     * Ensure entered value for "Number of Tweets in a Row" is between 1 to 5
     * Else revert the value to 3
     */
    $("#number_of_tweets_in_row").focusout(function(){
        var noOfTweetsInRow = $("#number_of_tweets_in_row").val();
        if ( noOfTweetsInRow  < 1 || noOfTweetsInRow > 5 ) {
            $("#number_of_tweets_in_row").val(3);
        }
    });
    
});//ends main function

