<?php

namespace App\Tests\Controller;

use App\Controller\LaunchController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * 
 */
class LaunchTest extends WebTestCase
{
	var $client;

	function setUp(){
		$this->client = static::createClient();
	}

	public function testPing(){

        $this->client->request('GET', '/launch/ping');
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
	}

	public function testShowIngredients(){
		$this->client->request('GET', '/launch/ingredients');
		$ingredients = $this->client->getResponse()->getContent();
		$this->assertGreaterThanOrEqual(1, count(json_decode($ingredients)));
	}

	public function testFindIngredients(){
		$this->client->request('GET', '/launch/find?ingredient=bread');		
		$detail = $this->client->getResponse()->getContent();
		$detail = json_decode($detail);
		$this->assertEquals(1, count($detail));
	}

	public function testShowRecipes(){
		$this->client->request('GET', '/launch/recipes');		
		$recipe = $this->client->getResponse()->getContent();
		$recipe = json_decode($recipe);		
		$this->assertGreaterThanOrEqual(1, count($recipe));
	}

	public function testAvailableRecipe(){
		$this->client->request('GET', '/launch');		
		$available = $this->client->getResponse()->getContent();
		$available = json_decode($available);			
		$this->assertGreaterThanOrEqual(1, count($available));
		$this->assertTrue(in_array('Cheese',end($available)->ingredients));
	}

}