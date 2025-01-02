<?php

namespace Drupal\reward;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the Reward storage.
 */
class RewardStorage extends SqlContentEntityStorage implements RewardStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadAllOfType(string $type): array {
    $query = $this->getQuery();
    $query
      ->condition('type', $type);
    $result = $query->accessCheck(FALSE)->execute();
    return !empty($result) ? $this->loadMultiple($result) : [];
  }

}
