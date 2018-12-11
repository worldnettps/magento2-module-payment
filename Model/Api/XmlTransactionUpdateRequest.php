<?php
namespace WorldnetTPS\Payment\Model\Api;

class XmlTransactionUpdateRequest extends WorldnetTPSRequest
{
    private $uniqueRef;
    private $terminalId;
    private $operator;
    private $fromStatus;
    private $toStatus;
    private $authCode;
    private $dateTime;
    private $hash;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlTransactionUpdateResponse
     */
    protected $XmlTransactionUpdateResponse;



    public function __construct(XmlTransactionUpdateResponse $XmlTransactionUpdateResponse)
    {
        $this->XmlTransactionUpdateResponse = $XmlTransactionUpdateResponse;
    }

    public function initXmlTransactionUpdateRequest($uniqueRef, $terminalId, $operator, $fromStatus, $toStatus)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->uniqueRef = $uniqueRef;
        $this->terminalId = $terminalId;
        $this->operator = $operator;
        $this->fromStatus = $fromStatus;
        $this->toStatus = $toStatus;
    }
    /**
     *  Setter for UniqueRef

     *
     *  @param uniqueRef
     *  Unique Reference of transaction returned from gateway in authorisation response
     */
    public function SetUniqueRef($uniqueRef)
    {
        $this->uniqueRef = $uniqueRef;
    }
    /**
     *  Setter for authCode
     *
     *  @param authCode
     */
    public function SetAuthCode($authCode)
    {
        $this->authCode = $authCode;
    }

    /**


     *  Setter for hash value
     *
     *  @param sharedSecret
     *  Shared secret either supplied by WorldNetTPS or configured under
     *  Terminal Settings in the Merchant Selfcare System.
     */
    public function SetHash($sharedSecret)
    {
        $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->uniqueRef .':'. $this->operator .':'. $this->fromStatus .':'. $this->toStatus .':'. ($this->authCode?'A:':'') . $this->dateTime .':'. $sharedSecret);
    }
    /**
     *  Method to process transaction and return parsed response from the WorldNetTPS XML Gateway
     *
     *  @param sharedSecret
     *  Shared secret either supplied by WorldNetTPS or configured under
     *  Terminal Settings in the Merchant Selfcare System.
     *
     *  @param testAccount
     *  Boolean value defining Mode
     *  true - This is a test account
     *  false - Production mode, all transactions will be processed by Issuer.
     *
     *  @return XmlTransactionUpdateResponse containing an error or the parsed payment response.
     */

    public function ProcessRequestToGateway($sharedSecret, $serverUrl)
    {
        $this->SetHash($sharedSecret);
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlTransactionUpdateResponse->initXmlTransactionUpdateResponse($responseString);
        return $response;
    }

    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("TRANSACTIONUPDATE");
        $this->requestXML->appendChild($requestString);

        $node = $this->requestXML->createElement("UNIQUEREF");
        $node->appendChild($this->requestXML->createTextNode($this->uniqueRef));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));

        $node = $this->requestXML->createElement("OPERATOR");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->operator));

        $node = $this->requestXML->createElement("FROMSTATUS");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->fromStatus));

        $node = $this->requestXML->createElement("TOSTATUS");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->toStatus));

        if($this->authCode !== NULL)
        {
            $node = $this->requestXML->createElement("AUTHCODE");
            $requestString->appendChild($node);
            $nodeText = $this->requestXML->createTextNode($this->authCode);
            $node->appendChild($nodeText);
        }

        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));

        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));

        return $this->requestXML->saveXML();

    }
}

