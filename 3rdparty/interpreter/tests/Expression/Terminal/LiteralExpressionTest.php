<?php

namespace Interpreter\Tests\Expression\Terminal;

use Interpreter\Context\RuleContext;
use Interpreter\Expression\Terminal\LiteralExpression;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour la classe LiteralExpression
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class LiteralExpressionTest extends TestCase
{
    /**
     * Teste la création d'expressions littérales avec différents types
     *
     * @dataProvider literalValuesProvider
     */
    public function testCreateLiteralExpression($input, $expectedValue, string $expectedType): void
    {
        $expression = new LiteralExpression($input);

        $this->assertEquals($expectedValue, $expression->getValue());
        $this->assertEquals($expectedType, $expression->getValueType());
        $this->assertEquals('literal', $expression->getType());
        $this->assertTrue($expression->validate());
    }

    /**
     * Fournit des données de test pour les valeurs littérales
     *
     * @return array
     */
    public function literalValuesProvider(): array
    {
        return [
            // Chaînes simples
            ['test', 'test', 'string'],
            ['"test"', 'test', 'string'],
            ["'test'", 'test', 'string'],
            ['', '', 'string'],

            // Booléens
            ['true', true, 'boolean'],
            ['false', false, 'boolean'],
            ['TRUE', true, 'boolean'],
            ['FALSE', false, 'boolean'],
            [true, true, 'boolean'],
            [false, false, 'boolean'],

            // Nombres
            ['42', 42, 'integer'],
            ['-42', -42, 'integer'],
            ['3.14', 3.14, 'double'],
            ['-3.14', -3.14, 'double'],
            [42, 42, 'integer'],
            [3.14, 3.14, 'double'],

            // Null
            ['null', null, 'null'],
            ['NULL', null, 'null'],
            [null, null, 'null'],

            // Chaînes avec guillemets
            ['"chaîne avec espaces"', 'chaîne avec espaces', 'string'],
            ["'another string'", 'another string', 'string'],
        ];
    }

    /**
     * Teste l'interprétation des expressions littérales
     */
    public function testInterpretation(): void
    {
        $context = new RuleContext();

        $stringExpr = new LiteralExpression('hello');
        $this->assertEquals('hello', $stringExpr->interpret($context));

        $numberExpr = new LiteralExpression(42);
        $this->assertEquals(42, $numberExpr->interpret($context));

        $boolExpr = new LiteralExpression(true);
        $this->assertEquals(true, $boolExpr->interpret($context));
    }

    /**
     * Teste les méthodes de vérification de type
     */
    public function testTypeChecking(): void
    {
        $stringExpr = new LiteralExpression('test');
        $this->assertTrue($stringExpr->isString());
        $this->assertFalse($stringExpr->isNumeric());
        $this->assertFalse($stringExpr->isBoolean());
        $this->assertFalse($stringExpr->isNull());

        $intExpr = new LiteralExpression(42);
        $this->assertTrue($intExpr->isNumeric());
        $this->assertFalse($intExpr->isString());

        $floatExpr = new LiteralExpression(3.14);
        $this->assertTrue($floatExpr->isNumeric());

        $boolExpr = new LiteralExpression(true);
        $this->assertTrue($boolExpr->isBoolean());

        $nullExpr = new LiteralExpression(null);
        $this->assertTrue($nullExpr->isNull());
    }

    /**
     * Teste les méthodes statiques de création
     */
    public function testStaticCreationMethods(): void
    {
        $stringExpr = LiteralExpression::fromString('test');
        $this->assertEquals('test', $stringExpr->getValue());

        $boolExpr = LiteralExpression::boolean(true);
        $this->assertTrue($boolExpr->getValue());

        $numberExpr = LiteralExpression::number(42);
        $this->assertEquals(42, $numberExpr->getValue());

        $nullExpr = LiteralExpression::null();
        $this->assertNull($nullExpr->getValue());
    }

    /**
     * Teste la représentation textuelle
     */
    public function testStringRepresentation(): void
    {
        $stringExpr = new LiteralExpression('test');
        $this->assertEquals("'test'", (string)$stringExpr);

        $numberExpr = new LiteralExpression(42);
        $this->assertEquals('42', (string)$numberExpr);

        $boolExpr = new LiteralExpression(true);
        $this->assertEquals('true', (string)$boolExpr);

        $nullExpr = new LiteralExpression(null);
        $this->assertEquals('null', (string)$nullExpr);
    }

    /**
     * Teste la validation d'expressions
     */
    public function testValidation(): void
    {
        $validExpr = new LiteralExpression('valid');
        $this->assertTrue($validExpr->validate());

        // Test avec différents types
        $expressions = [
            new LiteralExpression(42),
            new LiteralExpression(true),
            new LiteralExpression(false),
            new LiteralExpression(null),
            new LiteralExpression('string'),
            new LiteralExpression(3.14),
        ];

        foreach ($expressions as $expr) {
            $this->assertTrue($expr->validate(), 'L\'expression devrait être valide');
        }
    }

    /**
     * Teste le clonage profond
     */
    public function testDeepClone(): void
    {
        $original = new LiteralExpression('test');
        $clone = $original->deepClone();

        $this->assertNotSame($original, $clone);
        $this->assertEquals($original->getValue(), $clone->getValue());
        $this->assertEquals($original->getType(), $clone->getType());
    }

    /**
     * Teste l'optimisation
     */
    public function testOptimization(): void
    {
        $expression = new LiteralExpression(42);
        $optimized = $expression->optimize();

        // Les expressions littérales sont déjà optimales
        $this->assertSame($expression, $optimized);
    }

    /**
     * Teste la gestion des erreurs avec des types invalides
     */
    public function testInvalidTypeHandling(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Tentative de création avec un type non supporté (array)
        new LiteralExpression([1, 2, 3]);
    }

    /**
     * Teste la méthode number() avec une valeur non numérique
     */
    public function testNumberMethodWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        LiteralExpression::number('not a number');
    }
}

