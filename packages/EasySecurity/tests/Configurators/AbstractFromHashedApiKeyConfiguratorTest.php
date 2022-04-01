<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Configurators;

use EonX\EasyApiToken\Tokens\HashedApiKey;
use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\PermissionFromHashedApiKeyConfiguratorStub;
use Symfony\Component\HttpFoundation\Request;

final class AbstractFromHashedApiKeyConfiguratorTest extends AbstractTestCase
{
    public function testPermissionNotSetWhenNotHashedApiKeyToken(): void
    {
        $context = new SecurityContext();
        $configurator = new PermissionFromHashedApiKeyConfiguratorStub('my-permission');
        $configurator->configure($context, new Request());

        self::assertFalse($context->hasPermission('my-permission'));
    }

    public function testPermissionSetWhenHashedApiKeyToken(): void
    {
        $context = new SecurityContext();
        $context->setToken(new HashedApiKey('my-id', 'api-key', 'v1', 'api-key'));

        $configurator = new PermissionFromHashedApiKeyConfiguratorStub('my-permission');
        $configurator->configure($context, new Request());

        self::assertTrue($context->hasPermission('my-permission'));
    }
}
