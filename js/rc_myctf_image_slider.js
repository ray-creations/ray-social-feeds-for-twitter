/* 
 * Creating slider from Tweets
 * 
 * 
 * @package My Custom Twitter Feed
 * @author Ray Creations
 */



jQuery(document).ready(function($){
    
    //alert(rc_myctf_slides_params.scode_div_id);
    //alert(rc_myctf_slides_args[0]);
    //alert(rc_myctf_total_sliders.total);
    
    var totalSliders = rc_myctf_total_sliders.total;
    
    for ( i = 0; i < totalSliders; i++ ) {      
        rc_myctf_slider( '#' + rc_myctf_slides_args[i], true, 8000 );
    }
    
    
    function rc_myctf_slider( element, auto = false, pause ) {
    

    //get parent div id of the shortcode
    var $this = $( element );

    //Slides container
    var SlidesCont = $this.children( '.slides-container' );

    //Get all slides
    var slides = SlidesCont.children( '.slide' );

    //Get pager div
    //var pager = $this.children( '.pager' );
    var pager = $this.children( '.pager-wrap' ).children( '.pager' );

    //Get previous next buttons
    var arrowsCont = $this.children( '.arrows' );
    var prevSlide = arrowsCont.children( '.prev' );
    var nextSlide = arrowsCont.children( '.next' );

    //Total slide count
    var slidesCount = slides.length;

    //Set currentSlide to first child
    var currentSlide = slides.first();
    var currentSlideIndex = 1;

    //Holds setInterval for autoplay, so we can reset it when user navigates
    var autoPlay = null;

    //Hide all slides except first and add active class to first
    slides.not( ':first' ).css('display', 'none');
    currentSlide.addClass('active');


    //Function responsible for fading to next slide
    function fadeNext() {
        currentSlide.removeClass( 'active' ).fadeOut( 700 );
        
        if ( currentSlideIndex == slidesCount ) {
            currentSlide = slides.first();
            currentSlide.delay( 700 ).addClass( 'active' ).fadeIn( 700 );
            currentSlideIndex = 1;
        } else {
            currentSlideIndex++;
            currentSlide = currentSlide.next();
            currentSlide.delay( 700 ).addClass( 'active' ).fadeIn( 700 );
            //currentSlide.delay( 500 ).addClass( 'active' ).animate({width: "toggle"}).fadeIn();
        }

        pager.text( currentSlideIndex + ' / ' + slidesCount );

    }//ends fadeNext

    //function responsibe for fadin to prevous slide
    function fadePrev() {
        currentSlide.removeClass( 'active' ).fadeOut( 700 );

        if ( currentSlideIndex == 1 ) {
            currentSlide = slides.last();
            currentSlide.delay( 700 ).addClass( 'active' ).fadeIn();
            currentSlideIndex = slidesCount;
        } else {
            currentSlideIndex--;
            currentSlide = currentSlide.prev();
            currentSlide.delay( 700 ).addClass( 'active' ).fadeIn( 700 );
        }

        pager.text( currentSlideIndex + ' / ' + slidesCount );

    }//ends fadePrev


    //resetting timer for autoplay when users clicks on the next/prev arrows
    function AutoPlay() {
        clearInterval( autoPlay );

        if ( auto == true ) {
            autoPlay = setInterval( function() { fadeNext() }, pause );
        }

    }//ends AutoPlay



    //Detect if user clicked on arrow for next slide
    $(nextSlide).click( function(e) {
        e.preventDefault();
        fadeNext();
        AutoPlay();
    } );

    //Detect if user clicked on arrow for prev slide
    $(prevSlide).click( function(e) {
        e.preventDefault();
        fadePrev();
        AutoPlay();
    });
    
    // Start autoplay if auto is set to true
    AutoPlay();
    
    }//ends function rc_myctf_slider

});//ends main function

