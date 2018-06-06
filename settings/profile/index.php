<?php
if(!isset($_COOKIE['iff-id'])) header('Location: http://localhost');
$id = filter_var($_COOKIE['iff-id'], FILTER_SANITIZE_NUMBER_INT);

# Validate the user
include('../../php/db.php');
$check_q = @mysqli_query($db, "SELECT * FROM users WHERE id=$id");
if(mysqli_num_rows($check_q) > 0) {
	$user = mysqli_fetch_array($check_q);
	# confirm with social token
	$valid = false;
	if(isset($_COOKIE['iff-google']) and $_COOKIE['iff-google'] === $user['google']) $valid = true;
	if(isset($_COOKIE['iff-facebook']) and $_COOKIE['iff-facebook'] === $user['facebook']) $valid = true;
	if(!$valid) header('Location: http://localhost');
}

# Grab user's permission value
# 0 - normal user
# 1 - captain
# 2 - coach
# 3 - administrator (this option rarely used)
$perms = $user['permissions'];

# User is valid
$current_set = 'My profile';
$title = $current_set . ' - Settings';
$connected = true;
include('../../php/head-auth.php');

function settingsType($name, $link, $min_perm) {
	global $current_set;
	global $perms;
	if($perms >= $min_perm) {
		echo '<li';
		if($name == $current_set) echo ' class="active"';
		echo '><a href="/settings/'.$link.'">'.$name.'</a></li>';
	}
}

if($_POST['submitted'] == 'profile') {
	# process profile update requests
	$query = "UPDATE users SET ";
	if($_POST['gender'] != $user['gender']) {
		$gender = filter_var($_POST['gender'], FILTER_SANITIZE_NUMBER_INT);
		$user['gender'] = filter_var($_POST['gender'], FILTER_SANITIZE_NUMBER_INT);
		$query .= "gender=$gender, ";
	}
	if($user['gender'] <= 1) {
		# Profile is complete.
		$user['profile'] = 0;
		$query .= "profile=0";
	} else {
		$user['profile'] = 1;
		$query .= "profile=1";
	}
	$query .= " WHERE id=$id";
	$profile_updater = @mysqli_query($db, $query);
	if($profile_updater) $message = 'ok';
}
?>
<div class="row">
	<div class="col-xs-12">
		<?php
		if($user['profile'] == 1) echo '<div class="alert alert-info">
			<h4><i class="fa fa-info-circle"></i> Your profile is not ready yet.</h4>
			Please fill in all of the fields below, then click Save Changes to finish setting up your profile. You must do this before you can post records.</div>';
		?>
		<h2>Settings</h2>
	</div>
</div>
<div class="row">
	<div class="col-sm-3 col-md-2">
		<ul class="nav nav-pills nav-stacked">
			<?php
			settingsType('My profile','profile',0);
			settingsType('Goals', 'goals', 0);
			settingsType('My team', 'team', 1);
			settingsType('Seasons', 'seasons', 2);
			settingsType('People', 'people', 2);
			settingsType('Admin settings', 'admin', 2);
			?>
		</ul>
	</div>
	<div class="col-sm-9 col-md-10">
		<h2>My Profile</h2>
		<form name="profile" class="form-horizontal" method="post">
			<h4>Basics</h4>
			<div class="form-group">
				<label class="col-xs-2 control-label">First name</label>
				<div class="col-xs-10">
					<input type="text" name="first" class="form-control" value="<?=$user['first']?>" disabled>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-2 control-label">Last name</label>
				<div class="col-xs-10">
					<input type="text" name="last" class="form-control" value="<?=$user['last']?>" disabled>
				</div>
			</div>
			<div class="alert alert-info">
				If your name is misspelled or incorrect, please let a coach know.
			</div>
			<div class="form-group">
				<label class="col-xs-2 control-label">Gender</label>
				<div class="col-xs-10">
					<label class="radio-inline">
						<input type="radio" name="gender" value="0" <?php if($user['gender'] == 0) echo 'checked' ?>> <i class="fa fa-male"></i> Male
					</label>
					<label class="radio-inline">
						<input type="radio" name="gender" value="1" <?php if($user['gender'] == 1) echo 'checked' ?>> <i class="fa fa-female"></i> Female
					</label>
				</div>
			</div>
			<h4>Social Login</h4>
			<div class="form-group">
				<label class="col-xs-2 control-label"><i class="fa fa-google-plus"></i> Google</label>
				<div class="col-xs-10">
					<?php
					if(!empty($user['google'])) {
						echo '<p class="form-control-static"><span class="text-success"><i class="fa fa-check"></i> Connected!</span></p>';
					} else {
						// echo '<p class="form-control-static"><i class="fa fa-times"></i> Not connected! <a href="g-connect.php" target="_blank">Connect?</a></p>';
						echo '<p class="form-control-static">Connection coming soon!</p>';
					}
					?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-2 control-label"><i class="fa fa-facebook"></i> Facebook</label>
				<div class="col-xs-10">
					<?php
					if(!empty($user['facebook'])) {
						echo '<p class="form-control-static"><span class="text-success"><i class="fa fa-check"></i> Connected!</span></p>';
					} else {
						// echo '<p class="form-control-static"><i class="fa fa-times"></i> Not connected! <a href="f-connect.php" target="_blank">Connect?</a></p>';
						echo '<p class="form-control-static">Connection coming soon!</p>';
					}
					?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-2 control-label"><i class="fa fa-twitter"></i> Twitter</label>
				<div class="col-xs-10">
					<?php
					if(!empty($user['twitter'])) {
						echo '<p class="form-control-static"><span class="text-success"><i class="fa fa-check"></i> Connected!</span></p>';
					} else {
						// echo '<p class="form-control-static"><i class="fa fa-times"></i> Not connected! <a href="t-connect.php" target="_blank">Connect?</a></p>';
						echo '<p class="form-control-static">Connection coming soon!</p>';
					}
					?>
				</div>
			</div>
			<h4>Record Auto-Import</h4>
			<p>Set up Record Auto-Import <a href="/add/auto.php">here</a>.</p>
			<input type="hidden" name="submitted" value="profile">
			<input type="submit" class="btn btn-primary btn-block" value="Save settings">
		</form>
	</div>
</div>
<?php
include('../../php/foot.php');
?>
