<?php

namespace Drupal\islandora_solr_facet_pages\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class IslandoraSolrFacetPagesAdminSettings extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_solr_facet_pages_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Include admin CSS file.
    $admin_css = drupal_get_path('module', 'islandora_solr_facet_pages') . '/css/islandora_solr_facet_pages.admin.css';
    // @FIXME
    // The Assets API has totally changed. CSS, JavaScript, and libraries are now
    // attached directly to render arrays using the #attached property.
    //
    //
    // @see https://www.drupal.org/node/2169605
    // @see https://www.drupal.org/node/2408597
    // drupal_add_css($admin_css);


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
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/islandora_solr_facet_pages.settings.yml and config/schema/islandora_solr_facet_pages.schema.yml.
    $fields_data = \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_fields_data');

    // Add 3 empty fields.
    for ($i = 1; $i <= 3; $i++) {
      $fields_data[] = [''];
    }

    $fields = [];
    foreach ($fields_data as $key => $value) {
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
      '#default_value' => \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_limit'),
      '#description' => $this->t('Use a pager to display this many values per page.'),
    ];

    // Limit maximum results to be returned.
    $form['facet_pages']['islandora_solr_facet_pages_facet_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Maximum facet values'),
      '#multiple' => FALSE,
      '#options' => [],
      '#default_value' => \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_facet_limit'),
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
      '#default_value' => \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_search_form'),
    ];

    $form['facet_pages']['islandora_solr_facet_pages_lucene_syntax_escape'] = [
      '#title' => $this->t('Use Lucene syntax string escaping on search terms'),
      '#type' => 'checkbox',
      '#default_value' => \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_syntax_escape'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_solr_facet_pages_search_form"]' => [
            'checked' => TRUE
            ]
          ]
        ],
    ];

    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/islandora_solr_facet_pages.settings.yml and config/schema/islandora_solr_facet_pages.schema.yml.
    $form['facet_pages']['islandora_solr_facet_pages_lucene_escape_regex'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Regular expression evaluated on search term'),
      '#default_value' => \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_regex_default'),
      '#description' => $this->t("Used to escape special characters when found in search terms. Defaults to @regex", [
        '@regex' => ISLANDORA_SOLR_QUERY_FACET_LUCENE_ESCAPE_REGEX_DEFAULT
        ]),
      '#states' => [
        'visible' => [
          ':input[name="islandora_solr_facet_pages_lucene_syntax_escape"]' => [
            'checked' => TRUE
            ],
          ':input[name="islandora_solr_facet_pages_search_form"]' => [
            'checked' => TRUE
            ],
        ]
        ],
    ];

    $form['buttons']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#weight' => 50,
    ];
    $form['buttons']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset to defaults'),
      '#weight' => 51,
    ];

    return $form;
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

    // On save.
    if ($form_state->get(['clicked_button', '#id']) == 'edit-submit') {

      // Check for valid paths.
      foreach ($form_state->getValue([
        'islandora_solr_facet_pages_fields_data',
        'fields',
      ]) as $key => $value) {
        if (!preg_match("/^[a-zA-Z0-9-_]*$/", $value['path'])) {
          $form_state->setErrorByName('islandora_solr_facet_pages_fields_data][fields][' . $key . '][path', t('The path can only contain the following characters: a-z, A-Z, 0-9, - and _'));
        }
      }

      // Get limit value.
      $limit = $form_state->getValue([
        'islandora_solr_facet_pages_limit'
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

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

    // Get operation.
    $op = $form_state->getTriggeringElement();

    switch ($op) {
      case 'edit-submit':
        // Set variables.
        // Clean up array.
        $fields_data = $form_state->getValue(['islandora_solr_facet_pages_fields_data', 'fields']);
        foreach ($fields_data as $key => $value) {
          if (empty($value['solr_field']) AND empty($value['label']) AND empty($value['path'])) {
            unset($fields_data[$key]);
          }
        }
        \Drupal::configFactory()->getEditable('islandora_solr_facet_pages.settings')->set('islandora_solr_facet_pages_fields_data', $fields_data)->save();
        \Drupal::configFactory()->getEditable('islandora_solr_facet_pages.settings')->set('islandora_solr_facet_pages_limit', trim($form_state->getValue(['islandora_solr_facet_pages_limit'])))->save();
        \Drupal::configFactory()->getEditable('islandora_solr_facet_pages.settings')->set('islandora_solr_facet_pages_facet_limit', $form_state->getValue(['islandora_solr_facet_pages_facet_limit']))->save();
        \Drupal::configFactory()->getEditable('islandora_solr_facet_pages.settings')->set('islandora_solr_facet_pages_search_form', $form_state->getValue(['islandora_solr_facet_pages_search_form']))->save();

        \Drupal::configFactory()->getEditable('islandora_solr_facet_pages.settings')->set('islandora_solr_facet_pages_lucene_regex_default', $form_state->getValue(['islandora_solr_facet_pages_lucene_escape_regex']))->save();
        \Drupal::configFactory()->getEditable('islandora_solr_facet_pages.settings')->set('islandora_solr_facet_pages_lucene_syntax_escape', $form_state->getValue(['islandora_solr_facet_pages_lucene_syntax_escape']))->save();

        drupal_set_message($this->t('The configuration options have been saved.'));
        break;

      case 'edit-reset':
        // Empty variables.
        // Remove variables.
        $variables = [
          'islandora_solr_facet_pages_fields_data',
          'islandora_solr_facet_pages_limit',
          'islandora_solr_facet_pages_facet_limit',
        ];
        foreach ($variables as $variable) {
          // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// variable_del($variable);

        }

        drupal_set_message($this->t('The configuration options have been reset to their default values.'));
        break;
    }
  }

}
