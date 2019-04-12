<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-04
 */
class AlphaClass
{
    protected $object;

    protected $stack;

    public function __construct(ArrayAccess $arrayAccess, SplStack $stack)
    {
        $this->object = $arrayAccess;
        $this->stack = $stack;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getStack()
    {
        return $this->stack;
    }
}
