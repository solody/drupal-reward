<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity\BundlePlugin\BundlePluginInterface;

/**
 * Interface for reward_type plugins.
 */
interface RewardTypeInterface extends BundlePluginInterface, ContainerFactoryPluginInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

  /**
   * Load all task of current type.
   *
   * @return \Drupal\reward\RewardInterface[]
   *   Tasks.
   */
  public function loadAllRewards(): array;

  /**
   * Whether the user can claim the reward.
   *
   * @param \Drupal\reward\RewardInterface $reward
   *   The reward.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user.
   *
   * @return bool
   *   Whether the user can claim the reward.
   */
  public function canClaim(RewardInterface $reward, AccountInterface $user): bool;

}
