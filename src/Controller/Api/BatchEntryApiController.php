<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Manager\ApiRequestManager;
use App\Manager\BatchEntryApiManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class BatchEntryApiController extends AbstractController
{
    private BatchEntryApiManager $batchEntryApiManager;
    private ApiRequestManager $apiRequestManager;

    public function __construct(BatchEntryApiManager $batchEntryApiManager, ApiRequestManager $apiRequestManager)
    {
        $this->batchEntryApiManager = $batchEntryApiManager;
        $this->apiRequestManager = $apiRequestManager;
    }

    /**
     * @Route("/api/v1/batch-entry", name="api_v1_batch_entry")
     */
    public function handleRequest(Request $request): JsonResponse
    {
        $data = $this->batchEntryApiManager->getBatchEntries(
            $request->query->getInt('page', 1),
            $request->query->get('order'),
            $request->query->get('search')
        );

        $this->apiRequestManager->saveApiRequest('api_v1_batch_entry', $request->query->all());

        return new JsonResponse([
            'data' => $data,
        ]);
    }
}
