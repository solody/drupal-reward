<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a reward entity type.
 */
interface RewardInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
