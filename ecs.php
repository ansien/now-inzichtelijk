<?php

declare(strict_types=1);

use ECSPrefix20210612\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use SlevomatCodingStandard\Sniffs\ControlStructures\RequireShortTernaryOperatorSniff;
use SlevomatCodingStandard\Sniffs\Functions\UnusedInheritedVariablePassedToClosureSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Operators\RequireCombinedAssignmentOperatorSniff;
use SlevomatCodingStandard\Sniffs\PHP\DisallowDirectMagicInvokeCallSniff;
use SlevomatCodingStandard\Sniffs\PHP\UselessSemicolonSniff;
use SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff;
use SlevomatCodingStandard\Sniffs\Variables\UselessVariableSniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\Commenting\ParamReturnAndVarTagMalformsFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $services = $containerConfigurator->services();
    $services
        ->set(DeclareStrictTypesFixer::class)
        ->set(DisallowDirectMagicInvokeCallSniff::class)
        ->set(ParamReturnAndVarTagMalformsFixer::class)
        ->set(UnusedVariableSniff::class)
        ->set(UselessVariableSniff::class)
        ->set(UnusedInheritedVariablePassedToClosureSniff::class)
        ->set(UselessSemicolonSniff::class)
        ->set(ArraySyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]])
        ->set(NoUnusedImportsFixer::class)
        ->set(OrderedImportsFixer::class)
        ->set(NoEmptyStatementFixer::class)
        ->set(ProtectedToPrivateFixer::class)
        ->set(NoUnneededControlParenthesesFixer::class)
        ->set(NoUnneededCurlyBracesFixer::class)
        ->set(ReturnAssignmentFixer::class)
        ->set(RequireShortTernaryOperatorSniff::class)
        ->set(RequireCombinedAssignmentOperatorSniff::class)
        ->set(PrivatesAccessor::class)
        ->set(FinalInternalClassFixer::class)
        ->set(PhpdocLineSpanFixer::class)
        ->set(ReferenceUsedNamesOnlySniff::class)
        ->property('searchAnnotations', true)
        ->property('allowFallbackGlobalFunctions', true)
        ->property('allowFallbackGlobalConstants', true)
        ->property('allowPartialUses', false);

    $containerConfigurator->import(SetList::PSR_12);
};
