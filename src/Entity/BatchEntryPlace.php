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
     * @ORM\OneToMany(targetEntity="App\Entity\FirstBatchEntry", mappedBy="place")
     */
    private Collection $entries;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    // endregion

    public function __construct(string $name)
    {
        $this->entries = new ArrayCollection();
        $this->name = $name;
    }
}
