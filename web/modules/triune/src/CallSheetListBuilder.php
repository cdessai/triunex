<?php

/**
 * @file Contains \Drupal\triune\CallSheetListBuilder
 */
namespace Drupal\triune;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a list controller for call_sheet entity.
 * 
 * @ingroup triune
 */
class CallSheetListBuilder extends EntityListBuilder
{

    /**
     * {@inheritdoc}
     *
     * We override ::render() so that we can add our own content above the table.
     * parent::render() is where EntityListBuilder creates the table using our
     * buildHeader() and buildRow() implementations.
     */
    public function render()
    {
        $build['description'] = array(
            '#markup' => $this->t(
                '<a href="@addlink">Add a new call sheet</a>', array(
                '@addlink' => \Drupal::urlGenerator()->generateFromRoute('triune.entity.callsheet.add'),
                )
            ),
        );
        $build['table'] = parent::render();
        return $build;
    }


    public function load()
    {

        $entity_query = \Drupal::service('entity_type.manager')->getStorage('triune_callsheet')->getQuery();
        $header = $this->buildHeader();

        $entity_query->pager(50);
        $entity_query->tableSort($header);

        $uids = $entity_query->execute();

        return $this->storage->loadMultiple($uids);
    }


    /**
     * {@inheritdoc}
     *
     * Building the header and content lines for the inventory list.
     *
     * calling the parent::buildHeader() adds a column for the possible actions
     * and inserts the 'edit' and 'delete' links as defined for the entity type.
     */
    public function buildHeader()
    {
        $header = array(
        'label' => array(
          'data' => $this->t('Label'),
          'field' => 'label',
          'specifier' => 'label',
        ),
        'office_id' => array(
          'data' => $this->t('Office'),
          'field' => 'office_id',
          'specifier' => 'office_id',
        ),
        'notes' => array(
          'data' => $this->t('Notes'),
          'field' => 'notes',
          'specifier' => 'notes',
        ),
        'created' => array(
          'data' => $this->t('Created'),
          'field' => 'created',
          'specifier' => 'created',
        ),
        );

        return $header + parent::buildHeader();
    }


    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity)
    {
        $row['label'] = $entity->label->value;
        $row['office_id'] = $entity->office_id->target_id;
        $row['notes'] = $entity->notes->value;
        $row['created'] = $entity->created->value;

        return $row + parent::buildRow($entity);
    }    


    private function getEntity($entity_name, $id, $field)
    {
        $entity = $entity_name::load($id);
        $id = $entity->id->value;
        $field = $entity->$field->value;
        
        return $field;
    }
}

?>