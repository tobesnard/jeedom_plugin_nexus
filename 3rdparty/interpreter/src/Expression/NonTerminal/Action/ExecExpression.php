<?php

namespace Interpreter\Expression\NonTerminal\Action;

use Interpreter\Context\RuleContext;
use Interpreter\Expression\AbstractExpression;
use Interpreter\Expression\Expression;
use Interpreter\Expression\Terminal\CmdByIdExpression;
use Interpreter\Expression\Terminal\CmdByStringExpression;

/**
 * Expression d'action d'exécution
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class ExecExpression extends AbstractExpression
{
    private Expression $cmdExpression;
    private array $options;

    public function __construct(Expression $cmdExpression, array $options = [])
    {
        $this->cmdExpression = $cmdExpression;
        $this->options = $options;

        parent::__construct(['options' => $options]);
        $this->addChild($cmdExpression);
    }

    public function interpret(RuleContext $context = null)
    {
        $context = $this->validateContext($context);
        $cmdService = $context->getCmdService();

        // Si c'est une expression de commande, on extrait l'identifiant
        if ($this->cmdExpression instanceof CmdByStringExpression) {
            // Utilise la réflexion pour extraire la chaîne de commande
            $reflection = new \ReflectionClass($this->cmdExpression);
            $property = $reflection->getProperty('cmdString');
            $property->setAccessible(true);
            $cmdString = $property->getValue($this->cmdExpression);

            return $cmdService->execByString($cmdString, $this->options);
        } elseif ($this->cmdExpression instanceof CmdByIdExpression) {
            // Utilise la réflexion pour extraire l'ID de commande
            $reflection = new \ReflectionClass($this->cmdExpression);
            $property = $reflection->getProperty('cmdId');
            $property->setAccessible(true);
            $cmdId = $property->getValue($this->cmdExpression);

            return $cmdService->execById($cmdId, $this->options);
        } else {
            // Pour les autres expressions, on interprète la valeur
            $cmdValue = $this->cmdExpression->interpret($context);

            if (is_string($cmdValue)) {
                return $cmdService->execByString($cmdValue, $this->options);
            } elseif (is_int($cmdValue)) {
                return $cmdService->execById($cmdValue, $this->options);
            }

            throw new \InvalidArgumentException('La commande doit être une chaîne ou un entier');
        }
    }

    public function getType(): string
    {
        return 'action_exec';
    }

    public function __toString(): string
    {
        $optionsStr = empty($this->options) ? '' : ' ' . json_encode($this->options);

        return "exec({$this->cmdExpression}{$optionsStr})";
    }

    protected function validateExpression(): bool
    {
        return $this->cmdExpression->validate();
    }

    protected function requiresContext(): bool
    {
        return true;
    }

    public function getCmdExpression(): Expression
    {
        return $this->cmdExpression;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
