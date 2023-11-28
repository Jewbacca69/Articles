<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;

class Article
{
    private ?int $id;
    private string $title;
    private string $description;
    private string $picture;
    private Carbon $createdAt;
    private ?Carbon $updatedAt;

    public function __construct(
        ?int    $id = null,
        string  $title,
        string  $description,
        string  $picture,
        string  $createdAt = null,
        ?string $updatedAt = null
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->picture = $picture;
        $this->createdAt = $createdAt == null ? Carbon::now() : new Carbon($createdAt);
        $this->updatedAt = $updatedAt ? new Carbon($updatedAt) : null;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPicture(): string
    {
        return $this->picture;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt->format('Y/m/d H:i');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedAt(): ?string
    {
        if ($this->updatedAt != null) {
            return $this->updatedAt->format('Y/m/d H:i');
        }

        return $this->updatedAt;
    }

    public function update(array $data): void
    {
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->picture = $data['picture'];
        $this->updatedAt = Carbon::now();
    }
}