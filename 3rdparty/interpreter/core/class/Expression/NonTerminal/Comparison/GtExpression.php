<?php

namespace Nexus\Interpreter\Expression\NonTerminal\Comparison;

use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Expression\AbstractExpression;
use Nexus\Interpreter\Expression\Expression;

/**
 * Expression de comparaison "supérieur à"
 *
 * Cette expression compare deux expressions et retourne true
 * si la valeur de gauche est strictement supérieure à celle de droite.
 * Supporte la conversion automatique de types pour les comparaisons numériques.
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class GtExpression extends AbstractExpression
{
    /** @var Expression Expression de gauche */
    private Expression $left;

    /** @var Expression Expression de droite */
    private Expression $right;

    /**
     * Constructeur de l'expression "supérieur à"
     *
     * @param Expression $left Expression de gauche
     * @param Expression $right Expression de droite
     */
    public function __construct(Expression $left, Expression $right)
    {
        $this->left = $left;
        $this->right = $right;

        // Ajout des enfants pour le support des méthodes héritées
        parent::__construct();
        $this->addChild($left);
        $this->addChild($right);
    }

    /**
     * {@inheritDoc}
     */
    public function interpret(RuleContext $context = null): bool
    {
        $context = $this->validateContext($context);

        $leftValue = $this->left->interpret($context);
        $rightValue = $this->right->interpret($context);

        // Conversion automatique des chaînes numériques
        if (is_string($leftValue) && is_numeric($leftValue)) {
            $leftValue = (float)$leftValue;
        }
        if (is_string($rightValue) && is_numeric($rightValue)) {
            $rightValue = (float)$rightValue;
        }

        return $leftValue > $rightValue;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return 'comparison_gt';
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return "({$this->left} > {$this->right})";
    }

    /**
     * {@inheritDoc}
     */
    protected function validateExpression(): bool
    {
        return $this->left->validate() && $this->right->validate();
    }

    /**
     * {@inheritDoc}
     */
    protected function optimizeExpression(): Expression
    {
        // Si les deux expressions sont littérales, on peut évaluer directement
        if ($this->left instanceof \Nexus\Interpreter\Expression\Terminal\LiteralExpression &&
            $this->right instanceof \Nexus\Interpreter\Expression\Terminal\LiteralExpression) {

            $leftVal = $this->left->getValue();
            $rightVal = $this->right->getValue();

            if (is_numeric($leftVal) && is_numeric($rightVal)) {
                return new \Nexus\Interpreter\Expression\Terminal\LiteralExpression($leftVal > $rightVal);
            }
        }

        return $this;
    }

    /**
     * Retourne l'expression de gauche
     *
     * @return Expression
     */
    public function getLeft(): Expression
    {
        return $this->left;
    }

    /**
     * Retourne l'expression de droite
     *
     * @return Expression
     */
    public function getRight(): Expression
    {
        return $this->right;
    }
}
