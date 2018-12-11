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
class XmlPreAuthResponse extends DataObject
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

    private $responseCode;
    public function ResponseCode()
    {
        return $this->responseCode;
    }

    private $responseText;
    public function ResponseText()
    {
        return $this->responseText;
    }

    private $approvalCode;
    public function ApprovalCode()
    {
        return $this->approvalCode;
    }

    private $dateTime;
    public function DateTime()
    {
        return $this->dateTime;
    }

    private $avsResponse;
    public function AvsResponse()
    {
        return $this->avsResponse;
    }

    private $cvvResponse;
    public function CvvResponse()
    {
        return $this->cvvResponse;
    }

    private $uniqueRef;
    public function UniqueRef()
    {
        return $this->uniqueRef;
    }

    private $hash;
    public function Hash()
    {
        return $this->hash;
    }

    public function __construct()
    {
    }

    public function initXmlPreAuthResponse($responseXml)
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
            else if (strpos($responseXml, "PREAUTHRESPONSE") !== false)
            {
                $responseNodes = $this->doc->getElementsByTagName("PREAUTHRESPONSE");

                foreach( $responseNodes as $node )
                {
                    $this->uniqueRef = $node->getElementsByTagName('UNIQUEREF')->item(0)->nodeValue;
                    $this->responseCode = $node->getElementsByTagName('RESPONSECODE')->item(0)->nodeValue;
                    $this->responseText = $node->getElementsByTagName('RESPONSETEXT')->item(0)->nodeValue;
                    $this->approvalCode = $node->getElementsByTagName('APPROVALCODE')->item(0)->nodeValue;
                    $this->dateTime = $node->getElementsByTagName('DATETIME')->item(0)->nodeValue;
                    $this->avsResponse = $node->getElementsByTagName('AVSRESPONSE')->item(0)->nodeValue;
                    $this->cvvResponse = $node->getElementsByTagName('CVVRESPONSE')->item(0)->nodeValue;
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

