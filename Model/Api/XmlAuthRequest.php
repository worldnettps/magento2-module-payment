<?php
namespace WorldnetTPS\Payment\Model\Api;

class XmlAuthRequest extends WorldnetTPSRequest
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
    private $autoReady;
    private $description;
    private $email;
    private $cardNumber;
    private $trackData;
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
    private $avsOnly;
    private $mpiRef;
    private $mobileNumber;
    private $deviceId;
    private $phone;
    private $country;
    private $ipAddress;

    private $multicur = false;
    private $foreignCurInfoSet = false;
    private $fraudReviewSessionId;
    private $customFields = array();
    private $city;
    private $region;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlAuthResponse
     */
    protected $XmlAuthResponse;

    /**
     *  Creates the standard request less optional parameters for processing an XML Transaction
     *  through the WorldNetTPS XML Gateway
     *
     * @param terminalId Terminal ID provided by WorldNetTPS
     * @param orderId A unique merchant identifier. Alpha numeric and max size 12 chars.
     * @param currency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
     * @param amount Transaction Amount, Double formatted to 2 decimal places.
     * @param description Transaction Description
     * @param email Cardholder e-mail
     * @param cardNumber A valid Card Number that passes the Luhn Check.
     * @param cardType
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
     */
    public function __construct(XmlAuthResponse $XmlAuthResponse)
    {
        $this->dateTime = $this->GetFormattedDate();
        $this->XmlAuthResponse = $XmlAuthResponse;
    }

    /**
     *  Setter for hash value
     *
     * @param sharedSecret
     *  Shared secret either supplied by WorldNetTPS or configured under
     *  Terminal Settings in the Merchant Selfcare System.
     */
    public function initXmlAuthRequest($terminalId, $orderId, $currency, $amount, $cardNumber, $cardType)
    {
        $this->terminalId = $terminalId;
        $this->orderId = $orderId;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->cardNumber = $cardNumber;
        $this->cardType = $cardType;
    }

    /**
     *  Setter for Auto Ready Value
     *
     * @param autoReady
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
     *  Setter for Email Address Value
     *
     * @param email Alpha-numeric field.
     */
    public function SetEmail($email)
    {
        $this->email = $email;
    }

    /**
     *  Setter for Email Address Value
     *
     * @param email Alpha-numeric field.
     */
    public function SetDescription($description)
    {
        $this->description = $description;
    }

    /**
     *  Setter for Card Expiry and Card Holder Name values
     *  These are mandatory for non-SecureCard transactions
     *
     * @param cardExpiry Card Expiry formatted MMYY
     * @param cardHolderName Card Holder Name
     */
    public function SetNonSecureCardCardInfo($cardExpiry, $cardHolderName)
    {
        $this->cardExpiry = $cardExpiry;
        $this->cardHolderName = $cardHolderName;
    }

    /**
     *  Setter for Card Verification Value
     *
     * @param cvv Numeric field with a max of 4 characters.
     */
    public function SetCvv($cvv)
    {
        $this->cvv = $cvv;
    }

    /**
     *  Setter for Issue No
     *
     * @param issueNo Numeric field with a max of 3 characters.
     */
    public function SetIssueNo($issueNo)
    {
        $this->issueNo = $issueNo;
    }

    /**
     *  Setter for Address Verification Values
     *
     * @param address1 First Line of address - Max size 20
     * @param address2 Second Line of address - Max size 20
     * @param postCode Postcode - Max size 9
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
     * @param cardCurrency ISO 4217 3 Digit Currency Code, e.g. EUR / USD / GBP
     * @param cardAmount (Amount X Conversion rate) Formatted to two decimal places
     * @param conversionRate Converstion rate supplied in rate response
     */
    public function SetForeignCurrencyInformation($cardCurrency, $cardAmount, $conversionRate)
    {
        $this->cardCurrency = $cardCurrency;
        $this->cardAmount = $cardAmount;
        $this->conversionRate = $conversionRate;

        $this->foreignCurInfoSet = true;
    }

    /**
     *  Setter for AVS only flag
     *
     * @param avsOnly Only perform an AVS check, do not store as a transaction. Possible values: "Y", "N"
     */
    public function SetAvsOnly($avsOnly)
    {
        $this->avsOnly = $avsOnly;
    }

    /**
     *  Setter for MPI Reference code
     *
     * @param mpiRef MPI Reference code supplied by WorldNetTPS MPI redirect
     */
    public function SetMpiRef($mpiRef)
    {
        $this->mpiRef = $mpiRef;
    }

    /**
     *  Setter for Mobile Number
     *
     * @param mobileNumber Mobile Number of cardholder. If sent an SMS receipt will be sent to them
     */
    public function SetMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    }

    /**
     *  Setter for Device ID
     *
     * @param deviceId Device ID to identify this source to the XML gateway
     */
    public function SetDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
    }

    /**
     *  Setter for Phone number
     *
     * @param phone Phone number of cardholder
     */
    public function SetPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     *  Setter for the cardholders IP address
     *
     * @param ipAddress IP Address of the cardholder
     */
    public function SetIPAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     *  Setter for Country
     *
     * @param country Cardholders Country
     */
    public function SetCountry($country)
    {
        $this->country = $country;
    }

    /**
     *  Setter for multi-currency value
     *  This is required to be set for multi-currency terminals because the Hash is calculated differently.
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
     *  Setter to flag transaction as a Mail Order. If not set the transaction defaults to eCommerce
     */
    public function SetTrackData($trackData)
    {
        $this->terminalType = "3";
        $this->transactionType = "0";
        $this->cardNumber = "";
        $this->trackData = $trackData;
    }

    /**
     *  Setter transaction type. If not set the transaction type defaults to 7
     */
    public function SetTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     *  Setter for Fraud Review Session Id
     */
    public function SetFraudReviewSessionId($id)
    {
        $this->fraudReviewSessionId = $id;
    }

    /**
     *  Add custom field
     */
    public function AddCustomField($customField)
    {
        array_push($this->customFields, $customField);
    }

    /**
     *  Setter for City
     */
    public function SetCity($city)
    {
        $this->city = $city;
    }

    /**
     *  Setter for Region
     */
    public function SetRegion($region)
    {
        $this->region = $region;
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
        if (isset($this->multicur) && $this->multicur == true) $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->orderId .':'. $this->currency .':'. $this->amount .':'. $this->dateTime .':'. $sharedSecret);
        else $this->hash = $this->GetRequestHash($this->terminalId .':'. $this->orderId .':'. $this->amount .':'. $this->dateTime .':'. $sharedSecret);
    }

    /**
     *  (Old) Method to process transaction and return parsed response from the WorldNetTPS XML Gateway
     *
     * @param sharedSecret
     *  Shared secret either supplied by WorldNetTPS or configured under
     *  Terminal Settings in the Merchant Selfcare System.
     *
     * @param testAccount
     *  Boolean value defining Mode
     *  true - This is a test account
     *  false - Production mode, all transactions will be processed by Issuer.
     *
     * @return XmlAuthResponse containing an error or the parsed payment response.
     */
    public function ProcessRequestToGateway($sharedSecret, $serverUrl)
    {
        $this->SetHash($sharedSecret);
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlAuthResponse->initXmlAuthResponse($responseString);
        return $response;
    }

    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXMLformatOutput = true;

        $this->requestXML->version = "1.0";
        $this->requestXML->xmlVersion = "1.0";

        $requestString = $this->requestXML->createElement("PAYMENT");
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

        if ($this->trackData !== NULL) {
            $node = $this->requestXML->createElement("TRACKDATA");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->trackData));
        } else {
            $node = $this->requestXML->createElement("CARDNUMBER");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->cardNumber));
        }

        $node = $this->requestXML->createElement("CARDTYPE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->cardType));

        if ($this->cardExpiry !== NULL && $this->cardHolderName !== NULL && $this->trackData == NULL) {
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

        if ($this->foreignCurInfoSet) {
            $dcNode = $this->requestXML->createElement("FOREIGNCURRENCYINFORMATION");
            $requestString->appendChild($dcNode);

            $dcSubNode = $this->requestXML->createElement("CARDCURRENCY");
            $dcSubNode->appendChild($this->requestXML->createTextNode($this->cardCurrency));
            $dcNode->appendChild($dcSubNode);

            $dcSubNode = $this->requestXML->createElement("CARDAMOUNT");
            $dcSubNode->appendChild($this->requestXML->createTextNode($this->cardAmount));
            $dcNode->appendChild($dcSubNode);

            $dcSubNode = $this->requestXML->createElement("CONVERSIONRATE");
            $dcSubNode->appendChild($this->requestXML->createTextNode($this->conversionRate));
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

        if ($this->autoReady !== NULL) {
            $node = $this->requestXML->createElement("AUTOREADY");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->autoReady));
        }

        if ($this->email !== NULL) {
            $node = $this->requestXML->createElement("EMAIL");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->email));
        }

        if ($this->cvv !== NULL) {
            $node = $this->requestXML->createElement("CVV");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->cvv));
        }

        if ($this->issueNo !== NULL) {
            $node = $this->requestXML->createElement("ISSUENO");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->issueNo));
        }

        if ($this->postCode !== NULL) {
            if ($this->address1 !== NULL) {
                $node = $this->requestXML->createElement("ADDRESS1");
                $requestString->appendChild($node);
                $node->appendChild($this->requestXML->createTextNode($this->address1));
            }
            if ($this->address2 !== NULL) {
                $node = $this->requestXML->createElement("ADDRESS2");
                $requestString->appendChild($node);
                $node->appendChild($this->requestXML->createTextNode($this->address2));
            }

            $node = $this->requestXML->createElement("POSTCODE");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->postCode));
        }

        if ($this->avsOnly !== NULL) {
            $node = $this->requestXML->createElement("AVSONLY");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->avsOnly));
        }

        if ($this->description !== NULL) {
            $node = $this->requestXML->createElement("DESCRIPTION");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->description));
        }

        if ($this->mpiRef !== NULL) {
            $node = $this->requestXML->createElement("MPIREF");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->mpiRef));
        }

        if ($this->mobileNumber !== NULL) {
            $node = $this->requestXML->createElement("MOBILENUMBER");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->mobileNumber));
        }

        if ($this->deviceId !== NULL) {
            $node = $this->requestXML->createElement("DEVICEID");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->deviceId));
        }

        if ($this->phone !== NULL) {
            $node = $this->requestXML->createElement("PHONE");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->phone));
        }

        if ($this->city !== NULL) {
            $node = $this->requestXML->createElement("CITY");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->city));
        }

        if ($this->region !== NULL) {
            $node = $this->requestXML->createElement("REGION");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->region));
        }

        if ($this->country !== NULL) {
            $node = $this->requestXML->createElement("COUNTRY");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->country));
        }

        if ($this->ipAddress !== NULL) {
            $node = $this->requestXML->createElement("IPADDRESS");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->ipAddress));
        }

        foreach ($this->customFields as $customField) {
            $node = $this->requestXML->createElement("CUSTOMFIELD");
            $node->setAttribute("NAME", $customField['NAME']);
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($customField['value']));
        }

        if ($this->fraudReviewSessionId !== NULL) {
            $node = $this->requestXML->createElement("FRAUDREVIEWSESSIONID");
            $requestString->appendChild($node);
            $node->appendChild($this->requestXML->createTextNode($this->fraudReviewSessionId));
        }

        return $this->requestXML->saveXML();
    }
}

