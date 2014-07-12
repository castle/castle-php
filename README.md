[![Latest Stable Version](https://poser.pugx.org/userbin/userbin-php/v/stable.svg)](https://packagist.org/packages/userbin/userbin-php) [![Total Downloads](https://poser.pugx.org/userbin/userbin-php/downloads.svg)](https://packagist.org/packages/userbin/userbin-php) [![License](https://poser.pugx.org/userbin/userbin-php/license.svg)](https://packagist.org/packages/userbin/userbin-php)

[![Build Status](https://travis-ci.org/userbin/userbin-php.png)](https://travis-ci.org/userbin/userbin-php)
[![Code Climate](https://codeclimate.com/github/userbin/userbin-php.png)](https://codeclimate.com/github/userbin/userbin-php)

# PHP SDK for Userbin

[Userbin](https://userbin.com) provides an additional security layer to your application by adding multi-factor authentication, user activity monitoring, and real-time threat protection in a white-label package. Your users **do not** need to be signed up or registered for Userbin before using the service. Also, Userbin requires **no modification of your current database schema** as it uses your local user IDs.

Your users can now easily activate two-factor authentication, configure the level of security in terms of monitoring and notifications and take action on suspicious behaviour. These settings are available as a per-user security settings page which is easily customized to fit your current layout.

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

To activate user monitoring you only need to **insert code at two places**: when the user is **fetched from the database and logged in** and when the user **logs out**:

```php

// the $user variable would be a representation of the currently logged in user.
// The call to authorize should be made right after the user has been fetched
// from the database and authenticated.

Userbin::authorize($user->id, array(
  'email' => $user->email,
  'name'  => $user->name
));
```

```php

// If a user is currently logged in (by calling `authorize`) it is logged out
// by simply calling `logout`. It is safe to call this method even if there is
// no user logged in.

Userbin::logout();

```

### Authorize (after logging in)

`authorize` is the key component of the Userbin API. It lets you tie a user to their actions and record properties about them. Whenever any suspious behaviour is detected or a user gets locked out, a call to `authorize` may throw an exception which needs to be handled by your application.

You’ll want to `authorize` a user with any relevant information as soon as the current user object is assigned in your application.

> Note that every call to `authorize` **does not** result in an HTTP request. Only the very first call, as well as expired session tokens result in a request. Session tokens expire every 5 minutes.

```php
Userbin::authorize($user->id, array(
  "email" => $user->email
  "name"  => $user->name
));
```

#### Arguments

`$userId`: The first argument is a locally unique identifier for the logged in user, commonly the `id` field. This is the identifier you'll use further on when querying the user.

`$userData`: The second argument is an array of properties you know about the user. See the User reference documentation for available fields and their meaning.

### Logging out

Whenever a user is logged out from your application, you should inform Userbin about this so that the active session is properly terminated. This prevents the session from being used further on.

```php
Userbin::logout();
```

This method doesn't take any arguments.

## Installing Two-factor authentication

Two-factor authentication is available to your users out-of-the-box. By browsing to their Security Page, they're able to configure Google Authenticator and SMS settings, set up a backup phone number, and download their recovery codes.

The session token returned from `authorize` indicates if two-factor authentication is required from the user once your application asks for it. You can do this immediately after you've called `authorize`, or you can wait until later. You have complete control over what actions you when you want to require two-factor authentication, e.g. when logging in, changing account information, making a purchase etc.

### Step 1: Prompt the user

`Userbin::twoFactorAuthenticate()` acts as a gateway in your application. If the user has enabled two-factor authentication, this method will return the second factor that is used to authenticate. If SMS is used, this call will also send out an SMS to the user's registered phone number.

When `Userbin::twoFactorAuthenticate()` returns non-falsy value, you should display the appropriate form to the user, requesting their authentication code.

```php
$factor = Userbin::twoFactorAuthenticate();

switch ($factor) {
  case "authenticator":
    // show form for Google Authenticator
    break;
  case "sms":
    // show form for SMS
    break;
}
```

> Note that this call may return a factor more than once per session since Userbin continously scans for behaviour that would require another round of two-factor authentication, such as the user switching to another IP address or web browser.

### Step 2: Verify the code

The user enters the authentication code in the form and posts it to your handler. The last step is for your application to verify the code with Userbin by calling `twoFactorVerify`. The session token will get updated on a successful verification, so you'll need to update it in your local session or cookie.

`code` can be either a code from the Google Authenticator app, an SMS, or one of the user's recovery codes.

```php
try {
  Userbin::twoFactorVerify($_POST["code"]);
} catch (Userbin_UserUnauthorizedError $e) {
  // invalid code, show the form again
} catch (Userbin_ForbiddenError $e) {
  // no tries remaining, log out
  Userbin::logout();
} catch (Userbin_ApiError $e) {
  // other error, log out
  Userbin::logout();
}
```

## Security settings page

Every user has access to their security settings, which is a hosted page on Userbin. Here users can configure two-factor authentication, revoke suspicious sessions and set up notifications. The security page can be customized to fit your current layout by going to the appearance settings in your Userbin dashboard.

**Important:** Since the generated URL contains a Userbin session token that needs to be up-to-date, it's crucial that you don't use this helper directly in your HTML, but instead create a new route where you redirect to the security page.

```php
<?php
  $securityURL = Userbin::securitySettingsUrl();
  header('Location: ' . $securityURL);
?>
```

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
| `Userbin_UnauthorizedError`      | Wrong Userbin API secret key |
| `Userbin_BadRequest`             | The request was invalid. For example if a challenge is created without the user having MFA enabled. |
| `Userbin_ForbiddenError`         | The user has entered the wrong code too many times and a new challenge has to be requested. |
| `Userbin_NotFoundError`          | The resource requestd was not found. For example if a session has been revoked. |
| `Userbin_UserUnauthorizedError`  | The user is locked or has entered the wrong credentials |
| `Userbin_InvalidParametersError` | One or more of the supplied parameters are incorrect. Check the response for more information. |

## REST Bindings

To facilitate working with the [Userbin REST API](https://secure.userbin.com) the library provides a set of models.

Examples:

```php
// List all users
$users = Userbin_User::all();

// find by ID
$user = Userbin_User::find(1);
$user->name = "Napoleon Dynamite";
$user->save();

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



