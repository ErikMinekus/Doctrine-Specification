<?php

namespace Rb\Specification\Doctrine\Logic;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Rb\Specification\Doctrine\Condition;
use Rb\Specification\Doctrine\Exception\InvalidArgumentException;
use Rb\Specification\Doctrine\SpecificationInterface;

/**
 * Class Composite
 * @package Rb\Specification\Doctrine\Logic
 */
class Composite extends ArrayCollection implements SpecificationInterface
{
    const AND_X = 'andX';
    const OR_X = 'orX';

    /**
     * @var string[]
     */
    protected static $types = [self::OR_X, self::AND_X];

    /**
     * @var string
     */
    private $type;

    /**
     * @param string                   $type
     * @param SpecificationInterface[] $children
     */
    public function __construct($type, array $children = [])
    {
        $this->setType($type)
            ->setChildren($children);
    }

    /**
     * Set the type of comparison
     * @param  string                   $type
     * @return $this
     * @throws InvalidArgumentException
     */
    protected function setType($type)
    {
        if (!in_array($type, self::$types)) {
            $message = sprintf('"%s" is not a valid type! Valid types: %s', $type, implode(', ', self::$types));
            throw new InvalidArgumentException($message);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @param  SpecificationInterface   $value
     * @return bool
     * @throws InvalidArgumentException
     */
    public function add($value)
    {
        if (! $value instanceof SpecificationInterface) {
            throw new InvalidArgumentException(sprintf(
                '"%s" does not implement "%s"!',
                (is_object($value)) ? get_class($value) : $value,
                Condition\ModifierInterface::class
            ));
        }

        return parent::add($value);
    }

    /**
     * @param  SpecificationInterface[] $children
     * @return $this
     */
    protected function setChildren(array $children)
    {
        $this->clear();
        array_map([$this, 'add'], $children);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function modify(QueryBuilder $queryBuilder, $dqlAlias)
    {
        /**
         * @param SpecificationInterface $modifier
         * @return string|null
         */
        $match = function ($modifier) use ($queryBuilder, $dqlAlias) {
            return $modifier->modify($queryBuilder, $dqlAlias);
        };

        $result = array_filter(array_map($match, $this->toArray()));
        if (empty($result)) {
            return null;
        }

        return call_user_func_array(
            [$queryBuilder->expr(), $this->type],
            $result
        );
    }

    /**
     * Returns a boolean indicating whether or not this specification can support the given class
     * @param  mixed $value
     * @return bool
     */
    public function isSatisfiedBy($value)
    {
        /** @var SpecificationInterface $child */
        foreach ($this as $child) {
            if ($child->isSatisfiedBy($value)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
