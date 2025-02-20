<?php

declare(strict_types=1);

namespace Drupal\reward;

/**
 * Service to manager reward claim consistently.
 */
interface ClaimManagerInterface {

  /**
   * Claim a given reward for a given user.
   */
  public function claimReward(int $reward_id, int $uid): RewardClaimInterface;

}
