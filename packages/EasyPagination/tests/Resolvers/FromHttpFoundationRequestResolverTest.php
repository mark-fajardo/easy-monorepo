<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Tests\Resolvers;

use EonX\EasyPagination\Resolvers\FromHttpFoundationRequestResolver;
use EonX\EasyPagination\Tests\AbstractTestCase;

final class FromHttpFoundationRequestResolverTest extends AbstractTestCase
{
    public function testCustomConfigResolveSuccessfully(): void
    {
        $config = $this->createConfig(pageAttr: 'page', perPageAttr: 'perPage');
        $resolver = new FromHttpFoundationRequestResolver($config, $this->createServerRequest([
            'page' => 5,
            'perPage' => 100,
        ]));

        $data = $resolver();

        self::assertEquals(5, $data->getPage());
        self::assertEquals(100, $data->getPerPage());
    }

    public function testDefaultWhenQueryAttrNotSet(): void
    {
        $config = $this->createConfig();
        $resolver = new FromHttpFoundationRequestResolver($config, $this->createServerRequest([]));

        $data = $resolver();

        self::assertEquals($config->getPageDefault(), $data->getPage());
        self::assertEquals($config->getPerPageDefault(), $data->getPerPage());
    }
}
