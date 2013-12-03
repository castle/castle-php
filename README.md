[![Build Status](https://travis-ci.org/userbin/userbin-php.png?branch=master)](https://travis-ci.org/userbin/userbin-php)

Installation
------------

Begin with signing up at [https://userbin.com](https://userbin.com) to obtain
your App ID and API secret.

Download `userbin.php` directly from here into your project

```bash
curl -O https://raw.github.com/userbin/userbin-php/master/userbin.php
```

Include `userbin.php` in your PHP script. If you're not using [output buffering](http://php.net/manual/en/book.outcontrol.php) this needs to be done before any output has been written since Userbin will modify headers.
```php
<?php
require_once('userbin.php');
// Configuration
Userbin::set_app_id('<15 digit app ID>');
Userbin::set_api_secret('<32 byte API secret>');

// Do the Userbin authentication process
Userbin::authenticate();
?>
```

Include the [userbin script](https://userbin.com/js/v0) at the bottom of your HTML

```php
      ...
      <?= Userbin::javascript_include_tag(); ?>
  </body>
</html>
```

Usage
-----
Place links to login/logout (described more in detail in the [Documentation](https://userbin.com/docs/javascript#markup)).

```html
<a class="ub-login">Login</a>
or
<a class="ub-signup">Signup</a>
```

If you want, you can specify where you want Userbin to direct your users after a successful login or logout. This is done by calling  the `Userbin::configure` method with an associative array containing the options:

```php
<?
// Additional configuration
Userbin::configure(array(
  'root_path' => '/',
  'protected_path' => '/dashboard'
));
?>
```

In the documents that you want to protect you can check wether a user is logged
with:

```php
<?php
// Put this at the top of your file
if (!Userbin::authenticated()) {
  // Some code to handle unauthorized access...
  http_response_code(401);
  die('You need to be logged in');
}

// User is logged in, show a secret page
$user = Userbin::user();
?>

<h1>Welcome <?= $user['email'] ?></h1>
<p>This page is for your eyes only</p>
<a class="ub-logout">Logout</a>
```

Alternatively you can use the `protect` method to halt the execution and render a login page if the user is not logged in:

```php
<?php
// Put this at the top of the file, before any output has been sent.
Userbin::protect();
?>

<p>If you see this, you're logged in</p>
```

Example
-------
Check out the `examples` directory or the [documentation](https://userbin.com/docs/php#example) for a complete example


Documentation
-------------
For complete documentation go to [userbin.com/docs](https://userbin.com/docs)
