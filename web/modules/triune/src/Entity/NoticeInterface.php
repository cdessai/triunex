<?php
/**
 * @file Contains \Drupal\triune\Entity\NoticeInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface NoticeInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the office value.
     *
     * @return string
     */
    public function getNotice();
}
?>