<?php
# Sign out of iFF
$names = array();
foreach($_COOKIE as $name => $value) {
	setcookie($name, 0, 16, '/', '.localhost');
	setcookie($name, 0, 16, '/', '.www.localhost');
}
header("Location: http://www.localhost/");
?>