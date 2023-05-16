<?php
/**
 * @file Contains \Drupal\triune\Entity\PayrollInterface
 */
namespace Drupal\triune\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

interface PayrollInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
    /**
     * Gets the Payroll value.
     *
     * @return string
     */
    public function getPayroll();
}
