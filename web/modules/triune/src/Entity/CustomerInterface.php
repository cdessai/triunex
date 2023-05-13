<?php
/**
 * @file Contains \Drupal\triune\Entity\CustomerInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface CustomerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the customer value.
     *
     * @return string
     */
    public function getCustomer();
}
?>