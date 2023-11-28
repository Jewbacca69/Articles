<?php

namespace App\Repositories;

use App\Collections\ArticleCollection;
use App\Models\Article;

interface ArticleRepository
{
    public function getAll(): ArticleCollection;

    public function getById(string $id): ?Article;

    public function save(Article $article): void;

    public function delete(Article $article): void;
}