<?php

include 'Lib/CurlRequest.php';
include 'Lib/CurlResponse.php';
include 'Lib/MultiCurlRequest.php';

use MultiCurl\Lib\CurlRequest;
use MultiCurl\Lib\MultiCurlRequest;

$responses = MultiCurlRequest::create()
        ->open()
            ->setUrl("http://google.ca")
            //Can be GET, PUT, POST, HEAD, DELETE
            ->setMethod( CurlRequest::METHOD_GET ) 
        ->close()
        ->open()
            ->setUrl("http://yahoo.ca")
            ->setMethod( CurlRequest::METHOD_GET )
        ->close()
    ->send();
    
//Responses is an array of response objects for MultiCurl
//Same order in / Same order out
foreach( $responses as $x=>$response )
{
    print "Response: {$x}<br />";
    print "====================<br />";
    print_r( $response );
    print "====================<br />";
}
