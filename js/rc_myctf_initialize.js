/* 
 * Used to initialize Js & functions
 * 
 * 
 * @package My Custom Twitter Feed
 * @author Ray Creations
 */

jQuery(document).ready(function($) {
    
    $('.display_masonry .tweets_container').imagesLoaded( function() {
        
        $('.display_masonry .tweets_container').masonry({
            // options
            itemSelector: '.display_masonry .tweet_item',
            //columnWidth: '.tweet_item',
            gutter: 29,
            percentPosition: true
        });
        
    });
    
});