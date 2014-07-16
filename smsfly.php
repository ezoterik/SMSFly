<?php

/**
 * Class SMSFly
 */
class SMSFly {

	protected $_server = 'http://sms-fly.com/api/api.php';

	protected $_error = array();

/**
 * @param string $login Логин
 * @param string $password Пароль
 * @param string $alphaName Отправитель. Альфанумерическое имя (альфаимя). Допускаются только альфанумерические имена, зарегистрированные для пользователя
 */
	public function __construct($login, $password, $alphaName = '') {
		$this->_login = $login;
		$this->_password = $password;
		$this->_alphaName = $alphaName;
	}

/**
 * Отправляет одно SMS
 *
 * @param string $recipient Получатель (номер телефона)
 * @param string $text Текст сообщения
 * @param string $description Описание рассылки (отображается в веб интерфейсе). На саму рассылку никак не влияет. Можно оставлять пустым
 * @param string $startTime Время начала отправки сообщения(й). Формат AUTO или YYYY-MM-DD HH:MM:SS. Система допускает поправку времени в 5 минут. формат для PHP “Y-m-d H:i:s”). При выборе значения AUTO - будет выставлено текущее системное время - немедленная отправка
 * @param string $endTime Время окончания отправки сообщения(й),  Формат AUTO или YYYY-MM-DD HH:MM:SS. Не может быть раньше времени начала отправки. (формат для PHP “Y-m-d H:i:s”). Можно использовать значение AUTO для автоматического расчета времени системой
 * @param int $liveTime Срок жизни сообщения(й) в часах.  Допускаются только целые значения в диапазоне от 1 до 24
 * @param int $rate Скорость отправки сообщения(й) в количестве сообщений в минуту. Допускаются только целые значения в диапазоне от 1 до 120
 * @return bool|SimpleXMLElement
 */
	public function sendSMS($recipient, $text, $description = '', $startTime = 'AUTO', $endTime = 'AUTO', $liveTime = 4, $rate = 120) {
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
		$xml->writeAttribute('livetime', $liveTime);
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