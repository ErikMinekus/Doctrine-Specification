<?php

namespace Rb\Specification\Doctrine\Condition;

use Rb\Specification\Doctrine\Exception\InvalidArgumentException;

class GreaterThan extends Comparison
{
    /**
     * @param string      $field
     * @param string      $value
     * @param string|null $dqlAlias
     *
     * @throws InvalidArgumentException
     */
    public function __construct($field, $value, $dqlAlias = null)
    {
        parent::__construct(self::GT, $field, $value, $dqlAlias);
    }
}
