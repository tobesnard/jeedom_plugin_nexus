<?php

namespace Nexus\Interpreter\Expression\Terminal;

use Nexus\Jeedom\Services\ICmdService;
use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Expression\AbstractExpression;

/**
 * Expression terminale pour commandes par ID
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class CmdByIdExpression extends AbstractExpression
{
    private int $cmdId;
    private ICmdService $cmdService;

    public function __construct(int $cmdId, ICmdService $cmdService)
    {
        $this->cmdId = $cmdId;
        $this->cmdService = $cmdService;
        parent::__construct(['cmd_id' => $cmdId]);
    }

    public function interpret(RuleContext $context = null)
    {
        $context = $this->validateContext($context);

        return $this->cmdService->execById($this->cmdId);
    }

    public function getType(): string
    {
        return 'terminal_cmd_id';
    }

    public function __toString(): string
    {
        return "cmd_id({$this->cmdId})";
    }

    protected function validateExpression(): bool
    {
        return $this->cmdId > 0;
    }

    protected function requiresContext(): bool
    {
        return true;
    }

    public function getCmdId(): int
    {
        return $this->cmdId;
    }
}
