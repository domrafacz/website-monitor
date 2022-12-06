<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ResponseLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResponseLogRepository::class)]
class ResponseLog
{
    public const STATUS_OK = 1;
    public const STATUS_ERROR = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'responseLogs')]
    #[ORM\JoinColumn(nullable: false)]
    /** @phpstan-ignore-next-line */
    private ?Website $website = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(
        min: self::STATUS_OK,
        max: self::STATUS_ERROR,
    )]
    private int $status;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $time;

    #[ORM\Column]
    private int $responseTime;

    public function __construct(
        Website $website,
        int $status,
        \DateTimeInterface $time,
        int $responseTime,
    ) {
        $this->setWebsite($website);
        $this->setStatus($status);
        $this->setTime($time);
        $this->setResponseTime($responseTime);
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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTime(): \DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getResponseTime(): int
    {
        return $this->responseTime;
    }

    public function setResponseTime(int $responseTime): self
    {
        $this->responseTime = $responseTime;

        return $this;
    }
}
