<?php

namespace Drupal\reward;

use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\reward\Entity\RewardClaim;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Reward storage.
 */
class RewardClaimStorage extends SqlContentEntityStorage implements RewardClaimStorageInterface {

  const string LOCK_ID = 'reward_claim_unique_insert';

  /**
   * The lock service.
   */
  protected LockBackendInterface $lock;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('lock'),
    );
  }

  public function __construct(
    EntityTypeInterface $entity_type,
    Connection $database,
    EntityFieldManagerInterface $entity_field_manager,
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    MemoryCacheInterface $memory_cache,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityTypeManagerInterface $entity_type_manager,
    LockBackendInterface $lock,
  ) {
    $this->lock = $lock;
    parent::__construct($entity_type, $database, $entity_field_manager, $cache, $language_manager, $memory_cache, $entity_type_bundle_info, $entity_type_manager);
  }

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
  public function addRewardClaim(int $reward_id, int $uid): void {
    // Make unique check.
    // https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Lock%21LockBackendInterface.php/group/lock
    if ($this->lock->acquire(self::LOCK_ID)) {
      try {
        if ($this->getRewardClaim($reward_id, $uid) === NULL) {
          $claim = RewardClaim::create([
            'reward_id' => $reward_id,
            'uid' => $uid,
          ]);
          $claim->save();
        }
      }
      finally {
        $this->lock->release(self::LOCK_ID);
      }
    }
  }

}
