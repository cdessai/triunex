<?php

namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\triune\Entity\OfficeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Office entity.
 *
 * @ingroup triune
 *
 * @ContentEntityType(
 *     id = "triune_office",
 *     label = @Translation("Triune Office"),
 *     base_table = "triune_office",
 *     admin_permission = "administer office entity",
 *     fieldable = TRUE,
 *     handlers = {
 *         "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *         "list_builder" = "Drupal\triune\OfficeListBuilder",
 *         "views_data" = "Drupal\triune\Entity\OfficeViewsData",
 *         "form" = {
 *             "add" = "Drupal\triune\Entity\Form\OfficeForm",
 *             "edit" = "Drupal\triune\Entity\Form\OfficeForm",
 *             "delete" = "Drupal\triune\Entity\Form\OfficeDeleteForm",
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
 *         "canonical" = "/triune/entity/office/{triune_office}",
 *         "edit-form" = "/triune/entity/office/{triune_office}/edit",
 *         "delete-form" = "/triune/entity/office/{triune_office}/delete",
 *         "collection" = "/admin/content/office", 
 *     },
 *     field_ui_base_route = "triune.office_settings",
 * )
 */

class Office extends ContentEntityBase implements OfficeInterface
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
    /*public function loadByLocationID($location_id) {
      
      return $data;
        //return $this->get('location_id')->value;
    }*/
    
    
    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {

        // Standard field, used as unique if primary index.
        $fields['id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('ID'))
            ->setDescription(t('The ID of the Office entity.'))
            ->setReadOnly(true)
            ->setSetting('unsigned', true);

        // Standard field, unique outside of the scope of the current office.
        $fields['uuid'] = BaseFieldDefinition::create('uuid')
            ->setLabel(t('UUID'))
            ->setDescription(t('The UUID of the Office entity.'))
            ->setReadOnly(true);
        
        $fields['label'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Office Name'))
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
            
        $fields['location_id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Location ID'))
            ->setDescription(t('locationID assigned in Ascentis'))
            ->setReadOnly(false)
            ->setRevisionable(true)
            ->setDisplayOptions(
                'view', array(
                'label' => 'above',
                'type' => 'integer',
                'weight' => -5,
                )
            )
            ->setDisplayOptions(
                'form', array(
                'type' => 'integer',
                'weight' => -5,
                )
            )
            ->setDisplayConfigurable('form', true)
            ->setDisplayConfigurable('view', true);
            
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
    public function getOffice()
    {
        return $this->get('content')->value;
    }
}
?>