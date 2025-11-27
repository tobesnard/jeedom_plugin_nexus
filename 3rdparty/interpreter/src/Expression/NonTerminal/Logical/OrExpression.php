<?php

namespace Interpreter\Expression\NonTerminal\Logical;

use Interpreter\Context\RuleContext;
use Interpreter\Expression\AbstractExpression;
use Interpreter\Expression\Expression;

/**
 * Expression logique OR
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class OrExpression extends AbstractExpression
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

        $leftResult = $this->left->interpret($context);

        // Court-circuit : si la gauche est true, pas besoin d'évaluer la droite
        if ($leftResult) {
            return true;
        }

        $rightResult = $this->right->interpret($context);

        return (bool)$rightResult;
    }

    public function getType(): string
    {
        return 'logical_or';
    }

    public function __toString(): string
    {
        return "({$this->left} OR {$this->right})";
    }

    protected function validateExpression(): bool
    {
        return $this->left->validate() && $this->right->validate();
    }

    protected function optimizeExpression(): Expression
    {
        // Si une des expressions est littérale true, le résultat est true
        if ($this->left instanceof \Interpreter\Expression\Terminal\LiteralExpression) {
            $leftVal = $this->left->getValue();
            if ($leftVal === true) {
                return new \Interpreter\Expression\Terminal\LiteralExpression(true);
            }
            if ($leftVal === false) {
                return $this->right;
            }
        }

        if ($this->right instanceof \Interpreter\Expression\Terminal\LiteralExpression) {
            $rightVal = $this->right->getValue();
            if ($rightVal === true) {
                return new \Interpreter\Expression\Terminal\LiteralExpression(true);
            }
            if ($rightVal === false) {
                return $this->left;
            }
        }

        return $this;
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
