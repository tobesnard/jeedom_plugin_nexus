<?php

namespace Nexus\Interpreter\Expression;

use Nexus\Interpreter\Context\RuleContext;

/**
 * Interface Expression (AbstractExpression du GoF)
 *
 * Déclare l'opération d'interprétation commune à toutes les expressions.
 * Utilise le pattern Interpreter pour permettre l'évaluation d'expressions
 * complexes dans un langage de règles.
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
interface Expression
{
    /**
     * Interprète l'expression dans le contexte donné
     *
     * Cette méthode est le cœur du pattern Interpreter.
     * Elle évalue l'expression selon sa logique spécifique
     * et retourne le résultat.
     *
     * @param RuleContext|null $context Le contexte d'exécution contenant les variables et services
     *
     * @return mixed Le résultat de l'interprétation (bool, valeur, null)
     *
     * @throws \InvalidArgumentException Si les paramètres sont invalides
     * @throws \RuntimeException Si l'interprétation échoue
     */
    public function interpret(RuleContext $context = null);

    /**
     * Retourne une représentation textuelle de l'expression
     *
     * Utile pour le débogage, la journalisation et l'affichage
     * des expressions dans une forme lisible.
     *
     * @return string La représentation sous forme de chaîne
     */
    public function __toString(): string;

    /**
     * Valide la structure et les paramètres de l'expression
     *
     * Cette méthode permet de vérifier que l'expression
     * est bien formée avant son exécution.
     *
     * @return bool True si l'expression est valide, false sinon
     */
    public function validate(): bool;

    /**
     * Retourne le type de l'expression
     *
     * Permet de catégoriser les expressions pour l'optimisation,
     * la validation ou l'affichage.
     *
     * @return string Le type de l'expression (ex: 'comparison', 'logical', 'terminal')
     */
    public function getType(): string;

    /**
     * Retourne les expressions enfants (si applicable)
     *
     * Pour les expressions composites, retourne la liste
     * des sous-expressions.
     *
     * @return Expression[] Tableau des expressions enfants
     */
    public function getChildren(): array;

    /**
     * Clone profond de l'expression
     *
     * Crée une copie complète de l'expression et de ses enfants.
     * Utile pour la manipulation d'expressions sans effet de bord.
     *
     * @return Expression Une nouvelle instance de l'expression
     */
    public function deepClone(): Expression;

    /**
     * Optimise l'expression si possible
     *
     * Certaines expressions peuvent être simplifiées ou optimisées
     * (ex: true AND x -> x, false OR x -> x, etc.)
     *
     * @return Expression L'expression optimisée (peut être la même instance)
     */
    public function optimize(): Expression;
}
