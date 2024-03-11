<?php

namespace App\DTOs\OpenAI;

class Property
{
    /** @var string $name */
    private string $name;

    /** @var string $type */
    private string $type;

    /** @var string $description */
    private string $description;

    function __construct(string $name, string $type, string $description,)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            $this->name => [
                'type' => $this->type,
                'description' => $this->description,
            ],
        ];
    }
}
