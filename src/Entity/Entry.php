<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="deposited_amount_idx", columns={"deposited_amount"}),
 *     @ORM\Index(name="updated_amount_idx", columns={"updated_amount"}),
 * })
 */
class Entry
{
    // region Properties

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="entries", cascade={"persist"})
     */
    private Company $company;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     */
    private int $batch;

    /**
     * VERSTREKT VOORSCHOT.
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $depositedAmount;

    /**
     * VASTGESTELDE SUBSIDIE.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $updatedAmount;

    // endregion

    public function __construct(int $batch, Company $company, int $depositedAmount, ?int $updatedAmount)
    {
        $this->batch = $batch;
        $this->company = $company;
        $this->depositedAmount = $depositedAmount;
        $this->updatedAmount = $updatedAmount;
    }

    // region Getters

    public function getId(): int
    {
        return $this->id;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getBatch(): int
    {
        return $this->batch;
    }

    public function getDepositedAmount(): int
    {
        return $this->depositedAmount;
    }

    public function getUpdatedAmount(): ?int
    {
        return $this->updatedAmount;
    }

    // endregion
}
