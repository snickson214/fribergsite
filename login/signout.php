<?php
# Sign out of iFF
$names = array();
foreach($_COOKIE as $name => $value) {
	setcookie($name, 0, 16, '/');
	setcookie($name, 0, 16, '/');
}
header("Location: localhost/");
?>