<?php

namespace Drupal\reward;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the interface for Reward storage.
 */
interface RewardStorageInterface extends ContentEntityStorageInterface {

  /**
   * Load all rewards of the given type.
   *
   * @param string $type
   *   Type id.
   *
   * @return \Drupal\reward\RewardInterface[]
   *   List of reward.
   */
  public function loadAllOfType(string $type): array;

}
