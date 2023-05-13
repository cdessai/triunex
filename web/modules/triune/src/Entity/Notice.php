<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\NoticeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Notice entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_notice",
 *     label = @Translation("Triune Notice"),
 *     base_table = "triune_notice",
 *     admin_permission = "administer notice entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\NoticeListBuilder",
 *         "views_data" = "Drupal\triune\Entity\NoticeViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\NoticeForm",
 *             "edit" = "Drupal\triune\Entity\Form\NoticeForm",
 *             "delete" = "Drupal\triune\Entity\Form\NoticeDeleteForm",
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
 *         "canonical" = "/triune/entity/notice/{triune_notice}",
 *         "edit-form" = "/triune/entity/notice/{triune_notice}/edit",
 *         "delete-form" = "/triune/entity/notice/{triune_notice}/delete",
 *         "collection" = "/admin/content/notice", 
 *     },
 *     field_ui_base_route = "triune.notice_settings",
 * )
 */

class Notice extends ContentEntityBase implements NoticeInterface
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
            ->setDescription(t('The ID of the Notice entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current notice.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Notice entity.'))
            ->setReadOnly(true);
        
        $fields['label'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Message'))
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
        
        $fields['type'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Type'))
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
            
        $fields['employee_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Employee'))
            ->setDescription(t('The Employee ID'))
            ->setSetting('target_type', 'triune_employee')
            ->setSetting('handler', 'default')
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'entity_reference_label',
                'weight' => -3,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'entity_reference_autocomplete',
                'settings' => array(
                'match_operator' => 'CONTAINS',
                'size' => 60,
                'autocomplete_type' => 'tags',
                'placeholder' => '',
                ),
                'weight' => -3,
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
            
        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('User Id'))
            ->setDescription(t('The reference ID for the user.'))
            ->setSettings(
                array(
                'target_type' => 'user',
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
            
        $fields['status'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Status'))
            ->setDescription(t(''))
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
    public function getNotice()
    {
        return $this->get('content')->value;
    }
}
?>