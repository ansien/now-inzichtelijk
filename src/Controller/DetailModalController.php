<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Manager\BatchEntryApiManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DetailModalController extends AbstractController
{
    private BatchEntryApiManager $batchEntryApiManager;

    public function __construct(BatchEntryApiManager $batchEntryApiManager)
    {
        $this->batchEntryApiManager = $batchEntryApiManager;
    }

    #[Route('/company/{id}/detail-modal', name: 'company_detail_modal')]
    public function handleRequest(Company $company): Response
    {
        $entryData = $this->batchEntryApiManager->getEntriesForCompany($company);

        return $this->render('detail-modal.html.twig', [
            'entries' => $entryData,
        ]);
    }
}
