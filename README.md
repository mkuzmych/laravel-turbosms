laravel-turbosms
=============
A package for the Laravel Framework for sending emails using the Turbosms.ua by SOAP.

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```shell
composer require uapixart/laravel-turbosms
```
Add to config/app.php:
```php
'providers' => array(
//...
    Uapixart\LaravelTurbosms\TurbosmsServiceProvider::class,
//...
),
'aliases' => array(
//...
    'Turbosms' => Uapixart\LaravelTurbosms\TurbosmsFacade::class,
//...
),
```
Then run command:
```shell
$ php artisan vendor:publish --provider="Uapixart\LaravelTurbosms\TurbosmsServiceProvider"
```
## Basic setup

You should:
* registered account at http://turbosms.ua/
* add sender in page https://turbosms.ua/sign/add.html
* create login and password for soap api in page https://turbosms.ua/route.html

### Configuration

In your config/turbosms.php, change the following
```php
'login' => '',
'password' => '',
'sender' => '',
'options' => [],
'debug' => false,
```
in debug mode sms not send only add to db table.
If you need proxy:
```php
'options' => ['proxy_host' => "proxy.com", 'proxy_port' => 3128],
```

## Usage

### Send messages
Once the extension is installed, simply use it in your code by:
```php
Turbosms::send('+380XXXXXXXXX','test');
```
or for multiple recipients:
```php
Turbosms::send(['+380XXXXXXXXX','+380XXXXXXXXX'],'test');
```

Example for response this command:
```php
Turbosms::send(['+9873','+3805037512XX'],'Test');
```

```php
array:2 [▼
  0 => array:3 [▼
    "status" => 3
    "status_detail" => "Message undelivered: Не удалось распознать номер получателя "+9873""
    "messageid" => null
  ]
  1 => array:3 [▼
    "status" => 1
    "status_detail" => "Message send"
    "messageid" => "f7a6e2c8-5931-7dda-1d29-19c0bfec6beb"
  ]
]
```
#### Statuses:
`0` - new message

`1` - in queue

`2` - message wait retry in queue

`3` - message send

`4` - message failed




### Get credit balances
Get balance for user account from config
```php
Turbosms::getBalance();
```

### Get message status
Get status for message id
```php
Turbosms::getMessageStatus('f7a6e2c8-5931-7dda-1d29-19c0bfec6beb');
```
and response:
```php
array:1 [▼
  0 => array:2 [▼
    "status" => 4
    "status_description" => "Сообщение доставлено получателю"
  ]
]
```
Statuses can be next:
```php
'0' => 'Сообщение с ID X не найдено',
'1' => 'Отправлено', *
'2' => 'В очереди', *
'3' => 'Сообщение передано в мобильную сеть', *
'4' => 'Сообщение доставлено получателю',
'5' => 'Истек срок сообщения',
'6' => 'Удалено оператором',
'7' => 'Не доставлено',
'8' => 'Сообщение доставлено на сервер', *
'9' => 'Отклонено оператором',
'10' => 'Неизвестный статус',
'11' => 'Ошибка, сообщение не отправлено',
'12' => 'Не достаточно кредитов на счете',
'13' => 'Отправка отменена',
'14' => 'Отправка приостановлена',
'15' => 'Удалено пользователем',
```
`*` - The status of the messages will change until the final status
