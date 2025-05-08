<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    public function testExample()
    {
        $attendance = new Attendance();
        
        // Example test: Check if a new Attendance instance is created
        $this->assertInstanceOf(Attendance::class, $attendance);
        
        // Add more tests here...
    }
}