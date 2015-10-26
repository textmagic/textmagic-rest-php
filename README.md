## Synopsis

The TextMagic SMS API PHP wrapper can save you a lot of time, as it includes all the necessary API commands and tests. It only takes a few seconds to download it from GitHub and to install it into your own app or software. After installation, you’ll then be able to send text messages.

<!---
sms api for PHP
PHP api to send sms
PHP sms api
send sms from PHP
PHP send sms messages
PHP library send sms messages
-->

## Code Example
```
$client = new TextmagicRestClient('<USERNAME>', '<APIV2_TOKEN>');
$result = ' ';
try {
    $result = $client->messages->create(
        array(
            'text' => 'Hello from TextMagic PHP',
            'phones' => implode(', ', array('99900000'))
        )
    );
}
catch (\Exception $e) {
    if ($e instanceof RestException) {
        print '[ERROR] ' . $e->getMessage() . "\n";
        foreach ($e->getErrors() as $key => $value) {
            print '[' . $key . '] ' . implode(',', $value) . "\n";
        }
    } else {
        print '[ERROR] ' . $e->getMessage() . "\n";
    }
    return;
}
echo $result['id'];
```

## Installation Instructions
### Quick Installation

Run the following command to install the TextMagic PHP wrapper using composer: a package management system used to install and manage software packages written in PHP:
```
composer require textmagic/sdk
```
### Manual Installation

You can also install the TextMagic PHP wrapper from the GitHub repository using git. Run the following commands:
```
git clone git://github.com/textmagic/textmagic-rest-php.git
cd textmagic-rest-php
```

## Requirements
The PHP wrapper has the following requirements:
* PHP 5.2.1 or higher
* phpunit 4.5 or higher



## API Reference
* https://www.textmagic.com/docs/api/php/
* https://rest.textmagic.com/api/v2/doc


## License

The library is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
