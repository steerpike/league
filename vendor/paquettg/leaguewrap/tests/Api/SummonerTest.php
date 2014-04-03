<?php

use LeagueWrap\Api;
use Mockery as m;

class ApiSummonerTest extends PHPUnit_Framework_TestCase {

	protected $client;

	public function setUp()
	{
		$client       = m::mock('LeagueWrap\Client');
		$this->client = $client;
	}

	public function tearDown()
	{
		m::close();
	}

	public function testInfo()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/by-name/bakasan', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.bakasan.json'));

		$api = new Api('key', $this->client);
		$bakasan = $api->summoner()->info('bakasan');
		$this->assertEquals(74602, $bakasan->id);
	}

	public function testInfoId()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.74602.json'));

		$api = new Api('key', $this->client);
		$bakasan = $api->summoner()->info(74602);
		$this->assertEquals('bakasan', $bakasan->name);
	}

	public function testInfoMixed()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/by-name/bakasan', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.bakasan.json'));
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/7024,97235', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.7024,97235.json'));

		$api = new Api('key', $this->client);
		$summoners = $api->summoner()->info([
			'bakasan',
			7024,
			97235,
		]);
		$this->assertTrue(isset($summoners['IS1c2d27157a9df3f5aef47']));
	}

	public function testName()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602/name', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.name.74602.json'));

		$api = new Api('key', $this->client);
		$names = $api->summoner()->name(74602);
		$this->assertEquals('bakasan', $names[74602]);
	}

	public function testNameArray()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602,7024,97235/name', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.name.74602,7024,97235.json'));

		$api = new Api('key', $this->client);
		$names = $api->summoner()->name([
			74602,
			7024,
			97235,
		]);
		$this->assertEquals('Jigsaw', $names[7024]);
	}

	public function testRunes()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602/runes', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.runes.74602.json'));

		$api = new Api('key', $this->client);
		$runes = $api->summoner()->runePages(74602);
		$this->assertTrue($runes[0] instanceof LeagueWrap\Response\RunePage);
	}

	public function testRunesSummoner()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602/runes', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.runes.74602.json'));
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.74602.json'));

		$api = new Api('key', $this->client);
		$bakasan = $api->summoner()->info(74602);
		$api->summoner()->runePages($bakasan);
		$this->assertEquals('Greater Seal of Armor', $bakasan->runePage(1)->rune(15)->name);
	}

	public function testRunesSummonerArray()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/97235,7024/runes', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.runes.7024,97235.json'));
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/7024,97235', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.7024,97235.json'));

		$api = new Api('key', $this->client);
		$summoners = $api->summoner()->info([
			7024,
			97235,
		]);
		$api->summoner()->runePages($summoners);
		$this->assertEquals(0, count($summoners['IS1c2d27157a9df3f5aef47']->runePage(1)->runes));
	}

	public function testMasteries()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602/masteries', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.masteries.74602.json'));

		$api = new Api('key', $this->client);
		$runes = $api->summoner()->masteryPages(74602);
		$this->assertTrue($runes[0] instanceof LeagueWrap\Response\MasteryPage);
	}

	public function testMasteriesSummoner()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602/masteries', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.masteries.74602.json'));
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/74602', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.74602.json'));

		$api = new Api('key', $this->client);
		$bakasan = $api->summoner()->info(74602);
		$api->summoner()->masteryPages($bakasan);
		$this->assertEquals('Recovery', $bakasan->masteryPage(1)->talent(4212)->name);
	}

	public function testMasteriesSummonerArray()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/97235,7024/masteries', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.masteries.7024,97235.json'));
		$this->client->shouldReceive('request')
		             ->with('na/v1.3/summoner/7024,97235', [
						'api_key' => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/summoner.7024,97235.json'));

		$api = new Api('key', $this->client);
		$summoners = $api->summoner()->info([
			7024,
			97235,
		]);
		$api->summoner()->masteryPages($summoners);
		$this->assertEquals(0, count($summoners['IS1c2d27157a9df3f5aef47']->masteryPages));
	}
}
