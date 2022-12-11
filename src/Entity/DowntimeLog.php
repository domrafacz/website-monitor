<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\DowntimeLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DowntimeLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'downtimeLogs')]
    #[ORM\JoinColumn(nullable: false)]
    /** @phpstan-ignore-next-line  */
    private ?Website $website = null;

    #[ORM\Column]
    private \DateTimeImmutable $startTime;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endTime = null;

    /** @var array<int, string> $initialError */
    #[ORM\Column]
    private array $initialError = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWebsite(): ?Website
    {
        return $this->website;
    }

    public function setWebsite(?Website $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeImmutable $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    /** @return array<int, string> */
    public function getInitialError(): array
    {
        return $this->initialError;
    }

    /** @param array<int, string> $initialError */
    public function setInitialError(array $initialError): self
    {
        $this->initialError = $initialError;

        return $this;
    }
}
