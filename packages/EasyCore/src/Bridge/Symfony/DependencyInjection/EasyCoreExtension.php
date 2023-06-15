<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\DependencyInjection;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use EonX\EasyCore\Bridge\BridgeConstantsInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\EventListenerInterface;
use EonX\EasyCore\Bridge\Symfony\Interfaces\TagsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class EasyCoreExtension extends Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    private $loader;

    /**
     * @param mixed[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $this->container = $container;
        $this->loader = $loader;

        $this->autoconfigTag(EventListenerInterface::class, TagsInterface::EVENT_LISTENER_AUTO_CONFIG);

        if ($config['api_platform']['custom_pagination_enabled'] ?? false) {
            $this->loadIfBundlesExist('api_platform/pagination.php', ApiPlatformBundle::class);
        }

        // Search
        if ($config['search']['enabled'] ?? false) {
            $container->setParameter(
                BridgeConstantsInterface::PARAM_ELASTICSEARCH_HOST,
                $config['search']['elasticsearch_host']
            );

            $loader->load('search.php');
        }

        // Profiler storage
        if ($config['profiler_storage']['enabled'] ?? false) {
            $loader->load('profiler_storage.php');
        }

        // Trim strings
        if ($config['trim_strings']['enabled'] ?? false) {
            $container->setParameter(
                BridgeConstantsInterface::PARAM_TRIM_STRINGS_EXCEPT,
                $config['trim_strings']['except']
            );

            $loader->load('trim_strings.php');
        }

        // Aliases for custom collection operations HTTP methods
        if ($this->bundlesExist(ApiPlatformBundle::class)) {
            foreach (['PATCH', 'PUT'] as $method) {
                $alias = \sprintf('api_platform.action.%s_collection', \strtolower($method));
                $container->setAlias($alias, 'api_platform.action.placeholder')
                    ->setPublic(true);
            }
        }
    }

    /**
     * @param null|mixed[] $attributes
     */
    private function autoconfigTag(string $interface, string $tag, ?array $attributes = null): void
    {
        $this->container->registerForAutoconfiguration($interface)
            ->addTag($tag, $attributes ?? []);
    }

    /**
     * @param string|string[] $bundles
     */
    private function bundlesExist($bundles): bool
    {
        $kernelBundles = $this->container->getParameter('kernel.bundles');

        if (\is_array($kernelBundles) && \count($kernelBundles) > 0) {
            foreach ((array)$bundles as $bundle) {
                if (\in_array($bundle, $kernelBundles, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string|string[] $bundles
     *
     * @throws \Exception
     */
    private function loadIfBundlesExist(string $resource, $bundles): void
    {
        if ($this->bundlesExist($bundles)) {
            $this->loader->load($resource);
        }
    }
}
