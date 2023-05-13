<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\CustomerForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\triune\Entity\Customer;

/**
 * Customer form.
 *
 * @ingroup triune
 */
class CustomerForm extends ContentEntityForm
{
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        
        $form = parent::buildForm($form, $form_state);
        $entity = $this->entity;
        
        return $form;
        
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
        $customer = $this->entity;
        if ($customer->isNew()) {
            \Drupal::messenger()->addMessage($this->t('Created Customer @name', array('@name' => $form_state->getValue('label'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved Customer @name', array('@name' => $form_state->getValue('label'))), 'status');
        }
        
        if (!$customer) {
            $customer = Customer::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'office_id' => $form_state->getValue('office'),
                'label' => $form_state->getValue('label'),
                'address' => $form_state->getValue('address'),
                'email' => $form_state->getValue('email'),
                'created' => time(),
                'changed' => time(),
                's1_name' => $form_state->getValue('s1_name'),
                's1_phone' => $form_state->getValue('s1_phone'),
                's2_phone' => $form_state->getValue('s2_phone'),
                's3_phone' => $form_state->getValue('s3_phone'),
                's2_name' => $form_state->getValue('s2_name'),
                's3_name' => $form_state->getValue('s3_name'),
                'tsc_name' => $form_state->getValue('tsc_name'),
                'tsc_phone' => $form_state->getValue('tsc_phone'),
                'p_name' => $form_state->getValue('p_name'),
                'p_phone' => $form_state->getValue('p_phone'),
                ]
            );
        } else {
            $customer->office_id->setValue($form_state->getValue('office'));
            $customer->label->setValue($form_state->getValue('label'));
            $customer->address->setValue($form_state->getValue('address'));
            $customer->email->setValue($form_state->getValue('email'));
            $customer->s1_name->setValue($form_state->getValue('s1_name'));
            $customer->s1_phone->setValue($form_state->getValue('s1_phone'));
            $customer->s2_name->setValue($form_state->getValue('s2_name'));
            $customer->s3_name->setValue($form_state->getValue('s3_name'));
            $customer->s2_phone->setValue($form_state->getValue('s2_phone'));
            $customer->s3_phone->setValue($form_state->getValue('s3_phone'));
            $customer->tsc_name->setValue($form_state->getValue('tsc_name'));
            $customer->p_name->setValue($form_state->getValue('p_name'));
            $customer->tsc_phone->setValue($form_state->getValue('tsc_phone'));
            $customer->p_phone->setValue($form_state->getValue('p_phone'));


            $customer->changed->setValue(time());
        }
        $customer->save();

        $form_state->setRedirect('triune.entity.customer.collection');
        return;
        
    }
}
?>