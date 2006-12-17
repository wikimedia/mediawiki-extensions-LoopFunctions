<?php
/**
 * Some functions to enable limited looping functionallity,
 * will also replace the text '$n$' or given parameter in the given text to the current loop count plus one.
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @link http://www.mediawiki.org/wiki/Extension:LoopFunctions Documentation
 *
 * @author Carl Fürstenberg (AzaToth) <azatoth@gmail.com>
 * @copyright Copyright © 2006 Carl Fürstenberg
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

require_once('LoopFunctions.i18n.php');

$wgExtensionFunctions[] = 'wfSetupLoopFunctions';
$wgExtensionCredits['parserhook'][] = array(
	'version' => '1.0.3',
	'description' => 'Provides limited looping functionallity in the wikitext',
	'name' => 'LoopFunctions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:LoopFunctions',
	'author' => 'Carl Fürstenberg (AzaToth)'
);

$wgHooks['LanguageGetMagic'][]  = 'wfLoopFunctionsLanguageGetMagic';

class ExtLoopFunctions {
	public static $mMaxLoopCount = 100; // Maximum number of loops allowed per session
	private static $mCurrentLoopCount = 0; // number of executed loops this session

	public function forHook(&$parser, $nbr_of_loops = 1 , $text = '' , $param = '$n$' ) {
		$return = '';
		$text = trim($text);
		$param = trim($param);

		for($i = 0 ; $i < abs ( intval( $nbr_of_loops ) ) ; ++$i ) {

			if( ++self :: $mCurrentLoopCount > self :: $mMaxLoopCount ) {
				return wfMsg( 'loopfunc_max_loops' );
			}

			$return .= strtr( $text , array( $param => $i + 1 ) );

		}

		return $parser->replaceVariables( $return , current($parser->mArgStack) , true);
	}

	public function foreachHook(&$parser, $mask = '' , $text = '' , $param = '$n$' ) {
		$variables = current( $parser->mArgStack );
		$param = trim( $param );
		$text= trim( $text );
		list ( $prefix , $suffix ) = $param == '' ? array( $mask , '' ) : explode ( $param , $mask , 2 );

		for($i = 0; array_key_exists( $prefix . ( $i + 1 ) . $suffix , $variables ) ; ++$i ) {

			if( ++self :: $mCurrentLoopCount > self :: $mMaxLoopCount ) {
				return wfMsg( 'loopfunc_max_loops' );
			}

			$return .= strtr( $text , array( $param => $i + 1 ) );

		}

		return $parser->replaceVariables( $return , $variables , true);
	}

}

function wfSetupLoopFunctions() {
	global $wgParser, $wgExtLoopFunctions, $wgLoopFunctionsMessages, $wgMessageCache;

	$wgExtLoopFunctions = new ExtLoopFunctions();
	$wgParser->setFunctionHook( 'for', array( &$wgExtLoopFunctions, 'forHook' ) );
	$wgParser->setFunctionHook( 'foreach', array( &$wgExtLoopFunctions, 'foreachHook' ) );


	foreach( $wgLoopFunctionsMessages as $key => $value ) {
		$wgMessageCache->addMessages( $value, $key );
	}
}

function wfLoopFunctionsLanguageGetMagic( &$magicWords, $langCode ) {
	global $wgLoopFunctionsMagic;
	if(!in_array($langCode,$wgLoopFunctionsMagic)) $langCode = 'en';
	$magicWords = array_merge($magicWords, $wgLoopFunctionsMagic[$langCode]);
	return true;
}
