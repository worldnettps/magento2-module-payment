<?php

namespace WorldnetTPS\Payment\Model\Api;

class XmlSecureCardRegRequest extends WorldnetTPSRequest
{
    private $merchantRef;
    private $terminalId;
    private $cardNumber;
    private $cardExpiry;
    private $cardHolderName;
    private $dateTime;
    private $hash;
    private $dontCheckSecurity;
    private $cvv;
    private $issueNo;


    public function __construct(XmlSecureCardRegResponse $XmlSecureCardRegResponse)
    {
        $this->XmlSecureCardRegResponse = $XmlSecureCardRegResponse;

        $this->dateTime = $this->GetFormattedDate();
    }

    /**
     *  Creates the SecureCard Registration/Update request for processing
     *  through the WorldNetTPS XML Gateway
     *
     * @param merchantRef A unique subscription identifier. Alpha numeric and max size 48 chars.
     * @param terminalId Terminal ID provided by WorldNetTPS
     * @param secureCardMerchantRef A valid, registered SecureCard Merchant Reference.
     * @param name Name of the subscription
     * @param description Card Holder Name
     */
    public function initXmlSecureCardRegRequest($merchantRef, $terminalId, $cardNumber, $cardExpiry, $cardType, $cardHolderName, $sharedSecret)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->merchantRef = $merchantRef;
        $this->terminalId = $terminalId;
        $this->cardNumber = $cardNumber;
        $this->cardExpiry = $cardExpiry;
        $this->cardType = $cardType;
        $this->cardHolderName = $cardHolderName;

        $this->SetHash($sharedSecret);
    }

    /**
     *  Setter for dontCheckSecurity setting
     *
     *  @param dontCheckSecurity can be either "Y" or "N".
     */
    public function SetDontCheckSecurity($dontCheckSecurity)
    {
        $this->dontCheckSecurity = $dontCheckSecurity;
    }

    /**
     *  Setter for Card Verification Value
     *
     *  @param cvv Numeric field with a max of 4 characters.
     */
    public function SetCvv($cvv)
    {
        $this->cvv = $cvv;
    }

    /**
     *  Setter for Issue No
     *
     *  @param issueNo Numeric field with a max of 3 characters.
     */
    public function SetIssueNo($issueNo)
    {
        $this->issueNo = $issueNo;
    }

    /**
     *  Setter for hash value
     *
     * @param sharedSecret
     *  Shared secret either supplied by WorldNetTPS or configured under
     *  Terminal Settings in the Merchant Selfcare System.
     */
    public function SetHash($sharedSecret)
    {
        $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->merchantRef .':'. $this->dateTime .':'. $this->cardNumber .':'. $this->cardExpiry .':'. $this->cardType .':'. $this->cardHolderName .':'. $sharedSecret);
    }

    
    public function ProcessRequestToGateway($serverUrl)
    {
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlSecureCardRegResponse->initXmlSecureCardRegResponse($responseString);
        return $response;
    }

    public function GenerateXml() {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;
    
        $requestString = $this->requestXML->createElement("SECURECARDREGISTRATION");
        $this->requestXML->appendChild($requestString);
    
        $node = $this->requestXML->createElement("MERCHANTREF");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->merchantRef));
    
        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));
    
        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));
    
        $node = $this->requestXML->createElement("CARDNUMBER");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->cardNumber));
    
        $node = $this->requestXML->createElement("CARDEXPIRY");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->cardExpiry));
    
        $node = $this->requestXML->createElement("CARDTYPE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->cardType));
    
        $node = $this->requestXML->createElement("CARDHOLDERNAME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->cardHolderName));
    
        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));
    
        if($this->dontCheckSecurity !== NULL)
        {
            $node = $this->requestXML->createElement("DONTCHECKSECURITY");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->dontCheckSecurity));
        }
    
        if($this->cvv !== NULL)
        {
            $node = $this->requestXML->createElement("CVV");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->cvv));
        }
    
        if($this->issueNo !== NULL)
        {
            $node = $this->requestXML->createElement("ISSUENO");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->issueNo));
        }
    
        return $this->requestXML->saveXML();
    }
}

