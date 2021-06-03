[![Latest Stable Version](https://poser.pugx.org/castle/castle-php/v/stable.svg)](https://packagist.org/packages/castle/castle-php) [![Total Downloads](https://poser.pugx.org/castle/castle-php/downloads.svg)](https://packagist.org/packages/castle/castle-php) [![License](https://poser.pugx.org/castle/castle-php/license.svg)](https://packagist.org/packages/castle/castle-php)

[![Code Climate](https://codeclimate.com/github/castle/castle-php.png)](https://codeclimate.com/github/castle/castle-php)
[![Coverage Status](https://coveralls.io/repos/github/castle/castle-php/badge.svg?branch=fix%2Fcode-coverage)](https://coveralls.io/github/castle/castle-php?branch=fix%2Fcode-coverage)

# PHP SDK for Castle

**[Castle](https://castle.io) adds real-time monitoring of your authentication stack, instantly notifying you and your users on potential account hijacks.**

## Getting started

Obtain the latest version of the Castle PHP bindings with:

```bash
git clone --single-branch --branch master https://github.com/castle/castle-php
```

To get started, add the following to your PHP script:

```php
require_once("/path/to/castle-php/lib/Castle.php");
```

Configure the library with your Castle API secret.

```php
Castle::setApiKey('YOUR_API_SECRET');
```

## Optional Configurations

Set preferred connection and request timeouts:
valid options for setting are:
- `CURLOPT_CONNECTTIMEOUT`
- `CURLOPT_CONNECTTIMEOUT_MS`
- `CURLOPT_TIMEOUT`
- `CURLOPT_TIMEOUT_MS`

```php
Castle::setCurlOpts($curlOpts)
```

Set a specified list of request headers to include with event context (optional, not recommended):
```php
Castle::setUseAllowlist($headers)
```

## Request context

By default, Castle extracts all the necessary information, such as IP and request
headers, from the PHP globals in order to build and send the requests to the
Castle API. However in some cases you want to track data to Castle from a context
where these globals are not available, eg. when tracking async in a background
worker. In this case you can build the request context manually.

### Origin IP Address
By default, the SDK extracts the contextual client IP address from headers in the following priority:
1. `X-Forwarded-For`
2. `X-Real-Ip`
3. `REMOTE_ADDR`

If the true client IP address is not specified in the above headers, you can manually set the IP address like so:

```php
Castle_RequestContext['ip'] = '1.1.1.1'
$context = Castle_RequestContext::extractJson();
```

#### Example

```php
// While in a web request context, extract the information needed to send the
// request.
$context = Castle_RequestContext::extractJson();
$event = array(
	'user_id' => 1,
	'event' => '$login.succeeded'
);

// Now, push this data to your async worker, eg.
$castleWorker->perform($event, $context);
```

In your worker code (ie. non web environment):

```php
// Pass the context to track or authenticate
Castle::track(array(
  'event' => $event['event'],
  'user_id' => $event['user_id'],
  'context' => json_decode($context)
));
```

## Errors
Whenever something unexpected happens, an exception is thrown to indicate what went wrong.

| Name                             | Description     |
|:---------------------------------|:----------------|
| `Castle_Error`                  | A generic error |
| `Castle_RequestError`           | A request failed. Probably due to a network error |
| `Castle_ApiError`               | An unexpected error for the Castle API |
| `Castle_ConfigurationError`     | The Castle secret API key has not been set |
| `Castle_UnauthorizedError`      | Wrong Castle API secret key |
| `Castle_BadRequest`             | The request was invalid. For example if a challenge is created without the user having MFA enabled. |
| `Castle_ForbiddenError`         | The user has entered the wrong code too many times and a new challenge has to be requested. |
| `Castle_NotFoundError`          | The resource requestd was not found. For example if a session has been revoked. |
| `Castle_InvalidParametersError` | One or more of the supplied parameters are incorrect. Check the response for more information. |

## Running test suite
Execute `vendor/bin/phpunit test` to run the full test suite

## Documentation

[Official Castle docs](https://docs.castle.io)
