<?php

namespace Drupal\reward;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the interface for RewardClaim storage.
 */
interface RewardClaimStorageInterface extends ContentEntityStorageInterface {

  /**
   * Load all reward claims of the given user.
   *
   * @param int $uid
   *   The user id.
   *
   * @return \Drupal\reward\RewardClaimInterface[]
   *   List of reward claims.
   */
  public function loadAllOfUser(int $uid): array;

  /**
   * Load all reward claims of the given reward.
   *
   * @param int $reward_id
   *   The reward id.
   *
   * @return \Drupal\reward\RewardClaimInterface[]
   *   List of reward claims.
   */
  public function loadAllOfReward(int $reward_id): array;

  /**
   * Get reward claim of the given reward and user.
   *
   * @param int $reward_id
   *   The reward id.
   * @param int $uid
   *   The user.
   *
   * @return \Drupal\reward\RewardClaimInterface|null
   *   The reward claims or null.
   */
  public function getRewardClaim(int $reward_id, int $uid): ?RewardClaimInterface;

  /**
   * Add reward claim of the given reward and user.
   *
   * @param int $reward_id
   *   The reward id.
   * @param int $uid
   *   The user.
   */
  public function addRewardClaim(int $reward_id, int $uid): void;

}
