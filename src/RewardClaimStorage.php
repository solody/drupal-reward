<?php

namespace Drupal\reward;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\reward\Entity\RewardClaim;

/**
 * Defines the Reward storage.
 */
class RewardClaimStorage extends SqlContentEntityStorage implements RewardClaimStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadAllOfUser(int $uid): array {
    $query = $this->getQuery();
    $query
      ->condition('uid', $uid);
    $result = $query->accessCheck(FALSE)->execute();
    return !empty($result) ? $this->loadMultiple($result) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllOfReward(int $reward_id): array {
    $query = $this->getQuery();
    $query
      ->condition('reward_id', $reward_id);
    $result = $query->accessCheck(FALSE)->execute();
    return !empty($result) ? $this->loadMultiple($result) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getRewardClaim(int $reward_id, int $uid): ?RewardClaimInterface {
    $query = $this->getQuery();
    $query
      ->condition('reward_id', $reward_id)
      ->condition('uid', $uid);
    $result = $query->accessCheck(FALSE)->execute();
    return empty($result) ? NULL : RewardClaim::load(reset($result));
  }

  /**
   * {@inheritdoc}
   */
  public function addRewardClaim(int $reward_id, int $uid): RewardClaimInterface {
    // @todo make unique check.
    $claim = RewardClaim::create([
      'reward_id' => $reward_id,
      'uid' => $uid,
    ]);
    $claim->save();
    return $claim;
  }

}
