# Islandora Solr Facet Pages

## Introduction

Islandora Solr Facet Pages provides alphabetical "A to Z" browse lists from
metadata indexed in Solr. You can set up multiple A-to-Z browse lists from
different facets (Solr fields). For example, this can be used to list all
authors or all subject headings present in an Islandora repository.

Each facet page appears at _/browse/my-configured-path_.

## Requirements

This module requires the following modules/libraries:

* [Islandora](https://github.com/discoverygarden/islandora)
* [Tuque](https://github.com/islandora/tuque)
* [Islandora Solr](https://github.com/discoverygarden/islandora_solr)

## Installation

Install as
[usual](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

## Configuration

Configure facet pages at Administration » Configuration » Islandora » Solr
index » Facet Pages
(_/admin/config/islandora/search/islandora_solr/facet_pages_).

![Configuration](https://user-images.githubusercontent.com/1943338/32705823-6998d8f8-c7ee-11e7-8238-c801f56cffb7.png)

## Documentation

This module's documentation is also available at [our
wiki](https://wiki.duraspace.org/display/ISLANDORA/Islandora+Solr+Facet+Pages).

### Facet Pages

Each facet page requires a solr field (the facet) and a path (so the page will
be at _/browse/{path}_). The label, if present, is set as the Drupal page
title.

When choosing Solr fields for facets, you probably want to select _string_
fields. This depends on your Solr config, but often this includes fields ending
in \*_s or \*_ms. Strings  will display as full multi-word phrases. Text fields
(often \*_mt or the dc.* fields) will show raw parsed text (individual words or
parts of words) and is usually not what is desired.

If you need to configure more pages than the form provides, save the full
configuration form and more blank fields will automatically appear.

### Results per page

When the list of facets is long, it can be split over multiple pages using a
pager. This variable sets the page size.

### Maximum facet values

Set the maximum number of facet values to return, period. If this value is less
than the number of values that exist in Solr, they will be pruned arbitrarily.
However, lowering this value may improve page loading speeds.

### Facet search form

Provide the user with a search form to search within these facets.

Search is case-sensitive and must match the entire facet value. Therefore,
search does not work well on string fields, unless the user makes use of
wildcards. For example, `*Alice*` would  match that term anywhere within the
string, while `Alice` would only match a full string value of "Alice".

This search form works more intuitively on text (e.g. *_mt) facets, but as
mentioned above, raw tokenized values are generally _not_ what is desired for
display.


## Notes

### Islandora Solr Facet Pages block

This module provides a block named "Islandora Solr facet pages" that can be
configured in Block settings (Administration » Structure » Blocks). It contains
a list of links to all configured facet pages.

## Troubleshooting/Issues

Having problems or solved one? Create an issue, check out the Islandora Google
groups.

* [Users](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora)
* [Devs](https://groups.google.com/forum/?hl=en&fromgroups#!forum/islandora-dev)

or contact [discoverygarden](http://support.discoverygarden.ca).

## Maintainers/Sponsors

Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## Development

If you would like to contribute to this module, please check out the helpful
[Documentation](https://github.com/Islandora/islandora/wiki#wiki-documentation-for-developers),
[Developers](http://islandora.ca/developers) section on Islandora.ca and create
an issue, pull request and or contact
[discoverygarden](http://support.discoverygarden.ca).

## License

[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
