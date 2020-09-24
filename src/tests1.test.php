<?php declare(strict_types = 1);

describe("Test harness");

it("can use vanilla assertion and succeed", function () {
	assert(1 + 1 === 2);
});

it("can use vanilla exception and succeed", function () {
	try {
		throw new Exception('TestException');
	} catch (Exception $e) {
		// Test passed
	}
});

function throwing() {
	throw new AssertionError('TestException');
}

it("can use vanilla try/catch with throwing closure", function () {
	try {
		throwing();
		throw new Exception('This should not fail');
	} catch (AssertionError $e) {
		// Test passed
	}
});

describe("expectEquals");

it("works with simple values", function () {
	expectEquals(1 + 1, 2);
});

it("throws when values don't match", function () {
	try {
		expectEquals(1 + 1, 3);
	} catch(AssertionError $e) {
		// Test passed
	}
});

describe("expect");

it("has matcher 'equals'", function () {
	expect(1 + 1, equals(2));
});

it("'equals' throws when values don't match", function () {
	try {
		expect(1 + 1, equals(3));
	} catch(AssertionError $e) {
		// Test passed
	}
});

it("has matcher 'is'", function () {
	expect(1 + 1, equals(2));
});

it("'equals' throws when values don't match", function () {
	try {
		expect(1 + 1, is(3));
	} catch(AssertionError $e) {
		// Test passed
	}
});

it("has matcher 'throws'", function () {
	$throwing = function () {
		throw new AssertionError('TestException');
	};
	expect($throwing, throws());
});

it("has matcher is(true)", function () {
	expect( 1 + 1 === 2, is(true));
});

it("has matcher is(false)", function () {
	expect( 1 + 1 === 3, is(false));
});

it("has matcher throwsA( type of exception )", function () {
	$throwing = function () {
		throw new AssertionError('TestException');
	};
	expect( $throwing, throwsA( 'AssertionError' ));
});

describe("skip");

skip( '' ); // do not show in test harness' response

it( "should skip only one test", function () {
	throw new Exception( 'This test should have been skipped' );
});

$testHasRun = false;

it( "should run subsequent tests", function () use (&$testHasRun) {
	$testHasRun = true;
});

if ( ! $testHasRun ) throw new Exception( "Previous test should have been run");

skip( 'should not skip first test of next module' );

