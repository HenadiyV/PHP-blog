<?php

namespace App\Twig;

use Twig\TwigFunction;

class TwigFunctionFactory
{
    /**
     * @param ...$arguments
     * @return TwigFunction
     */
    public function create(...$arguments): TwigFunction{
        return new TwigFunction(...$arguments);
    }
}