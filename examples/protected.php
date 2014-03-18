<?php
require '../userbin.php';
Userbin::authorize();
$user = Userbin::current_profile();
?>
<html>
  <head>
    <title>Protected page</title>
  </head>
  <body>
    <p>Hello <?= $user['email'] ?>, this page is protected.</p>
  </body>
</html>