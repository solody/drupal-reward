<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for reward_type plugins.
 */
abstract class RewardTypePluginBase extends PluginBase implements RewardTypeInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
