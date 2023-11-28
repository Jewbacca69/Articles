<?php

namespace App\Services\Article;

use App\Models\Article;
use App\Repositories\ArticleRepository;
use App\Repositories\MysqlArticleRepository;

class ShowArticleService
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new MysqlArticleRepository();
    }

    public function execute(string $id): Article
    {
        $article = $this->articleRepository->getById($id);

        if (!$article) {
            $_SESSION["flush"] = ["success" => false, "message" => "Article not found."];
        }

        return $article;
    }
}