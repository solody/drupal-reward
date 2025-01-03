<?php

declare(strict_types=1);

namespace Drupal\reward\Entity;

use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\reward\RewardInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the reward entity class.
 *
 * @ContentEntityType(
 *   id = "reward",
 *   label = @Translation("Reward"),
 *   label_collection = @Translation("Rewards"),
 *   label_singular = @Translation("reward"),
 *   label_plural = @Translation("rewards"),
 *   label_count = @PluralTranslation(
 *     singular = "@count rewards",
 *     plural = "@count rewards",
 *   ),
 *   bundle_label = @Translation("Reward type"),
 *   bundle_plugin_type = "reward_type",
 *   handlers = {
 *     "list_builder" = "Drupal\reward\RewardListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\reward\RewardAccessControlHandler",
 *     "storage" = "Drupal\reward\RewardStorage",
 *     "form" = {
 *       "add" = "Drupal\reward\Form\RewardForm",
 *       "edit" = "Drupal\reward\Form\RewardForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "reward",
 *   data_table = "reward_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer reward",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "langcode" = "langcode",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/reward/reward",
 *     "add-form" = "/admin/reward/reward/add/{type}",
 *     "add-page" = "/admin/reward/reward/add",
 *     "canonical" = "/admin/reward/reward/{reward}",
 *     "edit-form" = "/admin/reward/reward/{reward}/edit",
 *     "delete-form" = "/admin/reward/reward/{reward}/delete",
 *     "delete-multiple-form" = "/admin/reward/reward/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.reward.settings",
 * )
 */
final class Reward extends ContentEntityBase implements RewardInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount(): ?Price {
    if (!$this->get('amount')->isEmpty()) {
      return $this->get('amount')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setTranslatable(TRUE)
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['amount'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Amount'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_list_price',
        'weight' => 0,
      ]);

    $fields['account_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Account type'))
      ->setDescription(t('Account type the amount will be save to.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'account_type')
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

    $fields['auto_claim'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Auto claim'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Yes')
      ->setSetting('off_label', 'No')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setTranslatable(TRUE)
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setTranslatable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
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
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the reward was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the reward was last edited.'));

    return $fields;
  }

}
