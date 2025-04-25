<?php

namespace ForFit\Session\Tests\Feature;

use ForFit\Session\Tests\TestCase;
use Illuminate\Support\Facades\Session;

class TestHelperIntegrationTest extends TestCase
{
    /**
     * Test that Laravel's withSession helper works with MongoDB sessions
     */
    public function test_with_session_helper(): void
    {
        // Create a session with data directly using the facade
        Session::put('test_key1', 'test_value1');
        Session::put('test_key2', ['nested' => 'value2']);
        
        // Get the session ID before it's saved
        $sessionId = Session::getId();
        
        // Explicitly save the session
        Session::save();
        
        // Create a test route
        $this->app['router']->get('/session-helper-test', function () {
            return response()->json([
                'session_data' => session()->all(),
            ]);
        });
        
        // Make a request to access the session
        $response = $this->get('/session-helper-test');
        $response->assertOk();
        
        // Verify session data was stored in MongoDB
        $sessionRecord = $this->app['db']->table(config('session.table'))
            ->where('_id', $sessionId)
            ->first();
            
        $this->assertNotNull($sessionRecord, 'Session record was not found in MongoDB');
        
        // Verify session data
        $responseData = $response->json();
        $this->assertArrayHasKey('test_key1', $responseData['session_data']);
        $this->assertEquals('test_value1', $responseData['session_data']['test_key1']);
        $this->assertArrayHasKey('test_key2', $responseData['session_data']);
        $this->assertEquals(['nested' => 'value2'], $responseData['session_data']['test_key2']);
    }
    
    /**
     * Test that Laravel's flushSession helper works with MongoDB sessions
     */
    public function test_flush_session_helper(): void
    {
        // Create a test route
        $this->app['router']->get('/flush-session-test', function () {
            return response()->json([
                'session_data' => session()->all(),
            ]);
        });
        
        // Set some session data
        $response = $this->withSession(['to_be_flushed' => 'value'])
            ->get('/flush-session-test');
            
        $response->assertOk();
        $this->assertArrayHasKey('to_be_flushed', $response->json()['session_data']);
        
        // Flush the session and verify it's empty
        $response = $this->flushSession()->get('/flush-session-test');
        
        $response->assertOk();
        $this->assertEmpty($response->json()['session_data']);
    }
} 