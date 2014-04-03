<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Search for a Summoner</title>
</head>
<body>
	<?php 
		echo Form::open(array('url' => '/search'));
		echo Form::select('region', array('oce' => 'Oceania', 'na' => 'North America'));
		echo Form::text('username'); 
		echo Form::close();
	?>	
</body>
</html>
	