<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BatchEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BatchEntryRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="company_name_idx", columns={"company_name"}),
 *     @ORM\Index(name="first_amount_idx", columns={"first_amount"}),
 *     @ORM\Index(name="second_amount_idx", columns={"second_amount"}),
 *     @ORM\Index(name="total_amount_idx", columns={"total_amount"}),
 * })
 */
class BatchEntry
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
    private int $firstAmount;

    /**
     * @ORM\Column(type="integer")
     */
    private int $secondAmount;

    /**
     * @ORM\Column(type="integer")
     */
    private int $totalAmount;

    // endregion

    public function __construct(string $companyName, BatchEntryPlace $place, int $firstAmount, int $secondAmount, int $totalAmount)
    {
        $this->companyName = $companyName;
        $this->place = $place;
        $this->firstAmount = $firstAmount;
        $this->secondAmount = $secondAmount;
        $this->totalAmount = $totalAmount;
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

    public function getFirstAmount(): int
    {
        return $this->firstAmount;
    }

    public function getSecondAmount(): int
    {
        return $this->secondAmount;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    // endregion

    // region Setters

    public function setFirstAmount(int $firstAmount): self
    {
        $this->firstAmount = $firstAmount;

        return $this;
    }

    public function setSecondAmount(int $secondAmount): self
    {
        $this->secondAmount = $secondAmount;

        return $this;
    }

    public function setTotalAmount(int $totalAmount): BatchEntry
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    // endregion
}
