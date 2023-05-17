<?php

namespace Drupal\triune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\triune\Entity\EmployeeInterface;

class EmployeeNoteForm extends FormBase {
    
    protected $database;
    protected $user;
    
    public function __construct(Connection $database, AccountInterface $account) {
        $this->database = $database;
        $this->user = $account;
    }
    
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database'),
            $container->get('current_user')
        );
    }
    
    public function getFormId() {
        return 'employee_note_form';
    }
    
    public function buildForm(array $form, FormStateInterface $form_state, EmployeeInterface $employee = NULL, $note = NULL) {
        $form_state->set('employee', $employee);
        $form_state->set('note', $note);
        
        $form['content'] = [
            '#type' => 'textarea',
            '#title' => $this->t('Notes'),
            '#required' => TRUE,
            '#default_value' => $note['content'] ?? '',
        ];
        
        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#button_type' => 'primary',
        ];
        return $form;
    }
    
    public function validateForm(array &$form, FormStateInterface $form_state) {
        
    }
    
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $employee = $form_state->get('employee');
        $note = $form_state->get('note');
        if (is_null($note)) {
            // Add new note
            $this->database->insert('triune_employee_notes')
            ->fields([
                'employee_id' => $employee->id(),
                'user_id' => $this->user->id(),
                'note' => $form_state->getValue('content'),
                'created' => \Drupal::time()->getRequestTime(),
            ])
            ->execute();
        }
        
        $form_state->setRedirect('triune.employee.note.list', ['employee' => $employee->id()]);
    }
}
