<?php

declare(strict_types=1);

namespace Drupal\reward\Plugin\RewardType;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity\BundleFieldDefinition;
use Drupal\reward\Attribute\RewardType;
use Drupal\reward\RewardInterface;
use Drupal\reward\RewardTypePluginBase;
use Drupal\task\Event\TaskFinishedEvent;

/**
 * Plugin implementation of the reward_type.
 */
#[RewardType(
  id: 'task',
  label: new TranslatableMarkup('Task'),
  description: new TranslatableMarkup('Finished specified task to get the reward.'),
)]
final class Task extends RewardTypePluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];
    $fields['task_id'] = BundleFieldDefinition::create('entity_reference')
      ->setLabel($this->t('Task'))
      ->setRequired(TRUE)
      ->setDescription($this->t('Which task should be finished to get the reward.'))
      ->setSetting('target_type', 'task')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['task_goal'] = BundleFieldDefinition::create('integer')
      ->setLabel($this->t('Task goal'))
      ->setDescription($this->t('Which task goal be finished to get the reward.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ]);
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function canClaim(RewardInterface $reward, AccountInterface $user): bool {
    /** @var \Drupal\task\TaskInterface $task */
    $task = $reward->get('task_id')->entity;
    $progress = $task->getTaskTypePlugin()->getProgress((int) $user->id(), $task);
    return $progress['current_goal'] >= $reward->get('task_goal')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function autoClaim(Event $event): void {
    if ($event instanceof TaskFinishedEvent) {
      foreach ($this->loadAllRewards() as $reward) {
        $task_id = $reward->get('task_id')->target_id;
        $reward_id = $reward->id();
        if ((int) $event->getTaskId() === (int) $task_id) {
          if ($this->canClaim($reward, $event->getUser()) && $reward->get('auto_claim')->value) {
            $this->claimManager->claimReward((int) $reward->id(), $event->getUid());
          }
          Cache::invalidateTags(["task:$task_id", 'task_list', "reward:$reward_id", 'reward_list']);
        }
      }
    }
  }

}
