<?php
namespace Drupal\triune\Entity;

use Drupal\views\EntityViewsDataInterface;

/**
 * Provides an interface to integrate an Customer entity type with views.
 */
interface CustomerViewsDataInterface extends EntityViewsDataInterface
{

    /**
     * Returns views data for the entity type.
     *
     * @return array
     *   Views data in the format of hook_views_data().
     */
    public function getViewsData();

}