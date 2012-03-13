<?php

namespace MRX\ServiceTalkBundle\Entity;

use Symfony\Component\HttpFoundation\Request;

class ObjectServices{
    
    protected $decoder;
    
    /**
     * Dependcy Injection for the Win
     */
    public function __construct( $decoder )
    {
        $this->decoder = $decoder;
    }
    
    /**
     * Basic Hydrate Method
     *
     * Takes an object and hydrates it using an associative array.
     *
     * @param $object - Object you want to hydrate
     * @param $array - associative array
     * 
     * @return $object
     * @author Justin Fortier
     *
     * P.S. Very dangerous method to change!
     *
     * Example:
     *  $this->get('object')->hydrate( new Object(), array('type'=>'new') );
     *
     *  This method will detect if there is a "setType" method for the
     *  object and will set it: $object->setType( $type );
     * 
     */
    public function hydrate( $object, Array $array )
    {
        foreach( $array as $field=>$value)
        {
            //object_type --> ObjectType
            if( strstr($field, '_') )
            {
                $parts = explode("_", $field);
                $field = '';
                foreach( $parts as $part )
                {
                    $field .= ucfirst($part);
                }
            }
            
            $setter = 'set' . ucfirst($field);
            if(method_exists( $object, $setter ))
            {
                $object->$setter($value);
            }
        }
        return $object;
    }
    
    /**
     * Takes a request and decodes/deserializes the input
     * from XML, or JSON and converts it to an Array.
     * Usually useful for taking the array, and converting
     * to an Object.
     *
     * @return Array
     * @author Justin Fortier
     */
    public function decodeRequest( Request $request, $format =  null, $content = null )
    {
        if( !$format )
            $format = $request->getRequestFormat();
        
        if( !$content )
            $content = $request->getContent();
        
        if($format === 'json')
        {
            $array = json_decode( $content, true );
        }elseif( $format === 'xml' )
        {
            $array = $this->decoder->decode($content);
        }
        
        if(!is_array($array))
            throw new \Exception("Could not parse data as " . $format . " to array");
        
        return $array;
    }
}
