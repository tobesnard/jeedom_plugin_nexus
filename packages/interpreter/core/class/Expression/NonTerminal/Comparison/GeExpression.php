<?php

namespace Nexus\Interpreter\Expression\NonTerminal\Comparison;

use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Expression\AbstractExpression;
use Nexus\Interpreter\Expression\Expression;

/**
 * Expression de comparaison "supérieur ou égal"
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class GeExpression extends AbstractExpression
{
    private Expression $left;
    private Expression $right;

    public function __construct(Expression $left, Expression $right)
    {
        $this->left = $left;
        $this->right = $right;

        parent::__construct();
        $this->addChild($left);
        $this->addChild($right);
    }

    public function interpret(RuleContext $context = null): bool
    {
        $context = $this->validateContext($context);

        $leftValue = $this->left->interpret($context);
        $rightValue = $this->right->interpret($context);

        if (is_string($leftValue) && is_numeric($leftValue)) {
            $leftValue = (float)$leftValue;
        }
        if (is_string($rightValue) && is_numeric($rightValue)) {
            $rightValue = (float)$rightValue;
        }

        return $leftValue >= $rightValue;
    }

    public function getType(): string
    {
        return 'comparison_ge';
    }

    public function __toString(): string
    {
        return "({$this->left} >= {$this->right})";
    }

    protected function validateExpression(): bool
    {
        return $this->left->validate() && $this->right->validate();
    }

    public function getLeft(): Expression
    {
        return $this->left;
    }

    public function getRight(): Expression
    {
        return $this->right;
    }
}
