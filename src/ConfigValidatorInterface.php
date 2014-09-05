<?php

namespace config;

interface ConfigValidatorInterface
{
    /**
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function validate($value, array $params = null);
}