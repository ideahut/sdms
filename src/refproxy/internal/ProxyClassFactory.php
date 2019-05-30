<?php

namespace Ideahut\sdms\refproxy\internal;

use \Ideahut\sdms\refproxy\ProxyClass;

interface ProxyClassFactory
{
    /**
     * @param \ReflectionClass $reflectionClass
     * @param \ReflectionClass[] $interfaces
     * @return ProxyClass
     */
    public function get(\ReflectionClass $reflectionClass, $interfaces = []);
}