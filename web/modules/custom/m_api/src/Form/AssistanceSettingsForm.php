<?php

namespace Drupal\m_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AssistanceSettingsForm.
 */
class AssistanceSettingsForm extends ConfigFormBase {
  const SETTINGS = 'm_api.assistance_form.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assistance_form_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::SETTINGS);

    $form['recipient_mail'] = [
      '#type' => 'email',
      '#title' => $this->t("Recipient's mail"),
      '#default_value' => $config->get('recipient_mail'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(self::SETTINGS)
      ->set('recipient_mail', $form_state->getValue('recipient_mail'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
