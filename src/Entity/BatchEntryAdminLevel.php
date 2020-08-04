<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BatchEntryAdminLevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BatchEntryAdminLevelRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="name_idx", columns={"name"}),
 * })
 */
class BatchEntryAdminLevel
{
    // region Properties

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BatchEntryPlace", mappedBy="adminLevels")
     */
    private Collection $places;

    /**
     * @ORM\Column(type="integer")
     */
    private int $level;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $code;

    // endregion

    public function __construct(int $level, string $name, string $code)
    {
        $this->level = $level;
        $this->name = $name;
        $this->code = $code;

        $this->places = new ArrayCollection();
    }

    // region Getters

    /**
     * @return Collection|BatchEntryPlace[]
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function getLevel(): int
    {
        return $this->level;
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

    // region Setters

    public function addPlace(BatchEntryPlace $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places[] = $place;
            $place->addAdminLevel($this);
        }

        return $this;
    }

    public function removePlace(BatchEntryPlace $place): self
    {
        if ($this->places->contains($place)) {
            $this->places->removeElement($place);
            $place->removeAdminLevel($this);
        }

        return $this;
    }

    // endregion
}
