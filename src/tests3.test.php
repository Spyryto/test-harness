<?php declare(strict_types = 1);

describe("Unskippable test");

$testHasRun = false;

it( "should be run", function () use (&$testHasRun) {
	$testHasRun = true;
});

if ( ! $testHasRun ) throw new Exception( "Previous test should have been run");

// Test count of total skipped tests

skipAll( "" ); // do not show in test harness' response

it( "should skip one test", function () {
	throw new Exception( 'This test should have been skipped' );
});


it( "should also skip this test", function () {
	throw new Exception( 'This test should have been skipped too' );
});



