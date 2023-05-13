<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\PayrollController.
 */
namespace Drupal\triune\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\triune\Entity\PayrollInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Controller routines for triune_jobrate page routes.
 */
class PayrollController implements ContainerInjectionInterface
{
    protected $payrollStorage;
    protected $user;

    /**
     * Constructs a \Drupal\triune\Controller\PayrollController object.
     *
     */
    public function __construct(EntityTypeManagerInterface $etm, AccountInterface $user)
    {
        $this->payrollStorage = $etm->getStorage('triune_payroll');
        $this->user = $user;
    }
    
    /**
     * Delete all data from database tables if tables exist.
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager'),
            $container->get('current_user')
        );
    }
    
    public function listPayrolls(Request $r) {
        $offset = 0;
        $limit = 10;
        
        $query = $this->payrollStorage->getQuery();
        $results = $query->range($offset, $limit)
            ->accessCheck(TRUE)
            ->execute();
        
        $items = [];
        if (!empty($results)) {
            $items = $this->payrollStorage->loadMultiple($results);
        }
        
        return [
            '#theme' => 'payroll_list',
            '#items' => $items,
            '#cache' => ['max-age' => 0],
        ];
    }
    
    public function viewPayroll(PayrollInterface $payroll, Request $r) {
        return [
            '#theme' => 'payroll_view',
            '#payroll' => $payroll,
            '#cache' => ['max-age' => 0],
        ];
    }
    
    public function deletePayroll(PayrollInterface $payroll, Request $r) {
        $id = $payroll->id();
        // TODO: delete entity
        return [
            '#theme' => 'payroll_delete',
            '#payroll_id' => $id,
            '#cache' => ['max-age' => 0],
        ];
    }
}
