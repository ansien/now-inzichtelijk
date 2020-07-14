<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApiRequestRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApiRequestRepository::class)
 */
class ApiRequest
{
    // region Properties

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $endpoint;

    /**
     * @ORM\Column(type="json")
     */
    private array $query;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $createdAt;

    // endregion

    public function __construct(string $endpoint, array $query)
    {
        $this->endpoint = $endpoint;
        $this->query = $query;
        $this->createdAt = new DateTime();
    }
}
