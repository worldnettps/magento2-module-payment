<?php
namespace WorldnetTPS\Payment\Model\Api;

class XmlPreAuthCompletionRequest extends WorldnetTPSRequest
{
    private $terminalId;
    private $orderId;
    private $uniqueRef;
    private $amount;
    public function Amount()
    {
        return $this->amount;
    }
    private $dateTime;
    private $hash;
    private $description;
    private $cvv;
    private $cardCurrency;
    private $cardAmount;
    private $conversionRate;
    private $multicur = false;

    private $foreignCurInfoSet = false;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlPreAuthCompletionResponse
     */
    protected $XmlPreAuthCompletionResponse;



    public function __construct(XmlPreAuthCompletionResponse $XmlPreAuthCompletionResponse)
    {
        $this->XmlPreAuthCompletionResponse = $XmlPreAuthCompletionResponse;
    }

    /**
     *  Creates the standard request less optional parameters for processing an XML Transaction
     *  through the WorldNetTPS XML Gateway
     *
     *  @param terminalId Terminal ID provided by WorldNetTPS
     *  @param orderId A unique merchant identifier. Alpha numeric and max size 12 chars.
     *  @param currency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
     *  @param amount Transaction Amount, Double formatted to 2 decimal places.
     *  @param description Transaction Description
     *  @param email Cardholder e-mail
     *  @param cardNumber A valid Card Number that passes the Luhn Check.
     *  @param cardType
     *  Card Type (Accepted Card Types must be configured in the Merchant Selfcare System.)
     *
     *  Accepted Values :
     *
     *  VISA
     *  MASTERCARD
     *  LASER
     *  SWITCH
     *  SOLO
     *  AMEX
     *  DINERS
     *  MAESTRO
     *  DELTA
     *  ELECTRON
     *
     *  @param cardExpiry Card Expiry formatted MMYY
     *  @param cardHolderName Card Holder Name
     */
    public function initXmlPreAuthCompletionRequest($terminalId, $orderId, $amount)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->terminalId = $terminalId;
        $this->orderId = $orderId;
        $this->amount = $amount;
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
        $this->orderId = "";
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
     *  Setter for transaction description
     *
     *  @param cvv Discretionary text value
     */
    public function SetDescription($description)
    {
        $this->description = $description;
    }
    /**
     *  Setter for Foreign Currency Information
     *
     *  @param cardCurrency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
     *  @param cardAmount (Amount X Conversion rate) Formatted to two decimal places
     *  @param conversionRate Converstion rate supplied in rate response
     */
    public function SetForeignCurrencyInformation($cardCurrency, $cardAmount, $conversionRate)
    {
        $this->cardCurrency = $cardCurrency;
        $this->cardAmount = $cardAmount;
        $this->conversionRate = $conversionRate;

        $this->foreignCurInfoSet = true;
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
        if($this->uniqueRef !== NULL)
        {
            $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->uniqueRef.':'. $this->amount .':'. $this->dateTime .':'. $sharedSecret);
        } else {
            $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->orderId .':'. $this->amount .':'. $this->dateTime .':'. $sharedSecret);
        }
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
     *  @return XmlPreAuthCompletionResponse containing an error or the parsed payment response.
     */

    public function ProcessRequestToGateway($sharedSecret, $serverUrl)
    {
        $this->SetHash($sharedSecret);
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlPreAuthCompletionResponse->initXmlPreAuthCompletionResponse($responseString);
        return $response;
    }

    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("PREAUTHCOMPLETION");
        $this->requestXML->appendChild($requestString);

        if($this->uniqueRef !== NULL)
        {
            $node = $this->requestXML->createElement("UNIQUEREF");
            $node->appendChild($this->requestXML->createTextNode($this->uniqueRef));
            $requestString->appendChild($node);
        } else {
            $node = $this->requestXML->createElement("ORDERID");
            $node->appendChild($this->requestXML->createTextNode($this->orderId));
            $requestString->appendChild($node);
        }

        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));

        $node = $this->requestXML->createElement("AMOUNT");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->amount));

        if($this->foreignCurInfoSet)
        {
            $dcNode = $this->requestXML->createElement("FOREIGNCURRENCYINFORMATION");
            $requestString->appendChild($dcNode );

            $dcSubNode = $this->requestXML->createElement("CARDCURRENCY");
            $dcSubNode ->appendChild($this->requestXML->createTextNode($this->cardCurrency));
            $dcNode->appendChild($dcSubNode);

            $dcSubNode = $this->requestXML->createElement("CARDAMOUNT");
            $dcSubNode ->appendChild($this->requestXML->createTextNode($this->cardAmount));
            $dcNode->appendChild($dcSubNode);

            $dcSubNode = $this->requestXML->createElement("CONVERSIONRATE");
            $dcSubNode ->appendChild($this->requestXML->createTextNode($this->conversionRate));
            $dcNode->appendChild($dcSubNode);
        }

        if($this->description !== NULL)
        {
            $node = $this->requestXML->createElement("DESCRIPTION");
            $requestString->appendChild($node);
            $nodeText = $this->requestXML->createTextNode($this->description);
            $node->appendChild($nodeText);
        }

        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));

        if($this->cvv !== NULL)
        {
            $node = $this->requestXML->createElement("CVV");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->cvv));
        }

        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));

        return $this->requestXML->saveXML();

    }
}

