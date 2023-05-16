<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\triune\Entity\PayrollInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\user\UserInterface;

/**
 * Defines the Payroll entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_payroll",
 *     label = @Translation("Triune Payroll"),
 *     base_table = "triune_jobrate",
 *     admin_permission = "administer_payroll",
 *     fieldable = TRUE,
 *     handlers = {
 *         "list_builder" = "Drupal\triune\PayrollListBuilder",
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "views_data" = "Drupal\views\Entity\EntityViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\PayrollForm",
 *             "edit" = "Drupal\triune\Entity\Form\PayrollForm",
 *             "delete" = "Drupal\triune\Entity\Form\PayrollDeleteForm",
 *         },
 *         "route_provider" = {
 *             "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *         },
 *     },
 *     entity_keys = {
 *         "id" = "id",
 *         "uuid" = "uuid",
 *         "owner" = "user_id",
 *     },
 *     links = {
 *         "canonical" = "/triune/entity/payroll/{triune_payroll}",
 *         "edit-form" = "/triune/entity/payroll/{triune_payroll}/edit",
 *         "delete-form" = "/triune/entity/payroll/{triune_payroll}/delete",
 *         "collection" = "/admin/content/payroll",
 *     },
 *     field_ui_base_route = "triune.payroll_settings",
 * )
 */

class Payroll extends ContentEntityBase implements PayrollInterface
{
    use EntityChangedTrait;
    use EntityOwnerTrait;
    
    /**
     * {@inheritdoc}
     *
     * When a new entity instance is added, set the user_id entity reference to
     * the current user as the creator of the instance.
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
        $values += array(
            'user_id' => \Drupal::currentUser()->id(),
        );
    }
    
    /**
     * {@inheritdoc}
     *
     * When a new entity instance is added, set the user_id entity reference to
     * the current user as the creator of the instance.
     */
    public function preSave(EntityStorageInterface $storage) {
        parent::preSave($storage);
        if (!$this->getOwnerId()) {
            // If no owner has been set explicitly, make the anonymous user the owner.
            $this->setOwnerId(0);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->get('created')->value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getChangedTime()
    {
        return $this->get('changed')->value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setChangedTime($timestamp)
    {
        $this->set('changed', $timestamp);
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getChangedTimeAcrossTranslations()
    {
        $changed = $this->getUntranslated()->getChangedTime();
        return $changed;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->get('user_id')->entity;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->get('user_id')->target_id;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid)
    {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account)
    {
        $this->set('user_id', $account->id());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        //$fields = parent::baseFieldDefinitions($entity_type);
        //$fields += static::ownerBaseFieldDefinitions($entity_type);

        // Standard field, used as unique if primary index.
        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setDescription(t('The ID of the Payroll entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current JobRate.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Payroll entity.'))
            ->setReadOnly(true);

        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('User Id'))
            ->setDescription(t('The reference ID for the user.'))
            ->setSettings(
                array(
                    'target_type' => 'user',
                    'default_value' => 0,
                ),
            )
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'list_default',
                'weight' => -6,
              ))
            ->setDisplayOptions(
                'form', array(
                    'type' => 'options_select',
                    'weight' => -6,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        $now = DrupalDateTime::createFromTimestamp(time());
        $fields['date'] = BaseFieldDefinition::create('datetime')
        ->setLabel(t('Date'))
        ->setDescription(t(''))
        ->setReadOnly(false)
        ->setRevisionable(true)
        ->setSettings([
            'datetime_type' => 'date',
            'default_value' => $now->format('Y-m-d'),
        ])
        ->setDisplayOptions('view', [
            'label' => 'above',
            'type' => 'datetime_default',
            'settings' => [
                'format_type' => 'medium',
            ],
            'weight' => -5,
        ])
        ->setDisplayOptions('form', [
            'type' => 'datetime_default',
            'weight' => -5,
        ])
        ->setDisplayConfigurable('form', true);

        $fields['customer_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Customer ID'))
            ->setDescription(t('Associated Customer ID'))
            ->setSettings(
                array(
                    'target_type' => 'triune_customer',
                    'default_value' => 0,
                )
            )
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'list_default',
                'weight' => -6,
              ))
            ->setDisplayOptions('form', array(
                'type' => 'options_select',
                'weight' => -6,
              ))
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        $fields['position_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Position Name'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
              ))
            ->setDisplayOptions('form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
              ))
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        $fields['position_code'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Position Code'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
              ))
            ->setDisplayOptions('form', array(
                'type' => 'string_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
              ))
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);

        $fields['rate'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Rate'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                    'label' => 'above',
                    'type' => 'string',
                    'weight' => -5,
                    ),
                )
            ->setDisplayOptions(
                'form', array(
                    'type' => 'string_textfield',
                    'settings' => array(
                        'size' => 50,
                    ),
                    'weight' => -5,
                    )
                )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['created'] = BaseFieldDefinition::create('created')
        ->setLabel(t('Authored on'))
        ->setDescription(t('The time that the node was created.'))
        ->setRevisionable(TRUE)
        ->setTranslatable(TRUE)
        ->setDisplayOptions('view', [
            'label' => 'hidden',
            'type' => 'timestamp',
            'weight' => 0,
        ])
        ->setDisplayOptions('form', [
            'type' => 'datetime_timestamp',
            'weight' => 10,
        ])
        ->setDisplayConfigurable('form', TRUE);
        
        $fields['changed'] = BaseFieldDefinition::create('changed')
        ->setLabel(t('Changed'))
        ->setDescription(t('The time that the node was last edited.'))
        ->setRevisionable(TRUE);

        return $fields;
    }

    
    /**
     * {@inheritdoc}
     */
    public function getPayroll()
    {
        return $this->get('content')->value;
    }
}
