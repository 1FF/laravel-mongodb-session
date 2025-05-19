<?php

namespace ForFit\Session\Tests\Feature;

use ForFit\Session\Tests\TestCase;

class HttpSessionTest extends TestCase
{
    /**
     * Test session storage using HTTP requests withSession helper
     */
    public function test_http_request_with_session(): void
    {
        // Create a test route that returns session data
        $this->app['router']->get('/test-session', function () {
            // Returns the session data for verification
            return response()->json([
                'session_data' => session()->all(),
            ]);
        });

        // Make request with session data
        $response = $this->withSession(['test_key' => 'test_value'])
            ->get('/test-session');

        $response->assertOk();
        $responseData = $response->json();

        // Verify that the session data was properly stored and retrieved
        $this->assertArrayHasKey('test_key', $responseData['session_data']);
        $this->assertEquals('test_value', $responseData['session_data']['test_key']);
    }

    /**
     * Test session persistence between requests
     */
    public function test_session_persists_between_requests(): void
    {
        // Create routes for testing
        $this->app['router']->get('/set-session', function () {
            session(['persisted_key' => 'persisted_value']);
            return response()->json(['status' => 'session_set']);
        });

        $this->app['router']->get('/get-session', function () {
            return response()->json([
                'session_data' => session()->all(),
            ]);
        });

        // First request to set the session
        $this->get('/set-session')->assertOk();

        // Second request to verify the session persists
        $response = $this->get('/get-session');
        $response->assertOk();

        $responseData = $response->json();
        $this->assertArrayHasKey('persisted_key', $responseData['session_data']);
        $this->assertEquals('persisted_value', $responseData['session_data']['persisted_key']);
    }

    /**
     * Test session exists during requests
     */
    public function test_session_data_in_request(): void
    {
        // Since we can't verify session in the request with `request()->hasSession()`,
        // we'll test with the session facade instead
        $this->app['router']->get('/session-test', function () {
            return response()->json([
                'has_session_facade' => session()->isStarted(),
                'session_data' => session()->all(),
            ]);
        });

        // Make request with session data
        $response = $this->withSession(['auth_test' => 'auth_value'])
            ->get('/session-test');

        $response->assertOk();
        $responseData = $response->json();

        // Verify session data is present
        $this->assertArrayHasKey('session_data', $responseData);
        $this->assertArrayHasKey('auth_test', $responseData['session_data']);
        $this->assertEquals('auth_value', $responseData['session_data']['auth_test']);
    }
}
