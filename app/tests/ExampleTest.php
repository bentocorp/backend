<?php


class ExampleTest extends TestCase {

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
    
	public function testHealthCheck()
	{
            // Given a healthcheck
            
            // When I call it
            $crawler = $this->client->request('GET', '/healthcheck');

            // Then I get ok
            $this->assertTrue($this->client->getResponse()->isOk());
	}

}
