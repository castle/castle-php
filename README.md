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

In the documents that you want to protect you can check whether a user is logged
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
profile = Userbin::current_profile();
?>

<h1>Welcome <?= profile['email'] ?></h1>
<p>This page is for your eyes only</p>
```

Alternatively you can use the `authorize` method to halt the execution and render a login page if the user is not logged in:

```php
<?php
// Put this at the top of the file, before any output has been sent.
Userbin::authorize();
?>

<p>If you see this, you're logged in</p>
```

## Forms

Once you have set up authentication it's time to choose among the different ways of integrating Userbin into your application.

### Ready-made forms

The easiest and fastest way to integrate login and signup forms is to use the Userbin Widget, which provides a set of ready-made views which can be customized to blend in with your current user interface. These views open up in a popup, and on mobile browsers they open up a new window tailored for smaller devices.

`rel` specifies action; possible options are `login` and `logout`.

```html
<a href="/account" rel="login">Log in</a>
<a href="/account" rel="signup">Sign up</a>
```

### Social buttons

Instead of signing up your users with a username and password, you can offer them to connect with a social identity like Facebook or LinkedIn. To use these button you must first configure your social identiy providers from the [dashboard](https://userbin.com/dashboard). It is also possible to connect a social identity to an already logged in user and the two accounts will be automatically linked.

`rel` determines action. If the user didn't exist before, it's created, otherwise it's logged in.

```html
<a href="/account" rel="connect-facebook">Connect with Facebook</a>
<a href="/account" rel="connect-linkedin">Connect with LinkedIn</a>
```

### Custom forms

The ready-made forms are fairly high level, so you might prefer to use Userbin with your own markup to get full control over looks and behavior.

If you create a form with `name` set to `login` or `signup`, the user will be sent to the URL specified by `action` after being successfully processed at Userbin.

Inputs with name `email` and `password` are processed, others are ignored.

If you add an element with the class `error-messages`, it will be automatically set to `display: block` and populated with a an error message when something goes wrong. So make sure to it is `display: hidden` by default.

```html
<form action="/account" name="signup">
  <span class="error-messages"></span>
  <div class="row">
    <label>E-mail</label>
    <input name="email" type="text"></input>
  </div>
  <div class="row">
    <label>Password</label>
    <input name="password" type="password"></input>
  </div>
  <button type="submit">Sign up</button>
</form>
```

### Log out

Clears the session and redirects the user to the specified URL.

```html
<a href="/" rel="logout">Log out</a>
```

Example
-------
Check out the `examples` directory or the [documentation](https://userbin.com/docs/php#example) for a complete example


Documentation
-------------
For complete documentation go to [userbin.com/docs](https://userbin.com/docs)
