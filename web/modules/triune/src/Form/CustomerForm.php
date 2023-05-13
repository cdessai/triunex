<?php
/**
 * @file
 * Contains \Drupal\triune\Form\CustomerForm.
 */
namespace Drupal\triune\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\triune\Entity\Customer;
use Drupal\triune\Entity\Office;
use Drupal\triune\Controller\CustomerController;

/**
 * Customer form.
 */
class CustomerForm extends FormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'customer_form';
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $location_list = array(), $customer_id = 0)
    {

      if ($customer_id == 0) {
        $label = '';
        $office_id = 0;
        $address = '';
        $emails = array();
        $s1_name = '';
        $s1_phone = '';
        $s2_phone = '';
        $s3_phone = '';
        $s2_name = '';
        $s3_name = '';
        $tsc_phone = '';
        $tsc_name = '';
        $p_name = '';
        $p_phone = '';
      } 
      else {
        $customer = Customer::load($customer_id);
        $office = Office::load($customer->office_id->target_id);
        $label = $customer->label->value;
        $office_id = $office->location_id->value;
        $address = $customer->address->value;
        $emails = json_decode($customer->email->value);
        $address = $customer->address->value;
        $s1_name = $customer->s1_name->value;
        $s1_phone = $customer->s1_phone->value;
        $s2_phone = $customer->s2_phone->value;
        $s3_phone = $customer->s3_phone->value;
        $s2_name = $customer->s2_name->value;
        $s3_name = $customer->s3_name->value;
        $tsc_phone = $customer->tsc_phone->value;
        $p_phone = $customer->p_phone->value;
        $tsc_name = $customer->tsc_name->value;
        $p_name = $customer->p_name->value;
      }

      $form['id'] = array(
        '#type' => 'hidden',
        '#title' => t('Customer ID'),
        '#required' => true,
        '#default_value' => $customer_id,
      );
      $form['label'] = array(
        '#type' => 'textfield',
        '#title' => t('Customer Name'),
        '#required' => TRUE,
        '#default_value' => $label,
      );
      foreach ($location_list as $location) {
        $office_options[$location->location_id] = $this->t($location->label);
      }
      $form['office'] = array(
        '#type' => 'select',
        '#title' => $this->t('Office'),
        '#options' => $office_options,
        '#default_value' => $office_id,
      );
      $form['address'] = array(
        '#type' => 'textfield',
        '#title' => t('Customer Address'),
        '#required' => false,
        '#default_value' => $address,
      );
      $form['1ss'] = array(
        '#type' => 'fieldset',
        '#title' => t('1st Shift Supervisor'),
        '#description' => t(''),
      );
      $form['1ss']['s1_name'] = array(
        '#type' => 'textfield',
        '#description' => t(''),
        '#title' => t('Name'),
        '#required' => false,
        '#default_value' => $s1_name,
      );
      $form['1ss']['s1_phone'] = array(
        '#type' => 'tel',
        '#title' => t('Phone'),
        '#required' => false,
        '#attributes' => array(
          'placeholder' => '(___) ___-____',
        ),
        '#default_value' => $s1_phone,
      );
      $form['1ss']['email_1'] = array(
        '#type' => 'textfield',
        '#title' => t('Email'),
        '#description' => t('For multiple email contacts per shift, separate each email address with a comma: (john@example.com,jane@example.com)'),
        '#required' => false,
        '#default_value' => $emails[0],
      );
      $form['2nd shift supervisor'] = array(
        '#type' => 'fieldset',
        '#title' => t('2nd Shift Supervisor'),
        '#description' => t(''),
      );
      $form['2nd shift supervisor']['s2_name'] = array(
        '#type' => 'textfield',
        '#description' => t(''),
        '#title' => t('Name'),
        '#required' => false,
        '#default_value' => $s2_name,
      );
      $form['2nd shift supervisor']['s2_phone'] = array(
        '#type' => 'tel',
        '#title' => t('Phone'),
        '#required' => false,
        '#attributes' => array(
          'placeholder' => '(___) ___-____',
        ),
        '#default_value' => $s2_phone,
      );
      $form['2nd shift supervisor']['email_2'] = array(
        '#type' => 'textfield',
        '#title' => t('Email'),
        '#description' => t('For multiple email contacts per shift, separate each email address with a comma: (john@example.com,jane@example.com)'),
        '#required' => false,
        '#default_value' => $emails[1],
      );
      $form['3rd shift supervisor'] = array(
        '#type' => 'fieldset',
        '#title' => t('3rd Shift Supervisor'),
        '#description' => t(''),
      );
      $form['3rd shift supervisor']['s3_name'] = array(
        '#type' => 'textfield',
        '#description' => t(''),
        '#title' => t('Name'),
        '#required' => false,
        '#default_value' => $s3_name,
      );
      $form['3rd shift supervisor']['s3_phone'] = array(
        '#type' => 'tel',
        '#title' => t('Phone'),
        '#required' => false,
        '#attributes' => array(
          'placeholder' => '(___) ___-____',
        ),
        '#default_value' => $s3_phone,
      );
      $form['3rd shift supervisor']['email_3'] = array(
        '#type' => 'textfield',
        '#title' => t('Email'),
        '#description' => t('For multiple email contacts per shift, separate each email address with a comma: (john@example.com,jane@example.com)'),
        '#required' => false,
        '#default_value' => $emails[2],
      );
      $form['tsc'] = array(
        '#type' => 'fieldset',
        '#title' => t('TIme Sheet Contact'),
        '#description' => t(''),
      );
      $form['tsc']['tsc_name'] = array(
        '#type' => 'textfield',
        '#description' => t(''),
        '#title' => t('Name'),
        '#required' => false,
        '#default_value' => $tsc_name,
      );
      $form['tsc']['tsc_phone'] = array(
        '#type' => 'tel',
        '#title' => t('Phone'),
        '#required' => false,
        '#attributes' => array(
          'placeholder' => '(___) ___-____',
        ),
        '#default_value' => $tsc_phone,
      );
      $form['tsc']['email_4'] = array(
        '#type' => 'textfield',
        '#title' => t('Email'),
        '#description' => t(''),
        '#required' => false,
        '#default_value' => $emails[3],
      );
      $form['payables'] = array(
        '#type' => 'fieldset',
        '#title' => t('Payables Contact'),
        '#description' => t(''),
      );
      $form['payables']['p_name'] = array(
        '#type' => 'textfield',
        '#description' => t(''),
        '#title' => t('Name'),
        '#required' => false,
        '#default_value' => $p_name,
      );
      $form['payables']['p_phone'] = array(
        '#type' => 'tel',
        '#title' => t('Phone'),
        '#required' => false,
        '#attributes' => array(
          'placeholder' => '(___) ___-____',
        ),
        '#default_value' => $p_phone,
      );
      $form['payables']['email_5'] = array(
        '#type' => 'textfield',
        '#title' => t('Email'),
        '#description' => t(''),
        '#required' => false,
        '#default_value' => $emails[4],
      );
      $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Save & Continue »'),
      );
      return $form;
    }



    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $id = $form_state->getValue('id');
        if (!$id) {
            $customer = false;
        } else {
            $customer = Customer::load($id);
        }

        $query = \Drupal::database()->select('triune_office', 'r');
        $query->fields('r', array('id', 'label'));
        $query->condition('r.location_id', $form_state->getValue('office'));
        $office = $query->execute()->fetch();

        $emails = json_encode(array($form_state->getValue('email_1'), $form_state->getValue('email_2'), $form_state->getValue('email_3'),
          $form_state->getValue('email_4'), $form_state->getValue('email_5')));

        if($customer) {
          $customer->label->setValue($form_state->getValue('label'));
          $customer->email->setValue($emails);
          $customer->address->setValue($form_state->getValue('address'));
          $customer->office_id->setValue($office->id);
          $customer->changed->setValue(time());
          $customer->s1_name->setValue($form_state->getValue('s1_name'));
          $customer->s2_name->setValue($form_state->getValue('s2_name'));
          $customer->s3_name->setValue($form_state->getValue('s3_name'));
          $customer->tsc_name->setValue($form_state->getValue('tsc_name'));
          $customer->p_name->setValue($form_state->getValue('p_name'));
          $customer->s1_phone->setValue($form_state->getValue('s1_phone'));
          $customer->s2_phone->setValue($form_state->getValue('s2_phone'));
          $customer->s3_phone->setValue($form_state->getValue('s3_phone'));
          $customer->tsc_phone->setValue($form_state->getValue('tsc_phone'));
          $customer->p_phone->setValue($form_state->getValue('p_phone'));
          $customer->save();
        } else {
            $customer = Customer::create(
              [
              'uuid' => \Drupal::service('uuid')->generate(),
              'office_id' => $office->id,
              'label' => $form_state->getValue('label'),
              'email' => $emails,
              'address' => $form_state->getValue('address'),
              'created' => time(),
              'changed' => time(),
              's1_name' => $form_state->getValue('s1_name'),
              's2_name' => $form_state->getValue('s2_name'),
              's3_name' => $form_state->getValue('s3_name'),
              'tsc_name' => $form_state->getValue('tsc_name'),
              'p_name' => $form_state->getValue('p_name'),
              's1_phone' => $form_state->getValue('s1_phone'),
              's2_phone' => $form_state->getValue('s2_phone'),
              's3_phone' => $form_state->getValue('s3_phone'),
              'tsc_phone' => $form_state->getValue('tsc_phone'),
              'p_phone' => $form_state->getValue('p_phone'),
              ]
            );
            $customer->save();
          }

        $form_state->setRedirect('triune.customer.list');
        return;
    }
}