<?php

declare(strict_types=1);

namespace Drupal\reward;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a reward claim entity type.
 */
interface RewardClaimInterface extends ContentEntityInterface, EntityOwnerInterface {

}
