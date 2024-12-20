<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\entity\BundlePlugin\BundlePluginInterface;

/**
 * Interface for reward_type plugins.
 */
interface RewardTypeInterface extends BundlePluginInterface, ContainerFactoryPluginInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

}
