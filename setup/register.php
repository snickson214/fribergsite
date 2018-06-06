<?php
if(!isset($_COOKIE['iff-id'])) header('Location: http://localhost');
include('../php/db.php');
$id = filter_var($_COOKIE['iff-id'], FILTER_SANITIZE_NUMBER_INT);
$now = time();

$check_q = @mysqli_query($db, "SELECT * FROM users WHERE id=$id");
if(mysqli_num_rows($check_q) > 0) {
	$user = mysqli_fetch_array($check_q);
	# confirm with social token
	$valid = false;
	if(isset($_COOKIE['iff-google']) and $_COOKIE['iff-google'] === $user['google']) $valid = true;
	if(isset($_COOKIE['iff-facebook']) and $_COOKIE['iff-facebook'] === $user['facebook']) $valid = true;
	if(!$valid) header('Location: http://localhost');
}

if(isset($_POST['submitted'])) {
	# first process profile changes
	$query = "UPDATE users SET ";
	if($_POST['first'] != $user['first']) {
		$first = filter_var($_POST['first'], FILTER_SANITIZE_SPECIAL_CHARS);
		$user['first'] = filter_var($_POST['first'], FILTER_SANITIZE_SPECIAL_CHARS);
		$query .= "first='$first', ";
	}
	if($_POST['last'] != $user['last']) {
		$last = filter_var($_POST['last'], FILTER_SANITIZE_SPECIAL_CHARS);
		$user['last'] = filter_var($_POST['last'], FILTER_SANITIZE_SPECIAL_CHARS);
		$query .= "last='$last', ";
	}
	if($_POST['gender'] != $user['gender']) {
		$gender = filter_var($_POST['gender'], FILTER_SANITIZE_NUMBER_INT);
		$user['gender'] = filter_var($_POST['gender'], FILTER_SANITIZE_NUMBER_INT);
		$query .= "gender=$gender, ";
	}
	if(!empty($user['first']) and !empty($user['last']) and ($user['gender'] <= 1)) {
		# Profile is complete.
		$user['profile'] = 0;
		$query .= "profile=0";
	} else {
		$user['profile'] = 1;
		$query .= "profile=1";
	}
	$query .= " WHERE id=$id";
	$profile_updater = @mysqli_query($db, $query);
	
	# then process team registration ONLY IF profile is OK and there are seasons for which to register.
	if($user['profile'] == 0 and $profile_updater and isset($_POST['season'])) {
		$slug = filter_var($_POST['season'], FILTER_SANITIZE_SPECIAL_CHARS);
		if($_POST['prediction'] > 0) $predict = filter_var($_POST['prediction'], FILTER_SANITIZE_NUMBER_INT);
		if($_POST['division'] >= 0) $division = filter_var($_POST['division'], FILTER_SANITIZE_NUMBER_INT);
		if($predict > 0 and $division >= 0 and $goal >= 0) {
			$registerer = @mysqli_query($db, "INSERT INTO tMembers (user, team, season, prediction, division) VALUES ($id, 1, '$slug', $predict, $division)");
			if($registerer) {
				setcookie('reg-welcome',$slug,$now+5,'/');
				header("Location: http://localhost/home");
			}
		} elseif ($predict == 0) {
			$no_goal = true;
		}
	} if ($user['profile'] == 0 and $profile_updater and !isset($_POST['season'])) {
		setcookie('reg-welcome',$slug,$now+5,'/');
		header("Location: http://localhost/home");
	}
}
$title = "Welcome";
include('../php/head-auth.php');
?>
<div class="row">
	<div class="col-xs-12">
		<form name="profile" class="form-horizontal" method="post">
			<h2>Welcome to iFantasyFitness. Please fill out all fields to set up your account.</h2>
			<h4>Account information</h4>
			<div class="form-group">
				<label class="col-xs-2 control-label">First name</label>
				<div class="col-xs-10">
					<input type="text" name="first" class="form-control" value="<?=$user['first']?>">
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-2 control-label">Last name</label>
				<div class="col-xs-10">
					<input type="text" name="last" class="form-control" value="<?=$user['last']?>">
				</div>
			</div>
			<h5><strong>Note:</strong> You need to use your real name, so please make sure it is correct. If you don't, your account may be blocked.</h5>
			<div class="form-group">
				<label class="col-xs-2 control-label">Gender</label>
				<div class="col-xs-10">
					<label class="radio-inline">
						<input type="radio" name="gender" value="0"> <i class="fa fa-male"></i> Male
					</label>
					<label class="radio-inline">
						<input type="radio" name="gender" value="1"> <i class="fa fa-female"></i> Female
					</label>
				</div>
			</div>
			<?php
			# Grab seasons available for registration
			$now = time();
			$season_grabber = @mysqli_query($db, "SELECT * FROM seasons WHERE reg_start <= $now AND reg_end >= $now");
			if(mysqli_num_rows($season_grabber) > 0) {
				# There are seasons available.
				echo '<h4>Season registration</h4>';
				if(mysqli_num_rows($season_grabber) == 1) {
					# Juse one season. :)
					$season = mysqli_fetch_array($season_grabber);
					echo '<p class="lead">You are registering for the '.$season['name'].' season.</p>
					<input type="hidden" name="season" value="'.$season['name'].'">';
				} else {
					echo '<div class="form-group">
					<label class="col-xs-6 control-label">Please choose a season to register for. You can register for more at any time.</label>
					<div class="col-xs-6">';
					while($season = mysqli_fetch_array($season_grabber)) {
						echo '<div class="radio">
						<label><input type="radio" name="season" value="'.$season['name'].'">'.$season['display_nane'].'</label></div>';
					}
					echo '</div>';
				}
				echo '<div class="form-group">
				<label class="col-xs-2 control-label">Prediction</label>
					<div class="col-xs-10">
						<p class="form-control-static">Please enter your prediction for <strong>how many TOTAL points you will score this season.</strong> 1 point = 1 mile of running, but there are other values for other types of activity. This will serve as your season point goal, and you can view it on your <a href="/home">home screen</a>. Captains and coaches will see this value when drafting you to a team, so please enter a realistic prediction.</p><br>
						<input type="number" name="prediction" class="form-control" min="0" max="5000">
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-2 control-label">Division</label>
					<div class="col-xs-10">
						<p class="form-control-static">Please select a division for the leaderboards.</p>
						<div class="radio">
							<label>
								<input type="radio" name="division" value="1"><strong>Upperclassmen</strong> - students entering junior or senior year
						</label>
					</div>
					<div class="radio">
						<label>
							<input type="radio" name="division" value="2">
							<strong>Underclassmen</strong> - students entering freshman or sophomore year
						</label>
					</div>
					<div class="radio">
						<label>
							<input type="radio" name="division" value="3">
							<strong>Middle School</strong> - students entering grades 6 through 8
						</label>
					</div>
					<div class="radio">
						<label>
							<input type="radio" name="division" value="4">
							<strong>Staff &amp; VIPs</strong> - Coaches, teachers, and VIP players
						</label>
					</div>
					<div class="radio">
						<label>
							<input type="radio" name="division" value="5">
							<strong>Parents</strong> of runners or skiers (who aren\'t SPPS staff)
						</label>
					</div>
					<div class="radio">
						<label>
							<input type="radio" name="division" value="6" >
							<strong>Alumni</strong> of Highland (who don\'t fall into a division above)
						</label>
					</div>
					<br>
					<input type="submit" class="btn btn-primary" value="Register">
					<input type="hidden" name="submitted" value="9000">
				</div>
			</div>';
			}
			?>
		</form>
	</div>
</div>
<?php
include('../php/foot.php');
?>
