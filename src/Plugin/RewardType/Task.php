<?php

declare(strict_types=1);

namespace Drupal\reward\Plugin\RewardType;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\reward\Attribute\RewardType;
use Drupal\reward\RewardTypePluginBase;
use Drupal\task\Event\TaskFinishedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    $fields['task'] = BaseFieldDefinition::create('entity_reference')
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
    return $fields;
  }

  public function onTaskFinished(TaskFinishedEvent $event) {
    // Load all task rewards.
    /** @var \Drupal\reward\RewardStorageInterface $rewardStorage */
    $rewardStorage = $this->entityTypeManager->getStorage('reward');
    $rewards = $rewardStorage->loadAllOfType($this->pluginDefinition['id']);

    foreach ($rewards as $reward) {
      $task = $reward->get('task')->referencedEntities();
      if (!empty($task)) {
        $task = reset($task);
        if ($task->id() === $event->getTask()) {

        }
      }
    }

  }

  public function getDescription() {}

}
