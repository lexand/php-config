<?php

namespace config;

use config\exceptions\ConfigValidationException;

/**
 * Class CompositeConfigValidator
 * <p/>
 *
 * Builtin validators:
 * <ul>
 *   <li>classExists</li>
 *   <li>IPv4</li>
 *   <li>socketPort</li>
 *   <li>text</li>
 * </ul>
 *
 * @package config
 */
class CompositeConfigValidator implements ConfigValidatorInterface
{
    /**
     * @var array
     */
    private $rules;

    /**
     * @var ConfigValidatorInterface[]
     */
    private $validators = [];

    public function  __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Add external validating function
     * <p/>
     * External validators has high priority
     *
     * @param string   $name
     * @param callable $func
     * @throws exceptions\ConfigValidationException
     */
    public function addValidatorFunc($name, callable $func)
    {
        if (isset($this->validators[$name]))
        {
            throw new ConfigValidationException("Validator '{$name}' already exists'");
        }
        $this->validators[$name] = new ValidatorFuncWrapper($func);
    }

    /**
     * Add external validator
     * <p/>
     * External validators has high priority
     *
     * @param string                   $name
     * @param ConfigValidatorInterface $valid
     * @throws exceptions\ConfigValidationException
     */
    public function addValidator($name, ConfigValidatorInterface $valid)
    {
        if (isset($this->validators[$name]))
        {
            throw new ConfigValidationException("Validator '{$name}' already exists'");
        }
        $this->validators[$name] = $valid;
    }


    /**
     * Validating config
     *
     * @param array $configs Validate whole config
     * @return bool
     * @throws ConfigValidationException
     */
    public function validate($configs)
    {
        foreach ($this->rules as $section => $params)
        {
            if (is_array($params))
            {
                foreach ($params as $paramName => $validator)
                {
                    if (!isset($configs[$section][$paramName]))
                    {
                        throw new ConfigValidationException("Configs validation error. Key [{$section}.{$paramName}] not found");
                    }

                    $paramValue = $configs[$section][$paramName];
                    $this->validateInternal($validator, $paramValue, "{$section}.{$paramName}");
                }

                continue;
            }


            if (!isset($configs[$section]))
            {
                throw new ConfigValidationException("Configs validation error. Key [$section] not found");
            }
            $this->validateInternal($params, $configs[$section], "{$section}");
        }

        return true;
    }

    /**
     * @param string $validatorName
     * @param mixed $value
     * @param string $path
     * @throws exceptions\ConfigValidationException
     */
    private function validateInternal($validatorName, $value, $path)
    {
        if (isset($this->validators[$validatorName]))
        {
            if (!$this->validators[$validatorName]->validate($value))
            {
                throw new ConfigValidationException("Configs validation error in {$validatorName} Validator with [{$path}]='{$value}' value");
            };
            return;
        }

        $method = $validatorName . 'Validator';
        if (method_exists($this, $method))
        {
            if (!call_user_func_array([$this, $method], [$value]))
            {
                throw new ConfigValidationException("Configs validation error in {$validatorName} Validator with [{$path}]='{$value}' value");
            }
            return;
        }

        throw new ConfigValidationException("Unknown validator {$validatorName}");
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @return bool
     */
    private function classExistValidator($value)
    {
        return class_exists($value);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @return bool
     */
    private function IPv4Validator($value)
    {
        return filter_var($value, \FILTER_VALIDATE_IP) !== false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @return bool
     */
    private function socketPortValidator($value)
    {
        // Max port number by http://tools.ietf.org/html/rfc6346
        return filter_var($value, \FILTER_VALIDATE_INT, ['options' => ['max_range' => 65535]]) !== false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @return int
     */
    private function textValidator($value)
    {
        return strlen($value);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @return bool
     */
    private function intValidator($value)
    {
        return filter_var($value, \FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) !== false;
    }
}