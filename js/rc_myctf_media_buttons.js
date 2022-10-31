/* 
 * Pause & Play buttons for Twitter videos
 * 
 * 
 * @package My Custom Twitter Feed
 * @author Ray Creations
 */

jQuery(document).ready(function($) {
        
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
});

