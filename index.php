<?php

/**
 * index.php
 * Description:
 *
 */

session_start();

$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$requestUri = trim($requestUri, '/');
$requestUri = explode('/', $requestUri);

require_once dirname(__FILE__) . '/../BotsFeedUsFramework/includes/includes.php';

$botsFeedUsAPI = new BotsFeedUsAPI();

if (!isset($requestUri[0]) || empty($requestUri[0])) {
	$requestUri[0] = 'KtbCLJxfEuDGCHhjEtUgnhoeTUiQMPkU';
	$requestUri[1] = 'KtbCLJxfEuDGCHhjEtUgnhoeTUiQMPkU';
} else if (!isset($requestUri[1]) || empty($requestUri[1])) {
	$requestUri[1] = 'KtbCLJxfEuDGCHhjEtUgnhoeTUiQMPkU';
}

$requestUri[0] = $botsFeedUsAPI->sanitizeWhoOrWhat($requestUri[0]);
$requestUri[1] = $botsFeedUsAPI->sanitizeWhoOrWhat($requestUri[1]);

$who = $requestUri[0];
$what = $requestUri[1];

$botsFeedUsAPI->validateWhoOrWhat($requestUri);

if ($who === 'testPass' || $what === 'testPass') {
	$botsFeedUsAPI->testPass();
}

$botsFeedUsAPI->botsFeedUs($who, $what);

class BotsFeedUsAPI
{
	private $_apiVersion;

	private $_who;
	private $_what;

	private $_timeStamp;
	private $_ip;
	private $_agent;
	private $_language;
	private $_method;

	private $_errorCode;
	private $_response;

	private $_queryTime;

	private $_start;
	private $_time;
	private $_packageSize;
	private $_size;
	private $_memoryUsage;

	private $_logger;

	private $_validation;

	public function __construct()
	{
		$this->_start = microtime(true);
		$this->_packageSize = null;
		$this->_response = null;
		$this->_responseType = 'json';

		$this->_queryTime = 0.0;

		$container = new Container();

		$this->_logger = $container->getLogger();

		$this->_validation = $container->getValidation();

		$this->_apiVersion = '1.0.0.201404261';

		$this->_who = '';
		$this->_what = '';

		$this->beginRequest();
	}

	private function beginRequest()
	{
		$this->logIt('info', '');
		$this->logIt('info', '--------------------------------------------------------------------------------');
		$this->logIt('info', 'API Session Started');

		$serverDump = var_dump($_SERVER);
		$this->_timeStamp = (isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : 'NA');
		$this->_ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'NA');
		$this->_agent = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'NA');
		$this->_language = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'NA');
		$this->_method = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'NA');

		$this->logIt('info', 'TIME: ' . $this->_timeStamp);
		$this->logIt('info', 'IP: ' . $this->_ip);
		$this->logIt('info', 'AGENT: ' . $this->_agent);
		$this->logIt('info', 'LANGUAGE: ' . $this->_language);
		$this->logIt('info', 'METHOD: ' . $this->_method);
	}

	public function logIt($level, $message)
	{
		$this->_logger->$level($message);
	}

	///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// RESPONSE FUNCTIONS /////////////////////////////
	///////////////////////////////////////////////////////////////////////////////

	public function testPass()
	{
		http_response_code(200);
		$this->echoResponse('none', array(), '', 'testPass success', (object)array());
		$this->completeRequest();
	}

	public function badRequest()
	{
		http_response_code(400);
		$errorCode = 'badRequest';
		$friendlyError = 'Bad Request';
		$errors = array($friendlyError);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', (object)array());
		$this->completeRequest();
	}

	public function resourceNotDefined()
	{
		http_response_code(400);
		$errorCode = 'ResourceNotDefined';
		$friendlyError = 'Bots and/or Food not defined, you must tell us who to feed and what to feed them: botsfeed.us/bots/cheetos.';
		$errors = array($friendlyError);
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', (object)array());
		$this->completeRequest();
	}

	///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// VALIDATION AND SANITIZATION FUNCTIONS //////////
	///////////////////////////////////////////////////////////////////////////////

	public function sanitizeWhoOrWhat($entity) {
		return $this->_validation->sanitizeWhoOrWhat($entity);
	}

	public function validateWhoOrWhat($requestUri)
	{
		$this->_validation->validateWhoOrWhat($requestUri);
		if ($this->_validation->getErrorCount() > 0) {
			$this->validationFailed();
		}
	}

	private function validationFailed()
	{
		http_response_code(400);
		$errorCode = $this->_validation->getErrorCode();
		$errors = $this->_validation->getErrors();
		$friendlyError = $this->_validation->getFriendlyError();
		$this->echoResponse($errorCode, $errors, $friendlyError, 'fail', (object)array());
		$this->completeRequest();
	}

	///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// BOTSFEEDUS FUNCTIONS ///////////////////////////
	///////////////////////////////////////////////////////////////////////////////

	public function botsFeedUs($who, $what)
	{
		$this->_who = $who;
		$this->_what = $what;

		$response = '';
		if ($this->_who == 'KtbCLJxfEuDGCHhjEtUgnhoeTUiQMPkU') {
			$this->_who = '';
			$this->_what = '';
			$response = 'We\'re not sure who you want to feed or what to even feed them. Add who to feed and a yummy botsnack at the end of the URI: botsfeed.us/bots/cheetos.';
		} else if ($this->_what == 'KtbCLJxfEuDGCHhjEtUgnhoeTUiQMPkU') {
			$this->_what = '';
			$response = 'You\'ve told us who to feed, but not what to feed it. Add a yummy botsnack at the end of the URI: botsfeed.us/bots/cheetos.';
		} else {
			$reaction = 'Yum!';
			$feeling = 'love';
			if (strtolower($what) == 'broccoli') {
				$reaction = 'Gross!';
				$feeling = 'hate';
			}

			$pronoun = ($who == 'bots') ? 'We' : 'I';
			$response = $reaction . ' ' . $pronoun . ' ' . $feeling . ' ' . $what . '!';
		}

		$this->logIt('info', 'Response: ' . $response);

		http_response_code(200);
		$this->echoResponse('none', array(), '', 'success', (object)array('response' => $response));

		$this->completeRequest();
	}

	///////////////////////////////////////////////////////////////////////////////
	////////////////////////////// CLOSING FUNCTIONS //////////////////////////////
	///////////////////////////////////////////////////////////////////////////////

	private function echoResponse($errorCode, $errors, $friendlyErrors, $result, $data)
	{
		// if a callback is set, assume jsonp and wrap the response in the callback function
		if (isset($_REQUEST['callback']) && strtolower($_REQUEST['responseType'] === 'jsonp')) {
			echo $_REQUEST['callback'] . '(';
		}

		$this->_errorCode = $errorCode;

		$jsonResponse = array();
		$jsonResponse['apiVersion'] = $this->_apiVersion;
		$jsonResponse['httpStatus'] = http_response_code();
		$jsonResponse['verb'] = $_SERVER['REQUEST_METHOD'];
		$jsonResponse['errorCode'] = $errorCode;
		$jsonResponse['errors'] = $errors;
		$jsonResponse['friendlyError'] = $friendlyErrors;
		$jsonResponse['result'] = $result;
		$jsonResponse['who'] = $this->_who;
		$jsonResponse['what'] = $this->_what;
		$jsonResponse['data'] = $data;
		foreach ($errors as $error) {
			$this->logIt('info', $error);
		}
		$this->_response = json_encode($jsonResponse);
		header('Content-type: application/json');
		echo $this->_response;

		if (isset($_REQUEST['callback']) && strtolower($_REQUEST['responseType'] === 'jsonp')) {
			echo ')';
		}
	}

	private function completeRequest()
	{
		$this->_time = (microtime(true) - $this->_start);
		$this->_packageSize = strlen($this->_response);
		$this->_size = number_format($this->_packageSize);
		$this->_memoryUsage = number_format(memory_get_usage());

		$this->logIt('info', 'Query Time: ' . $this->_queryTime);
		$this->logIt('info', 'Payload Time: ' . $this->_time);
		$this->logIt('info', 'Payload Size: ' . $this->_size);
		$this->logIt('info', 'Memory Usage: ' . $this->_memoryUsage);
		$this->logIt('info', 'API Session Ended');
		$this->logIt('info', '--------------------------------------------------------------------------------');
//		$this->logIt('info', 'SERVER: ' . $serverDump);
		$this->logIt('info', '--------------------------------------------------------------------------------');
		$this->logIt('info', '');

		exit();
	}
}
