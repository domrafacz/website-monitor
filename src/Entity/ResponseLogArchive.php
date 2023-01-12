<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResponseLogArchiveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: ResponseLogArchiveRepository::class)]
#[UniqueConstraint(name: 'website_date_idx', columns: ['website_id', 'date'])]
class ResponseLogArchive
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'responseLogArchives')]
    #[ORM\JoinColumn(nullable: false)]
    /** @phpstan-ignore-next-line */
    private ?Website $website = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $date;

    #[ORM\Column]
    private int $averageResponseTime;

    public function __construct(Website $website, \DateTimeImmutable $date, int $averageResponseTime)
    {
        $this->setWebsite($website);
        $this->setDate($date);
        $this->setAverageResponseTime($averageResponseTime);
    }

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAverageResponseTime(): ?int
    {
        return $this->averageResponseTime;
    }

    public function setAverageResponseTime(int $averageResponseTime): self
    {
        $this->averageResponseTime = $averageResponseTime;

        return $this;
    }
}
