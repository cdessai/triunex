<?php

namespace Drupal\triune\Entity;

use Drupal\views\EntityViewsDataInterface;
use Drupal\views\EntityViewsData;

/**
 * Provides an interface to integrate a Employee entity with views.
 */
class EmployeeViewsData extends EntityViewsData
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
        /*
        // Plain text field, exposed as a field, sort, filter, and argument.
        $data['triune_employee']['full_name'] = array(
        'title' => t('Full Name'),
        'help' => t('Just a plain text field.'),
        'field' => array(
        // ID of field handler plugin to use.
        'id' => 'standard',
        ),
        'sort' => array(
        // ID of sort handler plugin to use.
        'id' => 'standard',
        ),
        'filter' => array(
        // ID of filter handler plugin to use.
        'id' => 'string',
        ),
        'argument' => array(
        // ID of argument handler plugin to use.
        'id' => 'string',
        ),
        );
    
        $data['triune_employee']['triune_callsheet']['title'] = $this->t('Call Sheet');
        $data['triune_employee']['triune_callsheet']['relationship']['title'] = $this->t('Call Sheet');
        $data['triune_employee']['triune_callsheet']['relationship']['help'] = $this->t('Associated Call Sheet');
        $data['triune_employee']['triune_callsheet']['relationship']['label'] = $this->t('triune_callsheet');
        */
        return $data;
    
    }

}