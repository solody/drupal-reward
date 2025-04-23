<?php

namespace Drupal\reward\Event;

use Drupal\account\Entity\LedgerInterface;
use Drupal\Component\EventDispatcher\Event;
use Drupal\reward\RewardClaimInterface;

/**
 * The RewardClaimedEvent which dispatch when reward is claimed.
 */
class RewardClaimedEvent extends Event {

  const string EVENT_NAME = 'reward.claimed';

  public function __construct(
    public RewardClaimInterface $claim,
    public LedgerInterface $ledger,
  ) {}

}
