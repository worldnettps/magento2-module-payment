<?php

namespace WorldnetTPS\Payment\Model\Api;

class XmlSubscriptionUpdRequest extends WorldnetTPSRequest
{
    private $merchantRef;
    private $terminalId;
    private $secureCardMerchantRef;
    private $name;
    private $description;
    private $periodType;
    private $length;
    private $recurringAmount;
    private $type;
    private $startDate;
    private $endDate;
    private $dateTime;
    private $hash;
    private $eDCCDecision;

    /**
     *  Setter for subscription name
     *
     *  @param name Subscription name
     */
    public function SetSubName($name)
    {
        $this->name = $name;
    }
    /**
     *  Setter for subscription description
     *
     *  @param description Subscription description
     */
    public function SetDescription($description)
    {
        $this->description = $description;
    }
    /**
     *  Setter for subscription period type
     *
     *  @param periodType Subscription period type
     */
    public function SetPeriodType($periodType)
    {
        $this->periodType = $periodType;
    }
    /**
     *  Setter for subscription length
     *
     *  @param length Subscription length
     */
    public function SetLength($length)
    {
        $this->length = $length;
    }
    /**
     *  Setter for subscription recurring amount
     *
     *  @param recurringAmount Subscription recurring amount
     */
    public function SetRecurringAmount($recurringAmount)
    {
        $this->recurringAmount = $recurringAmount;
    }
    /**
     *  Setter for stored subscription type
     *
     *  @param endDate Stored subscription type
     */
    public function SetSubType($type)
    {
        $this->type = $type;
    }
    /**
     *  Setter for stored subscription start date
     *
     *  @param startDate Stored subscription start date
     */
    public function SetStartDate($startDate)
    {
        $this->startDate = $startDate;
    }
    /**
     *  Setter for stored subscription end date
     *
     *  @param endDate Stored subscription end date
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

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlSubscriptionUpdResponse
     */
    protected $XmlSubscriptionUpdResponse;


    public function __construct(XmlSubscriptionUpdResponse $XmlSubscriptionUpdResponse)
    {
        $this->XmlSubscriptionUpdResponse = $XmlSubscriptionUpdResponse;

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
    public function initXmlSubscriptionUpdRequest($merchantRef, $terminalId, $secureCardMerchantRef, $startDate, $sharedSecret)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->merchantRef = $merchantRef;
        $this->terminalId = $terminalId;
        $this->secureCardMerchantRef = $secureCardMerchantRef;
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
        $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->merchantRef .':'. $this->secureCardMerchantRef .':'. $this->dateTime .':'. $this->startDate .':'. $sharedSecret);
    }

    
    public function ProcessRequestToGateway($serverUrl)
    {
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlSubscriptionUpdResponse->initXmlSubscriptionUpdResponse($responseString);
        return $response;
    }

    public function GenerateXml() {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("UPDATESUBSCRIPTION");
        $this->requestXML->appendChild($requestString);

        $node = $this->requestXML->createElement("MERCHANTREF");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->merchantRef));

        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));

        $node = $this->requestXML->createElement("SECURECARDMERCHANTREF");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->secureCardMerchantRef));

        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));

        if($this->name !== NULL)
        {
            $node = $this->requestXML->createElement("NAME");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->name));
        }

        if($this->description !== NULL)
        {
            $node = $this->requestXML->createElement("DESCRIPTION");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->description));
        }

        if($this->periodType !== NULL)
        {
            $node = $this->requestXML->createElement("PERIODTYPE");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->periodType));
        }

        if($this->length != null)
        {
            $node = $this->requestXML->createElement("LENGTH");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->length));
        }

        if($this->recurringAmount !== NULL)
        {
            $node = $this->requestXML->createElement("RECURRINGAMOUNT");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->recurringAmount));
        }

        if($this->type !== NULL)
        {
            $node = $this->requestXML->createElement("TYPE");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->type));
        }

        if($this->startDate !== NULL)
        {
            $node = $this->requestXML->createElement("STARTDATE");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->startDate));
        }

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

        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));

        return $this->requestXML->saveXML();
    }
}

