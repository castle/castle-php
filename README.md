[![Latest Stable Version](https://poser.pugx.org/castle/castle-php/v/stable.svg)](https://packagist.org/packages/castle/castle-php) [![Total Downloads](https://poser.pugx.org/castle/castle-php/downloads.svg)](https://packagist.org/packages/castle/castle-php) [![License](https://poser.pugx.org/castle/castle-php/license.svg)](https://packagist.org/packages/castle/castle-php)

[![Build Status](https://travis-ci.org/castle/castle-php.png)](https://travis-ci.org/castle/castle-php)
[![Code Climate](https://codeclimate.com/github/castle/castle-php.png)](https://codeclimate.com/github/castle/castle-php)
[![Coverage Status](https://coveralls.io/repos/castle/castle-php/badge.png?branch=master)](https://coveralls.io/r/castle/castle-php?branch=master)

# PHP SDK for Castle

**[Castle](https://castle.io) adds real-time monitoring of your authentication stack, instantly notifying you and your users on potential account hijacks.**

## Getting started

Obtain the latest version of the Castle PHP bindings with:

```bash
git clone https://github.com/castle/castle-php
```

To get started, add the following to your PHP script:

```php
require_once("/path/to/castle-php/lib/Castle.php");
```
or

```php
require("/vendor/autoloader.php");
```
Configure the library with your Castle API secret.

```php
Castle\Castle::setApiKey('YOUR_API_SECRET');
```

## Include the JavaScript snippet

Before you add start using castle-php, make sure that you have included the Castle javascript snippet on every page in your app. See the [documentation](https://castle.io/docs/php/getting_started) for more information on how that's done.

## Tracking user behavior

`track` lets you record the security-related actions your users perform. The more actions you track, the more accurate Castle is in identifying fraudsters. We recommend that you at least track `$login.succeeded` and `$login.failed`:

**Track successful logins**
```php
Castle\Castle::track(array(
  'name' => '$login.succeeded',
  'user_id' => $user->id
));
```
> NOTE: `$user` refers to the currently logged in user.

**Track failed logins**
```php
Castle\Castle::track(array(
  'name' => '$login.failed',
  'details' => array(
    '$login' => 'johan@castle.io'
  )
));
```


### Supported events

Event names and detail properties that have semantic meaning are prefixed `$`, and we handle them in special ways. Here are all the events that Castle recognizes:

- `$login.succeeded`: Record when a user attempts to log in.
- `$login.failed`: Record when a user logs out.
- `$logout.succeeded`:  Record when a user logs out.
- `$registration.succeeded`: Capture account creation, both when a user signs up as well as when created manually by an administrator.
- `$registration.failed`: Record when an account failed to be created.
- `$challenge.requested`: Record when a user is prompted with additional verification, such as two-factor authentication or a captcha.
- `$challenge.succeeded`: Record when additional verification was successful.
- `$challenge.failed`: Record when additional verification failed.
- `$password_reset.requested`: An attempt was made to reset a user’s password.
- `$password_reset.succeeded`: The user completed all of the steps in the password reset process and the password was successfully reset. Password resets **do not** required knowledge of the current password.
- `$password_reset.failed`: Use to record when a user failed to reset their password.
- `$password_change.succeeded`: Use to record when a user changed their password. This event is only logged when users change their **own** password.
- `$password_change.failed`:  Use to record when a user failed to change their password.

### Supported detail properties

- `$login`: The submitted email or username from when the user attempted to log in or reset their password. Useful when there is no `user_id` available.

## Errors
Whenever something unexpected happens, an exception is thrown to indicate what went wrong.

| Name                                   | Description     |
|:---------------------------------------|:----------------|
| `Castle\Errors\CastleException`        | A generic error |
| `Castle\Errors\RequestError`           | A request failed. Probably due to a network error |
| `Castle\Errors\ApiError`               | An unexpected error for the Castle API |
| `Castle\Errors\SecurityError`          | The session signature doesn't match, either it has been tampered with or the Castle API key has been changed. |
| `Castle\Errors\ConfigurationError`     | The Castle secret API key has not been set |
| `Castle\Errors\UnauthorizedError`      | Wrong Castle API secret key |
| `Castle\Errors\ChallengeRequiredError` | You need to prompt the user for Two-step verification |
| `Castle\Errors\BadRequest`             | The request was invalid. For example if a challenge is created without the user having MFA enabled. |
| `Castle\Errors\ForbiddenError`         | The user has entered the wrong code too many times and a new challenge has to be requested. |
| `Castle\Errors\NotFoundError`          | The resource requestd was not found. For example if a session has been revoked. |
| `Castle\Errors\UserUnauthorizedError`  | The user is locked or has entered the wrong credentials |
| `Castle\Errors\InvalidParametersError` | One or more of the supplied parameters are incorrect. Check the response for more information. |




