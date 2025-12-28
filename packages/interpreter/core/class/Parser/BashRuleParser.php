<?php

namespace Nexus\Interpreter\Parser;

use Exception;
use Nexus\Jeedom\Services\ICmdService;
use Nexus\Interpreter\Expression\Expression;
use Nexus\Interpreter\Expression\NonTerminal\Action\EventExpression;
// Service
use Nexus\Interpreter\Expression\NonTerminal\Action\ExecExpression;
// Action
use Nexus\Interpreter\Expression\NonTerminal\Action\LogExpression;
use Nexus\Interpreter\Expression\NonTerminal\Comparison\EqExpression;
use Nexus\Interpreter\Expression\NonTerminal\Comparison\GeExpression;
// Opérande
use Nexus\Interpreter\Expression\NonTerminal\Comparison\GtExpression;
use Nexus\Interpreter\Expression\NonTerminal\Comparison\LeExpression;
use Nexus\Interpreter\Expression\NonTerminal\Comparison\LtExpression;
// Opérateur
use Nexus\Interpreter\Expression\NonTerminal\Comparison\NeExpression;
use Nexus\Interpreter\Expression\NonTerminal\IfExpression;
use Nexus\Interpreter\Expression\NonTerminal\Logical\AndExpression;
use Nexus\Interpreter\Expression\NonTerminal\Logical\NotExpression;
use Nexus\Interpreter\Expression\NonTerminal\Logical\OrExpression;
use Nexus\Interpreter\Expression\NonTerminal\SequenceExpression;
use Nexus\Interpreter\Expression\Terminal\CmdByIdExpression;
use Nexus\Interpreter\Expression\Terminal\CmdByStringExpression;
use Nexus\Interpreter\Expression\Terminal\LiteralExpression;

/**
 * BashRuleParser : Analyse la syntaxe de type Bash: "if <condition> : <action_then>; <action_else>"
 * OU une simple séquence d'actions: "<action_1>, <action_2>, ..."
 */
class BashRuleParser
{
    private ICmdService $cmdService;

    public function __construct(ICmdService $cmdService)
    {
        $this->cmdService = $cmdService;
    }

    /**
     * Analyse la règle de la forme "if <condition> : <action_then>; <action_else>"
     * OU une séquence d'actions simple.
     */
    public function parse(string $rule): Expression
    {
        $rule = trim($rule);
        $rule = preg_replace('/\s+/', ' ', $rule); // Nettoyage des espaces

        if (empty($rule)) {
            throw new Exception("Erreur de syntaxe : La règle est vide.");
        }

        // --- DÉBUT DE LA NOUVELLE LOGIQUE ---
        // Vérifie si la règle commence par 'if ' (insensible à la casse)
        if (strtolower(substr($rule, 0, 3)) === 'if ') {
            // ===================================================
            // CAS 1 : Règle complète de type IF
            // ===================================================
            $innerContent = trim(substr($rule, 3));

            // 1. Séparation Condition / Actions
            $separatorPos = strpos($innerContent, ':');

            if ($separatorPos === false) {
                throw new Exception("Erreur de syntaxe : Séparateur ':' manquant entre CONDITION et ACTION.");
            }

            $conditionString = trim(substr($innerContent, 0, $separatorPos));
            $actionsString = trim(substr($innerContent, $separatorPos + 1));

            if (empty($conditionString)) {
                throw new Exception("Erreur de syntaxe : La CONDITION est manquante.");
            }
            if (empty($actionsString)) {
                throw new Exception("Erreur de syntaxe : L'ACTION est manquante.");
            }

            // 2. Séparation ACTION_THEN / ACTION_ELSE (clause ELSE)
            $elseSeparatorPos = strpos($actionsString, ';');

            $actionThenString = $actionsString;
            $actionElseString = null;

            if ($elseSeparatorPos !== false) {
                $actionThenString = trim(substr($actionsString, 0, $elseSeparatorPos));
                $actionElseString = trim(substr($actionsString, $elseSeparatorPos + 1));

                if (empty($actionThenString)) {
                    throw new Exception("Erreur de syntaxe : L'ACTION THEN est manquante avant le ';'.");
                }

                if (empty($actionElseString)) {
                    throw new Exception("Erreur de syntaxe : L'ACTION ELSE est manquante après le ';'.");
                }
            }

            // 3. Analyse des expressions
            $conditionExpression = $this->parseCondition($conditionString);
            $actionThenExpression = $this->parseAction($actionThenString);
            $actionElseExpression = null;

            if ($actionElseString !== null) {
                $actionElseExpression = $this->parseAction($actionElseString);
            }

            // 4. Construction de l'Expression If (avec le 3ème argument optionnel)
            return new IfExpression($conditionExpression, $actionThenExpression, $actionElseExpression);

        } else {
            // ===================================================
            // CAS 2 : Séquence d'actions simple
            // ===================================================
            return $this->parseAction($rule);
        }
        // --- FIN DE LA NOUVELLE LOGIQUE ---
    }

    // --- Analyse des conditions (opérateurs binaires et unaires) ---

    public function parseCondition(string $conditionString): Expression
    {
        $conditionString = trim($conditionString);

        // --- Opérateur Unaure (NOT) ---
        if (preg_match('/^-not\s+(.+)/i', $conditionString, $matches)) {
            $operandString = trim($matches[1]);
            $operandExpression = $this->parseCondition($operandString);

            return new NotExpression($operandExpression);
        }

        // --- Opérateurs Binaires (comparaisons et logiques) ---
        $binaryOperators = '(?:-eq|-ne|-gt|-lt|-ge|-le|-and|-or)';
        $pattern = "/^(.+?)\s+($binaryOperators)\s+(.+)$/i";

        if (preg_match($pattern, $conditionString, $matches)) {
            $leftOperandString = trim($matches[1]);
            $operator = strtolower($matches[2]);
            $rightOperandString = trim($matches[3]);

            if (in_array($operator, ['-and', '-or'])) {
                $leftExpression = $this->parseCondition($leftOperandString);
                $rightExpression = $this->parseCondition($rightOperandString);
            } else {
                $leftExpression = $this->parseOperand($leftOperandString);
                $rightExpression = $this->parseOperand($rightOperandString);
            }

            switch ($operator) {
                case '-eq':
                    return new EqExpression($leftExpression, $rightExpression);
                case '-ne':
                    return new NeExpression($leftExpression, $rightExpression);
                case '-gt':
                    return new GtExpression($leftExpression, $rightExpression);
                case '-lt':
                    return new LtExpression($leftExpression, $rightExpression);
                case '-ge':
                    return new GeExpression($leftExpression, $rightExpression);
                case '-le':
                    return new LeExpression($leftExpression, $rightExpression);
                case '-and':
                    return new AndExpression($leftExpression, $rightExpression);
                case '-or':
                    return new OrExpression($leftExpression, $rightExpression);
            }
        }

        return $this->parseOperand($conditionString);
    }

    // --- Analyse de l'opérande (Cmd vs Littéral) ---
    private function parseOperand(string $operandString): Expression
    {
        $operandString = trim($operandString);

        // Test de la forme #[…][…][…]# pour CmdByStringExpression (permet les espaces internes)
        $pattern = '/^(\#\[.+?\](\[.+?\])*\#)$/';
        if (preg_match($pattern, $operandString, $matches)) {
            $cmd = $matches[1];

            // MODIF : La commande ne prend plus d'options ici
            return new CmdByStringExpression($cmd, $this->cmdService);
        }

        // Test la forme #1234# pour CmdByIdExpression (avec ancrage ^ et $)
        $pattern = '/^(\#\d+\#)$/';
        if (preg_match($pattern, $operandString, $matches)) {
            $id = (int) trim($matches[1], '#');

            // MODIF : La commande ne prend plus d'options ici
            return new CmdByIdExpression($id, $this->cmdService);
        }

        // Sinon, c'est une LiteralExpression
        return new LiteralExpression($operandString);
    }

    // --- LOGIQUE MULTI-ACTION (SequenceExpression) ---

    private function parseAction(string $actionString): Expression
    {
        $actionString = trim($actionString);

        // 1. Détecter et séparer les actions multiples par une virgule (,)
        $actionStrings = array_map('trim', explode(',', $actionString));

        if (count($actionStrings) === 1) {
            return $this->parseSingleAction($actionString);
        }

        $parsedActions = [];
        foreach ($actionStrings as $singleActionString) {
            if (empty($singleActionString)) {
                continue;
            }
            $parsedActions[] = $this->parseSingleAction($singleActionString);
        }

        if (count($parsedActions) === 1) {
            return $parsedActions[0];
        }

        return new SequenceExpression($parsedActions);
    }

    // --- Analyse de l'Action Unique (log, exec, event) ---

    private function parseSingleAction(string $actionString): Expression
    {
        $actionString = trim($actionString);

        if (! preg_match('/^(\w+)\s*(.*)$/i', $actionString, $matches)) {
            $funcName = strtolower($actionString);
            $argString = '';
        } else {
            $funcName = strtolower($matches[1]);
            $argString = trim($matches[2]);
        }

        $parsedArguments = [];

        if (! empty($argString)) {
            // REGEX DE TOKENISATION : Gère #...#, '...' (avec "), et "..." (avec ')
            $pattern = "/(\#.+?\#|'[^']+'|\"[^\"]+\"|\S+)/";

            if (! preg_match_all($pattern, $argString, $argMatches)) {
                throw new Exception("Erreur d'analyse des arguments de l'action.");
            }

            // --- LOGIQUE D'ASSEMBLAGE DES ARGUMENTS (Gestion de clé->'valeur' coupée) ---
            $tokens = $argMatches[0];
            $finalTokens = [];
            $i = 0;

            while ($i < count($tokens)) {
                $currentToken = $tokens[$i];

                // 1. Détecter si le token actuel est le début d'une structure clé->'valeur' coupée
                if (preg_match('/^[^>\s]+->[\'"]/', $currentToken) && ! preg_match('/[\'"]$/', $currentToken)) {

                    $assembledToken = $currentToken;
                    $i++;

                    // Continuer à assembler les tokens tant que le guillemet fermant n'est pas trouvé
                    while ($i < count($tokens) && ! preg_match('/[\'"]$/', $tokens[$i])) {
                        $assembledToken .= ' ' . $tokens[$i];
                        $i++;
                    }

                    // Ajouter le dernier morceau fermant s'il existe
                    if ($i < count($tokens)) {
                        $assembledToken .= ' ' . $tokens[$i];
                        $i++;
                    }

                    $finalTokens[] = $assembledToken;

                } else {
                    $finalTokens[] = $currentToken;
                    $i++;
                }
            }
            // --- FIN DE LA LOGIQUE D'ASSEMBLAGE ---

            // Re-traitement des tokens assemblés en Expressions
            foreach ($finalTokens as $argToken) {
                // Teste si le token commence et se termine par le MÊME type de quote (' ou ")
                $isQuoted = preg_match("/^(['\"])(.*)\\1$/s", $argToken, $quoteMatches);

                if ($isQuoted) {
                    $parsedArguments[] = new LiteralExpression($quoteMatches[2]);
                } else {
                    $parsedArguments[] = $this->parseOperand($argToken);
                }
            }
        }

        // --- Instanciation de l'Expression d'Action ---

        switch ($funcName) {
            // Extrait du switch dans parseSingleAction
            case 'log':
                if (count($parsedArguments) !== 1) {
                    throw new Exception("L'action 'log' doit avoir un seul argument.");
                }

                $argExpression = $parsedArguments[0];

                // On accepte soit un Littéral, soit une Commande (CmdBy*)
                if (
                    $argExpression instanceof LiteralExpression
                    || $argExpression instanceof CmdByIdExpression
                    || $argExpression instanceof CmdByStringExpression
                ) {
                    // LogExpression doit être mis à jour pour accepter une Expression et non seulement une valeur
                    return new LogExpression($argExpression);
                }

                throw new Exception("L'action 'log' doit avoir un seul argument de type chaîne ou une commande (Cmd).");

            case 'exec': // LOGIQUE 'EXEC' MIS À JOUR
                if (empty($parsedArguments)) {
                    throw new Exception("L'action 'exec' requiert au moins un argument (la commande à exécuter).");
                }

                $commandExpression = $parsedArguments[0];
                $optionsExpressions = array_slice($parsedArguments, 1);
                $optionsValues = [];

                // 1. Validation de la Commande
                if (
                    ! $commandExpression instanceof CmdByStringExpression
                    && ! $commandExpression instanceof CmdByIdExpression
                ) {
                    throw new Exception("Le premier argument de 'exec' doit être une commande (#ID# ou #[...]#).");
                }

                // 2. Analyse des options 'clé->valeur'
                foreach ($optionsExpressions as $optExp) {
                    $value = null;

                    if ($optExp instanceof LiteralExpression) {
                        $value = $optExp->getValue();
                    } else {
                        // Si l'argument n'est pas un littéral, il est passé tel quel (devrait être une erreur ici)
                        $optionsValues[] = $optExp;

                        continue;
                    }

                    // Tentative d'analyse des paires clé->valeur
                    if (is_string($value) && strpos($value, '->') !== false) {
                        $parts = explode('->', $value, 2);
                        $key = trim($parts[0]);
                        $val = trim($parts[1]);

                        // Nettoyage des guillemets
                        $val = preg_replace("/^['\"]|['\"]$/", '', $val);

                        if (! empty($key) && isset($val)) {
                            $optionsValues[$key] = $val;

                            continue;
                        }
                    }

                    $optionsValues[] = $value;
                }

                // 3. Instanciation de la nouvelle ExecExpression
                return new ExecExpression($commandExpression, $optionsValues);

            case 'event':
                if (count($parsedArguments) !== 2) {
                    throw new Exception("L'action 'event' requiert exactement deux arguments : une commande et une valeur.");
                }

                $commandExpression = $parsedArguments[0];
                $valueExpression = $parsedArguments[1];

                if (! $valueExpression instanceof LiteralExpression) {
                    throw new Exception("Le second argument de l'action 'event' doit être une valeur littérale (chaîne ou nombre).");
                }

                $value = $valueExpression->getValue();

                return new EventExpression($commandExpression, $value);


            default:
                throw new Exception("Action non supporté : " . $funcName);
        }
    }
}
