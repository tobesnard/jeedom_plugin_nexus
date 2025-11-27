<?php

namespace Interpreter\Expression;

use Interpreter\Context\RuleContext;
use InvalidArgumentException;

/**
 * Classe abstraite de base pour les expressions
 *
 * Fournit une implémentation par défaut des méthodes communes
 * à toutes les expressions et simplifie la création de nouvelles
 * expressions concrètes.
 *
 * @author Tony <tobesnard@gmail.com>
 * @since  1.0.0
 */
abstract class AbstractExpression implements Expression
{
    /** @var array Paramètres de l'expression */
    protected array $parameters = [];

    /** @var Expression[] Expressions enfants */
    protected array $children = [];

    /** @var bool Indique si l'expression a été validée */
    private bool $validated = false;

    /**
     * Constructeur de base
     *
     * @param array $parameters Paramètres de configuration
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
        $this->initialize();
    }

    /**
     * Méthode d'initialisation appelée après la construction
     *
     * Peut être surchargée par les classes filles pour
     * effectuer une initialisation spécifique.
     */
    protected function initialize(): void
    {
        // Implémentation par défaut vide
    }

    /**
     * {@inheritDoc}
     */
    abstract public function interpret(RuleContext $context = null);

    /**
     * {@inheritDoc}
     */
    public function validate(): bool
    {
        if ($this->validated) {
            return true;
        }

        // Validation des paramètres requis
        $required = $this->getRequiredParameters();
        foreach ($required as $param) {
            if (! isset($this->parameters[$param])) {
                return false;
            }
        }

        // Validation des enfants
        foreach ($this->children as $child) {
            if (! $child->validate()) {
                return false;
            }
        }

        $this->validated = $this->validateExpression();

        return $this->validated;
    }

    /**
     * Validation spécifique de l'expression
     *
     * À surcharger dans les classes filles pour ajouter
     * une logique de validation spécifique.
     *
     * @return bool True si valide
     */
    protected function validateExpression(): bool
    {
        return true;
    }

    /**
     * Retourne les paramètres requis pour cette expression
     *
     * @return string[] Noms des paramètres requis
     */
    protected function getRequiredParameters(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Ajoute une expression enfant
     *
     * @param Expression $child L'expression enfant à ajouter
     *
     * @return self Pour chaînage fluide
     */
    public function addChild(Expression $child): self
    {
        $this->children[] = $child;
        $this->validated = false; // Revalidation nécessaire

        return $this;
    }

    /**
     * Supprime tous les enfants
     *
     * @return self Pour chaînage fluide
     */
    public function clearChildren(): self
    {
        $this->children = [];
        $this->validated = false;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function deepClone(): Expression
    {
        $clone = clone $this;

        // Clone profond des enfants
        $clone->children = [];
        foreach ($this->children as $child) {
            $clone->children[] = $child->deepClone();
        }

        // Clone des paramètres (si nécessaire)
        $clone->parameters = $this->cloneParameters($this->parameters);
        $clone->validated = false;

        return $clone;
    }

    /**
     * Clone récursivement les paramètres
     *
     * @param array $params Paramètres à cloner
     *
     * @return array Paramètres clonés
     */
    private function cloneParameters(array $params): array
    {
        $cloned = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $cloned[$key] = $this->cloneParameters($value);
            } elseif (is_object($value)) {
                $cloned[$key] = clone $value;
            } else {
                $cloned[$key] = $value;
            }
        }

        return $cloned;
    }

    /**
     * {@inheritDoc}
     */
    public function optimize(): Expression
    {
        // Optimisation par défaut : optimise les enfants
        $optimized = false;
        for ($i = 0; $i < count($this->children); $i++) {
            $originalChild = $this->children[$i];
            $optimizedChild = $originalChild->optimize();

            if ($optimizedChild !== $originalChild) {
                $this->children[$i] = $optimizedChild;
                $optimized = true;
            }
        }

        if ($optimized) {
            $this->validated = false;
        }

        // Les classes filles peuvent surcharger pour ajouter leur propre logique
        return $this->optimizeExpression();
    }

    /**
     * Optimisation spécifique de l'expression
     *
     * À surcharger dans les classes filles pour ajouter
     * une logique d'optimisation spécifique.
     *
     * @return Expression L'expression optimisée
     */
    protected function optimizeExpression(): Expression
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        $className = static::class;
        $shortName = substr($className, strrpos($className, '\\') + 1);

        if (empty($this->children)) {
            return $shortName . '(' . $this->formatParameters() . ')';
        }

        $childrenStr = implode(', ', array_map('strval', $this->children));

        return $shortName . '(' . $this->formatParameters() . ')(' . $childrenStr . ')';
    }

    /**
     * Formate les paramètres pour l'affichage
     *
     * @return string Paramètres formatés
     */
    protected function formatParameters(): string
    {
        if (empty($this->parameters)) {
            return '';
        }

        $formatted = [];
        foreach ($this->parameters as $key => $value) {
            if (is_string($value)) {
                $formatted[] = $key . '="' . $value . '"';
            } elseif (is_bool($value)) {
                $formatted[] = $key . '=' . ($value ? 'true' : 'false');
            } elseif (is_null($value)) {
                $formatted[] = $key . '=null';
            } else {
                $formatted[] = $key . '=' . $value;
            }
        }

        return implode(', ', $formatted);
    }

    /**
     * Récupère un paramètre avec valeur par défaut
     *
     * @param string $name Nom du paramètre
     * @param mixed $default Valeur par défaut
     *
     * @return mixed La valeur du paramètre
     */
    protected function getParameter(string $name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }

    /**
     * Définit un paramètre
     *
     * @param string $name Nom du paramètre
     * @param mixed $value Valeur du paramètre
     *
     * @return self Pour chaînage fluide
     */
    protected function setParameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;
        $this->validated = false;

        return $this;
    }

    /**
     * Vérifie si un paramètre existe
     *
     * @param string $name Nom du paramètre
     *
     * @return bool True si le paramètre existe
     */
    protected function hasParameter(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    /**
     * Valide et prépare le contexte pour l'interprétation
     *
     * @param RuleContext|null $context Le contexte
     *
     * @return RuleContext Le contexte validé
     *
     * @throws InvalidArgumentException Si le contexte est requis mais null
     */
    protected function validateContext(?RuleContext $context): RuleContext
    {
        if ($context === null) {
            if ($this->requiresContext()) {
                throw new InvalidArgumentException(
                    'Cette expression requiert un contexte d\'exécution'
                );
            }

            // Création d'un contexte par défaut
            $context = new RuleContext();
        }

        return $context;
    }

    /**
     * Indique si l'expression requiert obligatoirement un contexte
     *
     * @return bool True si un contexte est requis
     */
    protected function requiresContext(): bool
    {
        return false;
    }
}
