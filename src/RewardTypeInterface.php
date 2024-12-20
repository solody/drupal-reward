<?php

declare(strict_types=1);

namespace Drupal\reward;

/**
 * Interface for reward_type plugins.
 */
interface RewardTypeInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

}
