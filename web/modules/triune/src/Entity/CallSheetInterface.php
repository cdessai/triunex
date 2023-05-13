<?php
/**
 * @file Contains \Drupal\triune\Entity\CallSheetInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface CallSheetInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the call_sheet value.
     *
     * @return string
     */
    public function getCallSheet();
}
?>