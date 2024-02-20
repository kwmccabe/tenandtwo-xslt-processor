=== Ten&Two XSLT Processor ===
Contributors: tenandtwo
Donate link: https://xsltproc.tenandtwo.com/donate/
Tags: xslt, xml, xsl, shortcode
Requires at least: 5.2
Tested up to: 6.4.3
Stable tag: 0.9.8
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


Transform and display XML from local and remote sources using PHP's XSL extension.


== Description ==

The Ten&Two XSLT Processor plugin brings the power of PHP's XSL module to Wordpress.  Once enabled, the plugin creates three (3) shortcodes - [xslt_transform/], [xslt_select_xml/], and [xslt_select_csv/] - which can be used separately or in tandem to enrich your site with content from XML sources.  The plugin also enables two custom post types - 'XSL Stylesheets' and 'XML Documents' - for managing and validating sources within WP Admin.

More information and sample code can be found at https://xsltproc.tenandtwo.com/


= Custom Post Types =

The XSLT Processor plugin enables two custom post types for managing sources within Wordpress - `XSL Stylesheets` and `XML Documents`.  Both types include basic syntax validation.  XML Documents can be validated further using DTD, XSD, or RNG.


= Shortcodes =

[xslt_transform/] is the plugin's primary function.  This shortcode processes XML data using an XSL stylesheet, and then outputs the result as HTML, more XML, or as simple TEXT.

-- [xslt_transform xsl="{file|url|id|slug}" xml="{file|url|id|slug}" /]
-- [xslt_transform xsl="{file|url|id|slug}"]<DATA>...</DATA>[/xslt_transform]

If either the `xsl` or `xml` parameter is left unspecified, defaults are used.  The default XML value is `<NODATA/>`.  The default XSL stylesheet prints all of the incoming data as HTML.  If extra attributes are specified in the shortcode - eg, `mykey="myval"` - those keys/values are passed along as parameters to the stylesheet - `<xsl:param name="mykey"/>`.

-- -- -- -- --

[xslt_select_xml/] is a helper function.  It reads XML and returns a selection of the data, based on a supplied XPATH expression.  There are two options for specifying the XPath.  First, using the `select` attribute or, second, using the body of the shortcode.  Complex select statements with quotes, square brackets or other special syntax, should use the second pattern :

-- [xslt_select_xml xml="{file|url|id|slug}" select="{XPath}" /]
-- [xslt_select_xml xml="{file|url|id|slug}"]{XPath}[/xslt_select_xml]

If the XPath select parameter is left unspecified, the default `/` is used, which returns the entire document.  The default output is `format="xml"`.  If `format="json"` is specified, the result is encoded as a JSON string.

-- -- -- -- --

[xslt_select_csv/] is a helper function for converting CSV data to XML.  The result can be output directly as an HTML <table>, or the result can be passed to [xslt_transform/] for further processing.

-- [xslt_select_csv csv="{file|url}" /]
-- [xslt_select_csv]{csv,data}[/xslt_select_csv]

Three (3) parameters control reading the input.  See https://www.php.net/manual/en/function.fgetcsv.php for details.
-- [xslt_select_csv separator="," enclosure="\"" escape="\\" /]

Two (2) parameters control writing columns to the output.  The `key_row` attribute is optional, but allows labels from that row to be used in `col` and `key_col`.
-- [xslt_select_csv key_row="{num}" col="{num|letter|label}+" /]

Three (3) parameters control writing rows to the output.
-- [xslt_select_csv row="{num}+" /]
-- [xslt_select_csv key_col="{num|letter|label}" key="{val}+" /]

-- -- -- -- --

If `htmlentities` or `htmlentities="yes"` is added to any of the three shortcodes, the result is escaped for easier in-browser debugging.

Combine [xslt_transform] with [xslt_select_xml] :
-- [xslt_transform][xslt_select_xml/][/xslt_transform]

Combine [xslt_transform] with [xslt_select_csv] :
-- [xslt_transform][xslt_select_csv/][/xslt_transform]

Combine [xslt_transform] with itself using [/xslt_transform_alias] (WP does not support nested shortcodes with identical names) :
-- [xslt_transform_alias][xslt_transform/][/xslt_transform_alias]

Combine multiple shortcodes in a single 'XML Document' (see Custom Post Types below) :
-- <DATA><PART1>[xslt_select_xml/]</PART1><PART2>[xslt_select_xml/]</PART2></DATA>


= Cache Parameters =

When either shortcode specifies a remote file - `xml="{url}"` or `csv="{url}"` - that source is cached locally using WP Transients. The default cache duration is set in the XSLT Processor Settings.  To override the default, add `cache="{minutes}"` to the shortcode.

-- [xslt_transform xml="{url}" cache="{minutes}" /]
-- [xslt_select_xml xml="{url}" cache="{minutes}" /]
-- [xslt_select_csv csv="{url}" cache="{minutes}" /]


= Namespace Parameters =

Within [xslt_select_xml/] the plugin provides two methods for handling XML containing namespaces.  The first is to add `strip-namespaces` to the shortcode.  The second method is to add the needed prefixes and namespace URIs using `xslns`.

-- [xslt_select_xml xml="{file}" strip-namespaces="yes" select="//node" /]
-- [xslt_select_xml xml="{file}" xmlns="ns1" ns1="{namespace-uri-1}" select="//ns1:node" /]
-- [xslt_select_xml xml="{file}" xmlns="ns1 ns2" ns1="{namespace-uri-1}" ns2="{namespace-uri-2}" select="//ns1:node/ns2:node" /]


= XSL Stylesheets =

The XSLT Processor plugin includes a number of useful XSL templates that you can include and use in your own projects. They are grouped into five files.

* date.xsl
-- date-format, date-microtime

* file.xsl
-- file-exists-local, file-exists-remote

* string.xsl
-- string-replace, string-upper, string-lower, string-title-case, string-trim, string-rtrim, string-ltrim, string-maxlength, string-maxwords, string-add-slashes, string-urlencode, string-strip-tags, string-nl2br, string-entity-decode, string-to-nodeset

* util.xsl
-- util-bytsize, util-hash-data, util-print-nodes, util-print-node-names, util-super-global

* wp.xsl
-- wp-select-xml, wp-select-csv, wp-post-item, wp-post-meta, wp-sanitize-title, wp-size-format


== Installation ==

In WordPress :

1. Go to Plugins > Add New > search for `tenandtwo-xslt-processor`
2. Press "Install Now" for the "Ten&Two XSLT Processor" plugin
3. Press "Activate Plugin"

WP-CLI installation :

1. `wp plugin install tenandtwo-xslt-processor --activate`

Manual installation

1. Download the latest plugin archive : https://xsltproc.tenandtwo.com/wp-content/uploads/tenandtwo-xslt-processor-latest.tgz
2. Upload the `tenandtwo-xslt-processor` directory to your `/wp-content/plugins/` directory
3. Activate the plugin through the "Plugins" menu in WordPress

For more details on installation options, see Manage Plugins at wordpreess.org - https://wordpress.org/documentation/article/manage-plugins/


= Requirements =

The Ten&Two XSLT Processor plugin relies upon PHP's XSL extension.  If the extension is installed, then the XSLT Processor Settings screen will display a message similar to the first message below.  If LIBXSLT_VERSION is undefined, on the other hand, all plugin options are disabled automatically, and the second message is displayed.

-- PHP's XSL extension is available : XSLT v1.1.32, EXSLT v1.1.32, LIBXML v2.9.4
-- PHP's XSL extension is NOT available

The extension's requirements are detailed at php.net - https://www.php.net/manual/en/book.xsl.php
"
This extension requires the libxml PHP extension. This means passing the --with-libxml, or prior to PHP 7.4 the --enable-libxml, configuration flag, although this is implicitly accomplished because libxml is enabled by default.

This extension uses libxslt which can be found at » http://xmlsoft.org/XSLT/. libxslt version 1.1.0 or greater is required.
"


== Frequently Asked Questions ==

= Documentation and Samples

-- XSLT Processor  : https://xsltproc.tenandtwo.com/xslt-processor/
-- Getting Started : https://xsltproc.tenandtwo.com/xslt-processor/getting-started
-- Shortcodes      : https://xsltproc.tenandtwo.com/xslt-processor/shortcodes
-- Stylesheets     : https://xsltproc.tenandtwo.com/xslt-processor/stylsheets
-- How To          : https://xsltproc.tenandtwo.com/xslt-processor/how-to


= Where are the plugin options?

In WordPress, go to Settings > XSLT Processor.


= Question

Answer


== Screenshots ==


== Changelog ==

= 1.0 =
* Release


== Upgrade Notice ==

Just do it.  (300 chars max)
