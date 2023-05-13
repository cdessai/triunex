<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\CustomerInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Customer entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_customer",
 *     label = @Translation("Triune Customer"),
 *     base_table = "triune_customer",
 *     admin_permission = "administer customer entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\CustomerListBuilder",
 *         "views_data" = "Drupal\views\Entity\EntityViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\CustomerForm",
 *             "edit" = "Drupal\triune\Entity\Form\CustomerForm",
 *             "delete" = "Drupal\triune\Entity\Form\CustomerDeleteForm",
 *         },
 *         "route_provider" = {
 *             "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *         },
 *     },
 *     entity_keys = {
 *         "id" = "id",
 *         "label" = "id",
 *         "uuid" = "uuid", 
 *     },
 *     links = {
 *         "canonical" = "/triune/entity/customer/{triune_customer}",
 *         "edit-form" = "/triune/entity/customer/{triune_customer}/edit",
 *         "delete-form" = "/triune/entity/customer/{triune_customer}/delete",
 *         "collection" = "/admin/content/customer", 
 *     },
 *     field_ui_base_route = "triune.customer_settings",
 * )
 */

class Customer extends ContentEntityBase implements CustomerInterface
{
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

        // Standard field, used as unique if primary index.
        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setDescription(t('The ID of the Customer entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current customer.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Customer entity.'))
            ->setReadOnly(true);
        
        $fields['office_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Office'))
            ->setDescription(t('Associated Office ID'))
            ->setSettings(
                array(
                'target_type' => 'triune_office',
                'default_value' => 0,
                )
            )
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'list_default',
                'weight' => -6,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'options_select',
                'weight' => -6,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
        $fields['label'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Customer Name'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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
            
        $fields['address'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Customer Address'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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
            
        $fields['email'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Customer Email'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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

        // Defining new fields in triune_customer table.
        $fields['s1_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Supervisor Name'))
            ->setDescription(t('Shift1 supervisor name'))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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

        $fields['s2_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Supervisor2 Name'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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
        $fields['tsc_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Time shift contact Name'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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
        
        $fields['s3_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Supervisor3 Name'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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
        $fields['p_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Payables Name'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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
        
        $fields['s1_phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Supervisor1 Phone'))
            ->setDescription(t('Shift1 Supervisor phone'))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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
            
        $fields['s2_phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Supervisor2 Phone'))
            ->setDescription(t(''))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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

        $fields['s3_phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Supervisor3 Phone'))
            ->setDescription(t('Shift3 supervisor phone'))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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

        $fields['tsc_phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('TSC Phone'))
            ->setDescription(t('Time shift contact'))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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

        $fields['p_phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Payables Phone'))
            ->setDescription(t('Payables Phone'))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -5,
                )
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

        // End of new fields definition.

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

            
        return $fields;
    }

    
    /**
     * {@inheritdoc}
     */
    public function getCustomer()
    {
        return $this->get('content')->value;
    }
}
?>