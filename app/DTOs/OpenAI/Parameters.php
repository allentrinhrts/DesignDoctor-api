<?php

namespace App\DTOs\OpenAI;

class Parameters
{
    /** @var string $type */
    private string $type;

    /** @var Property[] $properties */
    private array $properties;

    /** @var string[] $required */
    private array $required;

    function __construct(string $type, array $properties, array $required)
    {
        $this->type = $type;
        $this->properties = $properties;
        $this->required = $required;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getRequired(): array
    {
        return $this->required;
    }

    public function toArray(): array
    {
        $properties = [];
        foreach ($this->properties as $property) {
            $properties = array_merge($properties, $property->toArray());
        }

        return [
            'type' => $this->type,
            'properties' => (object) $properties,
            'required' => $this->required,
        ];
    }
}
