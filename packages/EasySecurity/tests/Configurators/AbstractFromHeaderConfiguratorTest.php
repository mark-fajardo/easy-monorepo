<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Configurators;

use EonX\EasySecurity\SecurityContext;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\PermissionFromHeaderConfiguratorStub;
use Symfony\Component\HttpFoundation\Request;

final class AbstractFromHeaderConfiguratorTest extends AbstractTestCase
{
    public function testPermissionNotSetWhenNotApiKeyToken(): void
    {
        $context = new SecurityContext();
        $configurator = new PermissionFromHeaderConfiguratorStub('my-permission', ['my-header']);
        $configurator->configure($context, new Request());

        self::assertFalse($context->hasPermission('my-permission'));
    }

    public function testPermissionSetWhenApiKeyToken(): void
    {
        $context = new SecurityContext();
        $request = new Request([], [], [], [], [], ['HTTP_my-header' => 'value']);

        $configurator = new PermissionFromHeaderConfiguratorStub('my-permission', ['my-header']);
        $configurator->configure($context, $request);

        self::assertTrue($context->hasPermission('my-permission'));
    }
}
