<?php
/**
 * @file Contains \Drupal\triune\Entity\CallSheetEmployeeInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface CallSheetEmployeeInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the call_sheet_employee value.
     *
     * @return string
     */
    public function getCallSheetEmployee();
}
?>