<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BatchEntryCountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BatchEntryCountryRepository::class)
 */
class BatchEntryCountry
{
    // region Properties

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BatchEntryPlace", mappedBy="country")
     */
    private Collection $places;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $code;

    // endregion

    public function __construct(string $name, string $code)
    {
        $this->name = $name;
        $this->code = $code;

        $this->places = new ArrayCollection();
    }

    // region Getters

    /**
     * @return Collection|BatchEntryPlace[]
     */
    public function getPlaces()
    {
        return $this->places;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    // endregion
}
