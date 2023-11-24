<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\HttpClient\HttpClient;
use App\Collections\ArticleCollection;
use App\Response\RedirectResponse;
use App\Response\ViewResponse;
use App\Response\Response;
use App\Models\Article;
use Carbon\Carbon;

class ArticleController extends BaseController
{
    public function index(): Response
    {
        $articles = $this->database->createQueryBuilder()
            ->select("*")
            ->from("articles")
            ->fetchAllAssociative();

        $articleCollection = new ArticleCollection();

        foreach ($articles as $article) {
            $articleCollection->addArticle(new Article(
                (int)$article["id"],
                $article["title"],
                $article["description"],
                $article["picture"],
                $article["created_at"],
                $article["updated_at"]
            ));
        }

        return new ViewResponse("index", ["articles" => $articleCollection->getAllArticles()]);
    }

    public function show(string $id): Response
    {
        $article = $this->database->createQueryBuilder()
            ->select("*")
            ->from("articles")
            ->where("id = ?")
            ->setParameter(0, $id)
            ->fetchAssociative();

        $article = new Article
        (
            (int)$article["id"],
            $article["title"],
            $article["description"],
            $article["picture"],
            $article["created_at"],
            $article["updated_at"]
        );

        return new ViewResponse("show", ["article" => $article]);
    }

    public function create(): Response
    {
        return new ViewResponse("create", []);
    }

    public function store(): RedirectResponse
    {

        if (empty($_POST["title"])) {
            $this->addNotification(false, "Title is required!");

            return new RedirectResponse("/article/create");
        }

        if (empty($_POST["description"])) {
            $this->addNotification(false, "Description is required!");

            return new RedirectResponse("/article/create");
        }

        $image = !empty($_POST["image"]) ? $_POST["image"] : $this->getRandomImage();

        $this->database->createQueryBuilder()
            ->insert('articles')
            ->values(
                [
                    'title' => '?',
                    'description' => '?',
                    'picture' => '?',
                    'created_at' => '?',
                ]
            )
            ->setParameter(0, $_POST["title"])
            ->setParameter(1, $_POST["description"])
            ->setParameter(2, $image)
            ->setParameter(3, Carbon::now())
            ->executeQuery();

        $this->addNotification(true, "Article created successfully!");

        return new RedirectResponse("/");
    }

    public function edit(string $id): Response
    {
        $article = $this->database->createQueryBuilder()
            ->select("*")
            ->from("articles")
            ->where("id = ?")
            ->setParameter(0, $id)
            ->fetchAssociative();

        $article = new Article
        (
            (int)$article["id"],
            $article["title"],
            $article["description"],
            $article["picture"],
            $article["created_at"],
            $article["updated_at"]
        );

        return new ViewResponse("edit", ["article" => $article]);
    }

    public function update(string $id): RedirectResponse
    {
        if (empty($_POST["title"])) {
            $this->addNotification(false, "Title is required!");
        }

        if (empty($_POST["description"])) {
            $this->addNotification(false, "Description is required!");
        }

        $image = !empty($_POST["image"]) ? $_POST["image"] : $this->getRandomImage();
        $createdAt = Carbon::now()->format('Y-m-d H:i:s');
        $this->database->createQueryBuilder()
            ->update('articles')
            ->set('title', '?')
            ->set('description', '?')
            ->set('picture', '?')
            ->set('updated_at', '?')
            ->where('id = ?')
            ->setParameters([
                $_POST["title"],
                $_POST["description"],
                $image,
                $createdAt,
                $id,
            ])
            ->executeQuery();

        $this->addNotification(true, "Article successfully updated!");

        return new RedirectResponse("/article/" . $id);
    }

    public function delete(string $id): RedirectResponse
    {
        $this->database->createQueryBuilder()
            ->delete("articles")
            ->where("id = ?")
            ->setParameter(0, $id)
            ->executeQuery();

        $this->addNotification(true, "Article successfully deleted!");

        return new RedirectResponse("/");
    }

    private function getRandomImage(): string
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://picsum.photos/v2/list');

        $content = $response->toArray();

        return $content[array_rand($content)]['download_url'];
    }

    private function addNotification(bool $success, string $message): void
    {
        $_SESSION["flush"] = ["success" => $success, "message" => $message];
    }
}
