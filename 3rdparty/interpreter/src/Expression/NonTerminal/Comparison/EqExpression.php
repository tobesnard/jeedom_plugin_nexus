<?php

namespace Interpreter\Expression\NonTerminal\Comparison;

use Interpreter\Context\RuleContext;
use Interpreter\Expression\AbstractExpression;
use Interpreter\Expression\Expression;

/**
 * Expression de comparaison d'égalité
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class EqExpression extends AbstractExpression
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

        // Conversion automatique des chaînes numériques
        if (is_string($leftValue) && is_numeric($leftValue)) {
            $leftValue = (float)$leftValue;
        }
        if (is_string($rightValue) && is_numeric($rightValue)) {
            $rightValue = (float)$rightValue;
        }

        return $leftValue == $rightValue;
    }

    public function getType(): string
    {
        return 'comparison_eq';
    }

    public function __toString(): string
    {
        return "({$this->left} == {$this->right})";
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
