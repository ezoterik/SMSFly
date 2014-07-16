<?php

/**
 * Class SMSFly
 */
class SMSFly {

	protected $_server = 'http://sms-fly.com/api/api.php';

	protected $_error = array();

/**
 * @param string $login
 * @param string $password
 * @param string $alphaName
 */
	public function __construct($login = '', $password = '', $alphaName = '') {
		$this->_login = $login;
		$this->_password = $password;
		$this->_alphaName = $alphaName;
	}

/**
 * @param $recipient
 * @param $text
 * @param $description
 * @param string $startTime
 * @param string $endTime
 * @param int $livetime
 * @param int $rate
 * @return bool|SimpleXMLElement
 */
	public function sendSMS($recipient, $text, $description, $startTime = 'AUTO', $endTime = 'AUTO', $livetime = 4, $rate = 120) {
		$result = false;

		$text = htmlspecialchars($text);
		$description = htmlspecialchars($description);

		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('request');
		$xml->writeElement('operation', 'SENDSMS');
		$xml->startElement('message');
		$xml->writeAttribute('start_time', $startTime);
		$xml->writeAttribute('end_time', $endTime);
		$xml->writeAttribute('livetime', $livetime);
		$xml->writeAttribute('rate', $rate);
		$xml->writeAttribute('desc', $description);
		$xml->writeAttribute('source', $this->_alphaName);
		$xml->writeElement('body', $text);
		$xml->writeElement('recipient', $recipient);
		$xml->endElement();
		$xml->endElement();
		$xml->endDocument();

		$xml = $xml->outputMemory();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERPWD, $this->_login . ':' . $this->_password);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $this->_server);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml", "Accept: text/xml"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$response = curl_exec($ch);
		curl_close($ch);

		$simpleXml = new SimpleXMLElement($response);
		if ($simpleXml->state['code'] == 'ACCEPT') {
			$result = $simpleXml->state['campaignID'];
		} else {
			$this->_error = $simpleXml->state[0];
		}

		return $result;
	}

/**
 * @return array
 */
	public function getError() {
		return $this->_error;
	}
}