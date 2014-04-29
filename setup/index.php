<?php
# Must have provider.
if(!isset($_GET['provider'])) header("Location: http://www.ifantasyfitness.com/login");

$provider = $_GET['provider'];
$time = $_GET['rq'];

# For requests to be considered "authentic" they must have been initiated within the last 15 seconds. If not, redo
if(time()-15 > $time) header("Location: http://www.ifantasyfitness.com/login");

include('../php/db.php');
$exp = time() + 90 * 24 * 60 * 60;

$uid = $_GET['uid'];
if($provider == "twitter") {
	# Twitter is special so handle it differently
	$ue_check = @mysqli_query($db, "SELECT * FROM users WHERE twitter=$uid");
	if(mysqli_num_rows($ue_check) == 0) {
		$ue_insert = @mysqli_query($db, "INSERT INTO users (twitter) VALUES ($uid)");
		$id = mysqli_insert_id($db);
	} else {
		$ue_grab = mysqli_fetch_array($ue_check);
		$id = $ue_grab['id'];
		if(!empty($ue_grab['facebook'])) setcookie('iff-facebook',$ue_grab['facebook'],$exp,'/','.ifantasyfitness.com');
		if(!empty($ue_grab['google'])) setcookie('iff-facebook',$ue_grab['google'],$exp,'/','.ifantasyfitness.com');
	}
	setcookie('iff-twitter',$uid,$exp,'/','.ifantasyfitness.com');
} else {
	$first = $_GET['first'];
	$last = $_GET['last'];
	$ue_check = @mysqli_query($db, "SELECT * FROM users WHERE $provider=$uid");
	if(mysqli_num_rows($ue_check) == 0) {
		# check names, they might not have linked accounts yet.
		$ue_name_check = @mysqli_query($db, "SELECT * FROM users WHERE LOWER(first)=LOWER('$first') AND LOWER(last)=LOWER('$last')");
		if(mysqli_num_rows($ue_name_check) == 0) {
			# Welcome
			$ue_insert = @mysqli_query($db, "INSERT INTO users (first, last, $provider) VALUES ('$first', '$last', $uid)");
			$id = mysqli_insert_id($db);
		} else {
			$ue_grab = mysqli_fetch_array($ue_name_check);
			$id = $ue_grab['id'];
			if(!empty($ue_grab['facebook'])) setcookie('iff-facebook',$ue_grab['facebook'],$exp,'/','.ifantasyfitness.com');
			if(!empty($ue_grab['twitter'])) setcookie('iff-twitter',$ue_grab['twitter'],$exp,'/','.ifantasyfitness.com');
			if(!empty($ue_grab['google'])) setcookie('iff-google',$ue_grab['google'],$exp,'/','.ifantasyfitness.com');
		}
	} else {
		$ue_grab = mysqli_fetch_array($ue_check);
		$id = $ue_grab['id'];
		if(!empty($ue_grab['facebook'])) setcookie('iff-facebook',$ue_grab['facebook'],$exp,'/','.ifantasyfitness.com');
		if(!empty($ue_grab['twitter'])) setcookie('iff-twitter',$ue_grab['twitter'],$exp,'/','.ifantasyfitness.com');
		if(!empty($ue_grab['google'])) setcookie('iff-google',$ue_grab['google'],$exp,'/','.ifantasyfitness.com');
	}
}

setcookie('iff-id',$id,$exp,'/','.ifantasyfitness.com');
header("Location: http://www.ifantasyfitness.com/home");
?>