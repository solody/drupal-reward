<?php

declare(strict_types=1);

namespace Drupal\reward\Plugin\RewardType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\reward\Attribute\RewardType;
use Drupal\reward\RewardTypePluginBase;

/**
 * Plugin implementation of the reward_type.
 */
#[RewardType(
  id: 'task',
  label: new TranslatableMarkup('Task'),
  description: new TranslatableMarkup('Finished specified task to get the reward.'),
)]
final class Task extends RewardTypePluginBase {

}
