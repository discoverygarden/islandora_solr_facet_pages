<?php

namespace Drupal\islandora_solr_facet_pages\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

use Drupal\islandora_solr\SolrPhpClient\Apache\Solr\Apache_Solr_Service;

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
  public function facetPagesAccess($path = NULL, AccountInterface $account = NULL) {
    $access = islandora_solr_facet_pages_access_callback($path);
    return $access ? AccessResult::allowed() : AccessResult::forbidden();
  }

  /**
   * Page callback function.
   *
   * @param string $path
   *   Machine name passed in the URL to decide what Solr field to facet on.
   * @param string $prefix
   *   Letter of the alphabet to filter on.
   * @param string $search_term
   *   Term to search on.
   *
   * @return string
   *   Rendered page including letter pager, numerical pager and search results.
   */
  public function facetPagesCallback($path = NULL, $prefix = NULL, $search_term = NULL) {
    module_load_include('inc', 'islandora_solr', 'includes/utilities');
    $search_term = islandora_solr_restore_slashes($search_term);
    $fields = $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_fields_data');

    // Set variables.
    foreach ($fields as $value) {
      if ($path == $value['path']) {
        $solr_field = $value['solr_field'];
        $title = $value['label'];
      }
    }

    // Set default prefix.
    if ($prefix == NULL) {
      $prefix = $this->t('ALL');
    }

    // Use Solr faceting to get list of names.
    $parsed_url = parse_url($this->config('islandora_solr.settings')->get('islandora_solr_url'));

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
    $show_form = $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_search_form');
    $escape_lucene = $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_syntax_escape');

    $search_term_escape = ($show_form && $escape_lucene) ?
      islandora_solr_facet_query_escape($search_term, $this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_lucene_regex_default')) :
      islandora_solr_lesser_escape($search_term);

    // Letters.
    $letterer_arr = islandora_solr_facet_pages_build_letterer($solr, $solr_field, $search_term_escape);
    $letterer = [
      '#theme' => 'islandora_solr_facet_pages_letterer',
      '#facet_queries' => $letterer_arr['facet_queries'],
      '#fq_map' => $letterer_arr['fq_map'],
      '#search_prefix' => $prefix,
      '#path' => $path,
    ];

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
    $results = [
      '#theme' => 'islandora_solr_facet_pages_results',
      '#results' => $results,
      '#solr_field' => $solr_field,
      '#path' => $path,
    ];

    // Pager.
    $pager = [
      '#type' => 'pager',
      '#element' => 0,
      '#quantity' => 5,
      '#route_name' => 'islandora_solr_facet_pages.callback',
      '#route_parameters' => [
        'path' => $path,
        'prefix' => $prefix,
        'search_term' => $search_term,
      ],
    ];

    if ($this->config('islandora_solr_facet_pages.settings')->get('islandora_solr_facet_pages_search_form')) {
      $search_form = $this->formBuilder()->getForm('islandora_solr_facet_pages_search_form', [
        'path' => $path,
        'prefix' => $prefix,
        'search_term' => $search_term,
      ]);
    }
    else {
      $search_form = '';
    }

    $facet_pages_wrapper = [
      '#theme' => 'islandora_solr_facet_pages_wrapper',
      '#search_form' => $search_form,
      '#letterer' => $letterer,
      '#results' => $results,
      '#pager' => $pager,
      '#path' => $path,
      '#pager_data' => $pager_data,
      '#title' => $title,
      '#attached' => [
        'library' => [
          'islandora_solr_facet_pages/base',
        ],
      ],
    ];

    return $facet_pages_wrapper;
  }

}
