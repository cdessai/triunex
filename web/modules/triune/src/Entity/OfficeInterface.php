<?php
/**
 * @file Contains \Drupal\triune\Entity\OfficeInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface OfficeInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the office value.
     *
     * @return string
     */
    public function getOffice();
}
?>