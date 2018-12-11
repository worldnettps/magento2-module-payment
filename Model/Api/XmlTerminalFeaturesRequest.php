<?php
namespace WorldnetTPS\Payment\Model\Api;

class XmlTerminalFeaturesRequest extends WorldnetTPSRequest
{
    private $terminalId;
    private $customFieldLanguage;
    private $hash;
    private $dateTime;

    /**
     * @var \WorldnetTPS\Payment\Model\Api\XmlAuthResponse
     */
    protected $XmlTerminalFeaturesResponse;

    /**
     *
     */
    public function __construct(
        XmlTerminalFeaturesResponse $XmlTerminalFeaturesResponse,
        \Magento\Framework\Locale\Resolver $resolver
    )
    {
        $this->XmlTerminalFeaturesResponse = $XmlTerminalFeaturesResponse;

        $this->customFieldLanguage = str_replace('_', '-', $resolver->getLocale());
        $this->dateTime = $this->GetFormattedDate();
    }


    public function initXmlTerminalFeaturesRequest($terminalId, $sharedSecret)
    {
        $this->terminalId = $terminalId;
        $this->hash = hash('sha512',$terminalId.':'.$this->customFieldLanguage.':'.$this->dateTime.':'.$sharedSecret);
    }


    public function ProcessRequestToGateway($serverUrl)
    {
        $responseString = $this->SendRequestToGateway($this->GenerateXml(), $serverUrl);
        $response = $this->XmlTerminalFeaturesResponse->initXmlTerminalFeaturesResponse($responseString);
        return $response;
    }

    public function GenerateXml()
    {
        $this->requestXML = new \DOMDocument('1.0', 'UTF-8');
        $this->requestXMLformatOutput = true;

        $this->requestXML->version = "1.0";
        $this->requestXML->xmlVersion = "1.0";

        $requestString = $this->requestXML->createElement("TERMINAL_CONFIGURATION");
        $this->requestXML->appendChild($requestString);

        $node = $this->requestXML->createElement("TERMINALID");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->terminalId));

        $node = $this->requestXML->createElement("CUSTOM_FIELD_LANGUAGE");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->customFieldLanguage));

        $node = $this->requestXML->createElement("DATETIME");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->dateTime));

        $node = $this->requestXML->createElement("HASH");
        $requestString->appendChild($node);
        $node->appendChild($this->requestXML->createTextNode($this->hash));

        return $this->requestXML->saveXML();
    }
}

