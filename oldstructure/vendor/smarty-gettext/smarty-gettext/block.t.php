<?php
/**
 * block.t.php - Smarty gettext block plugin
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package   smarty-gettext
 * @link      https://github.com/glensc/smarty-gettext
 * @author    Sagi Bashari <sagi@boom.org.il>
 * @author    Elan Ruusamäe <glen@delfi.ee>
 * @copyright 2004-2005 Sagi Bashari
 * @copyright 2010-2013 Elan Ruusamäe
 */

/**
 * Replaces arguments in a string with their values.
 * Arguments are represented by % followed by their number.
 *
 * @param string $str Source string
 * @param mixed mixed Arguments, can be passed in an array or through single variables.
 * @return string Modified string
 */
function smarty_gettext_strarg($str/*, $varargs... */) {
	$tr = array();
	$p = 0;

	$nargs = func_num_args();
	for ($i = 1; $i < $nargs; $i++) {
		$arg = func_get_arg($i);

		if (is_array($arg)) {
			foreach ($arg as $aarg) {
				$tr['%' . ++$p] = $aarg;
			}
		} else {
			$tr['%' . ++$p] = $arg;
		}
	}

	return strtr($str, $tr);
}

/**
 * Smarty block function, provides gettext support for smarty.
 *
 * The block content is the text that should be translated.
 *
 * Any parameter that is sent to the function will be represented as %n in the translation text,
 * where n is 1 for the first parameter. The following parameters are reserved:
 *   - escape - sets escape mode:
 *       - 'html' for HTML escaping, this is the default.
 *       - 'js' for javascript escaping.
 *       - 'url' for url escaping.
 *       - 'no'/'off'/0 - turns off escaping
 *   - plural - The plural version of the text (2nd parameter of ngettext())
 *   - count - The item count for plural mode (3rd parameter of ngettext())
 *   - domain - Textdomain to be used, default if skipped (dgettext() instead of gettext())
 *
 * @param array $params
 * @param string $text
 * @link http://www.smarty.net/docs/en/plugins.block.functions.tpl
 * @return string
 */
function smarty_block_t($params, $text) {
	if (!isset($text)) {
		return $text;
	}

	// set escape mode, default html escape
	if (isset($params['escape'])) {
		$escape = $params['escape'];
		unset($params['escape']);
	} else {
		$escape = 'html';
	}

	// set plural version
	if (isset($params['plural'])) {
		$plural = $params['plural'];
		unset($params['plural']);

		// set count
		if (isset($params['count'])) {
			$count = $params['count'];
			unset($params['count']);
		}
	}

	// set domain
	if (isset($params['domain'])) {
		$domain = $params['domain'];
		unset($params['domain']);
	} else {
		$domain = null;
	}

	// use plural if required parameters are set
	if (isset($count) && isset($plural)) {
		// use specified textdomain if available
		if (isset($domain)) {
			$text = dngettext($domain, $text, $plural, $count);
		} else {
			$text = ngettext($text, $plural, $count);
		}
	} else {
		// use specified textdomain if available
		if (isset($domain)) {
			$text = dgettext($domain, $text);
		} else {
			$text = gettext($text);
		}
	}

	// run strarg if there are parameters
	if (count($params)) {
		$text = smarty_gettext_strarg($text, $params);
	}

	switch ($escape) {
	case 'html':
		$text = nl2br(htmlspecialchars($text));
		break;
	case 'javascript':
	case 'js':
		// javascript escape
		$text = strtr($text, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
		break;
	case 'url':
		// url escape
		$text = urlencode($text);
		break;
	}

	return $text;
}
