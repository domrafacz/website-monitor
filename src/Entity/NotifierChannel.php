<?php

declare(strict_types=1);

namespace App\Entity;

use App\Form\NotifierDiscordChannelType;
use App\Form\NotifierTelegramChannelType;
use App\Message\Notifier\DiscordMessage;
use App\Message\Notifier\TelegramMessage;
use App\Repository\NotifierChannelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotifierChannelRepository::class)]
class NotifierChannel
{
    public const CHANNELS = [
        0 => [
            'name' => 'Telegram',
            'message' => TelegramMessage::class,
            'form' => NotifierTelegramChannelType::class,
        ],
        1 => [
            'name' => 'Discord',
            'message' => DiscordMessage::class,
            'form' => NotifierDiscordChannelType::class,
        ]
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifierChannels')]
    #[ORM\JoinColumn(nullable: false)]
    /** @phpstan-ignore-next-line */
    private ?User $owner;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $type;

    /** @var array<string, string>|null  */
    #[ORM\Column(nullable: true)]
    private ?array $options = [];

    /** @var Collection<int, Website> $websites */
    #[ORM\ManyToMany(targetEntity: Website::class, mappedBy: 'notifierChannels')]
    private Collection $websites;

    #[ORM\Column(length: 255, options: ['default' => 'noname'])]
    private string $name;

    /**
     * @param ArrayCollection<int, Website>|null $websites
     * @param array<string, string>|null $options
     */
    public function __construct(User $owner, int $type, string $name, ?array $options = null, ArrayCollection $websites = null)
    {
        $this->owner = $owner;
        $this->type = $type;
        $this->name = $name;
        $this->options = $options;
        $this->websites = $websites ?? new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /** @return array<string, string>|null */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /** @param array<string, string>|null $options */
    public function setOptions(?array $options): self
    {
        $this->options = $options;

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
            $website->addNotifierChannel($this);
        }

        return $this;
    }

    public function removeWebsite(Website $website): self
    {
        if ($this->websites->removeElement($website)) {
            $website->removeNotifierChannel($this);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
