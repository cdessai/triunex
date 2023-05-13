<?php
/**
 * @file Contains \Drupal\triune\Entity\EmployeeInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface EmployeeInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the employee value.
     *
     * @return string
     */
    public function getEmployee();
}
?>