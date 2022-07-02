<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function Symfony\Component\String\u;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('easy_swoole');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('access_log')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('timezone')->defaultValue('UTC')->end()
                    ->end()
                ->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('easy_batch')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->booleanNode('reset_batch_processor')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('request_limits')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->integerNode('min')->defaultValue(5000)->end()
                        ->integerNode('max')->defaultValue(10000)->end()
                    ->end()
                ->end()
                ->arrayNode('reset_services')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('static_php_files')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->arrayNode('allowed_dirs')
                            ->defaultValue(['%kernel.project_dir%/public'])
                            ->beforeNormalization()
                                ->always(static function ($value): array {
                                    if ($value === null) {
                                        $value = [];
                                    }

                                    // Remove trailing slashes
                                    $dirs = \array_map(static function ($mapValue): string {
                                        $dir = u((string) $mapValue);

                                        while ($dir->endsWith('/')) {
                                            $dir = $dir->trimSuffix('/');
                                        }

                                        return $dir->toString();
                                    }, \is_array($value) ? $value : [$value]);

                                    // Filter empty strings
                                    $dirs = \array_filter($dirs, static function ($filterValue): bool {
                                        return \is_string($filterValue) && $filterValue !== '';
                                    });

                                    // Default to public dir if not set
                                    return \count($dirs) > 0 ? $dirs : ['%kernel.project_dir%/public'];
                                })
                            ->end()
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('allowed_filenames')
                            ->beforeNormalization()
                                ->always(static function ($value): array {
                                    if ($value === null) {
                                        $value = [];
                                    }

                                    return \array_map(static function ($mapValue): string {
                                        $phpFile = u((string) $mapValue)->ensureStart('/');

                                        if ($phpFile->endsWith('.php') === false) {
                                            throw new \InvalidArgumentException(\sprintf(
                                                'Only PHP files allowed, %s given',
                                                $phpFile->toString()
                                            ));
                                        }

                                        return $phpFile->toString();
                                    }, \is_array($value) ? $value : [$value]);
                                })
                            ->end()
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
