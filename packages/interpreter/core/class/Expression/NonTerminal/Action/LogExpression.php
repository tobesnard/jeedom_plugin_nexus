<?php

namespace Nexus\Interpreter\Expression\NonTerminal\Action;

use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Expression\AbstractExpression;
use Nexus\Interpreter\Expression\Expression; // Assurez-vous d'importer l'interface ou la classe de base Expression

/**
 * Expression d'action de log
 *
 * Cette expression affiche un message de log lors de son interprétation.
 * Elle peut logguer une chaîne statique ou la valeur d'une autre Expression
 * (comme le résultat d'une commande).
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class LogExpression extends AbstractExpression
{
    /** @var Expression L'expression (Literal ou Cmd) dont la valeur doit être loggée */
    private Expression $messageExpression;

    /**
     * Constructeur de l'expression de log
     *
     * @param Expression $messageExpression L'expression dont le résultat sera loggé
     */
    public function __construct(Expression $messageExpression)
    {
        $this->messageExpression = $messageExpression;

        // Assurez-vous que l'AbstractExpression gère correctement les expressions enfants
        parent::__construct(['messageExpression' => $messageExpression]);
    }

    /**
     * {@inheritDoc}
     */
    public function interpret(RuleContext $context = null)
    {
        $context = $this->validateContext($context);
        $cmdService = $context->getCmdService();

        // 1. Interpréter l'expression interne pour obtenir la valeur réelle à logger.
        $valueToLog = $this->messageExpression->interpret($context);

        // 2. Formater le message (gérer les types non-scalaires si nécessaire)
        $logMessage = is_scalar($valueToLog) ?
            (string)$valueToLog :
            'Non-scalar result: ' . var_export($valueToLog, true);

        // Affichage console avec couleur (débogage)
        // echo "\033[32m \n[LOG] {$logMessage}\033[0m\n";

        // Optionnel: log dans Jeedom si disponible
        return $cmdService->log($logMessage);
        // if (function_exists('log::add')) {
        // \log::add('interpreter', 'info', "[Règle] {$logMessage}");
        // }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return 'action_log';
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        // Affiche la structure récursivement
        return "log({$this->messageExpression})";
    }

    /**
     * {@inheritDoc}
     */
    protected function validateExpression(): bool
    {
        // On vérifie simplement que l'expression interne existe
        return isset($this->messageExpression);
    }

    /**
     * Retourne l'expression de message à logger
     *
     * @return Expression
     */
    public function getMessageExpression(): Expression
    {
        return $this->messageExpression;
    }
}
