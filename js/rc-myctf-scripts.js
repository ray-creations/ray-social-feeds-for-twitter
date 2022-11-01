/* 
 * Various JS scripts needed on the frontend of the site
 * Pause & Play buttons for Twitter videos
 * 
 * 
 * @package My Custom Twitter Feed
 * @author Ray Creations
 */



jQuery(document).ready(function($){
    
    /**
     *  The code below handles the media pause/play buttons
     *  for videos for the native HTML5 video player
     */
    $('video.tweet-video').click(function(event) {
        
        var id = event.target.id;
        var myVideo = document.getElementById( id );
              
        playPause( myVideo );
        
        return false;
    });
    
    function playPause( myVideo ) {
        
        if (myVideo.paused) {
            myVideo.play();
        } else {
            myVideo.pause();
        }       
    }
    
    //concludes the above section
    
    
    
    
    /**
     *  Code to initialize Masonry 
     *  for the plugin
     */
    $('.display_masonry .tweets_container').imagesLoaded( function() {
        
        $('.display_masonry .tweets_container').masonry({
            // options
            itemSelector: '.display_masonry .tweet_item',
            //columnWidth: '.tweet_item',
            gutter: 28,
            percentPosition: true
        });
        
    });
    
    //concludes the above section
    
});//ends main function


jQuery(document).ready(function($){
    
    //get the total number of sliders
    var totalSliders = rc_myctf_total_owl_sliders.total;
    //alert ( 'total sliders: ' + totalSliders );
    
    for ( i = 1; i <= totalSliders; i++ ) {
        
        options = eval( "rc_myctf_owl_options_" + i );
        
        //var nav = parseInt( options['nav'] );
        //alert( nav );
        
        /**
        * Extract the options for OWL slider
        */

       var items = parseInt( options.items );
       var autoHeight = parseInt( options.autoHeight );
       var dots = parseInt( options.dots );
       var nav = parseInt( options.nav );
       var autoplay = parseInt( options.autoplay );
       var autoplayTimeout = parseInt( options.autoplayTimeout );
       var autoplayHoverPause = parseInt( options.autoplayHoverPause );
       var loop = parseInt( options.loop );
       var autoplaySpeed = parseInt( options.autoplaySpeed );
       var slider_id = options.slider_id;


       /**
        * Initialize the owl carousel
        */
       $( '.' + slider_id ).owlCarousel({
           items: items,
           autoHeight: autoHeight,
           dots: dots,
           nav: nav,
           autoplay: autoplay,
           autoplayTimeout: autoplayTimeout,
           autoplayHoverPause: autoplayHoverPause,
           loop: loop,
           autoplaySpeed: autoplaySpeed
       });
       
       
    } //ends for loop

    
});//ends main function