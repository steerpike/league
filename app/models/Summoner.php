<?php

class Summoner {
	var $email;
	var $name;
	var $tokens;
	public function createClient() {
		$client_id = '1055298325504-mbcmqtl7nev0pfo51njhfgegj7lc4mam.apps.googleusercontent.com';
		$client_secret = 'GwY7Idwz_u8U8cApK1zFMneH';
		$redirect_uri = 'http://localhost/league/public/';
		
		$client = new Google_Client();
		$client->setClientId($client_id);
		$client->setClientSecret($client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->setScopes(array("https://www.googleapis.com/auth/drive",
		"https://www.googleapis.com/auth/userinfo.email",
		"https://www.googleapis.com/auth/userinfo.profile",
		"http://spreadsheets.google.com/feeds"));
		$client->setAccessType('offline');
		return $client;
	}
	public function storeToken($email, $token) {
		file_put_contents($email.'.json',$token);
	}
	public function getToken($email) {
		return file_get_contents($email.'.json');
	}
	public function getEmail($client) {
		$userInfoService = new Google_Service_Plus($client);
		$user = $userInfoService->people->get("me");
		$email = $user["emails"][0]["value"];
		return $email;
	}
	public function createSpreadsheet($client) {
		$service = new Google_Service_Drive($client);
		$file = new Google_Service_Drive_DriveFile();
		$file->setTitle('League of Legends Stats');
		$result = $service->files->insert(
			$file,
			array(
				'data'=>'',
				'mimeType'=>'application/vnd.google-apps.spreadsheet',
				'uploadType'=>'multipart'
			)
		);
		$tokens = json_decode($client->getAccessToken());
		$request = new Google\Spreadsheet\Request($tokens->access_token);
		$serviceRequest = new Google\Spreadsheet\DefaultServiceRequest($request);
		Google\Spreadsheet\ServiceRequestFactory::setInstance($serviceRequest);
		$spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
		$spreadsheetFeed = $spreadsheetService->getSpreadSheets();
		$spreadsheet = $spreadsheetFeed->getByTitle('League of Legends Stats');
		$worksheetFeed = $spreadsheet->getWorksheets();
		$worksheet = $worksheetFeed->getByTitle('Sheet 1');
		$headerArray = array("Date", "Game Type", "Champion", "Side", "Kills", "Deaths", "KDA", "Gold", "Minions");		
		$worksheet->createHeader($headerArray);
	}
}
