[![Latest Stable Version](https://poser.pugx.org/castle/castle-php/v/stable.svg)](https://packagist.org/packages/castle/castle-php) [![Total Downloads](https://poser.pugx.org/castle/castle-php/downloads.svg)](https://packagist.org/packages/castle/castle-php) [![License](https://poser.pugx.org/castle/castle-php/license.svg)](https://packagist.org/packages/castle/castle-php)

[![Build Status](https://travis-ci.org/castle/castle-php.png)](https://travis-ci.org/castle/castle-php)
[![Code Climate](https://codeclimate.com/github/castle/castle-php.png)](https://codeclimate.com/github/castle/castle-php)
[![Coverage Status](https://coveralls.io/repos/castle/castle-php/badge.png?branch=master)](https://coveralls.io/r/castle/castle-php?branch=master)

# PHP SDK for Castle

[Castle](https://castle.io) provides an additional security layer to your application by adding multi-factor authentication, user activity monitoring, and real-time threat protection in a white-label package. Your users **do not** need to be signed up or registered for Castle before using the service. Also, Castle requires **no modification of your current database schema** as it uses your local user IDs.

Your users can now easily activate two-factor authentication, configure the level of security in terms of monitoring and notifications and take action on suspicious behaviour.

## Getting started

Obtain the latest version of the Castle PHP bindings with:

```bash
git clone https://github.com/castle/castle-php
```

To get started, add the following to your PHP script:

```php
require_once("/path/to/castle-php/lib/Castle.php");
```

Configure the library with your Castle API secret.

```php
Castle::setApiKey('YOUR_API_SECRET');
```

## Installing user monitoring

To activate user monitoring you only need to **insert code at three places**: when the user is **logged in**, when the user is **loaded** and when the user **logs out**:

```php

// the $user variable would be a representation of the currently logged in user.
// The call to login should be made right after the user has been fetched
// from the database and authenticated.

Castle::login($user->id, array(
  'email' => $user->email,
  'name'  => $user->name
));
```

```php

// On every route that requires a logged in user, authorize should be called
// right after the user has been loaded and authorized locally in your app.

Castle::authorize();

```

```php

// If a user is currently logged in (by calling `login`) it is logged out
// by simply calling `logout`. It is safe to call this method even if there is
// no user logged in.

Castle::logout();

```

### Log in

You should call `login` as soon as the user has logged in to your application. Pass a unique user identifier, and an *optional* hash of user properties which are used when searching for users in your dashboard. This will create a [Session](https://api.castle.io/#POST--version-users--user_id-sessions---format-) resource and return a corresponding [session token](https://api.castle.io/#session-tokens) which is stored in the Castle client.

##### Arguments

`$userId`: The first argument is a locally unique identifier for the logged in user, commonly the `id` field. This is the identifier you'll use further on when querying the user.

`$userData`: The second argument is an array of properties you know about the user. See the User reference documentation for available fields and their meaning.


### Authorize (after logging in)

`authorize` is the key component of the Castle API. It lets you tie a user to their actions and record properties about them. Whenever any suspious behaviour is detected or a user gets locked out, a call to `authorize` may throw an exception which needs to be handled by your application.

You’ll want to `authorize` a user with any relevant information as soon as the current user object is assigned in your application.

> Note that every call to `authorize` **does not** result in an HTTP request. Only the very first call, as well as expired session tokens result in a request. Session tokens expire every 5 minutes.

```php
Castle::authorize();
```

### Logging out

Whenever a user is logged out from your application, you should inform Castle about this so that the active session is properly terminated. This prevents the session from being used further on.

```php
Castle::logout();
```

This method doesn't take any arguments.

## Installing Two-factor authentication

Using two-factor authentication involves two steps: **pairing** and **authenticating**.

### Pairing

Before your users can protect their account with two-factor authentication, they will need to pair their their preferred way of authenticating. The [Pairing API](https://api.castle.io/#pairings) lets users add, verify, and remove authentication channels. Only *verified* pairings are valid for authentication.

### TODO...

## Errors
Whenever something unexpected happens, an exception is thrown to indicate what went wrong.

| Name                             | Description     |
|:---------------------------------|:----------------|
| `Castle_Error`                  | A generic error |
| `Castle_RequestError`           | A request failed. Probably due to a network error |
| `Castle_ApiError`               | An unexpected error for the Castle API |
| `Castle_SecurityError`          | The session signature doesn't match, either it has been tampered with or the Castle API key has been changed. |
| `Castle_ConfigurationError`     | The Castle secret API key has not been set |
| `Castle_UnauthorizedError`      | Wrong Castle API secret key |
| `Castle_ChallengeRequiredError` | You need to prompt the user for Two-step verification |
| `Castle_BadRequest`             | The request was invalid. For example if a challenge is created without the user having MFA enabled. |
| `Castle_ForbiddenError`         | The user has entered the wrong code too many times and a new challenge has to be requested. |
| `Castle_NotFoundError`          | The resource requestd was not found. For example if a session has been revoked. |
| `Castle_UserUnauthorizedError`  | The user is locked or has entered the wrong credentials |
| `Castle_InvalidParametersError` | One or more of the supplied parameters are incorrect. Check the response for more information. |

## REST Bindings

To facilitate working with the [Castle REST API](https://api.castle.io) the library provides a set of models.

Examples:

```php
// List all users
$users = Castle_User::all();

// find by ID
$user = Castle_User::find(1);
$user->name = "Napoleon Dynamite";
$user->save();

// Create and verify a pairing
$pairing = $user->pairings()->create(array(
  'type' => 'authenticator'
));
$pairing->verify(array('response' => '123456'));

// Create new
$user = new Castle_User(array(
  name => "Napoleon Dynamite"
));
$user->save();
echo $user->id;

// List sessions
$sessions = $user->sessions()->fetch();

// Create a session for a user with local id 1
$user = new Castle_User(1);
$session = $user->sessions()->create();

// Delete session
$session->delete();

// Delete existing session with id 1
Castle_Session::destroy(1);
```

## Session store

By default Castle stores its session data in the super global `$_SESSION` variable. If you for some reason need to change this, it can be done by implementing the `Castle_iSessionStore` interface:

```php
class MyCustomStore implements iCastle_SessionStore
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

Then you'll need to tell Castle to user the new store:

```php
Castle::setSessionStore('MyCustomStore');
```



