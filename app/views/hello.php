<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Laravel PHP Framework</title>
	<style>
		@import url(//fonts.googleapis.com/css?family=Lato:700);

		body {
			margin:0;
			font-family:'Lato', sans-serif;
			text-align:center;
			color: #999;
		}

		.welcome {
			width: 300px;
			height: 200px;
			position: absolute;
			left: 50%;
			top: 50%;
			margin-left: -150px;
			margin-top: -100px;
		}

		a, a:visited {
			text-decoration:none;
		}

		h1 {
			font-size: 32px;
			margin: 16px 0 0 0;
		}
	</style>
</head>
<body>
	<div class="welcome">
		<?php
		/*
		$client_id = '1055298325504-mbcmqtl7nev0pfo51njhfgegj7lc4mam.apps.googleusercontent.com';
		$client_secret = 'GwY7Idwz_u8U8cApK1zFMneH';
		$redirect_uri = 'http://localhost/league/public/';
		
		$client = new Google_Client();
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->setScopes(array("https://www.googleapis.com/auth/drive",
		"https://www.googleapis.com/auth/userinfo.email",
		"https://www.googleapis.com/auth/userinfo.profile"));
		$client->setAccessType('offline');
		*/
		$summoner = new Summoner();
		$client = $summoner->createClient();
		$service = new Google_Service_Drive($client);
		//Find out if we can get the user straight off the bat...
		try {
			$file = "brightcarvings@gmail.com.json";	
			if(file_exists($file)) {
				$tokens = file_get_contents($file);
				$client->setAccessToken($tokens);
				if($client->isAccessTokenExpired()) {
					echo "Token expired<br />";
					//$storage = $client->authenticate();
					$tokens = json_decode($client->getAccessToken());
					print_r($tokens);
					//$client->refreshToken($tokens->refresh_token);
					//file_put_contents('token.json',$tokens);
					echo "Refreshed token<br />";
				}
				$userInfoService = new Google_Service_Plus($client);
				$user = $userInfoService->people->get("me");
				$email = $user["emails"][0]["value"];
				print_r("Getting Token file for logged in authenticated user ".$email);
			} else {
				$authUrl = $client->createAuthUrl();
				print_r("Creating Auth URL ");
			}	
		} catch(Google_Exception $e) {
			print_r("Google error ".$e->getMessage());
		}
		if(isset($_GET['code'])) {
			$code = $_GET['code'];
			$auth = $client->authenticate($code);
			$email = $summoner->getEmail($client);
			$summoner->storeToken($email, $auth);
			print_r("Creating Token file ");
		} 
		//if yes, get their token
		
		//If no user, ask them to log in and approve access
		
		//Authenticate and store the token
		/*
		if(isset($_GET['code'])) {	
			file_put_contents('token.json',$client->authenticate($_GET['code']));
			$userInfoService = new Google_Service_Plus($client);
			try {
				$user = $userInfoService->people->get("me");
				$email = $user["emails"][0]["value"];
				print_r($email);
			} catch(Google_Exception $e) {
				print_r("An error occurred ".$e->getMessage());
			}
		}
		*/
		?>
		<h1>You have arrived.</h1>
		<div class="request">
	    <?php if (isset($authUrl)): ?>
	      <a class='login' href='<?php echo $authUrl; ?>'>Connect Me!</a>
	    <?php endif; ?>
	  </div>
	</div>
</body>
</html>
