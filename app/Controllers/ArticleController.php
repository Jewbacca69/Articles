<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;
use App\Repositories\MysqlArticleRepository;
use App\Services\Article\DeleteArticleService;
use App\Services\Article\IndexArticleService;
use App\Services\Article\ShowArticleService;
use App\Services\Article\StoreArticleService;
use App\Services\Article\UpdateArticleService;
use Symfony\Component\HttpClient\HttpClient;
use App\Response\RedirectResponse;
use App\Response\ViewResponse;
use App\Response\Response;

class ArticleController
{
    private ArticleRepository $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new MysqlArticleRepository();
    }

    public function index(): ViewResponse
    {
        $service = new IndexArticleService();

        $articles = $service->execute();

        return new ViewResponse("index", ["articles" => $articles]);
    }

    public function show(string $id): Response
    {
        $service = new ShowArticleService();

        $article = $service->execute($id);

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

        $service = new StoreArticleService();

        $service->execute($_POST['title'], $_POST['description'], $image);

        $this->addNotification(true, "Article created successfully!");

        return new RedirectResponse("/");
    }

    public function edit(string $id): Response
    {
        try {
            $service = new ShowArticleService();
            $article = $service->execute($id);

            $this->addNotification(true, "Article successfully updated.");

            return new ViewResponse('edit', ['article' => $article]);
        } catch (\Exception $e) {
            $this->addNotification(false, "Failed to add article.");
        }

        return new RedirectResponse('/');
    }

    public function update(string $id): RedirectResponse
    {
        $article = $this->articleRepository->getById($id);


        if (empty($_POST["title"])) {
            $this->addNotification(false, "Title is required!");

            return new RedirectResponse("/article/edit/" . $id);
        }

        if (empty($_POST["description"])) {
            $this->addNotification(false, "Description is required!");

            return new RedirectResponse("/article/edit/" . $id);
        }

        $image = !empty($_POST["image"]) ? $_POST["image"] : $this->getRandomImage();

        $service = new UpdateArticleService();
        $service->execute($id, $_POST["title"], $_POST["description"], $image);

        $this->addNotification(true, "Article successfully updated!");

        return new RedirectResponse('/article/' . $id);
    }

    public function delete(string $id): RedirectResponse
    {
        $service = new DeleteArticleService();
        $service->execute($id);

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
