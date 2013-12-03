<?php
require '../userbin.php';
Userbin::protect();
$user = Userbin::user();
?>
<html>
  <head>
    <title>Protected page</title>
  </head>
  <body>
    <p>Hello <?= $user['email'] ?>, this page is protected.</p>
  </body>
</html>