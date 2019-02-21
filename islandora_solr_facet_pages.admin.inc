<?php

/**
 * @file
 * Admin functionality.
 */

/**
 * Theme callback.
 */
function theme_islandora_solr_facet_pages_admin_table(&$vars) {
  // Set variable.
  $rows = [];
  $form = $vars['form'];

  // Render islandora_solr_primary_display_table.
  foreach ($form['fields'] as $key => $elements) {

    $row = [];
    // Check if $key is really the array we need. we shouldn't select the
    // #parent array for example.
    if (is_array($elements) && \Drupal\Core\Render\Element::child($key)) {

      $row[] = ['data' => \Drupal::service("renderer")->render($form['fields'][$key]['solr_field'])];
      $row[] = ['data' => \Drupal::service("renderer")->render($form['fields'][$key]['label'])];
      $row[] = ['data' => \Drupal::service("renderer")->render($form['fields'][$key]['path'])];
      $rows[] = $row;
    }
  }

  // Individual table headers.
  $header = [];
  $header[] = ['data' => t('Solr field')];
  $header[] = ['data' => t('Label')];
  $header[] = ['data' => t('Path')];

  // Render table.
  $output = [
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#attributes' => [
      'id' => 'islandora-solr-facet-pages-admin-table',
    ],
  ];
  return \Drupal::service("renderer")->render($output);
}
