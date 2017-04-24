<?php

namespace MultilingualRoutingBundle\Tests\Unit\DependencyInjection;

use MultilingualRoutingBundle\DependencyInjection\MultilingualRoutingExtension;
use PHPUnit\Framework\TestCase;

class MultilingualRoutingExtensionTest extends TestCase
{
    /** @var MultilingualRoutingExtension */
    private $extension;

    protected function setUp()
    {
        $this->extension = new MultilingualRoutingExtension();
    }

    public function testHasCorrectAlias()
    {
        $this->assertEquals('multilingual_routing', $this->extension->getAlias());
    }
}
