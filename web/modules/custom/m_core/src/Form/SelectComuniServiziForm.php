<?php

namespace Drupal\m_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SelectComuniServiziForm.
 */
class SelectComuniServiziForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'select_comuni_servizi_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = t('<h5>Choose the municipality</h5>');
    $form['comuni'] = [
      '#type'    => 'select',
      '#options' => $this->getComuni(),
      '#weight'  => '0',
    ];
    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @reminder: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('user.private_tempstore')->get('m_core');
    $tempstore->set('id_comune_selezionato', $form_state->getValue('comuni'));
    return $form_state->setRedirect('entity.node.canonical', ['node' => $form_state->getValue('comuni')]);
  }

  /**
   * Get options for "Comuni" select field.
   *
   * @return array
   *   The options array.
   */
  public function getComuni() {
    $comuni   = [];
    $database = \Drupal::database();
    $query    = $database->select('node_field_data', 'fd');

    $query->condition('fd.type', 'comune_servizi');
    $query->condition('fd.status', '1');
    $query->fields('fd', ['nid', 'title']);
    $query->orderBy('title', 'ASC');
    $result = $query->execute();
    foreach ($result as $record) {
      $comuni[$record->nid] = $record->title;
    }
    asort($comuni);
    return $comuni;
  }

}
