<?php

namespace Drupal\reward;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Item list for a computed field that displays the return orders of an order.
 */
class RewardItemList extends FieldItemList implements EntityReferenceFieldItemListInterface {

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
      foreach ($this->getRewards() as $reward) {
        if ($reward instanceof RewardInterface) {
          $this->list[] = $this->createItem(0, $reward);
        }
      }
      $this->isComputed = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function referencedEntities() {
    return $this->getRewards();
  }

  /**
   * Get the rewards belongs to the task.
   *
   * @return array|\Drupal\motobatt_merchant\MotoBattMerchantInterface[]
   *   An array of rewards or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getRewards() {
    /** @var \Drupal\reward\RewardStorageInterface $reward_storage */
    $reward_storage = \Drupal::entityTypeManager()->getStorage('reward');
    $task = $this->getEntity();
    $rewards = $reward_storage->loadByProperties([
      'task_id' => $task->id(),
    ]);
    if (!empty($rewards)) {
      return array_values($rewards);
    }
    else {
      return [];
    }
  }

}
