<?php
declare(strict_types=1);

use EonX\EasyApiPlatform\EasyErrorHandler\Builder\ApiPlatformErrorResponseBuilderInterface;
use EonX\EasyBugsnag\Bundle\EasyBugsnagBundle;
use EonX\EasyErrorHandler\Bundle\EasyErrorHandlerBundle;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    $rootNode = $definition->rootNode();
    $rootNode
        ->children()
            ->arrayNode('advanced_search_filter')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('iri_fields')
                        ->defaultValue([])
                        ->info('Fields that could be passed as IRI')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('custom_paginator')
                ->canBeDisabled()
            ->end()
            ->arrayNode('output_sanitizer')
                ->canBeEnabled()
            ->end()
            ->arrayNode('return_not_found_on_read_operations')
                ->canBeDisabled()
            ->end()
        ->end();

    if (\class_exists(EasyErrorHandlerBundle::class)) {
        $easyErrorHandlerDefinition = $rootNode->children()
            ->arrayNode('easy_error_handler')
                ->canBeDisabled()
                ->children();

        $easyErrorHandlerDefinition->append(
            (new NodeBuilder())
                ->arrayNode('custom_serializer_exceptions')
                    ->info('Custom serializer exceptions to be handled by '
                       . ApiPlatformErrorResponseBuilderInterface::class)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('class')->isRequired()->end()
                            ->scalarNode('message_pattern')->isRequired()->end()
                            ->scalarNode('violation_message')->isRequired()->end()
                        ->end()
                    ->end()
        );

        $easyErrorHandlerDefinition->append(
            (new NodeBuilder())
                ->variableNode('validation_error_code')
                ->validate()
                    ->ifTrue(
                        static fn ($value): bool => \is_int($value) === false
                            && \is_string($value) === false
                            && ($value instanceof BackedEnum) === false
                    )
                    ->thenInvalid('The validation_error_code must be an int, string, or BackedEnum.')
                ->end()
                ->defaultNull()
        );

        if (\class_exists(EasyBugsnagBundle::class)) {
            $easyErrorHandlerDefinition->append(
                (new NodeBuilder())
                    ->booleanNode('report_exceptions_to_bugsnag')
                        ->info('Report exceptions handled by '
                        . ApiPlatformErrorResponseBuilderInterface::class . ' to Bugsnag')
                        ->defaultFalse()
            );
        }
    }
};
