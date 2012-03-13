Note: please this bundle depends on the FOSRestBundle (which that relies on JMSSerializationBundle).
So please install those if you haven't already

1. First copy deps.dist to just 'deps'.

2. Modify the following to point use your git username:
        [service-talk-bundle]
            git=https://mygitusername@github.com/mrx-devs/ServiceTalkBundle.git
            target=/bundles/MRX/ServiceTalkBundle
    
3. Run 'php bin/vendors install' this will download the bundle

4. Edit /app/AppKernel.php and add the following to the bundles array:
        'new MRX\ServiceLocatorBundle\MRXServiceLocatorBundle()',

5. Edit ./app/autoload.php and enter this into the registerNamespaces array
        'MRX'              => __DIR__.'/../vendor/bundles',

6. Now make sure that your system is configured correctly. Open up ./app/config/config.yml

    services:
        curl:
            class:      MRX\ServiceTalkBundle\Curl\MultiCurlRequest
            arguments:  [true, false]
            
        decoder:
            class:      FOS\RestBundle\Decoder\XmlDecoder
            arguments:  [ ]
    
        object:
            class:      MRX\ServiceTalkBundle\Entity\ObjectServices
            arguments:  [ @decoder ]
    

