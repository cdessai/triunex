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
class CustomerListBuilder extends EntityListBuilder
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
                '<a href="@addlink">Add a new customer</a>', array(
                '@addlink' => \Drupal::urlGenerator()->generateFromRoute('triune.entity.customer.add'),
                )
            ),
        );
        $build['table'] = parent::render();
        return $build;
    }


    public function load()
    {

        $entity_query = \Drupal::service('entity_type.manager')->getStorage('triune_customer')->getQuery();
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
          'data' => $this->t('Customer ID'),
          'field' => 'id',
          'specifier' => 'id',
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        'label' => array(
          'data' => $this->t('Customer Name'),
          'field' => 'label',
          'specifier' => 'label',
        ),
        'office_id' => array(
          'data' => $this->t('Office'),
          'field' => 'office',
          'specifier' => 'office',
        ),
        'address' => array(
          'data' => $this->t('Customer Address'),
          'field' => 'address',
          'specifier' => 'address',
        ),
        'email' => array(
          'data' => $this->t('Customer Email'),
          'field' => 'email',
          'specifier' => 'email',
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
        $row['label'] = $entity->label->value;
        $row['office_id'] = $entity->office_id->value;
        $row['address'] = $entity->address->value;
        $row['email'] = $entity->email->value;

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