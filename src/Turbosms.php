<?php

namespace Uapixart\LaravelTurbosms;

class Turbosms
{
   	/**
	* Soap login
	*
	* @var string
	*/
	protected $login;

 	/**
	* Soap password
	*
	* @var string
	*/
	public $password;

 	/**
	* @var string
	*/
	public $sender = 'Msg';

	/**
	* Oprions for soap client
	*
	* @var array
	*/
	public $options = [];	

	/**
	* Debug mode
	*
	* @var bool
	*/
	public $debug = false;

  	/**
	* @var SoapClient
	*/
	protected $client;

   	/**
	* Wsdl url
	*
	* @var string
	*/
	protected $wsdl = 'http://turbosms.in.ua/api/wsdl.html';

	public function __construct()
    {
    	$this->login = config('turbosms.login');
    	$this->password = config('turbosms.password');
    	config('turbosms.sender') ? $this->sender = config('turbosms.sender') : '';
    	$this->options = config('turbosms.options');
    	$this->debug = config('turbosms.debug');
    }

   	/**
	* Get Soap client
	*
	* @return SoapClient
	* @throws InvalidConfigException
	*/
	protected function getClient()
	{
		/* if (!$this->client) {
			return $this->connect();
		}
		return $this->client; */
		
		return $this->connect();
	}

	/**
	* Connect to Turbosms by Soap
	*
	* @return SoapClient
	* @throws InvalidConfigException
	*/
	protected function connect()
	{
		// check for already exist client
		/* if ($this->client) {
			return $this->client;
		} */

		// check for soap module for php
		if(class_exists('SOAPClient')) {

			try {

				// create soap client object
				$client = new \SoapClient($this->wsdl, $this->options);

				// check for entered login and password
				if (!$this->login || !$this->password) {
					
					$error = 'Enter login and password for Turbosms in config file';

				} else {

					// make request for auth
					$result = $client->Auth([
						'login' => $this->login,
						'password' => $this->password,
					]);

					// check for authentification result
					if ($result->AuthResult . '' != 'Вы успешно авторизировались') {
				
						$error = 'Soap auth: '.$result->AuthResult;
					
					} else {				
						
						$this->client = $client;
					
					}

				}				

			} catch ( \SoapFault $e ) {
				
				$error = $e->getMessage();

				// disable laravel exception https://github.com/laravel/framework/issues/6618
				set_error_handler('var_dump', 0); // Never called because of empty mask.
            	@trigger_error("");
            	restore_error_handler();
					
			}

		} else {

			$error = 'No SOAP client. Install Extesions php-soap';

		}


		return $this->client ? $this->client : $error;
	}

	/**
	* Send sms and return array of message's info
	*
	* @param string $text
	* @param $phones
	*
	* @return array
	*
	* @throws InvalidConfigException
	*/
	public function send($phones, string $text)
	{

		// if no debug enabled
		if(!$this->debug) {

			// get SOAP client
			$client = $this->getClient();

			// fi we have successful client soap created
			if(is_a($client,'SoapClient')) {
		
			$destinations = $phones;
				
		        if (is_array($phones)) {
				$destinations = implode (",", $phones);
			}

				// send Sms with Soap
				$results = $client->SendSMS([
					'sender' => $this->sender,
					'destination' => $destinations,
					'text' => $text
				]);

				if (is_array($results->SendSMSResult->ResultArray)) {

					// remove first item from array
					unset($results->SendSMSResult->ResultArray['0']);

					if(is_array($results->SendSMSResult->ResultArray)) {
						
						foreach($results->SendSMSResult->ResultArray as $key=>$result){
	
							// if message sended
							if (preg_match('/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}\}?$/', $result)) {

				  				$status = 3; // Sended
				  				$status_detail = 'Message send'; 
				  				$messageid = $result;

							} else {
				  				// if final error
				  				if(strpos($result, 'Не удалось распознать номер получателя') !== false || strpos($result, 'Страна не поддерживается') !== false ){
				  				
				  					$status = 4; // Failed
				  					$status_detail = 'Message undelivered: '.$result;
				  					$messageid = null;
				  				
				  				} else {

				  					$status = 2; // Waiting in queue
				  					$status_detail = 'Message wait retry: '.$result;
				  					$messageid = null;

				  				}
							}

							$messages[] = [
											'status' => $status,
											'status_detail' => $status_detail,
											'messageid' => $messageid,
										];
						}

						return $messages;

					}
				} else {

					$status = 2; // Waiting for resolve problem with SendSMS
					$status_detail = $results->SendSMSResult->ResultArray;
					$messageid = null;

				}


			} else {

				$status = 2; // Waiting for resolve problem with SOAP
				$status_detail = $client; // Log SOAP problem
				$messageid = null;

			}
		
		} else {

			$status = 3; // Sended
			$status_detail = 'Debug mode'; // Debug mode enabled
			$messageid = null;

		}

		// Error response for message
		if (!is_array($phones)) {
			$phones = [$phones];
		}
				
		foreach($phones as $phone){
			$messages[] = [
							'status' => $status,
							'status_detail' => $status_detail,
							'messageid' => $messageid,
						];
		}


		return $messages;

	}

	/**
     * Get message status
     *
     * @param $messageId
     *
     * @return string
     */
	public function getMessageStatus(string $messageId){

		if(!$this->debug) {

			// get SOAP client
			$client = $this->getClient();

			// fi we have successful client soap created
			if(is_a($client,'SoapClient')) {
				
				$result = $client->GetMessageStatus(['MessageId' => $messageId])->GetMessageStatusResult;

				//default
				$status = '';

				// work with statuses
				$all_statuses = [
									'0' => 'не найдено',
									'1' => 'Отправлено',
									'2' => 'В очереди',
									'3' => 'Сообщение передано в мобильную сеть',
									'4' => 'Сообщение доставлено получателю',
									'5' => 'Истек срок сообщения',
									'6' => 'Удалено оператором',
									'7' => 'Не доставлено',
									'8' => 'Сообщение доставлено на сервер',
									'9' => 'Отклонено оператором',
									'10' => 'Неизвестный статус',
									'11' => 'Ошибка, сообщение не отправлено',
									'12' => 'Не достаточно кредитов на счете',
									'13' => 'Отправка отменена',
									'14' => 'Отправка приостановлена',
									'15' => 'Удалено пользователем',
								];

				foreach ($all_statuses as $key => $value) {
					if(strpos($result, $value) !== false) {
						$status = $key;
						break;
					}
				}

				$info[] = [
							'status' => $status,
							'status_description' => $result,
							];			

			} else {

				$info[] = [
								'status' => '',
								'status_description' => $client,
							];
			
			}

		} else {
			$info[] = [
							'status' => '',
							'status_description' => 'Debug mode',
						];
		}

		return $info;

	}


	/**
     * Get balance
     */
	public function getBalance(){

		if(!$this->debug) {

			// get SOAP client
			$client = $this->getClient();

			// fi we have successful client soap created
			if(is_a($client,'SoapClient')) {
				
				$balance = intval($client->GetCreditBalance()->GetCreditBalanceResult);

			} else {

				$balance = $client;
			
			}

		} else {

			$balance = 0;
		
		}

		return $balance;
	}
}
