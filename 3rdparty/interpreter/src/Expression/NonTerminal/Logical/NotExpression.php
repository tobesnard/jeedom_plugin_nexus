<?php

namespace Interpreter\Expression\NonTerminal\Logical;

use Interpreter\Context\RuleContext;
use Interpreter\Expression\AbstractExpression;
use Interpreter\Expression\Expression;

/**
 * Expression logique NOT
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class NotExpression extends AbstractExpression
{
    private Expression $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;

        parent::__construct();
        $this->addChild($expression);
    }

    public function interpret(RuleContext $context = null): bool
    {
        $context = $this->validateContext($context);

        $result = $this->expression->interpret($context);

        return ! $result;
    }

    public function getType(): string
    {
        return 'logical_not';
    }

    public function __toString(): string
    {
        return "NOT ({$this->expression})";
    }

    protected function validateExpression(): bool
    {
        return $this->expression->validate();
    }

    protected function optimizeExpression(): Expression
    {
        // Si l'expression est littérale, on peut évaluer directement
        if ($this->expression instanceof \Interpreter\Expression\Terminal\LiteralExpression) {
            $val = $this->expression->getValue();

            return new \Interpreter\Expression\Terminal\LiteralExpression(! $val);
        }

        // Si c'est déjà une négation, on peut simplifier (NOT NOT x = x)
        if ($this->expression instanceof NotExpression) {
            return $this->expression->getExpression();
        }

        return $this;
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }
}
