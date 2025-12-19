<?php

namespace Nexus\Interpreter\Expression\NonTerminal;

use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Expression\AbstractExpression;
use Nexus\Interpreter\Expression\Expression;

/**
 * Expression de séquence
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class SequenceExpression extends AbstractExpression
{
    /** @var Expression[] */
    private array $expressions;

    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;

        parent::__construct();
        foreach ($expressions as $expr) {
            $this->addChild($expr);
        }
    }

    public function interpret(RuleContext $context = null)
    {
        $context = $this->validateContext($context);

        $lastResult = null;
        foreach ($this->expressions as $expression) {
            $lastResult = $expression->interpret($context);
        }

        return $lastResult;
    }

    public function getType(): string
    {
        return 'sequence';
    }

    public function __toString(): string
    {
        return 'sequence(' . implode('; ', $this->expressions) . ')';
    }

    protected function validateExpression(): bool
    {
        foreach ($this->expressions as $expr) {
            if (! $expr->validate()) {
                return false;
            }
        }

        return ! empty($this->expressions);
    }

    public function getExpressions(): array
    {
        return $this->expressions;
    }
}
