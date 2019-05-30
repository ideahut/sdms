<?php

namespace Ideahut\sdms\refproxy\handler;


use \Ideahut\sdms\refproxy\InvocationHandler;

class BlankInvocationHandler implements InvocationHandler
{
    /**
     * @param object $proxy
     * @param string $method
     * @param mixed[] $args
     * @return mixed
     */
    function invoke($proxy, $method, $args)
    {
        // do nothing
        return $method == '__toString' ? '' : null;
    }
}