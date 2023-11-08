<?php

namespace Drupal\wso2_with_jwt\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CdfssoForm.
 *
 * @package Drupal\wso2_with_jwt\Form
 */
class Wso2Form extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormID()
    {
        return 'wso2_with_jwt_admin_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $config = \Drupal::config('wso2_with_jwt.settings');

        $form['base_url'] = array(
            '#type' => 'textfield',
            '#title' => t('URL API'),
            '#description' => t('Url base dove esposte le API'),
            '#default_value' => $config->get('wso2_with_jwt.base_url'),
        );

        $form['client_id'] = array(
            '#type' => 'textfield',
            '#title' => t('Client Id'),
            '#default_value' => $config->get('wso2_with_jwt.client_id'),
        );

        $form['client_secret'] = array(
            '#type' => 'textfield',
            '#title' => t('Client Secret'),
            '#default_value' => $config->get('wso2_with_jwt.client_secret'),
        );


        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     * Compares the submitted settings to the defaults and unsets any that are equal. This was we only store overrides.
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);
        $config = $this->config('wso2_with_jwt.settings');
        $base_url = $form_state->getValue('base_url');
        $client_id = $form_state->getValue('client_id');
        $client_secret = $form_state->getValue('client_secret');

        $config
                ->set('wso2_with_jwt.base_url', $base_url)
                ->set('wso2_with_jwt.client_id', $client_id)
                ->set('wso2_with_jwt.client_secret', $client_secret)
                ->save();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'wso2_with_jwt.settings',
        ];
    }

}
