<?php

namespace config;

class ConfigValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \config\exceptions\ConfigValidationException
     * @expectedExceptionMessage Configs validation error. Key [main.dbHost] not found
     */
    public function testConfigValidationException()
    {

        $configs = [
            'main' => ['dbHost'],
        ];

        $rules = [
            'main' => [
                'dbHost' => 'IPv4'
            ],
        ];

        $validator = new CompositeConfigValidator($rules);
        $validator->validate($configs);
    }

    /**
     * @expectedException \config\exceptions\ConfigValidationException
     * @expectedExceptionMessage Configs validation error. Key [dbClassImpl] not found
     */
    public function testConfigValidationExceptionKey()
    {
        $configs = [
            'main' => [
                'dbHost' => '127.0.0.1'
            ],
        ];

        $rules = [
            'dbClassImpl' => 'classExists',
            'main'        => [
                'dbHost' => 'IPv4'
            ],
        ];

        $validator = new CompositeConfigValidator($rules);
        $validator->validate($configs);
    }

    public function testValidation()
    {
        $configs = [
            'dbClassImpl' => '\microdb\PdoImpl',
            'main'        => [
                'dbHost' => '127.0.0.1',
                'dbPort' => '3306',
                'dbUser' => 'itsender_zh',
                'dbPass' => '123456',
                'dbName' => 'zh',
            ],
            'smpp'        => [
                'dbHost' => '127.0.0.1',
                'dbPort' => '3306',
                'dbUser' => 'itsender_zh',
                'dbPass' => '123456',
                'dbName' => 'zh',
            ],
        ];

        $rules = [
            'main'        => [
                'dbHost' => 'IPv4',
                'dbPort' => 'socketPort',
                'dbUser' => 'text',
                'dbPass' => 'text',
                'dbName' => 'text',
            ],
            'smpp'        => [
                'dbHost' => 'IPv4',
                'dbPort' => 'socketPort',
                'dbUser' => 'text',
                'dbPass' => 'text',
                'dbName' => 'text',
            ],
        ];

        $validator = new CompositeConfigValidator($rules);
        $this->assertTrue($validator->validate($configs));
    }
}