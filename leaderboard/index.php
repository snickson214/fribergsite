<?php
#  establish user identity
$valid = false;
include('../php/db.php');
if(isset($_COOKIE['iff-id'])) {
	$id = filter_var($_COOKIE['iff-id'], FILTER_SANITIZE_NUMBER_INT);
	$check_q = @mysqli_query($db, "SELECT * FROM users WHERE id=$id");
	if(mysqli_num_rows($check_q) > 0) {
		$user = mysqli_fetch_array($check_q);
		# confirm with social token
		if(isset($_COOKIE['iff-google']) and $_COOKIE['iff-google'] === $user['google']) $valid = true;
		if(isset($_COOKIE['iff-facebook']) and $_COOKIE['iff-facebook'] === $user['facebook']) $valid = true;
	} else {
		$id = 0;
	}
} else {
	$id = 0;
}

$title = 'Leaderboard';
$connected = true;
if($valid) {
	include('../php/head-auth.php');
} else {
	include('../php/head.php');
}
$mode = 'a';
if(isset($_GET['disp'])) {
	$mode = filter_var($_GET['disp'],FILTER_SANITIZE_SPECIAL_CHARS);
}

if(isset($_GET['season'])) {
	$season = filter_var($_GET['season'],FILTER_SANITIZE_SPECIAL_CHARS);
	$s = true;
} else {
	# figure out what season it is
	$now = time();
	$season_count = @mysqli_query($db, "SELECT * FROM seasons");
	if(mysqli_num_rows($season_count) == 1) {
		$season_data = mysqli_fetch_array($season_count);
		$season = $season_data['name'];
		$s = true;
	} elseif (mysqli_num_rows($season_count) == 0) {
		$s = false;
	} else {
		$season_finder = @mysqli_query($db, "SELECT * FROM seasons WHERE comp_start <= $now ORDER BY comp_start DESC");
		if(mysqli_num_rows($season_finder) == 0) {
			$s = false;
		} else {
			$season_data = mysqli_fetch_array($season_finder);
			$season = $season_data['name'];
			$s = true;
		}
	}
}

# Get the star thresholds
# Men's star thresholds
$star_thresh_m = array(0 => 0);
$star_data_grab = @mysqli_query($db, "SELECT * FROM globals WHERE name LIKE 'da\-m%'");
while($sdata = mysqli_fetch_array($star_data_grab)) {
	$star_thresh_m[$sdata['display']] = $sdata['value'];
}
# Women's star thresholds
$star_thresh_f = array(0 => 0);
$star_data_grab = @mysqli_query($db, "SELECT * FROM globals WHERE name LIKE 'da\-f%'");
while($sdata = mysqli_fetch_array($star_data_grab)) {
	$star_thresh_f[$sdata['display']] = $sdata['value'];
}
$stars = array("None", "Bronze", "Silver", "Gold", "Platinum", "Diamond");
$distrib_colors = array(
	'run' => 'warning', 'run_team' => 'warning',
	'rollerski' => 'info', 'bike' => 'info',
	'walk' => 'success', 'hike' => 'success',
	'paddle' => 'danger', 'swim' => 'danger',
	'strength' => 'default', 'sports' => 'default');
$distrib_stripes = array('run_team', 'bike','hike','swim','sports');
$display_names = array("run" => "running", "run_team" => "team running", "rollerski" => "rollerskiing", "walk" => "walking", "hike" => "hiking with packs", "bike" => "biking", "swim" => "swimming", "paddle" => "paddling, rowing or kayaking", "strength" => "strength or core training", "sports" => "aerobic sports");

# Function for printing stars
function star($number, $level) {
	$return = ' <abbr title="'.$level.' Distance Award recipient!">';
	for ($i = 0; $i < $number; $i++) {
		$return .= '<i class="fa fa-star"></i>';
	}
	$return .= "</abbr>";
	return $return;
}

function stars($miles, $gender) {
	global $star_thresh_f, $star_thresh_m, $stars;
	for($i = 5; $i >= 0; $i--) {
		if($gender == 0) {
			if ($miles >= $star_thresh_m[$i]) {
				echo star($i, $stars[$i]);
				break;
			}
		} elseif ($gender == 1) {
			if($miles >= $star_thresh_f[$i]) {
				echo star($i, $stars[$i]);
				break;
			}
		}
	}
}
?>
<div class="row">
	<div class="col-xs-12">
		<h2>Leaderboard</h2>
	</div>
</div>
<div class="row">
	<div class="col-md-3">
		<h4 class="hidden-xs hidden-sm">Select Season</h4>
		<div class="hidden-xs hidden-sm list-group">
			<?php
			$season_fetcher = @mysqli_query($db, "SELECT * FROM seasons ORDER BY display_name ASC");
			while($se = mysqli_fetch_array($season_fetcher)) {
				echo '<a href="?season='.$se['name'].'&disp='.$mode;
				echo '" class="list-group-item';
				if($se['name'] == $season) echo ' active';
				echo '">'.$se['display_name'].'</a>';
			}
			?>
		</div>
		<h4 class="hidden-xs hidden-sm">Select Division</h4>
		<div class="hidden-xs hidden-sm list-group">
			<?php
			$divisions = array('a' => "All Individuals", 'r' => "Running", 't' => "Teams", 'm' => "Men", 'w' => "Women", 1 => "Upperclassmen", 2 => "Underclassmen", 3 => "Middle School", 4 => "Staff / VIP", 5 => "Parents", 6 => "Alumni");
			foreach($divisions as $key => $value) {
				echo '<a href="?season='.$season.'&disp='.$key.'" class="list-group-item';
				if($mode == $key) echo ' active';
				echo '">'.$value.'</a>';
			}
			?>
		</div>
		<div class="row visible-xs visible-sm">
			<div class="col-xs-6">
				<h4>Select Season</h4>
				<form name="sel_season">
					<select name="SelSeason" onchange="document.location.href=document.sel_season.SelSeason.options[document.sel_season.SelSeason.selectedIndex].value" class="form-control">
					<?php
					$season_fetcher = @mysqli_query($db, "SELECT * FROM seasons ORDER BY display_name ASC");
					while($se = mysqli_fetch_array($season_fetcher)) {
						echo '<option value="/leaderboard?season='.$se['name'].'&disp='.$mode.'"';
						if($se['name'] == $season) echo ' selected';
						echo '>'.$se['display_name'].'</option>';
					}
					?>
					</select>
				</form>
			</div>
			<div class="col-xs-6">
				<h4>Select Division</h4>
				<form name="sel_disp">
					<select name="SelDisp" onchange="document.location.href=document.sel_disp.SelDisp.options[document.sel_disp.SelDisp.selectedIndex].value" class="form-control">
					<?php
					$divisions = array('a' => "All Individuals", 'r' => "Running", 't' => "Teams", 'm' => "Men", 'w' => "Women", 1 => "Upperclassmen", 2 => "Underclassmen", 3 => "Middle School", 4 => "Staff / VIP", 5 => "Parents", 6 => "Alumni");
					foreach($divisions as $key => $value) {
						echo '<option value="/leaderboard?season='.$season.'&disp='.$key.'"';
						if($mode == $key) echo ' selected';
						echo '>'.$value.'</option>';
					}
					?>
					</select>
				</form>
			</div>
		</div>
	</div>
	<div class="col-md-9">
		<?php
		# This page can be parameterized via GET parameters
		# season - restricts to a specific season id, if omitted, assumes most recent competition start
		# disp - restricts display of results to a specific group.
		#      0-5 gives division, t gives teams only, r gives running only
		# If we don't have a season, don't present data
		if($s) {
			# Figure out team number
			if(isset($id)) {
				$team_fetcher = @mysqli_query($db, "SELECT * FROM tMembers WHERE season='$season' AND user=$id");
				if(mysqli_num_rows($team_fetcher) == 0) {
					$team = 0;
				} else {
					$my_data = mysqli_fetch_array($team_fetcher);
					$team = $my_data['team'];
				}
			} else {
				$team = 0;
			}
			switch($mode) {
				case 1: # Upperclassmen
				case 2: # Underclassmen
				case 3: # Middle school
				case 4: # Staff
				case 5: # Parents
				case 6: # Alumni
					$data_fetcher = @mysqli_query($db, "SELECT * FROM tMembers WHERE season='$season' AND division='$mode' ORDER BY season_total DESC, season_run DESC, user ASC");
					if(mysqli_num_rows($data_fetcher) == 0) {
						echo '<h4>Nobody has registered for this division!</h4>';
					} else {
						echo '<table class="table table-hover">
						<thead>
						<tr>
						<th class="col-xs-1">Place</th>
						<th class="col-xs-3">User</th>
						<th class="col-xs-4">Team</th>';
						if($mode == 'r') {
							echo '<th class="col-xs-4 col-sm-2">Running</th>
							<th class="hidden-xs col-sm-2">Points</th>';
						} else {
							echo '<th class="col-xs-4 col-sm-2">Points</th>
							<th class="hidden-xs col-sm-2">Running</th>';
						}
						
						echo '</tr>
						</thead>
						<tbody>';
						$pl = 0;
						while($person = mysqli_fetch_array($data_fetcher)) {
							$pl++;
							echo '<tr class="show-team-members ';
							if($person['user'] == $id) echo ' success';
							if($person['team'] == $team and $team > 1 and $person['user'] != $id) echo ' info';
							echo '" data-target=".distrib-'.$person['user'].'"><td>'.$pl.'</td><td>';
							if($person['user'] == $id) echo '<abbr title="This is you!"><i class="fa fa-user"></i></abbr> ';
							if($person['team'] == $team and $team > 1 and $person['user'] != $id) echo '<abbr title="This is a teammate!"><i class="fa fa-users"></i></abbr> ';
							# Figure out their name!
							$pid = $person['user'];
							$the_user_fetcher = @mysqli_query($db, "SELECT * FROM users WHERE id=$pid");
							$the_user = mysqli_fetch_array($the_user_fetcher);
							echo $the_user['first'].' '.$the_user['last'];
							if($person['season_run'] >= 150) echo stars($person['season_run'], $the_user['gender']);
							
							# Figure out their team!
							$team_no = $person['team'];
							$team_fetcher = @mysqli_query($db, "SELECT * FROM tData WHERE id=$team_no");
							$team_data = mysqli_fetch_array($team_fetcher);
							echo '</td><td>'.$team_data['name'].'</td>';
							if($mode == 'r') {
								echo '<td>'.round($person['season_run'],3).'</td>
								<td class="hidden-xs">'.round($person['season_total'],3).'</td>';
							} else {
								echo '<td>'.round($person['season_total'],3).'</td>
								<td class="hidden-xs">'.round($person['season_run'],3).'</td>';
							}
							echo '</tr>';
							
							# Now display a progres bar of their points distribution. This is displayed on hover.
							if($person['season_total'] > 0) {
								echo '<tr class="show-team-members distrib-'.$person['user'].'" data-target=".distrib-'.$person['user'].'" style="display: none;">
								<td colspan="4">
								<div class="progress">';
								$progress_total = 0;
								foreach($distrib_colors as $type => $context) {
									if($person['stat_'.$type] > 0) {
										echo '<abbr title="'.round($person['stat_'.$type], 2).' points ('.floor(($person['stat_'.$type] / $person['season_total']) * 100).'% of total) for '.$display_names[$type].'"><div class="progress-bar progress-bar-'.$context.'" style="';
										echo 'width:'.floor(($person['stat_'.$type] / $person['season_total']) * 100).'%"></div></abbr>';
										$progress_total += floor(($person['stat_'.$type] / $person['season_total']) * 100);
									}
								}
								if($progress_total < 100) {
									echo '<div class="progress-bar progress-bar-'.$context.'" style="width:'.(100 - $progress_total).'%"></div>';
								}
								echo '
								</div>
								</td>
								<td class="hidden-xs"></td>
								</tr>';
							}
						}
						echo '</tbody>
						</table>';
					}
					break;
				case 'm':
				case 'w':
					if($mode == 'm') {
						$gNumber = 0;
					} else {
						$gNumber = 1;
					}
					$data_fetcher = @mysqli_query($db, "SELECT * FROM tMembers WHERE season='$season' ORDER BY season_total DESC, season_run DESC, user ASC");
					if(mysqli_num_rows($data_fetcher) == 0) {
						echo '<h4>Nobody has registered for this division!</h4>';
					} else {
						echo '<table class="table table-hover">
						<thead>
						<tr>
						<th class="col-xs-1">Place</th>
						<th class="col-xs-3">User</th>
						<th class="col-xs-4">Team</th>';
						if($mode == 'r') {
							echo '<th class="col-xs-4 col-sm-2">Running</th>
							<th class="hidden-xs col-sm-2">Points</th>';
						} else {
							echo '<th class="col-xs-4 col-sm-2">Points</th>
							<th class="hidden-xs col-sm-2">Running</th>';
						}
						
						echo '</tr>
						</thead>
						<tbody>';
						$pl = 0;
						while($person = mysqli_fetch_array($data_fetcher)) {
							$pid = $person['user'];
							$the_user_fetcher = @mysqli_query($db, "SELECT * FROM users WHERE id=$pid");
							$the_user = mysqli_fetch_array($the_user_fetcher);
							if($the_user['gender'] == $gNumber) {
								$pl++;
								echo '<tr class="show-team-members';
								if($person['user'] == $id) echo ' success';
								if($person['team'] == $team and $team > 1 and $person['user'] != $id) echo ' info';
								echo '" data-target=".distrib-'.$person['user'].'"><td>'.$pl.'</td><td>';
								if($person['user'] == $id) echo '<abbr title="This is you!"><i class="fa fa-user"></i></abbr> ';
								if($person['team'] == $team and $team > 1 and $person['user'] != $id) echo '<abbr title="This is a teammate!"><i class="fa fa-users"></i></abbr> ';
								echo $the_user['first'].' '.$the_user['last'];
								if($person['season_run'] >= 150) echo stars($person['season_run'], $the_user['gender']);
								
								# Figure out their team!
								$team_no = $person['team'];
								$team_fetcher = @mysqli_query($db, "SELECT * FROM tData WHERE id=$team_no");
								$team_data = mysqli_fetch_array($team_fetcher);
								echo '</td><td>'.$team_data['name'].'</td>';
								echo '<td>'.round($person['season_total'],3).'</td>
								<td class="hidden-xs">'.round($person['season_run'],3).'</td>';
								echo '</tr>';
								
								if($person['season_total'] > 0) {
									echo '<tr class="show-team-members distrib-'.$person['user'].'" data-target=".distrib-'.$person['user'].'" style="display: none;">
									<td colspan="4">
									<div class="progress">';
									$progress_total = 0;
									foreach($distrib_colors as $type => $context) {
										if($person['stat_'.$type] > 0) {
											echo '<abbr title="'.round($person['stat_'.$type], 2).' points ('.floor(($person['stat_'.$type] / $person['season_total']) * 100).'% of total) for '.$display_names[$type].'"><div class="progress-bar progress-bar-'.$context.'" style="';
											echo 'width:'.floor(($person['stat_'.$type] / $person['season_total']) * 100).'%"></div></abbr>';
											$progress_total += floor(($person['stat_'.$type] / $person['season_total']) * 100);
										}
									}
									if($progress_total < 100) {
										echo '<div class="progress-bar progress-bar-'.$context.'" style="width:'.(100 - $progress_total).'%"></div>';
									}
									echo '
									</div>
									</td>
									<td class="hidden-xs"></td>
									</tr>';
								}
							}
						}
						echo '</tbody>
						</table>';
					}
					break;
				case 't': # Display team scores
					$data_fetcher = @mysqli_query($db, "SELECT * FROM tData WHERE season='$season' ORDER BY total DESC, running DESC");
					if(mysqli_num_rows($data_fetcher) == 0) {
						echo '<h4>No teams have been configured for this season!</h4>';
					} else {
						echo '<h4>Hover over a team to see its members, and the Mini-Boards!</h4><p>If you\'re visiting localhost from your phone, just tap on the teams to toggle the visibility of the Mini-Boards.</p>
						<table class="table table-hover">
						<thead>
						<tr>
						<th class="col-xs-1">Place</th>
						<th class="col-xs-6 col-sm-4">Team</th>
						<th class="col-xs-3">Leader</th>
						<th class="col-xs-2">Points</th>
						<th class="hidden-xs col-sm-2">Running</th>
						</tr>
						</thead>
						<tbody>';
						$pl = 0;
						while($the_team = mysqli_fetch_array($data_fetcher)) {
							$pl++;
							$team_id = $the_team['id'];
							echo '<tr class="show-team-members ';
							if($team_id == $team) echo ' success';
							echo '" data-target=".team-'.$team_id.'"><td>'.$pl.'</td><td>'.$the_team['name'].'</td>
							<td>';
							$leader_id = $the_team['captain'];
							$leader_fetch = @mysqli_query($db, "SELECT * FROM users WHERE id=$leader_id");
							$leader = mysqli_fetch_array($leader_fetch);
							echo $leader['first'].' '.$leader['last'].'</td>
							<td>'.round($the_team['total'],4).'</td>
							<td class="hidden-xs">'.round($the_team['running'],4).'</td>
							</tr>';
							
							# And now for something completely different.
							# Show mini-boards for the teams :D
							
							# Grab members of the team
							$mpl = 0; # Member place - will be incremented
							$team_members = @mysqli_query($db, "SELECT * FROM tMembers WHERE team=$team_id ORDER BY season_total DESC, season_run DESC, user ASC");
							while($team_member = mysqli_fetch_array($team_members)) {
								$mpl++;
								echo '<tr class="show-team-members team-'.$team_id;
								if($team_member['user'] == $id) {
									echo ' success';
								} elseif ($team_member['user'] == $leader_id) {
									echo ' info';
								}
								echo '" data-target=".team-'.$team_id.'" style="display: none; font-size:12px;">
								<td class="col-xs-1" style="padding: 1px 8px">'.$mpl.'</td>';
								$person_id = $team_member['user'];
								$name_fetch = @mysqli_query($db, "SELECT id, first, last FROM users WHERE id=$person_id");
								$the_name = mysqli_fetch_array($name_fetch);
								echo '<td style="padding: 1px 8px">'.$the_name['first'].' '.$the_name['last'];
								if($team_member['user'] == $leader_id) echo ' <strong>Team Leader</strong>';
								echo '</td>
								<td style="padding: 1px 8px">'.$divisions[$team_member['division']].'</td>
								<td style="padding: 1px 8px">'.round($team_member['season_total'], 2).'</td>
								<td class="hidden-xs" style="padding: 1px 8px">'.round($team_member['season_run'], 2).'</td>';
								echo '</tr>';
							}
						}
						echo '</tbody>
							</table>';
					}
					break;
				case 'r': # Display sorted by running scores
				case 'a': # all individuals by points
				default: # Anything else, assume all individuals by points.
					$dftext = "SELECT * FROM tMembers WHERE season='$season' ORDER BY season_";
					if($_GET['disp'] == 'r') {
						$dftext .= "run DESC, season_total";
					} else {
						$dftext .= "total DESC, season_run";
					}
					$dftext .= " DESC, user ASC";
					$data_fetcher = @mysqli_query($db, $dftext);
					if(mysqli_num_rows($data_fetcher) == 0) {
						echo '<h4>Nobody has registered for this season!</h4>';
					} else {
						echo '<table class="table  table-hover">
						<thead>
						<tr>
						<th class="col-xs-1">Place</th>
						<th class="col-xs-3">User</th>
						<th class="col-xs-4">Team</th>';
						if($mode == 'r') {
							echo '<th class="col-xs-4 col-sm-2">Running</th>
							<th class="hidden-xs col-sm-2">Points</th>';
						} else {
							echo '<th class="col-xs-4 col-sm-2">Points</th>
							<th class="hidden-xs col-sm-2">Running</th>';
						}
						
						echo '</tr>
						</thead>
						<tbody>';
						$pl = 0;
						while($person = mysqli_fetch_array($data_fetcher)) {
							$pl++;
							echo '<tr class="show-team-members';
							if($person['user'] == $id) echo ' success';
							if($person['team'] == $team and $team > 1 and $person['user'] != $id) echo ' info';
							echo '" data-target=".distrib-'.$person['user'].'"><td>'.$pl.'</td><td>';
							if($person['user'] == $id) echo '<abbr title="This is you!"><i class="fa fa-user"></i></abbr> ';
							if($person['team'] == $team and $team > 1 and $person['user'] != $id) echo '<abbr title="This is a teammate!"><i class="fa fa-users"></i></abbr> ';
							# Figure out their name!
							$pid = $person['user'];
							$the_user_fetcher = @mysqli_query($db, "SELECT * FROM users WHERE id=$pid");
							$the_user = mysqli_fetch_array($the_user_fetcher);
							echo $the_user['first'].' '.$the_user['last'];
							if($person['season_run'] >= 150) echo stars($person['season_run'], $the_user['gender']);
							
							# Figure out their team!
							$team_no = $person['team'];
							$team_fetcher = @mysqli_query($db, "SELECT * FROM tData WHERE id=$team_no");
							$team_data = mysqli_fetch_array($team_fetcher);
							echo '</td><td>'.$team_data['name'].'</td>';
							if($mode == 'r') {
								echo '<td>'.round($person['season_run'],3).'</td>
								<td class="hidden-xs">'.round($person['season_total'],3).'</td>';
							} else {
								echo '<td>'.round($person['season_total'],3).'</td>
								<td class="hidden-xs">'.round($person['season_run'],3).'</td>';
							}
							echo '</tr>';
							
							if($person['season_total'] > 0) {
								echo '<tr class="show-team-members distrib-'.$person['user'].'" data-target=".distrib-'.$person['user'].'" style="display: none;">
								<td colspan="4">
								<div class="progress">';
								$progress_total = 0;
								foreach($distrib_colors as $type => $context) {
									if($person['stat_'.$type] > 0) {
										echo '<abbr title="'.round($person['stat_'.$type], 2).' points ('.floor(($person['stat_'.$type] / $person['season_total']) * 100).'% of total) for '.$display_names[$type].'"><div class="progress-bar progress-bar-'.$context.'" style="';
										echo 'width:'.floor(($person['stat_'.$type] / $person['season_total']) * 100).'%"></div></abbr>';
										$progress_total += floor(($person['stat_'.$type] / $person['season_total']) * 100);
									}
								}
								if($progress_total < 100) {
									echo '<div class="progress-bar progress-bar-'.$context.'" style="width:'.(100 - $progress_total).'%"></div>';
								}
								echo '
								</div>
								</td>
								<td class="hidden-xs"></td>
								</tr>';
							}
						}
						echo '</tbody>
						</table>';
					}
			}
		} else {
			echo '<h4>No seasons exist!</h4>
			<p>Ask your coach to make one in Settings &rarr; Seasons &rarr; Create Season.</p>';
		}
		?>
	</div>
</div>
<?php
include('../php/foot.php');
?>
<script>
$(".show-team-members").hover(function() {
	$($(this).data("target")).toggle();
})
</script>