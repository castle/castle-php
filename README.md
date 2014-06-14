[![Build Status](https://travis-ci.org/userbin/userbin-php.png)](https://travis-ci.org/userbin/userbin-php)
[![Code Climate](https://codeclimate.com/github/userbin/userbin-php.png)](https://codeclimate.com/github/userbin/userbin-php)

# PHP SDK for Userbin

This library's purpose is to provide an additional security layer to your application by adding multi-factor authentication, user activity monitoring, and real-time threat protection in a white-label package. Your users **do not** need to be signed up or registered for Userbin before using the service.

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

## Authorize

`authorize` is the key component of the Userbin API. It lets you tie a user to their actions and record properties about them. Whenever any suspious behaviour is detected or a user gets locked out, a call to `authorize` may throw an exception which needs to be handled by your application.

You’ll want to `authorize` a user with any relevant information as soon as the current user object is assigned in your application.

```php
<?php
  Userbin::authorize($user->id, array(
    "email" => $user->email
    "name"  => $user->name
  ));
?>
```

#### Arguments

The first argument is a locally unique identifier for the logged in user, commonly the `id` field. This is the identifier you'll use further on when querying the user.

The second argument is an array of properties you know about the user. See the User reference documentation for available fields and their meaning.

> Note that every call to `authorize` **does not** result in an HTTP request. Only the very first call, as well as expired session tokens result in a request. Session tokens expire every 5 minutes.

## Two-factor authentication

Two-factor authentication is available to your users out-of-the-box. By browsing to their Security Page, they're able to configure Google Authenticator and SMS settings, set up a backup phone number, and download their recovery codes.

The session token returned from `authorize` indicates if two-factor authentication is required from the user once your application asks for it. You can do this immediately after you've called `authorize`, or you can wait until later. You have complete control over what actions you when you want to require two-factor authentication, e.g. when logging in, changing account information, making a purchase etc.

### Step 1: Prompt the user

`Userbin::twoFactorAuthenticate()` acts as a gateway in your application. If the user has enabled two-factor authentication, this method will return the second factor that is used to authenticate. If SMS is used, this call will also send out an SMS to the user's registered phone number.

When `Userbin::twoFactorAuthenticate()` returns non-falsy value, you should display the appropriate form to the user, requesting their authentication code.

```php
<?php
  $factor = Userbin::twoFactorAuthenticate();

  switch ($factor) {
    case "authenticator":
      // show form for Google Authenticator
      break;
    case "sms":
      // show form for SMS
      break;
  }
?>
```

> Note that this call may return a factor more than once per session since Userbin continously scans for behaviour that would require another round of two-factor authentication, such as the user switching to another IP address or web browser.

### Step 2: Verify the code

The user enters the authentication code in the form and posts it to your handler. The last step is for your application to verify the code with Userbin by calling `twoFactorVerify`. The session token will get updated on a successful verification, so you'll need to update it in your local session or cookie.

`code` can be either a code from the Google Authenticator app, an SMS, or one of the user's recovery codes.

```php
<?php
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
?>
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

## Logging out

Whenever a user is logged out from your application, you should inform Userbin about this so that the active session is properly terminated. This prevents the session from being used further on.

```php
Userbin::logout();
```
