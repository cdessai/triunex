<?php
/**
 * @file Contains \Drupal\triune\Entity\OrderInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface OrderInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the order value.
     *
     * @return string
     */
    public function getOrder();
}
?>