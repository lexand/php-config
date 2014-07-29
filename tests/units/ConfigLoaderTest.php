<?php

namespace config;

use reflection\ReflectionInvoker;

class ConfigLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'dbClassImpl' => '\microdb\PdoImpl',
        'main'        => [
            'dbHost' => '192.168.34.205',
            'dbPort' => '3306',
            'dbUser' => 'zh',
            'dbPass' => '123456',
            'dbName' => 'itsender_zh',
        ],
        'smpp'        => [
            'dbHost' => '192.168.34.205',
            'dbPort' => '3306',
            'dbUser' => 'zh',
            'dbPass' => '123456',
            'dbName' => 'itsender_zh',
        ],
    ];

    /**
     * @expectedException \config\exceptions\ConfigFileNotFoundException
     */
    public function testFileNotFoundException()
    {
        $mock = $this->getMock('\config\ConfigLoader', ['getFilename'], [], '', false);

        /** @var ConfigLoader $mock */
        $mock->reload();
    }

    /**
     * @expectedException \config\exceptions\ConfigParseException
     */
    public function testConfigParseException()
    {
        $mock = $this->getMock('\config\ConfigLoader', ['getFilename', 'parseConfig'], [], '', false);

        /** @see \config\ConfigLoader::getFilename */
        $mock->expects($this->any())
             ->method('getFilename')
             ->will($this->returnValue(realpath(__DIR__ . '/../config/database-config.ini')));

        /** @see \config\ConfigLoader::parseConfig */
        $mock->expects($this->any())
             ->method('parseConfig')
             ->will($this->returnValue(false));

        /** @var ConfigLoader $mock */
        $mock->reload();
    }

    public function testParse()
    {
        $mock = $this->getMock('\config\ConfigLoader', ['getFilename'], [], '', false);

        /** @see \config\ConfigLoader::getFilename */
        $mock->expects($this->any())
             ->method('getFilename')
             ->will($this->returnValue(realpath(__DIR__ . '/../config/database-config.ini')));

        /** @see \config\ConfigLoader::parseConfig */
        $this->assertEquals($this->config, ReflectionInvoker::getInstance()->invoke($mock, 'parseConfig'));
    }

    public function testReload()
    {
        $mock = $this->getMock('\config\ConfigLoader', ['getFilename'], [], '', false);

        /** @see \config\ConfigLoader::getFilename */
        $mock->expects($this->any())
             ->method('getFilename')
             ->will($this->returnValue(realpath(__DIR__ . '/../config/database-config.ini')));

        /** @var ConfigLoader $mock */
        $mock->reload();

        $this->assertEquals('\microdb\PdoImpl', $mock['dbClassImpl']);
        $this->assertEquals('192.168.34.205', $mock['main']['dbHost']);
    }

    public function testConfigDefaultValue()
    {
        $defaults = [
            'dbClassImpl' => 'foo',
            'main' => [
                'dbName' => 'bar',
            ]
        ];

        $config = new ConfigLoader(realpath(__DIR__ . '/../config/test-default.ini'), $defaults);

        $this->assertEquals('foo', $config['dbClassImpl']);
        $this->assertEquals('bar', $config['main']['dbName']);
    }

    /**
     * @expectedException \LogicException
     */
    public function testOffsetSet()
    {
        $mock = $this->getMock('\config\ConfigLoader', ['getFilename'], [], '', false);
        $mock->expects($this->any())
             ->method('getFilename')
             ->will($this->returnValue(realpath(__DIR__ . '/../config/database-config.ini')));

        /** @var ConfigLoader $mock */
        $mock->reload();
        $mock['foo'] = 'bar';
    }

    public function testOffsetExists()
    {
        $mock = $this->getMock('\config\ConfigLoader', ['getFilename'], [], '', false);

        /** @see \config\ConfigLoader::getFilename */
        $mock->expects($this->any())
             ->method('getFilename')
             ->will($this->returnValue(realpath(__DIR__ . '/../config/database-config.ini')));

        /** @var ConfigLoader $mock */
        $mock->reload();

        $this->assertTrue(isset($mock['dbClassImpl']));
        $this->assertTrue(isset($mock['main']['dbHost']));
    }

    /**
     * @expectedException \LogicException
     */
    public function testOffsetUnset()
    {
        $mock = $this->getMock('\config\ConfigLoader', ['getFilename'], [], '', false);

        /** @see \config\ConfigLoader::getFilename */
        $mock->expects($this->any())
             ->method('getFilename')
             ->will($this->returnValue(realpath(__DIR__ . '/../config/database-config.ini')));

        /** @var ConfigLoader $mock */
        $mock->reload();

        unset($mock['dbClassImpl']);
    }
}