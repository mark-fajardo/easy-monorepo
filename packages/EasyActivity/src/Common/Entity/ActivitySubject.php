<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Entity;

final readonly class ActivitySubject implements ActivitySubjectInterface
{
    /**
     * @param list<string> $disallowedProperties
     * @param array<string, list<string>> $nestedObjectAllowedProperties
     * @param list<string>|array<string, list<string>>|null $allowedProperties
     */
    public function __construct(
        private string $id,
        private string $type,
        private array $disallowedProperties,
        private array $nestedObjectAllowedProperties,
        private ?array $allowedProperties = null,
    ) {
    }

    public function getActivitySubjectId(): string
    {
        return $this->id;
    }

    public function getActivitySubjectType(): string
    {
        return $this->type;
    }

    public function getAllowedActivityProperties(): ?array
    {
        return $this->allowedProperties;
    }

    public function getDisallowedActivityProperties(): array
    {
        return $this->disallowedProperties;
    }

    public function getNestedObjectAllowedActivityProperties(): array
    {
        return $this->nestedObjectAllowedProperties;
    }
}
