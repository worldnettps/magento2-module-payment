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
class SecureCardRemovalResponse extends DataObject
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

    private $dateTime;
    public function DateTime()
    {
        return $this->dateTime;
    }

    private $hash;
    public function Hash()
    {
        return $this->hash;
    }

    public function __construct()
    {
    }

    public function initSecureCardRemovalResponse($responseXml)
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
            else if (strpos($responseXml, "SECURECARDREMOVALRESPONSE") !== false)
            {
                $responseNodes = $this->doc->getElementsByTagName("SECURECARDREMOVALRESPONSE");

                foreach( $responseNodes as $node )
                {
                    $this->dateTime = $node->getElementsByTagName('DATETIME')->item(0)->nodeValue;
                    $this->hash = $node->getElementsByTagName('HASH')->item(0)->nodeValue;
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

