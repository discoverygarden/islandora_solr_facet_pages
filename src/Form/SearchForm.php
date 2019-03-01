<?php

namespace Drupal\islandora_solr_facet_pages\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A facet search form for search facets.
 */
class SearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_solr_facet_pages_search_form';
  }

  /**
   * Defines a facet search form for search facets.
   *
   * @param array $form
   *   The Drupal form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The Drupal form state.
   * @param array $vars
   *   Current facet variables to use in the search form.
   *
   * @return array
   *   The Drupal form definition.
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $vars = []) {
    $form = [];
    $form_state->setStorage(['vars' => $vars]);
    $form['search_term'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#default_value' => (isset($vars['search_term']) ? $vars['search_term'] : ''),
      '#size' => 60,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    module_load_include('inc', 'islandora_solr', 'includes/utilities');
    $form_state->setRedirect(
      'islandora_solr_facet_pages.callback',
      [
        'path' => $form_state->getStorage()['vars']['path'],
        'prefix' => $form_state->getStorage()['vars']['prefix'],
        'search_term' => islandora_solr_replace_slashes(trim($form_state->getValue('search_term'))),
      ]
    );
  }

}
