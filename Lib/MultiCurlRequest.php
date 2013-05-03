<?php
/**
 * Make an asynchronus curl request. Much faster than waiting for each
 * response one by one. It's basically a stack of \CurlRequest objects
 * that is really easy to use, and multi-threaded. It also has a chainable
 * structure.
 *
 * Note: Setting the server IP address will skip DNS lookup.
 * 
 * @author Justin Fortier
 *
 * Exmaple Usage:
 *
 * $responses = MultiCurlRequest::create()
 *           ->open()
 *               ->setServer('127.0.0.1')
 *               ->setUrl("http://api.logger.service.com/changelog/object/systemlog/1.xml")
 *           ->close()
 *           ->open()
 *               ->setServer('127.0.0.1')
 *               ->setUrl("http://api.logger.service.com/changelog/object/systemlog/10.xml")
 *           ->close()
 *           ->open()
 *               ->setUrl("http://google.ca")
 *           ->close()
 *       ->send();
 *
 * After each open any commands that are not in this class get passed on to the
 * child class; in this instance CurlRequest object (see __call() method. So be
 * careful to not override child methods by accident.
 */

namespace MultiCurl\Lib;

class MultiCurlRequest
{
    protected $stack = array();
    protected $handles = array();
    protected $responses = array();
    protected $throw_errors = true;
    protected $debug = false;
    protected $number = 0;
    
    protected $open = false; 
    
    public function __construct( $throw_errors = true, $debug = false )
    {
        $this->throw_errors = $throw_errors;
        $this->debug = $debug;
    }

    public function open( )
    {
        if($this->open)
        {
            throw new Exception("Failed to close() after open() request.");
        }
        
        $this->open = true;
        $this->stack[ $this->number ] = new CurlRequest( $this->throw_errors, $this->debug );
        return $this;
    }
    
    public function close( )
    {
        $this->open = false;
        $this->number++;
        return $this;
    }

    public function send( $clear_stack = true )
    {
        if($this->open)
        {
            throw new Exception("Failed to close() after open() method.");
        }
        
        $this->mh = curl_multi_init();
        $this->responses = null;
        
        foreach( $this->stack as $k=>$request )
        {
            $this->handles[ $k ] = curl_init();
            $request->alterHeader();
            
            curl_setopt_array( $this->handles[ $k ], $request->getOptions() );
            curl_setopt( $this->handles[ $k ], CURLOPT_HTTPHEADER, $request->getHeaderOptions(true) );
                        
            curl_multi_add_handle( $this->mh, $this->handles[ $k ] ); //add to multi curl
        }
        
        $active = null;
        
        do
        {
            $mrc = curl_multi_exec($this->mh, $active);
        }
        while ($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while ($active && $mrc == CURLM_OK)
        {
            if (curl_multi_select($this->mh) != -1)
            {
                do
                {
                    $mrc = curl_multi_exec($this->mh, $active);
                }
                while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        foreach( $this->handles as $k=>$handle )
        {
            $this->responses[ $k ] = new CurlResponse( $handle, curl_multi_getcontent( $handle ) );
            curl_multi_remove_handle($this->mh, $handle);
        }
        
        curl_multi_close($this->mh);
        
        if($clear_stack)
        {
            $this->clear();
        }
        
        return $this->responses;
        
    }
    
    public function clear()
    {
        $this->stack = array();
        $this->handles = array();
        $this->number = 0;
    }
    
    /**
     * Nice and chainable
     */
    public static function create( $throw_errors = true, $debug = false )
    {
        return new self( $throw_errors, $debug );
    }
    
    /**
     * If open() the method is not recognized then try it on the current
     * child object in the stack
     */
    public function __call( $name, $args )
    {
        if($this->open)
        {
            call_user_func_array(array( $this->stack[ $this->number ] , $name), $args);
            return $this;
        }
        
        if($this->throw_errors)
        {
            throw new \Exception("No method '{$name}' with arguments: " . implode(",", $args ) );
        }
    }
}
