if (session_status() === PHP_SESSION_NONE){session_start();}

<!-- if (session_status() === PHP_SESSION_NONE) {
    session_start();
} -->

 <?php
 session_start();

 if (isset($_SESSION['user'])) {
 ?>
   logged in HTML and code here
 <?php

 } else {
   ?>
   Not logged in HTML and code here
   <?php
 }
