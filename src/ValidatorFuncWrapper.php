<?php
/**
 * @author alex
 * @date 25.07.14
 * @time 21:42
 */

namespace config;

class ValidatorFuncWrapper implements ConfigValidatorInterface
{

    /**
     * @var callable
     */
    private $func;

    public function __construct(callable $func)
    {
        $this->func = $func;
    }

    /**
     * @param $values
     * @return bool
     */
    public function validate($values)
    {
        return call_user_func_array($this->func, [$values]);
    }


}