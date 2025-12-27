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
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Validator\Constraints as Assert;
use App\State\ArtworkProcessor;


use App\Enum\ArtworkStyle;
use App\Enum\ArtworkType;
use App\Repository\ArtworkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    paginationItemsPerPage: 10,
    paginationClientItemsPerPage: true,
    paginationClientEnabled: true,
    operations: [
        new GetCollection(),
        new Get(),
        new Post(processor: ArtworkProcessor::class),
        new Put(),
        new Patch(),
        new Delete()
        //new Put(security: "is_granted('ROLE_ADMIN')"),
        //new Delete(security: "is_granted('ROLE_ADMIN')")
    ],
    normalizationContext: ['groups' => ['artwork:read']],
    denormalizationContext: ['groups' => ['artwork:write']],
    formats: [
        'jsonld' => ['application/ld+json'],
        'multipart' => ['multipart/form-data'],
        'json' => ['application/json'],
    ]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial',
    'type' => 'exact',
    'style' => 'exact',
    'location' => 'partial',
    'artist' => 'exact',
    'artist.firstname' => 'partial',
    'artist.lastname' => 'partial',
])]

#[ApiFilter(BooleanFilter::class, properties: [
    'isDisplay',
    'isConfirmCreate'
])]

#[ApiFilter(DateFilter::class, properties: [
    'creationDate',
    'createdAt',
    'updatedAt',
])]

#[ApiFilter(OrderFilter::class, properties: [
    'title',
    'creationDate',
    'createdAt',
    'views',
], arguments: [
    'orderParameterName' => 'order'
])]
#[Vich\Uploadable]
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
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(min: 2, minMessage: "Le titre doit faire au moins 2 caractères.")]
    private ?string $title = null;

    #[ORM\Column(enumType: ArtworkType::class)]
    #[Groups(['artwork:read', 'artwork:write'])]
    #[Assert\NotBlank(message: "Le type est obligatoire.")]
    private ?ArtworkType $type = null;

    #[ORM\Column(enumType: ArtworkStyle::class)]
    #[Groups(['artwork:read', 'artwork:write'])]
    #[Assert\NotBlank(message: "Le style est obligatoire.")]
    private ?ArtworkStyle $style = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['artwork:read', 'artwork:write'])]
    #[Assert\NotBlank(message: "La date de création est obligatoire.")]
    #[Assert\LessThanOrEqual("today", message: "L'œuvre ne peut pas être datée du futur.")]
    private ?\DateTimeImmutable $creationDate = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['artwork:read', 'artwork:write'])]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(min: 20, minMessage: "La description doit faire au moins 20 caractères.")]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[ApiProperty(types: ['https://schema.org/image'])]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?MediaObject $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?string $location = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['artwork:read'])]
    private ?int $views = 0;

    #[ORM\ManyToOne(inversedBy: 'artworks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['artwork:read', 'artwork:write'])]
    #[Assert\NotNull(message: "L'artiste est obligatoire.")]
    private ?Artist $artist = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups(['artwork:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['artwork:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?bool $isDisplay = true;

    #[ORM\Column]
    #[Groups(['artwork:read', 'artwork:write'])]
    private ?bool $isConfirmCreate = true;

    /**
     * @var Collection<int, Gallery>
     */
    #[ORM\ManyToMany(targetEntity: Gallery::class, mappedBy: 'artworks')]
    private Collection $galleries;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'artwork')]
    #[Groups(['artwork:read'])]
    private Collection $ratings;

    public function __construct()
    {
        $this->galleries = new ArrayCollection();
        $this->ratings = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
         if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
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

    public function getImage(): ?MediaObject
    { 
        return $this->image; 
    }

    public function setImage(?MediaObject $img): static 
    { 
        $this->image = $img; return $this; 
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

    public function getIsDisplay(): ?bool
    {
        return $this->isDisplay;
    }

    public function setIsDisplay(bool $isDisplay): static
    {
        $this->isDisplay = $isDisplay;

        return $this;
    }

    public function getIsConfirmCreate(): ?bool
    {
        return $this->isConfirmCreate;
    }

    public function setIsConfirmCreate(?bool $isConfirmCreate): static
    {
        $this->isConfirmCreate = $isConfirmCreate;

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
