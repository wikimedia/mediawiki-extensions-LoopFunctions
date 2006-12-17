<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgLoopFunctionsMessages = array();
$wgLoopFunctionsMagic = array();

$wgLoopFunctionsMessages['en'] = array(
	'loopfunc_max_loops' => "Maximum number of allowed loops reached",
);

$wgLoopFunctionsMagic['en'] = array(
	'for' => array( 0, 'for' ),
	'foreach' => array( 0, 'foreach' ),
);
