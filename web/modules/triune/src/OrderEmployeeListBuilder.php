<?php

/**
 * @file Contains \Drupal\triune\OrderEmployeeListBuilder
 */
namespace Drupal\triune;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a list controller for order_employee entity.
 * 
 * @ingroup triune
 */
class OrderEmployeeListBuilder extends EntityListBuilder
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
                '<a href="@addlink">Add a new order employee</a>', array(
                '@addlink' => \Drupal::urlGenerator()->generateFromRoute('triune.entity.order_employee.add'),
                )
            ),
        );
        $build['table'] = parent::render();
        return $build;
    }


    public function load()
    {

        $entity_query = \Drupal::service('entity_type.manager')->getStorage('triune_order_employee')->getQuery();
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
        'order_id' => array(
          'data' => $this->t('Order'),
          'field' => 'order_id',
          'specifier' => 'order_id',
        ),
        'employee_id' => array(
          'data' => $this->t('Employee'),
          'field' => 'employee_id',
          'specifier' => 'employee_id',
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
        $row['order_id'] = $entity->callsheet_id->target_id;
        $row['employee_id'] = $entity->employee_id->target_id;
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