<?php

/**
 * Makes a simple curl request. Has some predefined defaults that are
 * annoying to remember. You can override them though, so no worries.
 *
 * @author Justin Fortier
 *
 * Usage:
 *  $resp = $curl = CurlRequest::create()
 *      ->setMethod( CurlRequest::GET )
 *      ->setUrl("http://api.logger.service.com/changelog/object/systemlog/1.xml")
 *      ->send( );
 */

namespace MultiCurl\Lib;

class CurlRequest
{
    const METHOD_GET = 'GET';
    const METHOD_PUT = 'PUT';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    const METHOD_DELETE = 'DELETE';
       
    protected $options = array(
        CURLOPT_AUTOREFERER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER=>false,              //mute header output
        CURLINFO_HEADER_OUT => true,        //more info from header
        CURLOPT_MAXREDIRS => 3,             //max redirects for follow location
        CURLOPT_RETURNTRANSFER => true,
    );
    
    protected $header_options = array(
        'Accept' => '*/*',
    );
    
    protected $ch = null;
    protected $debug;
    protected $server;
    protected $throw_errors;
    protected $url;
    
    public function __construct( $throw_errors = true, $debug = false )
    {
        $this->throw_errors = $throw_errors;
        $this->debug = $debug;
        
        //Override basic options
        if($debug)
        {
            $this->setOption( CURLOPT_RETURNTRANSFER, false);
        }
        
        return $this;
    }
    
    public function send( )
    {       
        $this->ch = curl_init();
        
        if($this->server)
        {   //server IP address was specified.. do some "magic"
            $this->alterHeader();
        }
                        
        curl_setopt_array( $this->ch, $this->options );
        curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $this->getHeaderOptions( true ) );
        
        $resp = curl_exec($this->ch);
        
        return new CurlResponse( $this->ch, $resp);
    }
       
    /**
     * Please use constants defined here:
     * http://ca3.php.net/manual/en/function.curl-setopt.php
     *
     * P.S. Stay away from integers for keys (CURLOPT constants are integers)
     */
    public function setOption( $key, $value )
    {
        $this->options[ $key ] = $value;
        return $this;
    }
    
    public function setHeaderOption( $key, $value )
    {
        $this->header_options[ $key ] = $value;
    }
    
    /**
     * Sets the Method of the Request
     */
    public function setMethod( $method )
    {
        $method = strtoupper($method);
        
        if( !in_array($method, self::getMethods()) ){
            throw new \Exception("No method by that name: $method" );
        }
        $this->options[ CURLOPT_CUSTOMREQUEST ] = $method;
        return $this;
    }
    
    /**
     * If set will make request directly to server and skip domain lookup.
     * Useful for internal services.
     */
    public function setServer( $ip )
    {
        if(!preg_match('!\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}!', $ip ))
        {
            throw new \Exception("Expected an IP address got: $ip" );
        }
        $this->server = $ip;
                
        return $this;
    }
    
    public function setUrl( $url )
    {
        if(preg_match( '!^(http(s)|(s)ftp|s3|file)://!i', $url) )
        {
            throw new \Exception("Unexpected protocol for url: $url" );
        }
        
        $this->options[ CURLOPT_URL ] = $url;
        $this->url = $url;
        
        return $this;
    }
    
    public function attachFile( $file_location )
    {
        if( !file_exists($file_location) )
        {
            throw new \Exception("No file at location: $file_location" );
        }
        
        $this->options[ CURLOPT_UPLOAD ] = true;
        $this->options[ CURLOPT_FILE ] = $file_location;
        
        return $this;
    }
    
    public function setBinary( $bool = false )
    {
        if( !is_bool( $bool )){
            throw new \Exception("Expected Boolean got '$bool' instead" );
        }
        
        $this->options[ CURLOPT_BINARYTRANSFER ] = $bool;
        return $this;
    }
    
    public function getOptions( )
    {
        return $this->options;
    }
    
    /**
     * For basic altering this returns an assoc array.
     * However, curl_setinfo() expects it to be a regular
     * array.. which is a bit annoying.
     */
    public function getHeaderOptions( $bind = false )
    {
        if( !$bind ) return $this->header_options;
        
        $a = array();
        foreach( $this->header_options as $key=>$value ){
            $a[] = $key . ': ' . $value;
        }
        return $a;
    }
    
    /**
     * Alter header performs a small optimization to use an
     * IP address so you don't have to do any DNS resolving. 
     * Should save some time.
     */
    public function alterHeader( )
    {
        if(!$this->server) return;
        
        $url_parts = parse_url( $this->url ); //bring back handy array
        
        //Is there a query string?
        $path = "";
        if( array_key_exists('query', $url_parts) )
        {
            $path = $url_parts['path'] . "?" . $url_parts['query'];
        }
                
        //Specify the host (name) we want to fetch...
        $this->setHeaderOption( "Host", $url_parts['host'] );
                
        //re-create the url with 
        $url = $url_parts['scheme']."://". $this->server . $url_parts['path'] ;
        $this->setUrl( $url );
    }
    
    /**
     * Returns an array of all avilable methods
     */
    public static function getMethods( )
    {
        return array(
            self::METHOD_GET,
            self::METHOD_PUT,
            self::METHOD_POST,
            self::METHOD_HEAD,
            self::METHOD_DELETE,
        );
    }
    
    /**
     * Nice and chainable
     */
    public static function create( $throw_errors = true, $debug = false )
    {
        return new self( $throw_errors, $debug );
    }
    
    public function __destroy()
    {
        curl_close( $this->ch );
    }
}
