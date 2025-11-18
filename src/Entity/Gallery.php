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

use App\Repository\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_ADMIN') or object.getOwner() == user"),
        new Delete(security: "is_granted('ROLE_ADMIN') or object.getOwner() == user")
    ],
    normalizationContext: ['groups' => ['gallery:read']],
    denormalizationContext: ['groups' => ['gallery:write']]
)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['gallery:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?string $coverImage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?int $views = null;

    #[ORM\Column]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?bool $isPublic = null;

    #[ORM\ManyToOne(inversedBy: 'galleries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['gallery:read', 'gallery:write'])]
    private ?User $owner = null;

    /**
     * @var Collection<int, Artwork>
     */
    #[ORM\ManyToMany(targetEntity: Artwork::class, inversedBy: 'galleries')]
    #[Groups(['gallery:read', 'gallery:write'])]
    private Collection $artworks;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->artworks = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Artwork>
     */
    public function getArtworks(): Collection
    {
        return $this->artworks;
    }

    public function addArtwork(Artwork $artwork): static
    {
        if (!$this->artworks->contains($artwork)) {
            $this->artworks->add($artwork);
        }

        return $this;
    }

    public function removeArtwork(Artwork $artwork): static
    {
        $this->artworks->removeElement($artwork);

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
}
