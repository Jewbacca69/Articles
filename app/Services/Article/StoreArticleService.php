<?php

namespace App\Services\Article;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Repositories\MysqlArticleRepository;

class StoreArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new MysqlArticleRepository();
    }

    public function execute(string $title, string $description, string $picture): void
    {
        $article = new Article(
            null,
            $title,
            $description,
            $picture
        );

        $this->articleRepository->save($article);
    }
}