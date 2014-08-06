<?php

namespace config;

use config\exceptions\ConfigFileNotFoundException;
use config\exceptions\ConfigParseException;

/**
 * Load/parse/validate INI files
 * <p/>
 * Support default values.
 */
class ConfigLoader implements \ArrayAccess
{
    /**
     * @var string
     */
    private $filename;

    private $validator = false;

    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var array
     */
    private $defaults = [];

    /**
     * @param string                        $filename Full path to INI file
     * @param array                         $defaults Default values
     * @param ConfigValidatorInterface|null $validator Config validators
     */
    public function __construct($filename, array $defaults = [], ConfigValidatorInterface $validator = null)
    {
        $this->filename = $filename;
        $this->defaults = $defaults;
        $this->validator = $validator;
        $this->reload();
    }

    /**
     * Reload INI file and do validation again.
     * <p/>
     * If new configs pass validation, it will be applied to current instance, otherwise configs leaved unchanged.
     *
     * @throws ConfigFileNotFoundException
     * @throws ConfigParseException
     * @return void
     */
    public function reload()
    {
        $filename = $this->getFilename();
        if (!is_file($filename))
        {
            throw new ConfigFileNotFoundException("Config file '$filename' not found");
        }

        $configs = $this->parseConfig();
        if ($configs)
        {
            $configs = array_replace_recursive($this->defaults, $configs);
            if (is_object($this->validator))
            {
                $this->validator->validate($configs);
            }

            $this->configs = $configs;
        }
        else
        {
            throw new ConfigParseException("Error parse config '$filename'");
        }
    }

    /**
     * @return array|false
     */
    protected function parseConfig()
    {
        return parse_ini_file($this->getFilename(), true);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->configs[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->configs[$offset]) ? $this->configs[$offset] : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @throws \LogicException
     */
    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Config is read-only');
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @throws \LogicException
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException('Config is read-only');
    }
}