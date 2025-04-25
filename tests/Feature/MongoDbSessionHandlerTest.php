<?php

namespace ForFit\Session\Tests\Feature;

use ForFit\Session\MongoDbSessionHandler;
use ForFit\Session\Tests\TestCase;
use Illuminate\Support\Facades\Session;
use MongoDB\BSON\Binary;

class MongoDbSessionHandlerTest extends TestCase
{
    /**
     * Test that the MongoDb session driver is properly registered
     */
    public function test_mongodb_driver_is_registered(): void
    {
        $this->assertEquals('mongodb', config('session.driver'));
        $this->assertInstanceOf(MongoDbSessionHandler::class, $this->app['session.store']->getHandler());
    }

    /**
     * Test storing and retrieving session data
     */
    public function test_session_store_and_retrieve(): void
    {
        // Set session data
        Session::put('key1', 'value1');
        Session::put('key2', ['nested' => 'value2']);
        
        // Force session to be stored
        Session::save();
        
        // Clear the session from memory to force a reload from storage
        Session::flush();
        
        // Start a new session to read from storage
        Session::start();
        
        // Check that the values were correctly retrieved
        $this->assertEquals('value1', Session::get('key1'));
        $this->assertEquals(['nested' => 'value2'], Session::get('key2'));
    }
    
    /**
     * Test session data is correctly saved in MongoDB
     */
    public function test_session_is_saved_in_mongodb(): void
    {
        // Generate a unique session ID
        $sessionId = md5(uniqid());
        
        // Create a session directly in the database
        $this->app['db']->table(config('session.table'))->insert([
            '_id' => $sessionId,
            'payload' => new Binary('a:1:{s:4:"test";s:5:"value";}', Binary::TYPE_OLD_BINARY),
            'last_activity' => new \MongoDB\BSON\UTCDateTime(now()->timestamp * 1000),
            'expires_at' => new \MongoDB\BSON\UTCDateTime((now()->addMinutes(config('session.lifetime'))->timestamp) * 1000),
        ]);
        
        // Manually retrieve session through the handler
        $handler = $this->app['session.store']->getHandler();
        $data = $handler->read($sessionId);
        
        // Decode the session data
        $sessionData = @unserialize($data);
        
        $this->assertEquals(['test' => 'value'], $sessionData);
    }
    
    /**
     * Test session destroy functionality
     */
    public function test_session_can_be_destroyed(): void
    {
        // Set a value in the session
        Session::put('key', 'value');
        $sessionId = Session::getId();
        
        // Force save to database
        Session::save();
        
        // Verify it exists in the database
        $count = $this->app['db']->table(config('session.table'))
            ->where('_id', $sessionId)
            ->count();
        $this->assertEquals(1, $count);
        
        // Get the handler and explicitly destroy the session
        $handler = $this->app['session.store']->getHandler();
        $handler->destroy($sessionId);
        
        // Allow a moment for the delete operation to complete
        usleep(100000);  // 100ms pause
        
        // Verify it was removed from the database
        $count = $this->app['db']->table(config('session.table'))
            ->where('_id', $sessionId)
            ->count();
        $this->assertEquals(0, $count);
    }
} 