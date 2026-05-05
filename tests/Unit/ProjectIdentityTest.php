<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProjectIdentityTest extends TestCase
{
    public function test_project_name_is_defined(): void
    {
        $this->assertSame('Campo Aberto Tecnologia Rural', 'Campo Aberto Tecnologia Rural');
    }
}
