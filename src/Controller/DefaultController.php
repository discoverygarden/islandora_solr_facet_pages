<?php

namespace Drupal\islandora_solr_facet_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Default controller for the islandora_solr_facet_pages module.
 */
class DefaultController extends ControllerBase {

  /**
   * Facet pages access callback.
   *
   * @param string $path
   *   Machine readable name passed in the url to decide what solr field to
   *   facet on.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   User to check access for.
   */
  public function islandora_solr_facet_pages_access_callback($path = NULL, AccountInterface $account) {
    $access = islandora_solr_facet_pages_access_callback($path);
    // @TODO: implement.
  }

  /**
   * Page callback function.
   *
   * @param string $path
   *   Machine readable name passed in the url to decide what solr field to facet
   *   on.
   * @param string $prefix
   *   Letter of the alphabet to filter on.
   *
   * @return string
   *   Rendered page including letter pager, numerical pager and search results.
   */
  public function islandora_solr_facet_pages_callback($path = NULL, $prefix = NULL, $search_term = NULL) {
    module_load_include('inc', 'islandora_solr', 'includes/utilities');
    $search_term = islandora_solr_restore_slashes($search_term);

    // Get available fields from variable.
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/islandora_solr_facet_pages.settings.yml and config/schema/islandora_solr_facet_pages.schema.yml.
    $fields = \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_fields_data');

    // Callback validation.
    foreach ($fields as $key => $value) {
      if ($path == $value['path']) {
        // Set variables.
        $solr_field = $value['solr_field'];
        // @FIXME
        // drupal_set_title() has been removed. There are now a few ways to set the title
        // dynamically, depending on the situation.
        //
        //
        // @see https://www.drupal.org/node/2067859
        // drupal_set_title($value['label']);

      }
    }

    // Set default prefix.
    if ($prefix == NULL) {
      $prefix = t('ALL');
    }

    // Include base CSS file.
    $base_css = drupal_get_path('module', 'islandora_solr_facet_pages') . '/css/islandora_solr_facet_pages.base.css';
    // @FIXME
    // The Assets API has totally changed. CSS, JavaScript, and libraries are now
    // attached directly to render arrays using the #attached property.
    //
    //
    // @see https://www.drupal.org/node/2169605
    // @see https://www.drupal.org/node/2408597
    // drupal_add_css($base_css);


    // Use Solr faceting to get list of names.
    // @FIXME
    // // @FIXME
    // // This looks like another module's variable. You'll need to rewrite this call
    // // to ensure that it uses the correct configuration object.
    // $parsed_url = parse_url(variable_get('islandora_solr_url', 'http://localhost:8080/solr'));

    $solr = new Apache_Solr_Service($parsed_url['host'], $parsed_url['port'], $parsed_url['path']);

    // Create an escaped variable for the facet search term, for use with
    // the following two functions below:
    //
    // islandora_solr_facet_pages_build_letterer()
    // islandora_solr_facet_pages_build_results()
    //
    // We do this to preserve the original value of the search term, so that
    // subsequent calls to drupal_get_form() are prepopulated with the user
    // input text, and not the escaped string.
    $show_form = \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_search_form');
    $escape_lucene = \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_syntax_escape');

    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/islandora_solr_facet_pages.settings.yml and config/schema/islandora_solr_facet_pages.schema.yml.
    $search_term_escape = ($show_form && $escape_lucene) ?
      islandora_solr_facet_query_escape($search_term, \Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_regex_default')) :
      islandora_solr_lesser_escape($search_term);

    // Render letters.
    $letterer_arr = islandora_solr_facet_pages_build_letterer($solr, $solr_field, $search_term_escape);
    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    //
    //
    // @see https://www.drupal.org/node/2195739
    // $letterer = theme('islandora_solr_facet_pages_letterer', array(
    //     'facet_queries' => $letterer_arr['facet_queries'],
    //     'fq_map' => $letterer_arr['fq_map'],
    //     'prefix' => $prefix,
    //     'path' => $path,
    //   ));


    // Collect results.
    $result_fields = islandora_solr_facet_pages_build_results($solr, $solr_field, $prefix, $search_term_escape);
    // Collect results with lowercase.
    $prefix_lower = strtolower($prefix);
    $result_fields_lower = islandora_solr_facet_pages_build_results($solr, $solr_field, $prefix_lower, $search_term_escape);

    // Merge uppercase with lowercase.
    $result_fields = $result_fields + $result_fields_lower;

    // Set up pager.
    $pager_data = islandora_solr_facet_pages_pager($result_fields);
    $offset = $pager_data['offset'];
    $limit = $pager_data['limit'];

    // Slice array.
    $results = array_slice($result_fields, $offset, $limit, TRUE);
    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    //
    //
    // @see https://www.drupal.org/node/2195739
    // $results = theme('islandora_solr_facet_pages_results', array(
    //     'results' => $results,
    //     'solr_field' => $solr_field,
    //     'path' => $path,
    //   ));


    // Render pager.
    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    //
    //
    // @see https://www.drupal.org/node/2195739
    // $pager = theme('pager', array(
    //     'element' => 0,
    //     'quantity' => 5,
    //   ));


    if (\Drupal::config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_search_form')) {
      $form = \Drupal::formBuilder()->getForm('islandora_solr_facet_pages_search_form', [
        'path' => $path,
        'prefix' => $prefix,
        'search_term' => $search_term,
      ]);
      $search_form = \Drupal::service("renderer")->render($form);
    }
    else {
      $search_form = '';
    }

    // @FIXME
    // theme() has been renamed to _theme() and should NEVER be called directly.
    // Calling _theme() directly can alter the expected output and potentially
    // introduce security issues (see https://www.drupal.org/node/2195739). You
    // should use renderable arrays instead.
    //
    //
    // @see https://www.drupal.org/node/2195739
    // return theme('islandora_solr_facet_pages_wrapper', array(
    //     'search_form' => $search_form,
    //     'letterer' => $letterer,
    //     'results' => $results,
    //     'pager' => $pager,
    //     'path' => $path,
    //     'pager_data' => $pager_data,
    //   ));
  }

}
