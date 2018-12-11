<?php

namespace WorldnetTPS\Payment\Model\Api;

class XmlSubscriptionRegRequest extends WorldnetTPSRequest
{
    private $merchantRef;
    private $terminalId;
    private $storedSubscriptionRef;
    private $secureCardMerchantRef;
    private $name;
    private $description;
    private $periodType;
    private $length;
    private $currency;
    private $recurringAmount;
    private $initialAmount;
    private $type;
    private $startDate;
    private $endDate;
    private $onUpdate;
    private $onDelete;
    private $dateTime;
    private $hash;
    private $eDCCDecision;

    private $newStoredSubscription = false;

    public function SetNewStoredSubscriptionValues($name, $description, $periodType, $length, $currency, $recurringAmount, $initialAmount, $type, $onUpdate, $onDelete)
    {
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

        $this->newStoredSubscription = true;
    }

    public function SetSubscriptionAmounts($recurringAmount, $initialAmount)
    {
        $this->recurringAmount = $recurringAmount;
        $this->initialAmount = $initialAmount;
    }
    /**
     *  Setter for end date
     *
     *  @param endDate End Date of subscription
     */
    public function SetEndDate($endDate)
    {
        $this->endDate = $endDate;
    }
    /**
     *  Setter for when the cardholder has accepted the eDCC offering
     *
     *  @param eDCCDecision eDCC decision ("Y" or "N")
     */
    public function EDCCDecision($eDCCDecision)
    {
        $this->eDCCDecision = $eDCCDecision;
    }


    public function __construct(XmlSubscriptionRegResponse $XmlSubscriptionRegResponse)
    {
        $this->XmlSubscriptionRegResponse = $XmlSubscriptionRegResponse;

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
    public function initXmlSubscriptionRegRequest($merchantRef, $terminalId, $storedSubscriptionRef, $secureCardMerchantRef, $startDate, $sharedSecret)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->storedSubscriptionRef = $storedSubscriptionRef;
        $this->secureCardMerchantRef = $secureCardMerchantRef;
        $this->merchantRef = $merchantRef;
        $this->terminalId = $terminalId;
        $this->startDate = $startDate;

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
        if($this->newStoredSubscription) $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->merchantRef .':'. $this->secureCardMerchantRef .':'. $this->dateTime .':'. $this->startDate .':'. $sharedSecret);
        else $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->merchantRef .':'. $this->storedSubscriptionRef .':'. $this->secureCardMerchantRef .':'. $this->dateTime .':'. $this->startDate .':'. $sharedSecret);

    }

    
    public function ProcessRequestToGateway($serverUrl)
    {
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlSubscriptionRegResponse->initXmlSubscriptionRegResponse($responseString);
        return $response;
    }

    public function GenerateXml() {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;
    
        $requestString = $this->requestXML->createElement("ADDSUBSCRIPTION");
        $this->requestXML->appendChild($requestString);
    
        $node = $this->requestXML->createElement("MERCHANTREF");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->merchantRef));
    
        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));
    
        if(!$this->newStoredSubscription)
        {
            $node = $this->requestXML->createElement("STOREDSUBSCRIPTIONREF");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->storedSubscriptionRef));
        }
    
        $node = $this->requestXML->createElement("SECURECARDMERCHANTREF");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->secureCardMerchantRef));
    
        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));
    
        if($this->recurringAmount != null && $this->recurringAmount != null && !$this->newStoredSubscription)
        {
            $node = $this->requestXML->createElement("RECURRINGAMOUNT");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->recurringAmount));
    
            $node = $this->requestXML->createElement("INITIALAMOUNT");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->initialAmount));
        }
    
        $node = $this->requestXML->createElement("STARTDATE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->startDate));
    
    
        if($this->endDate != null)
        {
            $node = $this->requestXML->createElement("ENDDATE");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->endDate));
        }
    
        if($this->eDCCDecision !== NULL)
        {
            $node = $this->requestXML->createElement("EDCCDECISION");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->eDCCDecision));
        }
    
        if($this->newStoredSubscription)
        {
            $ssNode = $this->requestXML->createElement("NEWSTOREDSUBSCRIPTIONINFO");
            $requestString->appendChild($ssNode );
    
            $ssSubNode = $this->requestXML->createElement("MERCHANTREF");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->storedSubscriptionRef));
    
            $ssSubNode = $this->requestXML->createElement("NAME");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->name));
    
            $ssSubNode = $this->requestXML->createElement("DESCRIPTION");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->description));
    
            $ssSubNode = $this->requestXML->createElement("PERIODTYPE");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->periodType));
    
            $ssSubNode = $this->requestXML->createElement("LENGTH");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->length));
    
            $ssSubNode = $this->requestXML->createElement("CURRENCY");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->currency));
    
            if($this->type != "AUTOMATIC (WITHOUT AMOUNTS)")
            {
                $ssSubNode = $this->requestXML->createElement("RECURRINGAMOUNT");
                $ssNode->appendChild($ssSubNode);
                $ssSubNode->appendChild($this->requestXML->createTextNode($this->recurringAmount));
    
                $ssSubNode = $this->requestXML->createElement("INITIALAMOUNT");
                $ssNode->appendChild($ssSubNode);
                $ssSubNode->appendChild($this->requestXML->createTextNode($this->initialAmount));
            }
    
            $ssSubNode = $this->requestXML->createElement("TYPE");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->type));
    
            $ssSubNode = $this->requestXML->createElement("ONUPDATE");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->onUpdate));
    
            $ssSubNode = $this->requestXML->createElement("ONDELETE");
            $ssNode->appendChild($ssSubNode);
            $ssSubNode->appendChild($this->requestXML->createTextNode($this->onDelete));
        }
    
        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));
    
        return $this->requestXML->saveXML();
    }
}

