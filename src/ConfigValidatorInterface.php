<?php

namespace config;

interface ConfigValidatorInterface
{
    /**
     * Validating value
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value);
}