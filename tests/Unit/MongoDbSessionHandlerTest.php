<?php

namespace ForFit\Session\Tests\Unit;

use ForFit\Session\MongoDbSessionHandler;
use ForFit\Session\Tests\TestCase;
use MongoDB\BSON\Binary;
use MongoDB\BSON\UTCDateTime;

class MongoDbSessionHandlerTest extends TestCase
{
    /**
     * @var MongoDbSessionHandler
     */
    protected $handler;
    
    /**
     * @var string
     */
    protected $sessionId;
    
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->handler = $this->app['session.store']->getHandler();
        $this->sessionId = md5(uniqid('test_session'));
    }
    
    /**
     * Test the open method
     */
    public function test_open_method(): void
    {
        $this->assertTrue($this->handler->open('path', 'name'));
    }
    
    /**
     * Test the close method
     */
    public function test_close_method(): void
    {
        $this->assertTrue($this->handler->close());
    }
    
    /**
     * Test reading non-existent session
     */
    public function test_read_non_existent_session(): void
    {
        $this->assertEquals('', $this->handler->read('non_existent_id'));
    }
    
    /**
     * Test write and read session
     */
    public function test_write_and_read_session(): void
    {
        $data = 'test_data_' . time();
        
        // Write session data
        $this->assertTrue($this->handler->write($this->sessionId, $data));
        
        // Read it back
        $readData = $this->handler->read($this->sessionId);
        
        $this->assertEquals($data, $readData);
        
        // Check database directly
        $session = $this->app['db']->table(config('session.table'))
            ->where('_id', $this->sessionId)
            ->first();
            
        $this->assertNotNull($session);
        $this->assertInstanceOf(Binary::class, $session['payload']);
        $this->assertInstanceOf(UTCDateTime::class, $session['expires_at']);
        $this->assertInstanceOf(UTCDateTime::class, $session['last_activity']);
    }
    
    /**
     * Test destroy session
     */
    public function test_destroy_session(): void
    {
        // First write a session
        $this->handler->write($this->sessionId, 'test_data');
        
        // Verify it exists
        $exists = $this->app['db']->table(config('session.table'))
            ->where('_id', $this->sessionId)
            ->exists();
        $this->assertTrue($exists);
        
        // Now destroy it
        $this->assertTrue($this->handler->destroy($this->sessionId));
        
        // Verify it's gone
        $exists = $this->app['db']->table(config('session.table'))
            ->where('_id', $this->sessionId)
            ->exists();
        $this->assertFalse($exists);
    }
    
    /**
     * Test garbage collection
     */
    public function test_garbage_collection(): void
    {
        // gc should return a truthy value as it's handled by MongoDB TTL index
        $this->assertNotFalse($this->handler->gc(100));
    }
} 