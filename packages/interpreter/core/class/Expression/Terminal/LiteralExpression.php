<?php

namespace Nexus\Interpreter\Expression\Terminal;

use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Expression\AbstractExpression;
use InvalidArgumentException;

/**
 * Expression littérale - Représente une valeur statique
 *
 * Cette expression encapsule une valeur constante qui ne change pas
 * pendant l'exécution. Elle supporte les types primitifs PHP :
 * booléens, entiers, flottants, chaînes et null.
 *
 * Exemples de valeurs :
 * - Nombres : 22, 3.14, -100
 * - Booléens : true, false
 * - Chaînes : 'temp_basse', "message d'erreur"
 * - Null : null
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class LiteralExpression extends AbstractExpression
{
    /** @var mixed La valeur littérale stockée */
    private $value;

    /** @var string Le type de la valeur */
    private string $valueType;

    /**
     * Constructeur de l'expression littérale
     *
     * @param mixed $value La valeur à encapsuler
     *
     * @throws InvalidArgumentException Si la valeur n'est pas du bon type
     */
    public function __construct($value)
    {
        $this->value = $this->extractAndValidateValue($value);
        $this->valueType = $this->determineValueType($this->value);

        parent::__construct(['raw_value' => $value]);
    }

    /**
     * {@inheritDoc}
     */
    public function interpret(RuleContext $context = null)
    {
        // Les expressions littérales ne nécessitent pas de contexte
        // et retournent toujours leur valeur directement
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return 'literal';
    }

    /**
     * Retourne la valeur encapsulée
     *
     * @return mixed La valeur littérale
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Retourne le type de la valeur
     *
     * @return string Le type ('boolean', 'integer', 'double', 'string', 'null')
     */
    public function getValueType(): string
    {
        return $this->valueType;
    }

    /**
     * Vérifie si la valeur est d'un type numérique
     *
     * @return bool True si la valeur est un entier ou un flottant
     */
    public function isNumeric(): bool
    {
        return in_array($this->valueType, ['integer', 'double']);
    }

    /**
     * Vérifie si la valeur est booléenne
     *
     * @return bool True si la valeur est booléenne
     */
    public function isBoolean(): bool
    {
        return $this->valueType === 'boolean';
    }

    /**
     * Vérifie si la valeur est une chaîne
     *
     * @return bool True si la valeur est une chaîne
     */
    public function isString(): bool
    {
        return $this->valueType === 'string';
    }

    /**
     * Vérifie si la valeur est null
     *
     * @return bool True si la valeur est null
     */
    public function isNull(): bool
    {
        return $this->valueType === 'null';
    }

    /**
     * {@inheritDoc}
     */
    protected function validateExpression(): bool
    {
        // Les valeurs littérales sont toujours valides si elles ont été construites
        return $this->value !== false || $this->isBoolean();
    }

    /**
     * {@inheritDoc}
     */
    protected function optimizeExpression(): AbstractExpression
    {
        // Les expressions littérales sont déjà optimales
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->formatValueForDisplay($this->value);
    }

    /**
     * Extrait et valide la valeur depuis l'entrée
     *
     * @param mixed $value La valeur d'entrée
     *
     * @return mixed La valeur extractée et validée
     *
     * @throws InvalidArgumentException Si la valeur est invalide
     */
    private function extractAndValidateValue($value)
    {
        // Si c'est déjà une valeur primitive, on la retourne
        if (! is_string($value)) {
            $this->validatePrimitiveValue($value);

            return $value;
        }

        // Traitement des chaînes qui peuvent représenter d'autres types
        return $this->parseStringValue($value);
    }

    /**
     * Valide qu'une valeur primitive est acceptée
     *
     * @param mixed $value La valeur à valider
     *
     * @throws InvalidArgumentException Si la valeur n'est pas acceptée
     */
    private function validatePrimitiveValue($value): void
    {
        $allowedTypes = ['boolean', 'integer', 'double', 'string', 'NULL'];
        $actualType = gettype($value);

        if (! in_array($actualType, $allowedTypes)) {
            throw new InvalidArgumentException(
                "Type de valeur non supporté : {$actualType}. Types acceptés : " .
                implode(', ', $allowedTypes)
            );
        }
    }

    /**
     * Parse une chaîne pour extraire sa vraie valeur
     *
     * @param string $valueString La chaîne à parser
     *
     * @return mixed La valeur parsée
     */
    private function parseStringValue(string $valueString)
    {
        // Suppression des espaces au début et à la fin
        $trimmed = trim($valueString);

        // Gestion du cas spécial pour null
        if (strtolower($trimmed) === 'null') {
            return null;
        }

        // Suppression des guillemets (simples ou doubles) et espaces
        if (preg_match('/^(["\'])(.*)\\1$/', $trimmed, $matches)) {
            // La chaîne est entourée de guillemets identiques
            $stripped = $matches[2];
        } else {
            // Pas de guillemets, on garde la chaîne telle quelle (après trim)
            $stripped = $trimmed;
        }

        // Vérification si c'est vide après suppression des guillemets
        if ($stripped === '') {
            return $stripped;
        }

        // Conversion des booléens
        if (in_array(strtolower($stripped), ['true', 'false'])) {
            return strtolower($stripped) === 'true';
        }

        // Conversion des nombres
        if (is_numeric($stripped)) {
            // Détection des flottants par la présence d'un point
            if (strpos($stripped, '.') !== false) {
                return (float)$stripped;
            }

            return (int)$stripped;
        }

        // Retour de la chaîne nettoyée
        return $stripped;
    }

    /**
     * Détermine le type d'une valeur
     *
     * @param mixed $value La valeur
     *
     * @return string Le type de la valeur
     */
    private function determineValueType($value): string
    {
        $type = gettype($value);

        // Normalisation du type NULL
        if ($type === 'NULL') {
            return 'null';
        }

        return $type;
    }

    /**
     * Formate une valeur pour l'affichage
     *
     * @param mixed $value La valeur à formater
     *
     * @return string La valeur formatée
     */
    private function formatValueForDisplay($value): string
    {
        if (is_null($value)) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }

        return (string)$value;
    }

    /**
     * Crée une expression littérale à partir d'une chaîne
     *
     * Méthode statique de facilité pour créer rapidement
     * des expressions littérales.
     *
     * @param string $valueString La chaîne représentant la valeur
     *
     * @return self Nouvelle instance de LiteralExpression
     */
    public static function fromString(string $valueString): self
    {
        return new self($valueString);
    }

    /**
     * Crée une expression littérale booléenne
     *
     * @param bool $value La valeur booléenne
     *
     * @return self Nouvelle instance de LiteralExpression
     */
    public static function boolean(bool $value): self
    {
        return new self($value);
    }

    /**
     * Crée une expression littérale numérique
     *
     * @param int|float $value La valeur numérique
     *
     * @return self Nouvelle instance de LiteralExpression
     */
    public static function number($value): self
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException('La valeur doit être numérique');
        }

        return new self($value);
    }

    /**
     * Crée une expression littérale null
     *
     * @return self Nouvelle instance de LiteralExpression
     */
    public static function null(): self
    {
        return new self(null);
    }
}
