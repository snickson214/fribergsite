<?php
# Must have provider.
if(!isset($_GET['provider'])) header("Location: http://www.localhost/login");

$provider = filter_var($_GET['provider'], FILTER_SANITIZE_STRING);
$time = filter_var($_GET['rq'],FILTER_SANITIZE_NUMBER_INT);

# For requests to be considered "authentic" they must have been initiated within the last 15 seconds. If not, redo
if(time()-15 > $time) header("Location: http://www.localhost/login");

include('../php/db.php');
$exp = time() + 90 * 24 * 60 * 60;

$uid = filter_var($_GET['uid'],FILTER_SANITIZE_NUMBER_INT);
if($provider == "twitter") {
	# Twitter is special so handle it differently
	$ue_check = @mysqli_query($db, "SELECT * FROM users WHERE twitter=$uid");
	if(mysqli_num_rows($ue_check) == 0) {
		$ue_insert = @mysqli_query($db, "INSERT INTO users (twitter) VALUES ($uid)");
		$id = mysqli_insert_id($db);
	} else {
		$ue_grab = mysqli_fetch_array($ue_check);
		$id = $ue_grab['id'];
		if(!empty($ue_grab['facebook'])) setcookie('iff-facebook',$ue_grab['facebook'],$exp,'/');
		if(!empty($ue_grab['google'])) setcookie('iff-facebook',$ue_grab['google'],$exp,'/');
	}
	setcookie('iff-twitter',$uid,$exp,'/');
} else {
	$first = filter_var($_GET['first'], FILTER_SANITIZE_SPECIAL_CHARS);
	$last = filter_var($_GET['last'], FILTER_SANITIZE_SPECIAL_CHARS);
	$ue_check = @mysqli_query($db, "SELECT * FROM users WHERE `$provider` LIKE $uid");
	if(mysqli_num_rows($ue_check) == 0) {
		$ue_name_check = @mysqli_query($db, "SELECT * FROM users WHERE LOWER(first)=LOWER('$first') AND LOWER(last)=LOWER('$last')");
		if(mysqli_num_rows($ue_name_check) == 0) {
			# Welcome
			$ue_insert = @mysqli_query($db, "INSERT INTO users (first, last, $provider) VALUES ('$first', '$last', $uid)");
			$id = mysqli_insert_id($db);
			
			if($provider == 'facebook') setcookie('iff-facebook',$uid,$exp,'/');
			if($provider == 'google') setcookie('iff-google',$uid,$exp,'/');
		} else {
			$ue_grab = mysqli_fetch_array($ue_name_check);
			
			$id = $ue_grab['id'];
			
			# Let's update the database
			$user_updater = @mysqli_query($db, "UPDATE users SET $provider=$uid WHERE id=$id");
			if(!empty($ue_grab['facebook'])) setcookie('iff-facebook',$ue_grab['facebook'],$exp,'/');
			if(!empty($ue_grab['twitter'])) setcookie('iff-twitter',$ue_grab['twitter'],$exp,'/');
			if(!empty($ue_grab['google'])) setcookie('iff-google',$ue_grab['google'],$exp,'/');
		}
	} else {
		$ue_grab = mysqli_fetch_array($ue_check);
		$id = $ue_grab['id'];
		if(!empty($ue_grab['facebook'])) setcookie('iff-facebook',$ue_grab['facebook'],$exp,'/');
		if(!empty($ue_grab['twitter'])) setcookie('iff-twitter',$ue_grab['twitter'],$exp,'/');
		if(!empty($ue_grab['google'])) setcookie('iff-google',$ue_grab['google'],$exp,'/');
	}
}

setcookie('iff-id',$id,$exp,'/');
if(isset($ue_insert) or $ue_grab['profile'] == 1) {
	header("Location: http://www.localhost/setup/register.php");
} else {
	header("Location: http://www.localhost/home");
}
?>
