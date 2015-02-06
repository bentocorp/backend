<?php


class ExampleTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
    
	public function testHealthCheck()
	{
            $crawler = $this->client->request('GET', '/healthcheck');

            $this->assertTrue($this->client->getResponse()->isOk());
	}

}
