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
class XmlSubscriptionRegResponse extends DataObject
{
    private $isError = false;
    public function IsError()
    {
        return $this->isError;
    }

    private $errorCode;
    public function ErrorCode()
    {
        return $this->errorCode;
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

    private $merchantRef;
    public function MerchantRef()
    {
        return $this->merchantRef;
    }

    private $hash;
    public function Hash()
    {
        return $this->hash;
    }

    public function __construct()
    {
    }

    public function initXmlSubscriptionRegResponse($responseXml)
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
                    $this->errorCode = $node->getElementsByTagName('ERRORCODE')->item(0)->nodeValue;
                    $this->errorString = $node->getElementsByTagName('ERRORSTRING')->item(0)->nodeValue;
                }
                $this->isError = true;
            }
            else if (strpos($responseXml, "ADDSUBSCRIPTIONRESPONSE") !== false)
            {
                $responseNodes = $this->doc->getElementsByTagName("ADDSUBSCRIPTIONRESPONSE");

                foreach( $responseNodes as $node )
                {
                    $this->merchantRef = $node->getElementsByTagName('MERCHANTREF')->item(0)->nodeValue;
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

