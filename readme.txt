=== Ten&Two XSLT Processor ===
Contributors: tenandtwo
Donate link:
Tags: xml, xsl, xslt, csv, shortcode
Requires at least: 5.2
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


Transform and display XML from local and remote sources using PHP's XSL extension.


== Description ==

The Ten&Two XSLT Processor plugin brings the power of PHP's XSL extension to Wordpress.  Once enabled, the plugin creates three (3) shortcodes - `[xslt_transform_xml/]`, `[xslt_select_xml/]`, and `[xslt_select_csv/]` - which can be used separately or in tandem to enrich your site with content from XML and CSV sources.  The plugin also enables two (2) custom post types - `XSL Stylesheets` and `XML Documents` - for managing and validating sources within WP Admin.

Detailed documentation and sample code can be found at https://plugins.tenandtwo.com/


### Custom Post Types

The XSLT Processor plugin provides two (2) custom post types for managing sources within Wordpress - `XSL Stylesheets` and `XML Documents`.  Both types include basic syntax validation.  XML Documents can be validated further using DTD, XSD, or RNG.  Both types are enabled in Settings > XSLT Processor Settings > Activate Content Types.


### Shortcode : [xslt_transform_xml/]

`[xslt_transform_xml/]` is the plugin's primary function.  This shortcode processes XML data using an XSL stylesheet, and then outputs the result as HTML, more XML, or as simple TEXT.

 - `[xslt_transform_xml xsl="{file|url|id|slug}" xml="{file|url|id|slug}" /]`
 - `[xslt_transform_xml xsl="{file|url|id|slug}"]<DATA>...</DATA>[/xslt_transform_xml]`

If either the `xsl` or `xml` parameter is left unspecified, defaults are used.  The default XML value is `<NODATA/>`.  The default XSL stylesheet prints all of the incoming data as HTML.  If extra attributes are specified in the shortcode - eg, `mykey="myval"` - those keys/values are passed along as parameters to the stylesheet - `<xsl:param name="mykey"/>`.


### Shortcode : [xslt_select_xml/]

`[xslt_select_xml/]` is a helper function.  It reads XML and returns a selection of the data, based on a supplied XPath expression.  There are two (2) options for specifying the XPath.  First, using the `select` attribute or, second, using the body of the shortcode.  Complex select statements with quotes, square brackets or other special syntax, should use the second pattern :

 - `[xslt_select_xml xml="{file|url|id|slug}" select="{XPath}" /]`
 - `[xslt_select_xml xml="{file|url|id|slug}"]{XPath}[/xslt_select_xml]`

If the XPath select parameter is left unspecified, the default `/` is used, which returns the entire document.  The default output is `format="xml"`.  If `format="json"` is specified, the result is encoded as a JSON string.


### Shortcode : [xslt_select_csv/]

`[xslt_select_csv/]` is a helper function for converting CSV file data to XML.  The result can be output directly as an HTML `<table>`, or the result can be passed to `[xslt_transform_xml/]` for further processing.

 - `[xslt_select_csv csv="{file|url}" /]`
 - `[xslt_select_csv]{csv,data}[/xslt_select_csv]`

Three (3) parameters - `separator`, `enclosure`, `escape` - control reading the input.  See PHP's `fgetcsv()` function for details.

 - `[xslt_select_csv separator="," enclosure="\"" escape="\\" /]`

Two (2) parameters - `key_row`, `col` - control writing columns to the output.  The `key_row` attribute is optional, but allows labels from that row to be used in `col` and `key_col`.

 - `[xslt_select_csv key_row="{num}" col="{num|letter|label}+" /]`

Three (3) parameters - `row`, `key_col`, `key` - control writing rows to the output.

 - `[xslt_select_csv row="{num}+" /]`
 - `[xslt_select_csv key_col="{num|letter|label}" key="{val}+" /]`


### Nested Shortcodes

Combine `[xslt_transform_xml]` with `[xslt_select_xml]` :

 - `[xslt_transform_xml][xslt_select_xml/][/xslt_transform_xml]`

Combine `[xslt_transform_xml]` with `[xslt_select_csv]` :

 - `[xslt_transform_xml][xslt_select_csv/][/xslt_transform_xml]`

Combine `[xslt_transform_xml]` with itself using `[/xslt_transform_alias]` (WP does not support nested shortcodes with identical names) :

 - `[xslt_transform_alias][xslt_transform_xml/][/xslt_transform_alias]`

Combine multiple shortcodes/sources to create a single `XML Document` (see Custom Post Types above) :

 - `<DATA><PART1>[xslt_select_xml xml="f1.xml" /]</PART1><PART2>[xslt_select_xml xml="f2.xml" /]</PART2></DATA>`


### Cache Parameters

When a shortcode specifies a remote file - `xml="{url}"` or `csv="{url}"` - that source is cached locally using WP Transients. The default cache duration is set in the XSLT Processor Settings.  To override the default, add `cache="{minutes}"` to the shortcode.

 - `[xslt_transform_xml xml="{url}" cache="{minutes}" /]`
 - `[xslt_select_xml xml="{url}" cache="{minutes}" /]`
 - `[xslt_select_csv csv="{url}" cache="{minutes}" /]`


### Namespace Parameters

Within `[xslt_select_xml/]` the plugin provides two (2) methods for handling XML containing namespaces.  The first is to add `strip-namespaces` to the shortcode.  The second method is to add the needed prefixes and namespace URIs using `xslns`.

 - `[xslt_select_xml xml="{file}" strip-namespaces="yes" select="//node" /]`
 - `[xslt_select_xml xml="{file}" xmlns="ns1" ns1="{namespace-uri-1}" select="//ns1:node" /]`
 - `[xslt_select_xml xml="{file}" xmlns="ns1 ns2" ns1="{namespace-uri-1}" ns2="{namespace-uri-2}" select="//ns1:node/ns2:node" /]`


### WP-CLI

All three (3) shortcodes have command-line equivalents. They can be used, for instance, to run quick tests. Or they can be used, by piping the outputs into files, to pre-generate results.

 *      wp xslt transform_xml
            --xsl='{file|url|id|slug}'
            --xml='{file|url|id|slug}'
            --cache='{minutes, if xsl|xml={url}}'
            --tidy='{yes|html}' or tidy or --tidy='xml'
            --{myparam}='{myvalue}'
            --outfile='{filepath}'
            --htmlentities or --htmlentities='yes'

 *      wp xslt select_xml
            --xml='{file|url|id|slug}'
            --cache='{minutes, if xml={url}}'
            --select='{xpath}'
            --root='{nodename|empty}'
            --tidy='{yes|html}' or tidy or --tidy='xml'
            --strip-namespaces='yes' or strip-namespaces
            --strip-declaration='no'
            --format='{xml|json}'
            --htmlentities or --htmlentities='yes'

 *      wp xslt select_csv
            --csv='{file|url}'
            --cache='{minutes, if csv={url}}'
            --separator=','
            --enclosure='\"'
            --escape='\\'
            --key_row='{row number for column labels}'
            --col='{return column number(s), letter(s), or label(s)}'
            --key_col='{col number, letter, or label for key matching}'
            --key='{value(s) for key_col matching}'
            --row='{return row number(s)}'
            --class='{css classname(s) for result table}'
            --htmlentities or --htmlentities='yes'


### XSL Stylesheets

The XSLT Processor plugin includes a number of useful XSL templates that you can include and use in your own projects. They are grouped into five files.

 - date.xsl : `date-format`, `date-microtime`
 - file.xsl : `file-exists-local`, `file-exists-remote`
 - string.xsl : `string-replace`, `string-upper`, `string-lower`, `string-title-case`, `string-trim`, `string-rtrim`, `string-ltrim`, `string-maxlength`, `string-maxwords`, `string-add-slashes`, `string-urlencode`, `string-strip-tags`, `string-nl2br`, `string-entity-decode`, `string-to-nodeset`
 - util.xsl : `util-bytsize`, `util-hash-data`, `util-print-nodes`, `util-print-node-names`, `util-super-global`
 - wp.xsl : `wp-select-xml`, `wp-select-csv`, `wp-post-item`, `wp-post-meta`, `wp-sanitize-title`, `wp-size-format`


== Installation ==

### WordPress installation

1. Go to Plugins > Add New > Search for "tenandtwo-xslt-processor"
2. Press "Install Now" for the "Ten&Two XSLT Processor" plugin
3. Press "Activate Plugin"

### WP-CLI installation

1. `wp plugin install tenandtwo-xslt-processor --activate`

### Manual installation

1. Download the latest archive from the Plugin Homepage : https://wordpress.org/plugins/tenandtwo-xslt-processor
2. Upload the `tenandtwo-xslt-processor` directory to your `/wp-content/plugins/` directory
3. Activate the plugin through the "Plugins" menu in WordPress

For more details on installation options, see Manage Plugins at wordpress.org - https://wordpress.org/documentation/article/manage-plugins/

## Requirements

The Ten&Two XSLT Processor plugin relies upon PHP's XSL extension.  If the extension is installed, the XSLT Processor Settings screen will display a message similar to the first message below.  If `LIBXSLT_VERSION` is undefined, all plugin options are disabled automatically and the second message is displayed.

 - `PHP's XSL extension is available : XSLT v1.1.32, EXSLT v1.1.32, LIBXML v2.9.4`
 - `PHP's XSL extension is NOT available`

The XSL extension's requirements are detailed at php.net - https://www.php.net/manual/en/book.xsl.php

> "This extension requires the libxml PHP extension. This means passing the --with-libxml,
> or prior to PHP 7.4 the --enable-libxml, configuration flag, although this is implicitly
> accomplished because libxml is enabled by default.
>
> This extension uses libxslt which can be found at » http://xmlsoft.org/XSLT/. libxslt
> version 1.1.0 or greater is required."


== Frequently Asked Questions ==


### Where are the plugin options?

In WordPress, go to Settings > XSLT Processor Settings.  There are four (4) sections :

 - Activate Content Types
 - Activate Shortcodes
 - Cache Lifetime
 - Local File Search Paths


### Where is the documentation?

For a quick reference to the shortcodes and their main parameters, go to Settings > XSLT Processor Settings.  The samples for each shortcode show common usage.

Full documentation and working examples are available at https://plugins.tenandtwo.com/.  There are four (4) main sections :

 - Getting Started : https://plugins.tenandtwo.com/xslt-processor/getting-started
 - Shortcodes      : https://plugins.tenandtwo.com/xslt-processor/shortcodes
 - Stylesheets     : https://plugins.tenandtwo.com/xslt-processor/stylsheets
 - How To          : https://plugins.tenandtwo.com/xslt-processor/how-to


== Screenshots ==


1. XSLT Processor Settings


== Changelog ==

= 1.0.x =
* Initial Release
* Add WP-CLI commands
* Test with WP 6.5

