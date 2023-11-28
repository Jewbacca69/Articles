<?php

namespace App\Repositories;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use App\Models\Article;
use App\Collections\ArticleCollection;

class MysqlArticleRepository implements ArticleRepository
{
    protected Connection $db;

    public function __construct()
    {
        $connectionParams = [
            'dbname' => $_ENV['DB_DATABASE'],
            'user' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => $_ENV['DB_HOSTNAME'],
            'driver' => $_ENV['DB_DRIVER'],
        ];
        $this->db = DriverManager::getConnection($connectionParams);
    }

    public function getAll(): ArticleCollection
    {

        $articles = $this->db->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->orderBy('id', 'desc')
            ->fetchAllAssociative();

        $articlesCollection = new ArticleCollection();

        foreach ($articles as $article) {
            $articlesCollection->addArticle(
                $this->buildArticleModel($article)
            );
        }

        return $articlesCollection;
    }

    public function getByID(string $id): ?Article
    {
        $articleData = $this->db->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->fetchAssociative();

        if (empty($articleData)) {
            $_SESSION["flush"] = ["success" => false, "message" => "Article not found."];
        }

        return $this->buildArticleModel($articleData);
    }

    public function save(Article $article): void
    {
        if ($article->getId()) {
            $this->update($article);
            return;
        }
        $this->insert($article);
    }

    public function insert(Article $article): void
    {
        $this->db->createQueryBuilder()
            ->insert('articles')
            ->values(
                [
                    'title' => ':title',
                    'description' => ':description',
                    'picture' => ':picture',
                    'created_at' => ':created_at'
                ]
            )->setParameters([
                'title' => $article->getTitle(),
                'description' => $article->getDescription(),
                'picture' => $article->getPicture(),
                'created_at' => $article->getCreatedAt()
            ])->executeQuery();
    }

    public function update(Article $article): void
    {
        $this->db->createQueryBuilder()
            ->update('articles')
            ->set('title', ':title')
            ->set('description', ':description')
            ->set('picture', ':picture')
            ->set('updated_at', ':updated_at')
            ->where('id = :id')
            ->setParameters([
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'description' => $article->getDescription(),
                'picture' => $article->getPicture(),
                'updated_at' => $article->getUpdatedAt()
            ])->executeQuery();
    }

    public function delete(Article $article): void
    {
        $this->db->createQueryBuilder()
            ->delete('articles')
            ->where('id = :id')
            ->setParameter('id', $article->getId())
            ->executeQuery();
    }

    private function buildArticleModel(array $articleData): Article
    {
        return new Article(
            $articleData["id"],
            $articleData['title'],
            $articleData['description'],
            $articleData['picture'],
            $articleData['created_at'],
            $articleData['updated_at'],
        );
    }
}