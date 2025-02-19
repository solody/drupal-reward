<?php

namespace Drupal\reward;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Item list for a computed field that displays the return orders of an order.
 */
class RewardClaimItemList extends FieldItemList implements EntityReferenceFieldItemListInterface {

  use ComputedItemListTrait;

  /**
   * Flag is computed.
   */
  private bool $isComputed = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $this->ensurePopulated();
  }

  /**
   * Computes the calculated values for this item list.
   *
   * In this example, there is only a single item/delta for this field.
   *
   * The ComputedItemListTrait only calls this once on the same instance; from
   * then on, the value is automatically cached in $this->items, for use by
   * methods like getValue().
   */
  protected function ensurePopulated() {
    if (!$this->isComputed) {
      foreach ($this->getRewardClaims() as $index => $reward) {
        if ($reward instanceof RewardInterface) {
          $this->list[] = $this->createItem($index, $reward);
        }
      }
      $this->isComputed = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function referencedEntities() {
    return $this->getRewardClaims();
  }

  /**
   * Get the rewards belongs to the task.
   *
   * @return array|\Drupal\reward\RewardClaimInterface[]
   *   An array of rewards or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getRewardClaims() {
    /** @var \Drupal\reward\RewardClaimStorageInterface $reward_claim_storage */
    $reward_claim_storage = \Drupal::entityTypeManager()->getStorage('reward_claim');
    $reward = $this->getEntity();
    $rewards = $reward_claim_storage->loadByProperties([
      'reward_id' => $reward->id(),
    ]);
    if (!empty($rewards)) {
      return array_values($rewards);
    }
    else {
      return [];
    }
  }

}
