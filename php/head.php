<?php
date_default_timezone_set ("America/Chicago");

echo '<!DOCTYPE html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>'.$title.' - iFantasyFitness - Train Competitively</title>
	    <link href="//netdna.bootstrapcdn.com/bootswatch/3.1.1/simplex/bootstrap.min.css" rel="stylesheet">
	    <link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css" rel="stylesheet">
	    <link href="//localhost/css/bootstrap-datetimepicker.css" rel="stylesheet">
	</head>
    <body>
		<div class="navbar navbar-default" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="/">iFantasyFitness</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li><a href="/">Home</a></li>
						<li><a href="/leaderboard">Leaderboard</a></li>
						<li><a href="/rules">Rules</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="/login">Sign in</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="container">';
if(!$connected) include('db.php');
$announce_data = array();
$announcement_grab = @mysqli_query($db, "SELECT * FROM globals WHERE name LIKE 'announcement\_%'");
while($announcement_info = mysqli_fetch_array($announcement_grab)) {
	$ada_field = substr($announcement_info['name'],13);
	$announce_data[$ada_field] = $announcement_info['value'];
}
if(!empty($announce_data['text'])) {
	echo '<div class="row hidden-print">
	<div class="col-xs-12">
	<div class="well">'.$announce_data['text'];
	if(!empty($announce_data['link'])) {
		echo ' <a href="'.$announce_data['link'].'">';
		if(!empty($announce_data['link_text'])) {
			echo $announce_data['link_text'];
		} else {
			echo $announce_data['link'];
		}
		echo '</a>';
	}
	echo '</div></div></div>';
}
?>