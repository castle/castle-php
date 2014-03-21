<?php
require_once('../userbin.php');
// Configure with your app id and api secret
// Sign up and get them at https://userbin.com
//Userbin::set_app_id();
//Userbin::set_api_secret();
Userbin::authenticate();
?>
<!DOCTYPE html>
  <head>
    <meta charset="utf-8">
    <title>Example usage of Userbin PHP</title>
    <link href="http://fonts.googleapis.com/css?family=Droid+Sans:400,700" rel="stylesheet" type="text/css" />
    <style type="text/css">
      body {
        text-align: center;
        color: #434951;
        background-color: #D2DBDC;
        font: normal normal 14px/1.81 'Droid Sans', sans-serif;
      }
      h1, p { text-shadow: 0 1px 1px #fff; }
      h1    { font-size: 30px; }
      a, a:visited, a:link { color: #E94946; }
      .ok   { color: #070; }
    </style>
  </head>
  <body>
    <?php if(Userbin::authenticated()): ?>
      <?php $user = Userbin::current_profile() ?>
      <h1>
        <span class="ok">&#x2713;</span>
        Congratulations!
      </h1>
      <p>
         You have successfully logged in as:
         <br>
         <b><?= $user['email'] ?></b>
      </p>
      <p>
        <a href="/" rel="logout">Log out</a>
      </p>
    <?php else: ?>
      <h1>
        Userbin example page
      </h1>
      <p>
        <a href="protected.php" rel="login">Login</a>
        or
        <a href="protected.php" rel="signup">Signup</a>
      </p>
    <?php endif; ?>
    <?= Userbin::javascript_include_tag(); ?>
  </body>
</html>