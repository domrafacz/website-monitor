<?php
declare(strict_types=1);

namespace App\Service\Notifier;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Repository\NotifierChannelRepository;
use Doctrine\Common\Collections\ArrayCollection;

class ChannelManager
{
    public function __construct(
        private readonly NotifierChannelRepository $channelsRepository,
    ) {}

    /** @param null|array<string, string> $options */
    public function add(int $type, User $user, string $name, ?array $options = null, ArrayCollection $websites = null): void
    {
        $channel = new NotifierChannel(
            $user,
            $type,
            $name,
            $options,
            $websites
        );

        $this->channelsRepository->save($channel, true);
    }

    public function update(NotifierChannel $channel, string $name, ?array $options): void
    {
        $channel->setName($name);
        $channel->setOptions($options);
        $this->channelsRepository->save($channel, true);
    }
}