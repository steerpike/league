<?php

use LeagueWrap\Api;
use Mockery as m;

class ApiChampionTest extends PHPUnit_Framework_TestCase {

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

	public function testAll()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.1/champion', [
						'freeToPlay' => 'false',
						'api_key'    => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/champion.json'));

		$api = new Api('key', $this->client);
		$champion = $api->champion();
		$champion->all();
		$this->assertTrue($champion->blitzcrank instanceof LeagueWrap\Response\Champion);
	}

	public function testFree()
	{
		$this->client->shouldReceive('baseUrl')
		             ->once();
		$this->client->shouldReceive('request')
		             ->with('na/v1.1/champion', [
						'freeToPlay' => 'true',
						'api_key'    => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/champion.free.json'));

		$api  = new Api('key', $this->client);
		$free = $api->champion()->free();
		$this->assertEquals(10, count($free));
	}
}

