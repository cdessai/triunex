<?php

/**
 * @file Contains \Drupal\triune\OrderListBuilder
 */
namespace Drupal\triune;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Provides a list controller for order entity.
 * 
 * @ingroup triune
 */
class OrderListBuilder extends EntityListBuilder
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
                '<a href="@addlink">Add a new order</a>', array(
                '@addlink' => \Drupal::urlGenerator()->generateFromRoute('triune.entity.order.add'),
                )
            ),
        );
        $build['table'] = parent::render();
        return $build;
    }


    public function load()
    {

        $entity_query = \Drupal::service('entity_type.manager')->getStorage('triune_order')->getQuery();
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
          'data' => $this->t('Order ID'),
          'field' => 'id',
          'specifier' => 'id',
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        'label' => array(
          'data' => $this->t('Order Name'),
          'field' => 'label',
          'specifier' => 'label',
        ),
        );

        return $header + parent::buildHeader();
    }


    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity)
    {
        $row['id'] = $entity->id();
        $row['label'] = $entity->label->value;

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