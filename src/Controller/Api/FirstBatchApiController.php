<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Manager\ApiRequestManager;
use App\Manager\FirstBatchEntryApiManager;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FirstBatchApiController extends AbstractController
{
    private FirstBatchEntryApiManager $firstBatchEntryApiManager;
    private ApiRequestManager $apiRequestManager;

    public function __construct(FirstBatchEntryApiManager $firstBatchEntryApiManager, ApiRequestManager $apiRequestManager)
    {
        $this->firstBatchEntryApiManager = $firstBatchEntryApiManager;
        $this->apiRequestManager = $apiRequestManager;
    }

    /**
     * @Route("/api/v1/first-batch", name="api_v1_first_batch")
     */
    public function handleRequest(Request $request): JsonResponse
    {
        try {
            $data = $this->firstBatchEntryApiManager->getFirstBatchEntries(
                $request->query->getInt('page', 1),
                $request->query->get('order'),
                $request->query->get('search')
            );
        } catch (JsonException $e) {
            $data = [];
        }

        $this->apiRequestManager->saveApiRequest('api_v1_first_batch', $request->query->all());

        return new JsonResponse([
            'data' => $data,
        ]);
    }
}
