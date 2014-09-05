<?php

namespace config;

use config\exceptions\ConfigValidationException;

/**
 * Class CompositeConfigValidator
 * <p/>
 * ini-files Must have sections.
 * Builtin validators:
 * <ul>
 * <li>classExists</li>
 * <li>IPv4</li>
 * <li>socketPort</li>
 * <li>text</li>
 * <li>int</li>
 * <li>directory, support additional params ['baseDir' => '<base dir for relative path>']</li>
 * <li>file, support additional params ['baseDir' => '<base dir for relative path>']</li>
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
     * @param array $params Additional params
     * @throws exceptions\ConfigValidationException
     * @return bool
     */
    public function validate($configs, array $params = null)
    {
        foreach ($this->rules as $section => $sectionRules)
        {
            foreach ($sectionRules as $paramName => $validator)
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
        return true;
    }

    /**
     * @param string|array $validator
     * @param mixed        $value
     * @param string       $path
     * @throws exceptions\ConfigValidationException
     */
    private function validateInternal($validator, $value, $path)
    {
        list($valName, $valParam) = $this->validatorResolve($validator);

        if (isset($this->validators[$valName]))
        {
            if (!$this->validators[$valName]->validate($value, $valParam))
            {
                throw new ConfigValidationException("Configs validation error in {$validator} Validator with [{$path}]='{$value}' value");
            };
            return;
        }

        $method = $valName . 'Validator';
        if (method_exists($this, $method))
        {
            if (!call_user_func_array([$this, $method], [$value, $valParam]))
            {
                throw new ConfigValidationException("Configs validation error in {$valName} Validator with [{$path}]='{$value}' value");
            }
            return;
        }

        throw new ConfigValidationException("Unknown validator {$valName}");
    }


    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @param array  $params
     * @return bool
     */
    private function classExistValidator($value, array $params = null)
    {
        return class_exists($value);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @param array  $params
     * @return bool
     */
    private function IPv4Validator($value, array $params = null)
    {
        return filter_var($value, \FILTER_VALIDATE_IP) !== false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @param array  $params
     * @return bool
     */
    private function socketPortValidator($value, array $params = null)
    {
        // Max port number by http://tools.ietf.org/html/rfc6346
        return filter_var($value, \FILTER_VALIDATE_INT, ['options' => ['max_range' => 65535]]) !== false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @param array  $params
     * @return bool
     */
    private function textValidator($value, array $params = null)
    {
        return strlen($value);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param string $value
     * @param array  $params
     * @return bool
     */
    private function intValidator($value, array $params = null)
    {
        return filter_var($value, \FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) !== false;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param       $value
     * @param array $params <p>baseDir => <baseDir for relative path></p>
     * @return bool
     */
    private function directoryValidator($value, array $params = null)
    {
        if (strpos($value, '/') === 0)
        {
            $path = $value;
        }
        else
        {
            $path = realpath(rtrim($params['baseDir'], '/') . DIRECTORY_SEPARATOR . $value);
        }
        return ($path !== false) && file_exists($path) && is_dir($path);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * @param       $value
     * @param array $params <p>baseDir => <baseDir for relative path></p>
     * @return bool
     */
    private function fileValidator($value, array $params = null)
    {
        if (strpos($value, '/') === 0)
        {
            $path = $value;
        }
        else
        {
            $path = realpath(rtrim($params['baseDir'], '/') . DIRECTORY_SEPARATOR . $value);
        }
        return ($path !== false) && file_exists($path) && is_file($path);
    }

    private function validatorResolve($validator)
    {
        if (is_string($validator))
        {
            return [$validator, null];
        }
        elseif (is_array($validator))
        {
            $vName = array_shift($validator);
            return [$vName, $validator];
        }
        else
        {
            throw new \LogicException('Incorrect type of input value');
        }
    }
}