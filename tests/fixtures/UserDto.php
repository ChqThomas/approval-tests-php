<?php

namespace Tests\fixtures;

class UserDto
{
    public string $name;
    public \DateTimeInterface $createdAt;

    public ?UserDto $child = null;

    public function __construct(string $name = 'John Doe', UserDto $child = null)
    {
        $this->name = $name;
        $this->createdAt = new \DateTime('now');
        $this->child = $child;
    }

    public function __toString()
    {
        return $this->name;
    }
}
