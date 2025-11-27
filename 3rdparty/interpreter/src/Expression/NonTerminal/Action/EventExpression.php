<?php

namespace Interpreter\Expression\NonTerminal\Action;

use Interpreter\Context\RuleContext;
use Interpreter\Expression\AbstractExpression;
use Interpreter\Expression\Expression;

/**
 * Expression d'action d'événement
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class EventExpression extends AbstractExpression
{
    private Expression $commandExpression;
    private $value;

    public function __construct(Expression $commandExpression, $value)
    {
        $this->commandExpression = $commandExpression;
        $this->value = $value;

        parent::__construct(['value' => $value]);
        $this->addChild($commandExpression);
    }

    public function interpret(RuleContext $context = null)
    {
        $context = $this->validateContext($context);

        $cmdService = $context->getCmdService();

        // Récupération de la commande à partir de l'expression
        if ($this->commandExpression instanceof \Interpreter\Expression\Terminal\CmdByStringExpression) {
            // Pour CmdByStringExpression, nous devons extraire la chaîne de commande
            // Utilisons la réflexion pour accéder à la propriété privée cmdString
            $reflection = new \ReflectionClass($this->commandExpression);
            $property = $reflection->getProperty('cmdString');
            $property->setAccessible(true);
            $cmdString = $property->getValue($this->commandExpression);

            return $cmdService->eventByString($cmdString, $this->value);

        } elseif ($this->commandExpression instanceof \Interpreter\Expression\Terminal\CmdByIdExpression) {
            // Pour CmdByIdExpression, nous devons extraire l'ID
            $reflection = new \ReflectionClass($this->commandExpression);
            $property = $reflection->getProperty('cmdId');
            $property->setAccessible(true);
            $cmdId = $property->getValue($this->commandExpression);

            return $cmdService->eventById($cmdId, $this->value);

        } else {
            // Fallback: utiliser le contexte pour déclencher l'événement
            $eventName = (string)$this->commandExpression;
            $context->triggerEvent($eventName, $this->value);

            return true;
        }
    }

    public function getType(): string
    {
        return 'action_event';
    }

    public function __toString(): string
    {
        return "event({$this->commandExpression}, {$this->value})";
    }

    protected function validateExpression(): bool
    {
        return $this->commandExpression->validate();
    }

    public function getCommandExpression(): Expression
    {
        return $this->commandExpression;
    }

    public function getValue()
    {
        return $this->value;
    }
}
