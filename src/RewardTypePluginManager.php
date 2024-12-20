<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\reward\Attribute\RewardType;

/**
 * RewardType plugin manager.
 */
final class RewardTypePluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/RewardType', $namespaces, $module_handler, RewardTypeInterface::class, RewardType::class);
    $this->alterInfo('reward_type_info');
    $this->setCacheBackend($cache_backend, 'reward_type_plugins');
  }

}
