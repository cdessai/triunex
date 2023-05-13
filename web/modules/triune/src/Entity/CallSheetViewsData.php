<?php

namespace Drupal\triune\Entity;

use Drupal\views\EntityViewsDataInterface;
use Drupal\views\EntityViewsData;

/**
 * Provides an interface to integrate a CallSheet entity with views.
 */
class CallSheetViewsData extends EntityViewsData
{

    /**
     * Returns views data for the entity type.
     *
     * @return array
     *   Views data in the format of hook_views_data().
     */
    public function getViewsData()
    {
        $data = parent::getViewsData();
        return $data;
    }

}