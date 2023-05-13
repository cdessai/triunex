<?php
/**
 * @file Contains \Drupal\triune\Entity\OrderEmployeeInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface OrderEmployeeInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the order_employee value.
     *
     * @return string
     */
    public function getOrderEmployee();
}
?>