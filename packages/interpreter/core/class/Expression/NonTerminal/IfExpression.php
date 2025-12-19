<?php

namespace Nexus\Interpreter\Expression\NonTerminal;

use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Expression\AbstractExpression;
use Nexus\Interpreter\Expression\Expression;

/**
 * Expression conditionnelle if-then
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class IfExpression extends AbstractExpression
{
    private Expression $condition;
    private Expression $thenExpression;
    private ?Expression $elseExpression;

    public function __construct(Expression $condition, Expression $thenExpression, Expression $elseExpression = null)
    {
        $this->condition = $condition;
        $this->thenExpression = $thenExpression;
        $this->elseExpression = $elseExpression;

        parent::__construct();
        $this->addChild($condition);
        $this->addChild($thenExpression);
        if ($elseExpression) {
            $this->addChild($elseExpression);
        }
    }

    public function interpret(RuleContext $context = null)
    {
        $context = $this->validateContext($context);

        $conditionResult = $this->condition->interpret($context);

        if ($conditionResult) {
            return $this->thenExpression->interpret($context);
        } elseif ($this->elseExpression) {
            return $this->elseExpression->interpret($context);
        }

        return null;
    }

    public function getType(): string
    {
        return 'control_if';
    }

    public function __toString(): string
    {
        $str = "if ({$this->condition}) then ({$this->thenExpression})";
        if ($this->elseExpression) {
            $str .= " else ({$this->elseExpression})";
        }

        return $str;
    }

    protected function validateExpression(): bool
    {
        $valid = $this->condition->validate() && $this->thenExpression->validate();
        if ($this->elseExpression) {
            $valid = $valid && $this->elseExpression->validate();
        }

        return $valid;
    }

    public function getCondition(): Expression
    {
        return $this->condition;
    }

    public function getThenExpression(): Expression
    {
        return $this->thenExpression;
    }

    public function getElseExpression(): ?Expression
    {
        return $this->elseExpression;
    }
}
