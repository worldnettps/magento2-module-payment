<?php
namespace WorldnetTPS\Payment\Model\Api;

use Magento\Framework\DataObject;

/**
 *  Holder class for parsed response. If there was an error there will be an error string
 *  otherwise all values will be populated with the parsed payment response values.
 *
 *  IsError should be checked before accessing any fields.
 *
 *  ErrorString will contain the error if one occurred.
 */
class XmlTerminalFeaturesResponse extends DataObject
{
    private $isError = false;
    public function IsError()
    {
        return $this->isError;
    }

    private $errorString;
    public function ErrorString()
    {
        return $this->errorString;
    }

    private $hash;
    public function Hash()
    {
        return $this->hash;
    }

    private $settings = array();
    public function getSettings()
    {
        return $this->settings;
    }

    public function __construct()
    {
    }

    public function getNodeTree($node) {
        $settings = array();

        foreach ($node->childNodes as $child) {
            if (!$this->hasChild($child)) {
                if (!isset($settings[$child->nodeName]))
                    $settings[$child->nodeName] = $child->nodeValue;
                else {
                    if(!is_array($settings[$child->nodeName])) {
                        $settings[$child->nodeName] = [$settings[$child->nodeName]];
                        array_push($settings[$child->nodeName], $child->nodeValue);
                    }
                    else
                        array_push($settings[$child->nodeName], $child->nodeValue);
                }
            }
            else {
                if (!isset($settings[$child->nodeName])) {
                    $settings[$child->nodeName] = array();

                    $settings[$child->nodeName] = $this->getNodeTree($child);
                } else {
                    if(!isset($settings[$child->nodeName][0]))
                        $settings[$child->nodeName] = [$settings[$child->nodeName]];

                    array_push($settings[$child->nodeName], $this->getNodeTree($child));
                }
            }
        }

        return $settings;
    }


    public function hasChild($p)
    {
        if ($p->hasChildNodes()) {
            foreach ($p->childNodes as $c) {
                if ($c->nodeType == XML_ELEMENT_NODE)
                    return true;
            }
        }
    }

    public function initXmlTerminalFeaturesResponse($responseXml)
    {

        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        $this->doc->loadXML($responseXml);
         try
         {
             if (strpos($responseXml, "ERROR") !== false)
             {
                 $responseNodes = $this->doc->getElementsByTagName("ERROR");
                 foreach( $responseNodes as $node )
                 {
                     $this->errorString = $node->getElementsByTagName('ERRORSTRING')->item(0)->nodeValue;
                 }
                 $this->isError = true;
             }
             else if (strpos($responseXml, "TERMINAL_CONFIGURATION_RESPONSE") !== false)
             {
                 $responseNodes = $this->doc->getElementsByTagName("TERMINAL_CONFIGURATION_RESPONSE");

                 foreach( $responseNodes as $node )
                 {
                     foreach($node->childNodes as $child) {
                         if(!$this->hasChild($child))
                             $this->settings[$child->nodeName] = $child->nodeValue;
                         else {
                             if(!isset($this->settings[$child->nodeName]))
                                 $this->settings[$child->nodeName] = array();

                             $this->settings[$child->nodeName] = $this->getNodeTree($child);
                         }
                     }
                 }
             }
             else
             {
                 throw new \Exception("Invalid Response");
             }
         }
         catch (\Exception $e)
         {
             $this->isError = true;
             $this->errorString = $e->getMessage();
         }

        return $this;
    }
}

