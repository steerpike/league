<?php

use Mockery as m;

class FacadeStaticChampionTest extends PHPUnit_Framework_TestCase {

	protected $client;

	public function setUp()
	{
		$this->client = m::mock('LeagueWrap\Client');
		LeagueWrap\StaticApi::mount();
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
		             ->with('na/v1.1/champion', [
						'freeToPlay' => 'false',
						'api_key'    => 'key',
		             ])->once()
		             ->andReturn(file_get_contents('tests/Json/champion.json'));

		Api::setKey('key', $this->client);
		Champion::all();
		$this->assertTrue(Champion::get('blitzcrank') instanceof LeagueWrap\Response\Champion);
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

		Api::setKey('key', $this->client);
		$free = Champion::free();
		$this->assertEquals(10, count($free));
	}
}
