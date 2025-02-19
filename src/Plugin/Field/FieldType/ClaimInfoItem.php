<?php

declare(strict_types=1);

namespace Drupal\task\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\Plugin\Field\FieldType\MapItem;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\reward\RewardClaimInterface;

/**
 * Defines the 'task_progress' field type.
 */
#[FieldType(
  id: 'reward_claim_info',
  label: new TranslatableMarkup('Reward claim information'),
  description: new TranslatableMarkup('Reward claim information for current user.'),
  default_widget: 'string_textfield',
  default_formatter: 'string',
)]
final class ClaimInfoItem extends MapItem {

  /**
   * Whether the value has been calculated.
   *
   * @var bool
   */
  protected $isCalculated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    $this->ensureCalculated();
    return parent::__get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $this->ensureCalculated();
    return parent::isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    $this->ensureCalculated();
    return parent::getValue();
  }

  /**
   * Calculates the value of the field and sets it.
   */
  protected function ensureCalculated() {
    if (!$this->isCalculated) {
      /** @var \Drupal\reward\RewardInterface $reward */
      $reward = $this->getEntity();
      if ($reward->bundle() !== 'task') {
        /** @var \Drupal\task\TaskInterface $task */
        $task = $this->getEntity();
        if (!$reward->isNew()) {
          // Check whether the task has been finished for current user.
          /** @var \Drupal\task\TaskTypePluginManager $task_type_plugin_manager */
          $task_type_plugin_manager = \Drupal::service('plugin.manager.task_type');
          /** @var \Drupal\task\TaskTypeInterface $plugin */
          $plugin = $task_type_plugin_manager->createInstance($task->bundle());
          $progress = $plugin->getProgress((int) \Drupal::currentUser()->id(), (int) $task->id());
          $can_be_claimed = $progress['current_goal'] >= $reward->get('task_goal')->value;

          // Check is there any reward_claim entities
          // of current user to the reward.
          /** @var \Drupal\reward\RewardClaimStorageInterface $reward_claim_storage */
          $reward_claim_storage = \Drupal::entityTypeManager()->getStorage('reward_claim');
          $claim = $reward_claim_storage->getRewardClaim((int) $reward->id(), (int) \Drupal::currentUser()->id());
          $is_claimed = $claim instanceof RewardClaimInterface;

          $this->setValue([
            'can_be_claimed' => $can_be_claimed,
            'is_claimed' => $is_claimed,
          ]);
        }
      }
      $this->isCalculated = TRUE;
    }
  }

}
