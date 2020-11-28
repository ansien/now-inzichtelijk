<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SecondBatchEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SecondBatchEntryRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="company_name_idx", columns={"company_name"}),
 *     @ORM\Index(name="amount_idx", columns={"amount"})
 * })
 */
class SecondBatchEntry
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
    private string $companyName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BatchEntryPlace", inversedBy="entries")
     */
    private BatchEntryPlace $place;

    /**
     * @ORM\Column(type="integer")
     */
    private int $amount;

    // endregion

    public function __construct(string $companyName, BatchEntryPlace $place, int $amount)
    {
        $this->companyName = $companyName;
        $this->place = $place;
        $this->amount = $amount;
    }

    // region Getters

    public function getId(): int
    {
        return $this->id;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getPlace(): BatchEntryPlace
    {
        return $this->place;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    // endregion
}
