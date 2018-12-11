<?php
namespace WorldnetTPS\Payment\Model\Api;

class XmlRefundRequest extends WorldnetTPSRequest
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
    private $operator;
    private $reason;
    private $autoReady;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlRefundResponse
     */
    protected $XmlRefundResponse;

    public function __construct(XmlRefundResponse $XmlRefundResponse)
    {
        $this->dateTime = $this->GetFormattedDate();
        $this->XmlRefundResponse = $XmlRefundResponse;
    }

    /**
     *  Creates the refund request for processing an XML Transaction
     *  through the WorldNetTPS XML Gateway
     *
     *  @param terminalId Terminal ID provided by WorldNetTPS
     *  @param orderId A unique merchant identifier. Alpha numeric and max size 12 chars.
     *  @param currency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
     *  @param amount Transaction Amount, Double formatted to 2 decimal places.
     *  @param operator An identifier for who executed this transaction
     *  @param reason The reason for the refund
     */
    public function initXmlRefundRequest($terminalId, $orderId, $amount, $operator, $reason)
    {
        $this->dateTime = $this->GetFormattedDate();
        $this->amount = $amount;
        $this->terminalId = $terminalId;
        $this->orderId = $orderId;
        $this->operator = $operator;
        $this->reason = $reason;
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
     *  Setter for Auto Ready Value

     *
     *  @param autoReady
     *  Auto Ready is an optional parameter and defines if the transaction should be settled automatically.
     *
     *  Accepted Values :

     *
     *  Y   -   Transaction will be settled in next batch
     *  N   -   Transaction will not be settled until user changes state in Merchant Selfcare Section
     */
    public function SetAutoReady($autoReady)
    {
        $this->autoReady = $autoReady;
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
            $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->uniqueRef .':'. $this->amount .':'. $this->dateTime .':'. $sharedSecret);
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
     *  @return XmlRefundResponse containing an error or the parsed refund response.
     */
    public function ProcessRequestToGateway($sharedSecret, $serverUrl)
    {
        $this->SetHash($sharedSecret);
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlRefundResponse->initXmlRefundResponse($responseString);
        return $response;
    }
    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("REFUND");
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
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("AMOUNT");
        $node->appendChild($this->requestXML->createTextNode($this->amount));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("DATETIME");
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("HASH");
        $node->appendChild($this->requestXML->createTextNode($this->hash));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("OPERATOR");
        $node->appendChild($this->requestXML->createTextNode($this->operator));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("REASON");
        $node->appendChild($this->requestXML->createTextNode($this->reason));
        $requestString->appendChild($node);

        if($this->autoReady !== NULL)
        {
            $node = $this->requestXML->createElement("AUTOREADY");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->autoReady));
        }

        return $this->requestXML->saveXML();

    }
}

