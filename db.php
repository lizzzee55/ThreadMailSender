<?php

$mysqlLink = mysqli_connect("home.aferon.com", "login", "");
mysqli_select_db($mysqlLink, "karma8_inc");
mysqli_query($mysqlLink, "SET NAMES utf8");

return $mysqlLink;
