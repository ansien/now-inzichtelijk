<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BatchEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BatchEntryRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="company_name_idx", columns={"company_name"}),
 *     @ORM\Index(name="one_zero_idx", columns={"one_zero_amount"}),
 *     @ORM\Index(name="one_one_idx", columns={"one_one_amount"}),
 *     @ORM\Index(name="two_zero_idx", columns={"two_zero_amount"}),
 *     @ORM\Index(name="three_zero_idx", columns={"three_zero_amount"}),
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
    private int $oneZeroAmount;
    /**
     * @ORM\Column(type="integer")
     */
    private int $oneOneAmount;

    /**
     * @ORM\Column(type="integer")
     */
    private int $twoZeroAmount;

    /**
     * @ORM\Column(type="integer")
     */
    private int $threeZeroAmount;

    /**
     * @ORM\Column(type="integer")
     */
    private int $totalAmount;

    // endregion

    public function __construct(string $companyName, BatchEntryPlace $place)
    {
        $this->companyName = $companyName;
        $this->place = $place;
        $this->oneZeroAmount = 0;
        $this->oneOneAmount = 0;
        $this->twoZeroAmount = 0;
        $this->threeZeroAmount = 0;
        $this->totalAmount = 0;
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

    public function getOneZeroAmount(): int
    {
        return $this->oneZeroAmount;
    }

    public function getOneOneAmount(): int
    {
        return $this->oneOneAmount;
    }

    public function getTwoZeroAmount(): int
    {
        return $this->twoZeroAmount;
    }

    public function getThreeZeroAmount(): int
    {
        return $this->threeZeroAmount;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    // endregion

    // region Setters

    public function setOneZeroAmount(int $oneZeroAmount): self
    {
        $this->oneZeroAmount = $oneZeroAmount;

        return $this;
    }

    public function setOneOneAmount(int $oneOneAmount): self
    {
        $this->oneOneAmount = $oneOneAmount;

        return $this;
    }

    public function setTwoZeroAmount(int $twoZeroAmount): self
    {
        $this->twoZeroAmount = $twoZeroAmount;

        return $this;
    }

    public function setThreeZeroAmount(int $threeZeroAmount): self
    {
        $this->threeZeroAmount = $threeZeroAmount;

        return $this;
    }

    public function recalculateTotalAmount(): self
    {
        $this->totalAmount = $this->oneOneAmount + $this->twoZeroAmount + $this->threeZeroAmount;

        return $this;
    }

    // endregion
}
