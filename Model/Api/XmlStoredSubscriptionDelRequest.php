<?php
namespace WorldnetTPS\Payment\Model\Api;

class XmlStoredSubscriptionDelRequest extends WorldnetTPSRequest
{
    private $merchantRef;
    private $terminalId;
    private $dateTime;
    private $hash;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlStoredSubscriptionDelResponse
     */
    protected $XmlStoredSubscriptionDelResponse;

    public function __construct(XmlStoredSubscriptionDelResponse $XmlStoredSubscriptionDelResponse)
    {
        $this->XmlStoredSubscriptionDelResponse = $XmlStoredSubscriptionDelResponse;
    }

    public function initXmlStoredSubscriptionDelRequest($merchantRef, $terminalId)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->merchantRef = $merchantRef;
        $this->terminalId = $terminalId;
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
        $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->merchantRef .':'. $this->dateTime .':'. $sharedSecret);
    }


    public function ProcessRequestToGateway($sharedSecret, $serverUrl)
    {
        $this->SetHash($sharedSecret);
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlStoredSubscriptionDelResponse->initXmlStoredSubscriptionDelResponse($responseString);
        return $response;
    }

    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("DELETESTOREDSUBSCRIPTION");
        $this->requestXML->appendChild($requestString);

        $node = $this->requestXML->createElement("MERCHANTREF");
        $node->appendChild($this->requestXML->createTextNode($this->merchantRef));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));

        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));

        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));

        return $this->requestXML->saveXML();

    }
}

