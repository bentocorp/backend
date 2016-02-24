<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
            $unitTesting = true;

            $testEnvironment = 'testing';

            return require __DIR__.'/../../bootstrap/start.php';
    }
        
        
    protected function getIdempotentToken() 
    {
        return rand(1000,9999) . chr(rand(65,90));
    }

}
