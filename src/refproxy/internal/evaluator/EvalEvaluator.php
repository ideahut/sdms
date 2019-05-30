<?php

namespace Ideahut\sdms\refproxy\internal\evaluator;

use \Ideahut\sdms\refproxy\internal\Evaluator;

class EvalEvaluator implements Evaluator
{

    /**
     * Evaluates code
     *
     * @param $code
     */
    public function evaluate($code)
    {
        eval($code);
    }
}
