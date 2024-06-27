<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Middleware;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Stack\StackInterface;

interface MiddlewareInterface extends HasPriorityInterface
{
    public const PRIORITY_CORE_AFTER = 5000;

    public const PRIORITY_CORE_BEFORE = -5000;

    public function process(WebhookInterface $webhook, StackInterface $stack): WebhookResultInterface;
}
