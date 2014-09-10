<?php

namespace config;

class CompositeConfigValidatorTest extends \PHPUnit_Framework_TestCase
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
     * @expectedExceptionMessage Configs validation error. Key [main.dbClassImpl] not found
     */
    public function testConfigValidationExceptionKey()
    {
        $configs = [
            'main' => [
                'dbHost' => '127.0.0.1'
            ],
        ];

        $rules = [
            'main' => [
                'dbClassImpl' => 'classExists',
                'dbHost'      => 'IPv4'
            ],
        ];

        $validator = new CompositeConfigValidator($rules);
        $validator->validate($configs);
    }

    public function testValidation()
    {
        $configs = [
            'main' => [
                'dbClassImpl' => '\microdb\PdoImpl',
                'dbHost'      => '127.0.0.1',
                'dbPort'      => '3306',
                'dbUser'      => 'itsender_zh',
                'dbPass'      => '123456',
                'dbName'      => 'zh',
            ],
            'smpp' => [
                'dbHost' => '127.0.0.1',
                'dbPort' => '3306',
                'dbUser' => 'itsender_zh',
                'dbPass' => '123456',
                'dbName' => 'zh',
            ],
        ];

        $rules = [
            'main' => [
                'dbHost' => 'IPv4',
                'dbPort' => 'socketPort',
                'dbUser' => 'text',
                'dbPass' => 'text',
                'dbName' => 'text',
            ],
            'smpp' => [
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

    public function testDirectoryValidator()
    {
        $configs = [
            'general' => [
                'logDir' => 'config'
            ]
        ];

        $rules = [
            'general' => [
                'logDir' => ['directory', 'baseDir' => __DIR__ . '/..']
            ]
        ];

        $validator = new CompositeConfigValidator($rules);
        $this->assertTrue($validator->validate($configs));
    }

    public function testClassConstantName()
    {
        $configs = [
            'general' => [
                'const' => 'SOME_CONST'
            ]
        ];
        $rules = [
            'general' => [
                'const' => ['classConstant', 'class' => '\config\A', 'check' => 'name']
            ]
        ];
        $validator = new CompositeConfigValidator($rules);
        $this->assertTrue($validator->validate($configs));
    }

    /**
     * @expectedException \config\exceptions\ConfigValidationException
     */
    public function testClassConstantAbsentName()
    {
        $configs = [
            'general' => [
                'const' => 'SOME_CONST1'
            ]
        ];
        $rules = [
            'general' => [
                'const' => ['classConstant', 'class' => '\config\A', 'check' => 'name']
            ]
        ];
        $validator = new CompositeConfigValidator($rules);
        $validator->validate($configs);
    }

}

class A
{
    const SOME_CONST = 1;
}