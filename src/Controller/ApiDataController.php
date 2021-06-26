<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\BatchEntryApiManager;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ApiDataController extends AbstractController
{
    private BatchEntryApiManager $batchEntryApiManager;

    public function __construct(BatchEntryApiManager $batchEntryApiManager)
    {
        $this->batchEntryApiManager = $batchEntryApiManager;
    }

    #[Route('/api/v1/data', name: 'api_v1_data')]
    public function handleRequest(Request $request): Response
    {
        try {
            $data = $this->batchEntryApiManager->getBatchEntries(
                $request->query->getInt('page', 1),
                $request->query->get('order'),
                $request->query->get('search')
            );
        } catch (JsonException) {
            return new Response('Failed to load API data', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'data' => $data,
        ]);
    }
}
