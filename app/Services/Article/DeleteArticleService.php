<?php

namespace App\Services\Article;

use App\Repositories\ArticleRepository;
use App\Repositories\MysqlArticleRepository;

class DeleteArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new MysqlArticleRepository();
    }

    public function execute(string $id): void
    {
        $article = $this->articleRepository->getById($id);

        $this->articleRepository->delete($article);
    }
}