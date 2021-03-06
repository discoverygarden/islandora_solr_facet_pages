<?php

namespace Drupal\islandora_solr_facet_pages\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin form building class.
 */
class IslandoraSolrFacetPagesAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_solr_facet_pages_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['islandora_solr_facet_pages.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'islandora_solr_facet_pages/admin';

    $form['facet_pages'] = [
      '#type' => 'fieldset',
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    // Fields.
    $form['facet_pages']['islandora_solr_facet_pages_fields_data'] = [
      '#type' => 'item',
      '#title' => $this->t('Facet pages'),
      '#description' => $this->t('Values in the selected solr field will be browsable (alphabetically) at <i>/browse/PATH</i>. <br />Save settings to access additional empty fields.'),
      '#tree' => TRUE,
      '#theme' => 'islandora_solr_facet_pages_admin_table',
    ];

    // Get fields from variable.
    $fields_data = $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_fields_data');

    // Add 3 empty fields.
    for ($i = 1; $i <= 3; $i++) {
      $fields_data[] = [''];
    }

    $fields = [];
    foreach ($fields_data as $value) {
      $field = [
        'solr_field' => [
          '#type' => 'textfield',
          '#default_value' => isset($value['solr_field']) ? $value['solr_field'] : '',
          '#autocomplete_path' => 'islandora_solr/autocomplete_luke',
        ],
        'label' => [
          '#type' => 'textfield',
          '#default_value' => isset($value['label']) ? $value['label'] : '',
        ],
        'path' => [
          '#type' => 'textfield',
          '#default_value' => isset($value['path']) ? $value['path'] : '',
        ],
      ];
      $fields[] = $field;
    }

    // Add fields.
    $form['facet_pages']['islandora_solr_facet_pages_fields_data']['fields'] = $fields;

    // Limit results per page.
    $form['facet_pages']['islandora_solr_facet_pages_limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Results per page'),
      '#size' => 5,
      '#default_value' => $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_limit'),
      '#description' => $this->t('Use a pager to display this many values per page.'),
    ];

    // Limit maximum results to be returned.
    $form['facet_pages']['islandora_solr_facet_pages_facet_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Maximum facet values'),
      '#multiple' => FALSE,
      '#options' => [],
      '#default_value' => $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_facet_limit'),
      '#description' => $this->t('The maximum number of values returned. A higher number can cause slower page loads.'),
    ];
    $options = [
      1000,
      5000,
      10000,
      100000,
      250000,
      500000,
      750000,
      1000000,
      2500000,
      5000000,
      7500000,
      10000000,
    ];

    foreach ($options as $o) {
      $form['facet_pages']['islandora_solr_facet_pages_facet_limit']['#options'][$o] = number_format($o);
    }

    // Display search form on facet page.
    $form['facet_pages']['islandora_solr_facet_pages_search_form'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display a search form on the facet page.'),
      '#default_value' => $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_search_form'),
    ];

    $form['facet_pages']['islandora_solr_facet_pages_lucene_syntax_escape'] = [
      '#title' => $this->t('Use Lucene syntax string escaping on search terms'),
      '#type' => 'checkbox',
      '#default_value' => $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_syntax_escape'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_solr_facet_pages_search_form"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    $form['facet_pages']['islandora_solr_facet_pages_lucene_escape_regex'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Regular expression evaluated on search term'),
      '#default_value' => $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_regex_default'),
      '#description' => $this->t("Used to escape special characters when found in search terms. Defaults to @regex", [
        '@regex' => ISLANDORA_SOLR_QUERY_FACET_LUCENE_ESCAPE_REGEX_DEFAULT,
      ]),
      '#states' => [
        'visible' => [
          ':input[name="islandora_solr_facet_pages_lucene_syntax_escape"]' => [
            'checked' => TRUE,
          ],
          ':input[name="islandora_solr_facet_pages_search_form"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $form['buttons']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 50,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // On save.
    if ($form_state->get(['clicked_button', '#id']) == 'edit-submit') {

      // Check for valid paths.
      foreach ($form_state->getValue([
        'islandora_solr_facet_pages_fields_data',
        'fields',
      ]) as $key => $value) {
        if (!preg_match("/^[a-zA-Z0-9-_]*$/", $value['path'])) {
          $form_state->setErrorByName('islandora_solr_facet_pages_fields_data][fields][' . $key . '][path', $this->t('The path can only contain the following characters: a-z, A-Z, 0-9, - and _'));
        }
      }

      // Get limit value.
      $limit = $form_state->getValue([
        'islandora_solr_facet_pages_limit',
      ]);
      $limit = trim($limit);
      // Check numeric.
      if (!is_numeric($limit)) {
        $form_state->setErrorByName('islandora_solr_facet_pages_limit', $this->t('Results per page must be numeric.'));
      }
      // Check for no decimals.
      if (strpos($limit, '.')) {
        $form_state->setErrorByName('islandora_solr_facet_pages_limit', $this->t('Results per page cannot include decimals.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('islandora_solr_facet_pages.settings');
    // Clean up array.
    $fields_data = $form_state->getValue(['islandora_solr_facet_pages_fields_data', 'fields']);
    foreach ($fields_data as $key => $value) {
      if (empty($value['solr_field']) && empty($value['label']) && empty($value['path'])) {
        unset($fields_data[$key]);
      }
    }
    $config->set('islandora_solr_facet_pages_fields_data', $fields_data)->save();
    $config->set('islandora_solr_facet_pages_limit', trim($form_state->getValue(['islandora_solr_facet_pages_limit'])))->save();
    $config->set('islandora_solr_facet_pages_facet_limit', $form_state->getValue(['islandora_solr_facet_pages_facet_limit']))->save();
    $config->set('islandora_solr_facet_pages_search_form', $form_state->getValue(['islandora_solr_facet_pages_search_form']))->save();
    $config->set('islandora_solr_facet_pages_lucene_regex_default', $form_state->getValue(['islandora_solr_facet_pages_lucene_escape_regex']))->save();
    $config->set('islandora_solr_facet_pages_lucene_syntax_escape', $form_state->getValue(['islandora_solr_facet_pages_lucene_syntax_escape']))->save();
    drupal_set_message($this->t('The configuration options have been saved.'));
  }

}
