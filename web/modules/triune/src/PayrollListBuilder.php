<?php

/**
 * @file Contains \Drupal\triune\CustomerListBuilder
 */
namespace Drupal\triune;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a list controller for customer entity.
 *
 * @ingroup triune
 */
class PayrollListBuilder extends EntityListBuilder
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
                '<a href="@addlink">Add a new payroll</a>', array(
                '@addlink' => \Drupal::urlGenerator()->generateFromRoute('triune.entity.payroll.add'),
                )
            ),
        );
        $build['table'] = parent::render();
        return $build;
    }


    public function load()
    {

        $entity_query = \Drupal::service('entity_type.manager')->getStorage('triune_payroll')->getQuery();
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
            'id' => array(
              'data' => $this->t('Payroll ID'),
              'field' => 'id',
              'specifier' => 'id',
              'class' => array(RESPONSIVE_PRIORITY_LOW),
            ),
            'changed' => array(
                'data' => $this->t('Last Updated'),
                'class' => [RESPONSIVE_PRIORITY_LOW],
            ),
            'customer_id' => array(
              'data' => $this->t('Customer Name'),
              'field' => 'customer_id',
              'specifier' => 'customer_id',
            ),
            'position_name' => array(
              'data' => $this->t('Position'),
              'field' => 'position_name',
              'specifier' => 'position_name',
            ),
            'rate' => array(
              'data' => $this->t('Rate'),
              'field' => 'rate',
              'specifier' => 'rate',
            ),
        );

        return $header + parent::buildHeader();
    }


    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity)
    {
        $row['id'] = $entity->id->value;
        $row['changed'] = $entity->changed->value;
        $row['customer_id'] = $entity->customer_id->target_id;
        $row['position_name'] = $entity->position_name->value;
        $row['rate'] = $entity->rate->value;

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
