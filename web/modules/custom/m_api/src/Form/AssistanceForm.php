<?php

namespace Drupal\m_api\Form;

use Drupal\contact\MailHandlerException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AssistanceForm.
 */
class AssistanceForm extends FormBase {
  /**
   * Drupal\Core\Mail\MailManagerInterface definition.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $pluginManagerMail;

  /**
   * Drupal\Core\Config\ConfigManagerInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * Drupal\user\PrivateTempStoreFactory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->pluginManagerMail = $container->get('plugin.manager.mail');
    $instance->configManager = $container->get('config.manager');
    $instance->tempStore = $container->get('tempstore.private');
    $instance->tempStore = $instance->tempStore->get('m_api');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assistance_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $mail_sended = $this->tempStore->get('mail_sended');

    $form['title'] = [
      '#markup' => '<div class="assistance-form__title">' . $this->t('Is something wrong?') . '</div>',
    ];

    // Check if is already setted the 'mail_sended' user session's key to avoid
    // multiple send and suspicious send.
    if (!$mail_sended) {
      $form['something_wrong'] = [
        '#type' => 'textarea',
        '#title' => $this->t('description of the event'),
        '#weight' => '0',
        '#attributes' => [
          'placeholder' => $this->t('Type the text here'),
        ],
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
        '#attributes' => [
          'class' => ['btn', 'btn-primary', 'assistance-form__send'],
        ],
      ];
    }
    else {
      // Get current username from currentUser.
      $data['@username'] = $this->currentUser()->getDisplayName();

      $form['thankyou'] = [
        '#markup' => "<div class='assistance-form__thank-you'> <br>"
        . t("Thank you @username for contacting US. We will contact you as soon as possible.", $data)
        . "<br>" . t("If you need other contact us agin:")
        . " </div>",
      ];
      $form['contact_again'] = [
        '#type' => 'submit',
        '#value' => t("clicking here"),
        '#attributes' => [
          'class' => ['assistance-form__contact_again'],
        ],
        '#submit' => [
          '::resetSessionMailSendedKey',
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    // Send mail.
    try {
      $recipientMail = \Drupal::configFactory()->getEditable(AssistanceSettingsForm::SETTINGS)->get('recipient_mail');
      if (!isset($recipientMail)) {
        $recipientMail = "impleme@comune.messina.it";
      }

      $this->pluginManagerMail->mail('m_api', 'something_wrong_form', $recipientMail, 'it', [
        'body' => $form_state->getValue('something_wrong'),
        'subject' => 'New message on "Visure" from user @username',
        'account' => $this->currentUser(),
      ]);

      // Set statex.
      $this->tempStore->set('mail_sended', TRUE);
    }
    catch (MailHandlerException $exception) {
      \Drupal::messenger()->addError($exception->getMessage());
      // Set state.
      $this->tempStore->set('mail_sended', FALSE);
    }
  }

  /**
   * Reset the user 'mail_sended' session's key.clea.
   */
  public function resetSessionMailSendedKey() {
    if ($this->tempStore->get('mail_sended')) {
      $this->tempStore->set('mail_sended', FALSE);
    }
  }

}
