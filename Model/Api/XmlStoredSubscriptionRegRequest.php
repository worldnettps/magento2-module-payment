<?php

namespace WorldnetTPS\Payment\Model\Api;

class XmlStoredSubscriptionRegRequest extends WorldnetTPSRequest
{
    private $merchantRef;
    private $terminalId;
    private $name;
    private $description;
    private $periodType;
    private $length;
    private $recurringAmount;
    private $initialAmount;
    private $type;
    private $onUpdate;
    private $onDelete;
    private $dateTime;
    private $hash;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlStoredSubscriptionRegResponse
     */
    protected $XmlStoredSubscriptionRegResponse;


    public function __construct(XmlStoredSubscriptionRegResponse $XmlStoredSubscriptionRegResponse)
    {
        $this->XmlStoredSubscriptionRegResponse = $XmlStoredSubscriptionRegResponse;

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
    public function initXmlStoredSubscriptionRegRequest($merchantRef, $terminalId, $name, $description, $periodType, $length, $currency, $recurringAmount, $initialAmount, $type, $onUpdate, $onDelete, $sharedSecret)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->merchantRef = $merchantRef;
        $this->terminalId = $terminalId;

        $this->name = $name;
        $this->description = $description;
        $this->periodType = $periodType;
        $this->length = $length;
        $this->currency = $currency;
        $this->recurringAmount = $recurringAmount;
        $this->initialAmount = $initialAmount;
        $this->type = $type;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;

        $this->SetHash($sharedSecret);
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
        $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->merchantRef .':'. $this->dateTime .':'. $this->type .':'. $this->name .':'. $this->periodType .':'. $this->currency .':'. $this->recurringAmount .':'. $this->initialAmount .':'. $this->length .':'. $sharedSecret);
    }

    
    public function ProcessRequestToGateway($serverUrl)
    {
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlStoredSubscriptionRegResponse->initXmlStoredSubscriptionRegResponse($responseString);
        return $response;
    }

    public function GenerateXml() {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("ADDSTOREDSUBSCRIPTION");
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

        $node = $this->requestXML->createElement("NAME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->name));

        if ($this->description) {
            $node = $this->requestXML->createElement("DESCRIPTION");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->description));
        }

        $node = $this->requestXML->createElement("PERIODTYPE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->periodType));

        $node = $this->requestXML->createElement("LENGTH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->length));

        $node = $this->requestXML->createElement("CURRENCY");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->currency));

        $node = $this->requestXML->createElement("RECURRINGAMOUNT");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->recurringAmount));

        $node = $this->requestXML->createElement("INITIALAMOUNT");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->initialAmount));

        $node = $this->requestXML->createElement("TYPE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->type));

        $node = $this->requestXML->createElement("ONUPDATE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->onUpdate));

        $node = $this->requestXML->createElement("ONDELETE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->onDelete));

        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));

        return $this->requestXML->saveXML();
    }
}

