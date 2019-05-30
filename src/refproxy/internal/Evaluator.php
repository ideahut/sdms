<?php

namespace Ideahut\sdms\refproxy\internal;

interface Evaluator
{
    /**
     * Evaluates code
     *
     * @param $code
     */
    public function evaluate($code);
} 