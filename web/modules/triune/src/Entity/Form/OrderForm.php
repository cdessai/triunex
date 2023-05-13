<?php
/**
 * @file
 * Contains \Drupal\triune\Entity\Form\OrderForm.
 */
namespace Drupal\triune\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Order form.
 *
 * @ingroup triune
 */
class OrderForm extends ContentEntityForm
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
        
        $order = $this->entity;
        if ($order->isNew()) {
            \Drupal::messenger()->addMessage($this->t('Created Order @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        } else {
            \Drupal::messenger()->addMessage($this->t('Saved Order @name', array('@name' => $form_state->getValue('customer_name'))), 'status');
        }
        
        if (!$order) {
            $order = Order::create(
                [
                'uuid' => \Drupal::service('uuid')->generate(),
                'label' => $form_state->getValue('label'),
                'created' => time(),
                'changed' => time(),
                ]
            );
        } else {
            $order->label->setValue($form_state->getValue('label'));
            $order->changed->setValue(time());
        }
        $order->save();

        $form_state->setRedirect('triune.entity.order.collection');
        return;
        
    }
}
?>