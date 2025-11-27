<?php

namespace Interpreter\Expression\Terminal;

use Interpreter\Application\Services\ICmdService;
use Interpreter\Context\RuleContext;
use Interpreter\Expression\AbstractExpression;

/**
 * Expression terminale pour commandes par chaîne
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class CmdByStringExpression extends AbstractExpression
{
    private string $cmdString;
    private ICmdService $cmdService;

    public function __construct(string $cmdString, ICmdService $cmdService)
    {
        $this->cmdString = $cmdString;
        $this->cmdService = $cmdService;
        parent::__construct(['cmd_string' => $cmdString]);
    }

    public function interpret(RuleContext $context = null)
    {
        $context = $this->validateContext($context);

        return $this->cmdService->execByString($this->cmdString);
    }

    public function getType(): string
    {
        return 'terminal_cmd_string';
    }

    public function __toString(): string
    {
        return "cmd_string('{$this->cmdString}')";
    }

    protected function validateExpression(): bool
    {
        return ! empty(trim($this->cmdString));
    }

    protected function requiresContext(): bool
    {
        return true;
    }

    public function getCmdString(): string
    {
        return $this->cmdString;
    }
}
