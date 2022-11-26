<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebsiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebsiteRepository::class)]
class Website
{
    public const STATUS_OK = 1;
    public const STATUS_ERROR = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'websites')]
    #[ORM\JoinColumn(nullable: false)]
    /** @phpstan-ignore-next-line  */
    private ?User $owner;

    #[ORM\Column(length: 255)]
    private string $url;

    #[ORM\Column(length: 100)]
    private string $requestMethod;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $maxRedirects;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $timeout;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $lastStatus = self::STATUS_OK;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastCheck = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $certExpiryTime = null;

    #[ORM\Column]
    private int $frequency;

    #[ORM\Column]
    private bool $enabled;

    /** @var Collection<int, ResponseLog> $responseLogs */
    #[ORM\OneToMany(mappedBy: 'website', targetEntity: ResponseLog::class, orphanRemoval: true)]
    private Collection $responseLogs;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 200])]
    private int $expectedStatusCode;

    /** @var Collection<int, DowntimeLog> $downtimeLogs */
    #[ORM\OneToMany(mappedBy: 'website', targetEntity: DowntimeLog::class, orphanRemoval: true)]
    private Collection $downtimeLogs;

    public function __construct()
    {
        $this->responseLogs = new ArrayCollection();
        $this->downtimeLogs = new ArrayCollection();
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

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(string $requestMethod): self
    {
        $this->requestMethod = $requestMethod;

        return $this;
    }

    public function getMaxRedirects(): int
    {
        return $this->maxRedirects;
    }

    public function setMaxRedirects(int $maxRedirects): self
    {
        $this->maxRedirects = $maxRedirects;

        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getLastStatus(): int
    {
        return $this->lastStatus;
    }

    public function setLastStatus(int $lastStatus): self
    {
        $this->lastStatus = $lastStatus;

        return $this;
    }

    public function getLastCheck(): ?\DateTimeInterface
    {
        return $this->lastCheck;
    }

    public function setLastCheck(?\DateTimeInterface $lastCheck): self
    {
        $this->lastCheck = $lastCheck;

        return $this;
    }

    public function getCertExpiryTime(): ?\DateTimeInterface
    {
        return $this->certExpiryTime;
    }

    public function setCertExpiryTime(?\DateTimeInterface $CertExpiryTime): self
    {
        $this->certExpiryTime = $CertExpiryTime;

        return $this;
    }

    public function getFrequency(): int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection<int, ResponseLog>
     */
    public function getResponseLogs(): Collection
    {
        return $this->responseLogs;
    }

    public function addResponseLog(ResponseLog $responseLog): self
    {
        if (!$this->responseLogs->contains($responseLog)) {
            $this->responseLogs->add($responseLog);
            $responseLog->setWebsite($this);
        }

        return $this;
    }

    public function removeResponseLog(ResponseLog $responseLog): self
    {
        if ($this->responseLogs->removeElement($responseLog)) {
            // set the owning side to null (unless already changed)
            if ($responseLog->getWebsite() === $this) {
                $responseLog->setWebsite(null);
            }
        }

        return $this;
    }

    public function getExpectedStatusCode(): int
    {
        return $this->expectedStatusCode;
    }

    public function setExpectedStatusCode(int $expectedStatusCode): self
    {
        $this->expectedStatusCode = $expectedStatusCode;

        return $this;
    }

    /**
     * @return Collection<int, DowntimeLog>
     */
    public function getDowntimeLogs(): Collection
    {
        return $this->downtimeLogs;
    }

    public function addDowntimeLog(DowntimeLog $downtimeLog): self
    {
        if (!$this->downtimeLogs->contains($downtimeLog)) {
            $this->downtimeLogs->add($downtimeLog);
            $downtimeLog->setWebsite($this);
        }

        return $this;
    }

    public function removeDowntimeLog(DowntimeLog $downtimeLog): self
    {
        if ($this->downtimeLogs->removeElement($downtimeLog)) {
            // set the owning side to null (unless already changed)
            if ($downtimeLog->getWebsite() === $this) {
                $downtimeLog->setWebsite(null);
            }
        }

        return $this;
    }
}
