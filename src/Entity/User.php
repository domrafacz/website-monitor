<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'username_taken')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private string $email = '';

    /** @var array<string> */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password = '';

    #[ORM\Column(length: 255, options: ['default' => 'en'])]
    private string $language = 'en';

    /** @var Collection<int, Website> $websites */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Website::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $websites;

    /** @var Collection<int, NotifierChannel> $notifierChannels  */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: NotifierChannel::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    private Collection $notifierChannels;

    #[ORM\Column(options: ['default' => 10])]
    private int $quota = 10;

    #[ORM\Column(type: Types::SMALLINT, enumType: UserStatus::class, options: ['default' => UserStatus::ACTIVE])]
    private UserStatus $status;

    public function __construct()
    {
        $this->websites = new ArrayCollection();
        $this->notifierChannels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return Collection<int, Website>
     */
    public function getWebsites(): Collection
    {
        return $this->websites;
    }

    public function addWebsite(Website $website): self
    {
        if (!$this->websites->contains($website)) {
            $this->websites->add($website);
            $website->setOwner($this);
        }

        return $this;
    }

    public function removeWebsite(Website $website): self
    {
        if ($this->websites->removeElement($website)) {
            // set the owning side to null (unless already changed)
            if ($website->getOwner() === $this) {
                $website->setOwner(null);
            }
        }

        return $this;
    }

    public function findWebsite(int $id): ?Website
    {
        foreach ($this->getWebsites()->getIterator() as $website) {
            if ($website->getId() == $id) {
                return $website;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, NotifierChannel>
     */
    public function getNotifierChannels(): Collection
    {
        return $this->notifierChannels;
    }

    public function addNotifierChannel(NotifierChannel $notifierChannel): self
    {
        if (!$this->notifierChannels->contains($notifierChannel)) {
            $this->notifierChannels->add($notifierChannel);
            $notifierChannel->setOwner($this);
        }

        return $this;
    }

    public function removeNotifierChannel(NotifierChannel $notifierChannel): self
    {
        if ($this->notifierChannels->removeElement($notifierChannel)) {
            // set the owning side to null (unless already changed)
            if ($notifierChannel->getOwner() === $this) {
                $notifierChannel->setOwner(null);
            }
        }

        return $this;
    }

    public function findNotifierChannel(int $id): ?NotifierChannel
    {
        foreach ($this->getNotifierChannels()->getIterator() as $channel) {
            if ($channel->getId() == $id) {
                return $channel;
            }
        }

        return null;
    }

    public function getQuota(): int
    {
        return $this->quota;
    }

    public function setQuota(int $quota): self
    {
        $this->quota = $quota;

        return $this;
    }

    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->getStatus() === UserStatus::ACTIVE;
    }

    public function isBlocked(): bool
    {
        return $this->getStatus() === UserStatus::BLOCKED;
    }
}
