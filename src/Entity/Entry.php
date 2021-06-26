<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntryRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="amount_idx", columns={"amount"}),
 * })
 */
final class Entry
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
     * @ORM\Column(type="integer")
     */
    private int $amount;

    // endregion

    public function __construct(int $batch, Company $company, int $amount)
    {
        $this->batch = $batch;
        $this->company = $company;
        $this->amount = $amount;
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

    public function getAmount(): int
    {
        return $this->amount;
    }

    // endregion
}
