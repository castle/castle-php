[![Latest Stable Version](https://poser.pugx.org/userbin/userbin-php/v/stable.svg)](https://packagist.org/packages/userbin/userbin-php) [![Total Downloads](https://poser.pugx.org/userbin/userbin-php/downloads.svg)](https://packagist.org/packages/userbin/userbin-php) [![License](https://poser.pugx.org/userbin/userbin-php/license.svg)](https://packagist.org/packages/userbin/userbin-php)

[![Build Status](https://travis-ci.org/userbin/userbin-php.png)](https://travis-ci.org/userbin/userbin-php)
[![Code Climate](https://codeclimate.com/github/userbin/userbin-php.png)](https://codeclimate.com/github/userbin/userbin-php)
[![Coverage Status](https://coveralls.io/repos/userbin/userbin-php/badge.png?branch=master)](https://coveralls.io/r/userbin/userbin-php?branch=master)

# PHP SDK for Userbin

[Userbin](https://userbin.com) provides an additional security layer to your application by adding multi-factor authentication, user activity monitoring, and real-time threat protection in a white-label package. Your users **do not** need to be signed up or registered for Userbin before using the service. Also, Userbin requires **no modification of your current database schema** as it uses your local user IDs.

Your users can now easily activate two-factor authentication, configure the level of security in terms of monitoring and notifications and take action on suspicious behaviour.

## Getting started

Obtain the latest version of the Userbin PHP bindings with:

```bash
git clone https://github.com/userbin/userbin-php
```

To get started, add the following to your PHP script:

```php
require_once("/path/to/userbin-php/lib/Userbin.php");
```

Configure the library with your Userbin API secret.

```php
Userbin::setApiKey('YOUR_API_SECRET');
```

## Installing user monitoring

To activate user monitoring you only need to **insert code at three places**: when the user is **logged in**, when the user is **loaded** and when the user **logs out**:

```php

// the $user variable would be a representation of the currently logged in user.
// The call to login should be made right after the user has been fetched
// from the database and authenticated.

Userbin::login($user->id, array(
  'email' => $user->email,
  'name'  => $user->name
));
```

```php

// On every route that requires a logged in user, authorize should be called
// right after the user has been loaded and authorized locally in your app.

Userbin::authorize();

```

```php

// If a user is currently logged in (by calling `login`) it is logged out
// by simply calling `logout`. It is safe to call this method even if there is
// no user logged in.

Userbin::logout();

```

### Log in

You should call `login` as soon as the user has logged in to your application. Pass a unique user identifier, and an *optional* hash of user properties which are used when searching for users in your dashboard. This will create a [Session](https://api.userbin.com/#POST--version-users--user_id-sessions---format-) resource and return a corresponding [session token](https://api.userbin.com/#session-tokens) which is stored in the Userbin client.

##### Arguments

`$userId`: The first argument is a locally unique identifier for the logged in user, commonly the `id` field. This is the identifier you'll use further on when querying the user.

`$userData`: The second argument is an array of properties you know about the user. See the User reference documentation for available fields and their meaning.


### Authorize (after logging in)

`authorize` is the key component of the Userbin API. It lets you tie a user to their actions and record properties about them. Whenever any suspious behaviour is detected or a user gets locked out, a call to `authorize` may throw an exception which needs to be handled by your application.

You’ll want to `authorize` a user with any relevant information as soon as the current user object is assigned in your application.

> Note that every call to `authorize` **does not** result in an HTTP request. Only the very first call, as well as expired session tokens result in a request. Session tokens expire every 5 minutes.

```php
Userbin::authorize();
```

### Logging out

Whenever a user is logged out from your application, you should inform Userbin about this so that the active session is properly terminated. This prevents the session from being used further on.

```php
Userbin::logout();
```

This method doesn't take any arguments.

## Installing Two-factor authentication

Using two-factor authentication involves two steps: **pairing** and **authenticating**.

### Pairing

Before your users can protect their account with two-factor authentication, they will need to pair their their preferred way of authenticating. The [Pairing API](https://api.userbin.com/#pairings) lets users add, verify, and remove authentication channels. Only *verified* pairings are valid for authentication.

### TODO...

## Errors
Whenever something unexpected happens, an exception is thrown to indicate what went wrong.

| Name                             | Description     |
|:---------------------------------|:----------------|
| `Userbin_Error`                  | A generic error |
| `Userbin_RequestError`           | A request failed. Probably due to a network error |
| `Userbin_ApiError`               | An unexpected error for the Userbin API |
| `Userbin_SecurityError`          | The session signature doesn't match, either it has been tampered with or the Userbin API key has been changed. |
| `Userbin_ConfigurationError`     | The Userbin secret API key has not been set |
| `Userbin_UnauthorizedError`      | Wrong Userbin API secret key |
| `Userbin_ChallengeRequiredError` | You need to prompt the user for Two-step verification |
| `Userbin_BadRequest`             | The request was invalid. For example if a challenge is created without the user having MFA enabled. |
| `Userbin_ForbiddenError`         | The user has entered the wrong code too many times and a new challenge has to be requested. |
| `Userbin_NotFoundError`          | The resource requestd was not found. For example if a session has been revoked. |
| `Userbin_UserUnauthorizedError`  | The user is locked or has entered the wrong credentials |
| `Userbin_InvalidParametersError` | One or more of the supplied parameters are incorrect. Check the response for more information. |

## REST Bindings

To facilitate working with the [Userbin REST API](https://api.userbin.com) the library provides a set of models.

Examples:

```php
// List all users
$users = Userbin_User::all();

// find by ID
$user = Userbin_User::find(1);
$user->name = "Napoleon Dynamite";
$user->save();

// Create and verify a pairing
$pairing = $user->pairings()->create(array(
  'type' => 'authenticator'
));
$pairing->verify(array('response' => '123456'));

// Create new
$user = new Userbin_User(array(
  name => "Napoleon Dynamite"
));
$user->save();
echo $user->id;

// List sessions
$sessions = $user->sessions()->fetch();

// Create a session for a user with local id 1
$user = new Userbin_User(1);
$session = $user->sessions()->create();

// Delete session
$session->delete();

// Delete existing session with id 1
Userbin_Session::destroy(1);
```

## Session store

By default Userbin stores its session data in the super global `$_SESSION` variable. If you for some reason need to change this, it can be done by implementing the `Userbin_iSessionStore` interface:

```php
class MyCustomStore implements iUserbin_SessionStore
{
  public function destroy()
  {
    // Code
  }
  public function read()
  {
    // Code
  }
  public function write($data)
  {
    // Code
  }
}
```

Then you'll need to tell Userbin to user the new store:

```php
Userbin::setSessionStore('MyCustomStore');
```



