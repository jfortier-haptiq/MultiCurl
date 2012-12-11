<?php

/**
 * Takes a curl response and breaks it down into an easy to use object.
 * Nothing more, nothing less.
 *
 * @author Justin Fortier
 *
 * Turns this:
 *  curl_getinfo( $curl_handle, CURLINFO_CONTENT_TYPE );
 *  
 * into this...
 *  $response->getContentType();
 */

namespace MultiCurl\Lib;

class CurlResponse
{
    protected $ch;
    protected $header;
    protected $body;
    
    protected $url;
    protected $status_code;
    protected $content_type;
    protected $request_size;
    protected $request_time;
    protected $dns_lookup_time;
    
    public function __construct( $ch, $body )
    {
        $this->ch = $ch;
        $this->body = $body;
        
        if($this->ch === false){
            throw new \Exception( "Curl error: " . curl_error($ch) . " (#" . curl_errno($ch) . ")" );
        }
        
        $this->status_code = curl_getinfo( $this->ch, CURLINFO_HTTP_CODE );

        $this->url = curl_getinfo( $this->ch, CURLINFO_EFFECTIVE_URL );

        $this->content_type = curl_getinfo( $this->ch, CURLINFO_CONTENT_TYPE );
        $this->content_length = curl_getinfo( $this->ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD );
        $this->content_length_upload = curl_getinfo( $this->ch, CURLINFO_CONTENT_LENGTH_UPLOAD );
        
        $this->header = curl_getinfo( $this->ch, CURLINFO_HEADER_OUT );
        $this->request_size = curl_getinfo( $this->ch, CURLINFO_REQUEST_SIZE );
        $this->request_time = curl_getinfo( $this->ch, CURLINFO_TOTAL_TIME );
        $this->dns_lookup_time = curl_getinfo( $this->ch, CURLINFO_NAMELOOKUP_TIME) ;
    }

    public function getUrl( )
    {
        return $this->url;
    }
    
    public function getStatusCode( )
    {
        return (int) $this->status_code;
    }
    
    public function getBody( )
    {
        return $this->body;
    }
    
    public function getContentType( )
    {
        return $this->content_type;
    }
    
    public function getContentLength()
    {
        return $this->content_length;
    }
    
    public function getContentLentghUpload()
    {
        return $this->content_length_upload;
    }

    public function getHeader( )
    {
        return $this->header;
    }

    public function getRequestSize( )
    {
        return $this->request_size;
    }
    
    public function getRequestTime( )
    {
        return $this->request_time;
    }

    public function getDNSLookupTime( )
    {
        return $this->dns_lookup_time;
    }
    
}
