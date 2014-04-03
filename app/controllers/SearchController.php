<?php
use LeagueWrap\Api;

class SearchController extends BaseController {

	public function searchSummoner()
	{
		$data = Input::all();
		if(Input::has("username") && Input::has("region")) {
			$name = Input::get("username");
			$region = Input::get("region");
			$api = new Api("8a08647e-9a3b-4fe0-9f7c-40ea0c6cab2c");
			$api->setRegion($region);
			$summoner = $api->summoner();
			$user = $summoner->info($name);
			echo $user->id;
		}
		$summoner = new Summoner();
		$client = $summoner->createClient();
		$file = "brightcarvings@gmail.com.json";
		$tokens = file_get_contents($file);
		$client->setAccessToken($tokens);
		$tokens = json_decode($client->getAccessToken());
		if($client->isAccessTokenExpired()) {
			$client->refreshToken($tokens->refresh_token);
		}
		$summoner->createSpreadsheet($client);
		return View::make('search');
	}

}