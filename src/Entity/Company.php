<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompanyRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="company_name_idx", columns={"company_name"}),
 *     @ORM\Index(name="place_name_idx", columns={"place_name"}),
 * })
 */
class Company
{
    // region Properties

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var Collection<Entry>
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Entry", mappedBy="company")
     */
    private Collection $entries;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $companyName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $placeName;

    // endregion

    public function __construct(string $companyName, string $placeName)
    {
        $this->companyName = $companyName;
        $this->placeName = $placeName;

        $this->entries = new ArrayCollection();
    }

    // region Getters

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<Entry>
     */
    public function getEntries()
    {
        return $this->entries;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getPlaceName(): string
    {
        return $this->placeName;
    }

    // endregion
}
