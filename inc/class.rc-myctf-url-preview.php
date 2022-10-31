<?php

/**
 * Generates URL preview from URL
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


class Rc_Myctf_Url_Preview {
     
    
    /*
    * Holds the DOM of the web page.
    *
    * @since  1.0.0
    * @access private
    * @var    object
    */
    private $dom = "";
    
    
    /*
    * Holds the elements of the website
    *
    * @since  1.0.0
    * @access private
    * @var    array
    */
    public $websiteDetails = array();
    
    
    /*
    * Holds the images on the website
    *
    * @since  1.0.0
    * @access private
    * @var    array
    */
    private $imageArr = array();
    
    
    /*
     * Constructor Function
     * 
     * @since 1.0
     * @access public
     * @return void
     */
    public function __construct($url) {
        $this->initializeDom($url);
    }
    
    
    
    /*
     * Fetches the DOM of the webpage of the given url
     * and fills the $dom variable with this data
     * 
     * @since 1.0
     * @access private
     * @return void
     */
    private function initializeDom( $RawUrl ){
        
        /* sanitizing the URL received as input */
        $url = esc_url( $RawUrl );
        
        if( $url != "" ){
            
            $response = wp_safe_remote_get( $url );
            if ( is_wp_error( $response ) ) {
                
                $this->websiteDetails[ "Error" ] = $response->get_error_message();
                //echo 'This is the error message: ' . $response->get_error_message() . '<br>';
                //$this->websiteDetails[ "Url" ] = $url;
                return false;
                
            } else {
                
                $response = $response[ 'body' ];
                $this->dom = new DOMDocument();
                @$this->dom->loadHTML( $response );
                $this->websiteDetails[ "Url" ] = $url;
                
            }
            
        }
        else
        {
            //echo "Url is empty";
            return FALSE;
        }
    }
    
    
    
    
    /*
     * Stores all the fetched webpage info in array
     * 
     * @since 1.0
     * @access public
     * @return void
     */
    public function listWebsiteDetails(){
        
        /* If there was an error and $dom was not set, then set all variables as empty */
        if ( empty( $this->dom ) ) {
            $this->websiteDetails[ "Title" ] = '';
            $this->websiteDetails[ "Description" ] = '';
            //$this->websiteDetails[ "Keywords" ] = '';
            $this->websiteDetails[ "OgImage" ] = '';
            $this->websiteDetails[ "Image" ] = '';
            $this->websiteDetails[ "LargestImgDetails" ] = '';
        } else {
            $this->websiteDetails[ "Title" ] = sanitize_text_field( $this->rc_myctf_get_website_title() );
            $this->websiteDetails[ "Description" ] = sanitize_text_field( $this->rc_myctf_get_website_description() );
            //$this->websiteDetails[ "Keywords" ] = sanitize_text_field( $this->rc_myctf_get_website_keywords() );
            $this->websiteDetails[ "OgImage" ] = esc_url( $this->rc_myctf_get_website_og_image() );
            $this->websiteDetails[ "Image" ] = $this->rc_myctf_get_website_images();                //this is an array of iamges
            $this->websiteDetails[ "LargestImgDetails" ] = $this->rc_myctf_fetch_largest_image();
            //return json_encode($this->websiteDetails);
            
            /* store this value in transient so that it can be fetched using cache */
            set_transient( $this->websiteDetails[ "Url" ], $this->websiteDetails, 60*60*24 );
        
        }
        
        return $this->websiteDetails;
    }
    
    
    /*
     * Fetches title from dom and returns it
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public function rc_myctf_get_website_title(){
        
        /* Check if "title" exists in object "dom" */
        
            $titleNode = $this->dom->getElementsByTagName( "title" );
            $titleValue = isset( $titleNode->item(0)->nodeValue ) ? sanitize_text_field( $titleNode->item(0)->nodeValue ) : "";
            
            /* shorten the title to under 100 characters */
            if ( !empty( $titleValue ) ) {
                $title = sanitize_text_field( $this->rc_myctf_fix_title_desc_length( $titleValue, $type='title' ) );
                return $title;
            }
            
            return '';
            
    }//ends rc_myctf_get_website_title
    
    
    /*
     * Fetches the Description from dom and returns it
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public function rc_myctf_get_website_description(){
        $descriptionNode = $this->dom->getElementsByTagName( "meta" );
        for ($i=0; $i < $descriptionNode->length; $i++) {
             $descriptionItem = $descriptionNode->item($i);
             if( $descriptionItem->getAttribute( 'name' ) == "description" ){
                 
                /* we have found the description */
                //return $descriptionItem->getAttribute( 'content' );
                $descValue = sanitize_text_field( $descriptionItem->getAttribute( 'content' ) );
                 
                /* shorten the title to under 100 characters */
                if ( !empty( $descValue ) ) {
                    $desc = sanitize_text_field( $this->rc_myctf_fix_title_desc_length( $descValue, $type='desc' ) );
                    return $desc;
                } else {
                    return '';
                }
                 
             }
        }
    }
    
    
    /*
     * Fetches the Keyword from dom and returns it
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public function rc_myctf_get_website_keywords(){
        $keywordNode = $this->dom->getElementsByTagName( "meta" );
        for ( $i=0; $i < $keywordNode->length; $i++ ) {
             $keywordItem = $keywordNode->item( $i );
             if( $keywordItem->getAttribute( 'name' ) == "keywords" ){
                return $keywordItem->getAttribute( 'content' );
             }
        }
    }
    
    
    /*
     * Fetches the OgImage from dom and returns it
     * 
     * @since 1.0
     * @access public
     * @return string
     */
    public function rc_myctf_get_website_og_image(){
        $descriptionNode = $this->dom->getElementsByTagName( "meta" );
        for ( $i=0; $i < $descriptionNode->length; $i++ ) {
             $descriptionItem = $descriptionNode->item( $i );
             if( $descriptionItem->getAttribute( 'property' ) == "og:image" ){
                //return $descriptionItem->getAttribute( 'content' );
                $OgImg = $descriptionItem->getAttribute( 'content' );
                $ObImgAbs = $this->rc_myctf_convert_relative_to_absolute_url( $OgImg );
                return $ObImgAbs;
             }
        }
    }
    
    
    
    /*
     * Fetches the images from dom and returns it in an array
     * 
     * @since 1.0
     * @access public
     * @return array
     */
    public function rc_myctf_get_website_images(){
        
        /*
         * if already have found OgImage for this URL no need to go further.
         * return empty string. 
         */
        if ( !empty( $this->websiteDetails[ "OgImage" ] ) ) {
            return '';
        }
        
        
        $imageNode = $this->dom->getElementsByTagName( "img" );
        
        if ( $imageNode->length !== 0 ) {
            
            for ( $i=0; $i < $imageNode->length; $i++ ) {
             $imageItem = $imageNode->item( $i );
                $this->imageArr[] = array(
                    'src' => $imageItem->getAttribute( 'src' ),
                    'width' => $imageItem->getAttribute( 'width' )
                );
            }
            
        }
        
        return $this->imageArr;
    }
    
    
    
    /*
     * Fetches the largest image src and width attribute and stores in array
     * 
     * @since 1.0
     * @access public
     * @return array
     */
    public function rc_myctf_fetch_largest_image() {
        
        /* 
         * if already have found OgImage for this URL no need to go further.
         * return empty string. 
         */
        if ( !empty( $this->websiteDetails[ "OgImage" ] ) ) {
            return '';
        }
        
        //Declaring variables
        $largest_image_src = '';
        $largest_image_width = 0;
        
        /* Loop through the images to find the largest width image */
        foreach ( $this->imageArr as $image ) {
            
            //Check for the largest image size
            if ( $image[ 'width' ] > $largest_image_width ) {
                $largest_image_width = intval( $image[ 'width' ] );
                $largest_image_src = esc_url( $image[ 'src' ] );
            }
        }
        
        /* check if image url is relative, if so, change it to absolute */
        if ( empty( $largest_image_src ) ) {
            return '';
        }
        
        $largest_abs_img_url = $this->rc_myctf_convert_relative_to_absolute_url( $largest_image_src );
        
        if ( $largest_abs_img_url === FALSE ) {
            return '';
        }
        
        $largestImageDetails = array(
            'width' => $largest_image_width,
            'src' => $largest_abs_img_url
        );

        return $largestImageDetails;
    }
    
    
    /*
     * Ensures that the Title is less than 100 characters.
     * And Desc is less than 150 characters.
     * 
     * @since 1.0
     * @access private
     * 
     * @param   $str    string      String whose length needs to be fixed
     * @param   $type   string      Either 'title' or 'desc'
     * 
     * @return  $str_fixed  string
     */
    private function rc_myctf_fix_title_desc_length( $string, $type) {
        
        /* check the current string length */
        $str_length = strlen( $string );
        
        /* check if the string is a title or desc */
        if ( $type == 'title' && $str_length > 60 ) {
            
            /*
             * checking for first space ' ' after 60 characters 
             * this ensures that words are not truncated. 
             */
            $pos = strpos( $string, ' ', 60 );
            $string = substr($string, 0, $pos );
            $string .= '...';
            
        }else if ( $type == 'desc' && $str_length > 120 ) {
            
            $pos = strpos( $string, ' ', 120 );
            $string = substr( $string, 0, $pos );
            $string .= '...';
            
        }
        
        return $string;
        
    }
    
    
    /* 
     * Checks if the largest image URL is relative.
     * If yes, converts it to absolute.
     * 
     * @since 1.0
     * @access private
     * 
     * @param   string  $img_to_check        imge url to check
     * @return  string  $abs_img_url    absolute image url
     * 
     */
    private function rc_myctf_convert_relative_to_absolute_url( $img_to_check ) {
        
        /* $img_to_check is empty, return */
        if ( empty( $img_to_check ) ) {
            return FALSE;
        }
        
        /*
         * check if $img_to_check is relative
         * if not, return the $img_to_check itself, as it is already absolute
         */
        $img_to_check_parsed = wp_parse_url( $img_to_check );
        if ( !empty( $img_to_check_parsed[ 'host' ] ) ) {
            return $img_to_check;
        }
        
        /*
         * So image is relative. 
         * check if $img_to_check starts with '/'. If not, prefix one. 
         */
        if ( $img_to_check[0] !== '/' ) {
            $img_to_check = '/' . $img_to_check;
        }
        
        /*
         * Get the $external_url from which this $img_to_check has been fetched.
         * $img_to_check is fetched from the $external_url only
         */
        $external_url = $this->websiteDetails[ "Url" ];
        
        /*
         * Get the $external_domain that needs to be appended to a relative url to make it absolute
         * remember that $external_domain will be without a trailing forward slash
         */
        $external_parsed_url = wp_parse_url( $external_url );
        $external_domain = $external_parsed_url[ 'scheme' ] . '://' . $external_parsed_url[ 'host' ];
        
        /* Finally create the absolute url for the $img_to_check fetched from this $external_url */
        $abs_img_url = $external_domain . $img_to_check;
        
        return $abs_img_url;
        
    }//ends rc_myctf_convert_relative_to_absolute_url
    
    
    
} // Ends Class