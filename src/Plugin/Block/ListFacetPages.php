<?php

namespace Drupal\islandora_solr_facet_pages\Plugin\Block;

use Drupal\islandora\Plugin\Block\AbstractConfiguredBlockBase;
use Drupal\Core\Link;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Provides a block to list facet pages.
 *
 * @Block(
 *   id = "islandora-solr-facet-pages",
 *   admin_label = @Translation("Islandora Solr facet pages"),
 *   category = @Translation("Islandora Solr facet pages"),
 * )
 */
class ListFacetPages extends AbstractConfiguredBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('islandora_solr_facet_pages.settings');
    $fields = $config->get('islandora_solr_facet_pages_fields_data');

    $cache_meta = (new CacheableMetadata())
      ->addCacheableDependency($config);

    $block = !empty($fields) ?
      [
        '#theme' => 'item_list',
        '#items' => array_map([$this, 'mapConfigItemToRenderArray'], $fields),
        '#list_type' => 'ul',
        '#wrapper_attributes' => [
          'class' => 'islandora-solr-facet-pages-list',
        ],
      ] :
      [];

    $cache_meta->applyTo($block);

    return $block;
  }

  /**
   * Helper; map configuration info into a render array.
   *
   * @param array $value
   *   An array from configuration containing:
   *   - label: The human-readable label for the field
   *   - path: The path suffix for the field.
   *
   * @return array
   *   A render array representing a link to the given config item's page.
   */
  protected function mapConfigItemToRenderArray(array $value) {
    return Link::createFromRoute(
      $value['label'],
      'islandora_solr_facet_pages.callback',
      ['path' => $value['path']],
      ['attributes' => ['title' => $value['label']]]
    )->toRenderable();
  }

}
