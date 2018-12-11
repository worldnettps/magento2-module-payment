<?php
namespace WorldnetTPS\Payment\Model\Api;

class SecureCardRemovalRequest extends WorldnetTPSRequest
{
    private $merchantRef;
    private $cardReference;
    private $terminalId;
    private $dateTime;
    private $hash;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\SecureCardRemovalResponse
     */
    protected $SecureCardRemovalResponse;

    public function __construct(SecureCardRemovalResponse $SecureCardRemovalResponse)
    {
        $this->SecureCardRemovalResponse = $SecureCardRemovalResponse;
    }

    public function initSecureCardRemovalRequest($merchantRef, $cardReference, $terminalId)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->merchantRef = $merchantRef;
        $this->cardReference = $cardReference;
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
        $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->merchantRef .':'. $this->dateTime .':'. $this->cardReference .':'. $sharedSecret);
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
     *  @return SecureCardRemovalResponse containing an error or the parsed payment response.
     */

    public function ProcessRequestToGateway($sharedSecret, $serverUrl)
    {
        $this->SetHash($sharedSecret);
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->SecureCardRemovalResponse->initSecureCardRemovalResponse($responseString);
        return $response;
    }

    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("SECURECARDREMOVAL");
        $this->requestXML->appendChild($requestString);

        $node = $this->requestXML->createElement("MERCHANTREF");
        $node->appendChild($this->requestXML->createTextNode($this->merchantRef));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("CARDREFERENCE");
        $node->appendChild($this->requestXML->createTextNode($this->cardReference));
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

