<?php
namespace WorldnetTPS\Payment\Model\Api;

class XmlPreAuthRequest extends WorldnetTPSRequest
{

    private $terminalId;
    private $orderId;
    private $currency;
    private $amount;
    public function Amount()
    {
        return $this->amount;
    }
    private $dateTime;
    private $hash;
    private $description;
    private $email;
    private $cardNumber;
    private $cardType;
    private $cardExpiry;
    private $cardHolderName;
    private $cvv;
    private $issueNo;
    private $address1;
    private $address2;
    private $postCode;
    private $cardCurrency;
    private $cardAmount;
    private $conversionRate;
    private $terminalType = "2";
    private $transactionType = "7";
    private $multicur = false;
    private $foreignCurInfoSet = false;
    private $ipAddress;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlPreAuthResponse
     */
    protected $XmlPreAuthResponse;



    public function __construct(XmlPreAuthResponse $XmlPreAuthResponse)
    {
        $this->dateTime = $this->GetFormattedDate();
        $this->XmlPreAuthResponse = $XmlPreAuthResponse;
    }

    /**
     *  Creates the pre-auth request less optional parameters for processing an XML Transaction
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
    public function initXmlPreAuthRequest($terminalId, $orderId, $currency, $amount, $cardNumber, $cardType)
    {
        $this->dateTime = $this->GetFormattedDate();

        $this->terminalId = $terminalId;
        $this->orderId = $orderId;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->cardNumber = $cardNumber;
        $this->cardType = $cardType;
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
     *  Setter for Email Address Value
     *
     *  @param email Alpha-numeric field.
     */
    public function SetEmail($email)
    {
        $this->email = $email;
    }
    /**
     *  Setter for Email Address Value
     *
     *  @param email Alpha-numeric field.
     */
    public function SetDescription($description)
    {
        $this->description = $description;
    }
    /**
     *  Setter for Card Expiry and Card Holder Name values
     *  These are mandatory for non-SecureCard transactions
     *
     *  @param email Alpha-numeric field.
     */
    public function SetNonSecureCardCardInfo($cardExpiry, $cardHolderName)
    {
        $this->cardExpiry = $cardExpiry;
        $this->cardHolderName = $cardHolderName;
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
     *  Setter for Address Verification Values
     *
     *  @param address1 First Line of address - Max size 20
     *  @param address2 Second Line of address - Max size 20
     *  @param postCode Postcode - Max size 9
     */
    public function SetAvs($address1, $address2, $postCode)
    {
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->postCode = $postCode;
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
     *  Setter for the cardholders IP address
     *
     *  @param ipAddress IP Address of the cardholder
     */
    public function SetIPAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }
    /**
     *  Setter for Multicurrency value
     */
    public function SetMultiCur()
    {
        $this->multicur = true;
    }
    /**
     *  Setter to flag transaction as a Mail Order. If not set the transaction defaults to eCommerce
     */
    public function SetMotoTrans()
    {
        $this->terminalType = "1";
        $this->transactionType = "4";
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
        if(isset($this->multicur) && $this->multicur == true) $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->orderId .':'. $this->currency .':'. $this->amount .':'. $this->dateTime .':'. $sharedSecret);
        else $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->orderId .':'. $this->amount .':'. $this->dateTime .':'. $sharedSecret);
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
     *  @return XmlPreAuthResponse containing an error or the parsed payment response.
     */
    public function ProcessRequestToGateway($sharedSecret, $serverUrl)
    {
        $this->SetHash($sharedSecret);
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlPreAuthResponse->initXmlPreAuthResponse($responseString);
        return $response;
    }

    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXML->formatOutput = true;

        $requestString = $this->requestXML->createElement("PREAUTH");
        $this->requestXML->appendChild($requestString);

        $node = $this->requestXML->createElement("ORDERID");
        $node->appendChild($this->requestXML->createTextNode($this->orderId));
        $requestString->appendChild($node);

        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));

        $node = $this->requestXML->createElement("AMOUNT");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->amount));

        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));

        $node = $this->requestXML->createElement("CARDNUMBER");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->cardNumber));

        $node = $this->requestXML->createElement("CARDTYPE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->cardType));

        if($this->cardExpiry !== NULL && $this->cardHolderName !== NULL) {
            $node = $this->requestXML->createElement("CARDEXPIRY");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->cardExpiry));

            $node = $this->requestXML->createElement("CARDHOLDERNAME");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->cardHolderName));
        }

        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));

        $node = $this->requestXML->createElement("CURRENCY");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->currency));

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

        $node = $this->requestXML->createElement("TERMINALTYPE");
        $requestString->appendChild($node);
        $nodeText = $this->requestXML->createTextNode($this->terminalType);
        $node->appendChild($nodeText);

        $node = $this->requestXML->createElement("TRANSACTIONTYPE");
        $requestString->appendChild($node);
        $nodeText = $this->requestXML->createTextNode($this->transactionType);
        $node->appendChild($nodeText);

        if($this->email !== NULL)
        {
            $node = $this->requestXML->createElement("EMAIL");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->email));
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

        if($this->postCode !== NULL)
        {
            if($this->address1 !== NULL)
            {
                $node = $this->requestXML->createElement("ADDRESS1");
                $requestString->appendChild($node);
                $node->appendChild($this->requestXML->createTextNode($this->address1));
            }
            if($this->address2 !== NULL)
            {
                $node = $this->requestXML->createElement("ADDRESS2");
                $requestString->appendChild($node);
                $node->appendChild($this->requestXML->createTextNode($this->address2));
            }

            $node = $this->requestXML->createElement("POSTCODE");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->postCode));
        }

        if($this->description !== NULL)
        {
            $node = $this->requestXML->createElement("DESCRIPTION");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->description));
        }

        if($this->ipAddress !== NULL)
        {
            $node = $this->requestXML->createElement("IPADDRESS");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->ipAddress));
        }

        return $this->requestXML->saveXML();

    }
}

