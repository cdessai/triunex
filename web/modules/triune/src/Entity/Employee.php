<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\EmployeeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Employee entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_employee",
 *     label = @Translation("Triune Employee"),
 *     base_table = "triune_employee",
 *     admin_permission = "administer employee entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\EmployeeListBuilder",
 *         "views_data" = "Drupal\triune\Entity\EmployeeViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\EmployeeForm",
 *             "edit" = "Drupal\triune\Entity\Form\EmployeeForm",
 *             "delete" = "Drupal\triune\Entity\Form\EmployeeDeleteForm",
 *         },
 *         "route_provider" = {
 *             "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *         },
 *     },
 *     entity_keys = {
 *         "id" = "id",
 *         "label" = "label",
 *         "uuid" = "uuid", 
 *     },
 *     links = {
 *         "canonical" = "/triune/entity/employee/{triune_employee}",
 *         "edit-form" = "/triune/entity/employee/{triune_employee}/edit",
 *         "delete-form" = "/triune/entity/employee/{triune_employee}/delete",
 *         "collection" = "/admin/content/employee", 
 *     },
 *     field_ui_base_route = "triune.employee_settings",
 * )
 */

class Employee extends ContentEntityBase implements EmployeeInterface
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
            ->setDescription(t('The ID of the Employee entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current employee.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Employee entity.'))
            ->setReadOnly(true);
        
        $fields['label'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Label'))
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
            
        $fields['resource_id'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Resource ID'))
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
            
        $fields['office_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Office'))
            ->setDescription(t('Associated Office ID'))
            ->setSettings(
                array(
                'target_type' => 'triune_office',
                //'default_value' => 0,
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
            
        $fields['customer_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Company'))
            ->setDescription(t('Associated Company ID'))
            ->setSettings(
                array(
                'target_type' => 'triune_customer',
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
            
        $fields['first_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('First Name'))
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
            
        $fields['last_name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Last Name'))
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
            
        $fields['phone'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Phone'))
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
        
        $fields['job'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Job'))
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
            
        $fields['shift'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Shift'))
            ->setDescription(t('Which shift the employee is assigned'))
            ->setReadOnly(false)
            ->setSetting('unsigned', true)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'form', array(
                'type' => 'text_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true);
            
        $fields['driver'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Driver'))
            ->setDescription(t('Whether the employee is designated as transport driver'))
            ->setReadOnly(false)
            ->setSetting('unsigned', true)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'form', array(
                'type' => 'text_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true);
            
        $fields['status'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Status'))
            ->setDescription(t('Whether the employee is active or inactive'))
            ->setReadOnly(false)
            ->setSetting('unsigned', true)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'form', array(
                'type' => 'text_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true);
        
        $fields['hire_date'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Hire Date'))
            ->setDescription(t('The date that the Employee was hired.'))
            ->setReadOnly(false)
            ->setSetting('unsigned', true)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'form', array(
                'type' => 'text_textfield',
                'settings' => array(
                    'size' => 50,
                ),
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true);

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
    public function getEmployee()
    {
        return $this->get('content')->value;
    }
}
?>