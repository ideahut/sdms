<?php

namespace Ideahut\sdms\refproxy;


interface ProxyClass
{
    /**
     * @param InvocationHandler $handler
     * @return object
     */
    function newInstance(InvocationHandler $handler);

    /**
     * @return \ReflectionClass
     */
    function getParentClass();
}