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

$wgHooks['ParserFirstCallInit'][] = "ExtLoopFunctions::setup";
$wgExtensionCredits['parserhook'][] = array(
	'version' => '1.0.5',
	'description' => 'Provides limited looping functionallity in the wikitext',
	'name' => 'LoopFunctions',
	'url' => 'http://www.mediawiki.org/wiki/Extension:LoopFunctions',
	'author' => 'Carl Fürstenberg (AzaToth), adapted to MediaWiki 1.12 by Xiloynaha'
);

$wgHooks['LanguageGetMagic'][]  = 'ExtLoopFunctions::languageGetMagic';

class ExtLoopFunctions {
	public static $mMaxLoopCount = 100; // Maximum number of loops allowed per session
	private static $mCurrentLoopCount = 0; // number of executed loops this session

	public static function setup(&$parser) {
		global $wgParser, $wgExtLoopFunctions, $wgLoopFunctionsMessages, $wgMessageCache;

		$wgExtLoopFunctions = new ExtLoopFunctions();
		$wgParser->setFunctionHook( 'for', array(&$wgExtLoopFunctions, 'forHook'), SFH_OBJECT_ARGS) ;
		$wgParser->setFunctionHook('foreach', array(__CLASS__, 'foreachHook'), SFH_OBJECT_ARGS) ;

		foreach( $wgLoopFunctionsMessages as $key => $value ) {
			$wgMessageCache->addMessages( $value, $key );
		}

		return true ;
	}

	public static function languageGetMagic( &$magicWords, $langCode ) {
		global $wgLoopFunctionsMagic;

		if (!in_array($langCode, $wgLoopFunctionsMagic)) {
			$langCode = 'en';
		}

		$magicWords = array_merge($magicWords, $wgLoopFunctionsMagic[$langCode]);

		return true ;
	}

	public function forHook(&$parser, $frame, $args) {
		$nbr_of_loops = isset($args[0]) ? abs(intval(trim($frame->expand($args[0])))) : 1 ;
		$text = isset($args[1]) ? $frame->expand($args[1]) : '' ; // If you want the text to be trimmed, comment out this line and uncomment the next one.
		//$text = isset($args[1]) ? trim($frame->expand($args[1])) : '' ;
		$param = isset($args[2]) ? trim($frame->expand($args[2])) : '$n$' ;
		$return = '';

		for ($i = 0 ; $i < $nbr_of_loops ; ++$i) {
			if( ++self :: $mCurrentLoopCount > self :: $mMaxLoopCount ) {
				return wfMsg( 'loopfunc_max_loops' );
			}

			$return .= strtr( $text , array( $param => $i + 1 ) );
		}

		return $parser->replaceVariables($return , $frame , true) ;
	}

	public function foreachHook(&$parser, $frame, $args) {
		$mask = isset($args[0]) ? trim($frame->expand($args[0])) : '' ;
		$text = isset($args[1]) ? trim($frame->expand($args[1])) : '' ;
		$param = isset($args[2]) ? trim($frame->expand($args[2])) : '$n$' ;
		$variables = $frame->namedArgs + $frame->numberedArgs ;
		list ($prefix , $suffix) = $param == '' ? array($mask , '') : explode($param , $mask , 2) ;
		$return = '';

		for ($i = 0 ; array_key_exists($prefix . ($i + 1) . $suffix , $variables) ; ++$i) {
			if (++self::$mCurrentLoopCount > self::$mMaxLoopCount) {
				return wfMsg('loopfunc_max_loops') ;
			}

			$return .= strtr($text , array($param => $i + 1)) ;
		}

		return $parser->replaceVariables($return , $frame , true) ;
	}

}

?>
