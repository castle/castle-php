<?php

require 'lib/Castle.php';
$_SESSION = array();
//Castle::$apiBase ='http://lvh.me:3001';
Castle::setApiKey('zN7rLygEabq5134bPLXZeTJQP4RyWseu');
// Castle::setApiKey('secretkey');

Castle::kolle();

$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlzcyI6IjIiLCJzdWIiOiJpV2Zhamlta3ZYbmNwTWdnVTZUSnpVeWQ5czFSZ1pFdCIsImF1ZCI6Ijc2Mzc5NDcyNTY2NTk2NiIsImV4cCI6MTQwMTcyMjYwNywiaWF0IjoxNDAxNzIyNTk3LCJqdGkiOjN9.e30.X5BHgV42uXoZTXM6_EtI2dEDjA52PAO1P40sggKdrIw';

try {
  $user = Castle::login(2, array('email' => 'jan@banan.se'));
  Castle::authorize();
  $user->fetch();
}
catch (Exception $e) {
  print "Request Error: ";
  print_r($e->getMessage());
  print "\n";
}


//$user = new Castle_User("2");
//$user->sessions()->destroy();
