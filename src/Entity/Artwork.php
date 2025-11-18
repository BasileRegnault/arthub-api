<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Enum\ArtworkStyle;
use App\Enum\ArtworkType;
use App\Repository\ArtworkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ],
    normalizationContext: ['groups' => ['artwork:read']],
    denormalizationContext: ['groups' => ['artwork:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'type' => 'exact',
    'style' => 'exact',
    'artist' => 'exact',
    'isDisplay' => 'exact'
])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ArtworkRepository::class)]
class Artwork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['artwork:read', 'artist:read', 'gallery:read', 'rating:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['artwork:read', 'artwork:write', 'artist:read', 'gallery:read'])]
    private ?string $title = null;

    #[ORM\Column(enumType: ArtworkType::class)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?ArtworkType $type = null;

    #[ORM\Column(enumType: ArtworkStyle::class)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?ArtworkStyle $style = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?\DateTimeImmutable $creationDate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?string $location = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?int $views = null;

    #[ORM\ManyToOne(inversedBy: 'artworks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?Artist $artist = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?bool $isDisplay = null;

    /**
     * @var Collection<int, Gallery>
     */
    #[ORM\ManyToMany(targetEntity: Gallery::class, mappedBy: 'artworks')]
    private Collection $galleries;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'artwork')]
    #[Groups(['artwork:read', 'artwork:write'])]
    private Collection $ratings;

    public function __construct()
    {
        $this->galleries = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?ArtworkType
    {
        return $this->type;
    }

    public function setType(ArtworkType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStyle(): ?ArtworkStyle
    {
        return $this->style;
    }

    public function setStyle(ArtworkStyle $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(?int $views): static
    {
        $this->views = $views;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): static
    {
        $this->artist = $artist;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isDisplay(): ?bool
    {
        return $this->isDisplay;
    }

    public function setIsDisplay(bool $isDisplay): static
    {
        $this->isDisplay = $isDisplay;

        return $this;
    }

    /**
     * @return Collection<int, Gallery>
     */
    public function getGalleries(): Collection
    {
        return $this->galleries;
    }

    public function addGallery(Gallery $gallery): static
    {
        if (!$this->galleries->contains($gallery)) {
            $this->galleries->add($gallery);
            $gallery->addArtwork($this);
        }

        return $this;
    }

    public function removeGallery(Gallery $gallery): static
    {
        if ($this->galleries->removeElement($gallery)) {
            $gallery->removeArtwork($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setArtwork($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getArtwork() === $this) {
                $rating->setArtwork(null);
            }
        }

        return $this;
    }
}
