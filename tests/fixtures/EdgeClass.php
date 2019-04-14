<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2019-04
 */
class EdgeClass
{
    public function __construct()
    {
        throw new RuntimeException('testing inject');
    }
}
