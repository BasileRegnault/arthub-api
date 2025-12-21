<?php

namespace App\Entity;

use App\Repository\UserLoginLogRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserLoginLogRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['user_login_log:read']],
    denormalizationContext: ['groups' => ['user_login_log:write']],
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post()
    ]
)]
class UserLoginLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_login_log:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userLoginLogs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_login_log:read', 'user_login_log:write'])]
    private ?User $userConnected = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_login_log:read', 'user_login_log:write'])]
    private ?string $ip = null;

    #[ORM\Column(length: 20)]
    #[Groups(['user_login_log:read', 'user_login_log:write'])]
    private ?string $event = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user_login_log:read', 'user_login_log:write'])]
    private ?string $userAgent = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user_login_log:read', 'user_login_log:write'])]
    private ?string $message = null;

    #[ORM\Column]
    #[Groups(['user_login_log:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserConnected(): ?User
    {
        return $this->userConnected;
    }

    public function setUserConnected(?User $userConnected): static
    {
        $this->userConnected = $userConnected;
        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(string $event): static
    {
        $this->event = $event;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;
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
}
