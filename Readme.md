#MultiCurl

Allows you to interact with Curl like an object, great for interacting with REST APIs! Free to use under MIT License.

Example:

    namespace MultiCurl;
    
    include 'Lib/CurlRequest.php';
    include 'Lib/CurlResponse.php';
    include 'Lib/MultiCurlRequest.php';
    
    use MultiCurl\Lib\CurlRequest;
    use MultiCurl\Lib\MultiCurlRequest;
    
    $responses = MultiCurlRequest::create()
            ->open()
                ->setUrl("http://google.ca")
                ->setMethod( CurlRequest::METHOD_GET )
            ->close()
            ->open()
                ->setUrl("http://yahoo.ca")
                ->setMethod( CurlRequest::METHOD_GET )
            ->close()
        ->send();
    Responses come back inside objects as well.
    
    foreach( $responses as $x=>$response )
    {
        print "Response: {$x}<br />";
        print "====================<br />";
        print $response->getBody();
        print "====================<br />";
    }
