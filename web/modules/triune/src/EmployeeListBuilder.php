<?php

/**
 * @file Contains \Drupal\triune\EmployeeListBuilder
 */
namespace Drupal\triune;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a list controller for employee entity.
 * 
 * @ingroup triune
 */
class EmployeeListBuilder extends EntityListBuilder
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
                '<a href="@addlink">Add a new employee</a>', array(
                '@addlink' => \Drupal::urlGenerator()->generateFromRoute('triune.entity.employee.add'),
                )
            ),
        );
        $build['table'] = parent::render();
        return $build;
    }


    public function load()
    {

        $entity_query = \Drupal::service('entity_type.manager')->getStorage('triune_employee')->getQuery();
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
        'resource_id' => array(
          'data' => $this->t('Resource ID'),
          'field' => 'resource_id',
          'specifier' => 'resource_id',
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        'first_name' => array(
          'data' => $this->t('First Name'),
          'field' => 'first_name',
          'specifier' => 'first_name',
        ),
        'last_name' => array(
          'data' => $this->t('Last Name'),
          'field' => 'last_name',
          'specifier' => 'last_name',
        ),
        'phone' => array(
          'data' => $this->t('Phone'),
          'field' => 'phone',
          'specifier' => 'phone',
        ),
        'office_id' => array(
          'data' => $this->t('Office'),
          'field' => 'office_id',
          'specifier' => 'office_id',
        ),
        'status' => array(
          'data' => $this->t('Status'),
          'field' => 'status',
          'specifier' => 'status',
        ),
        );

        return $header + parent::buildHeader();
    }


    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity)
    {
        $row['resource_id'] = $entity->resource_id->value;
        $row['first_name'] = $entity->first_name->value;
        $row['last_name'] = $entity->last_name->value;
        $row['phone'] = $entity->phone->value;
        $row['office_id'] = $entity->office_id->target_id;
        $row['status'] = $entity->status->value;

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