<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BatchEntryPlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BatchEntryPlaceRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="name_idx", columns={"name"}),
 * })
 */
class BatchEntryPlace
{
    // region Properties

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BatchEntryCountry", inversedBy="places")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?BatchEntryCountry $country;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BatchEntryAdminLevel", inversedBy="places")
     */
    private Collection $adminLevels;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FirstBatchEntry", mappedBy="place")
     */
    private Collection $entries;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=8, nullable=true)
     */
    private ?string $latitude = null;

    /**
     * @ORM\Column(type="string", precision=10, scale=8, nullable=true)
     */
    private ?string $longitude = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $requiresHydration;

    // endregion

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->requiresHydration = true;

        $this->entries = new ArrayCollection();
        $this->adminLevels = new ArrayCollection();
    }

    // region Getters

    public function getCountry(): ?BatchEntryCountry
    {
        return $this->country;
    }

    /**
     * @return Collection|BatchEntryAdminLevel[]
     */
    public function getAdminLevels(): Collection
    {
        return $this->adminLevels;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    // endregion

    // region Setters

    public function setCountry(?BatchEntryCountry $country): BatchEntryPlace
    {
        $this->country = $country;

        return $this;
    }

    public function addAdminLevel(BatchEntryAdminLevel $adminLevel): self
    {
        if (!$this->adminLevels->contains($adminLevel)) {
            $this->adminLevels[] = $adminLevel;
        }

        return $this;
    }

    public function removeAdminLevel(BatchEntryAdminLevel $adminLevel): self
    {
        if ($this->adminLevels->contains($adminLevel)) {
            $this->adminLevels->removeElement($adminLevel);
        }

        return $this;
    }

    public function setLatitude(?string $latitude): BatchEntryPlace
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function setLongitude(?string $longitude): BatchEntryPlace
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function setRequiresHydration(bool $requiresHydration): BatchEntryPlace
    {
        $this->requiresHydration = $requiresHydration;

        return $this;
    }

    // endregion
}
