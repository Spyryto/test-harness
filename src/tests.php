<?php

// Delete all files and sub-folders from a folder.
function runAll($dir) {
	global $currentGroup, $skippedTests, $passedTests;
	global $skipNext;
	global $skipAll, $skippedInModule, $skippedAllReason;
	// echo '$dir: ', var_dump($dir), PHP_EOL;
    foreach(glob($dir . '/*.test.php') as $file) {
		$cleanPath = realpath($file);
        $testRunner = function () use($file) {
			include $file;
		};
		$realFile = realpath( $file );
		echo "testing $realFile", PHP_EOL;
		$testRunner();
		$currentGroup = '';
		$skipNext = false;
		if ( $skipAll ) {
			if ( $skippedAllReason ) echo "skipped $skippedInModule test(s), $skippedAllReason \n";
			if ( $skippedAllReason ) $skippedTests += $skippedInModule; else $passedTests += $skippedInModule;
			$skipAll = false;
			$skippedInModule = 0;
		}
	}
	foreach(glob($dir . '/*', GLOB_ONLYDIR) as $folder) {
		$cleanPath = realpath($folder);
		runAll($folder);
	}
}

$currentGroup;
$passedTests = 0;
$failedTests = 0;
$totalTests = 0;
$skippedTests = 0;

function describe(string $group) {
	global $currentGroup;
	$currentGroup = $group;
}

function it(string $description, Closure $test) {
	global $currentGroup, $passedTests, $failedTests, $skippedTests, $totalTests;
	global $skipNext, $skipReason, $skipAll, $skippedInModule;
	$totalTests++;
	if ( $skipNext ) {
		if ( $skipReason ) echo "skipped 1 test, $skipReason \n";
		if ( $skipReason ) $skippedTests++; else $passedTests++;
		$skipNext = false;
		return;
	}
	if ( $skipAll ) {
		$skippedInModule++;
		return;
	}
	try {
		$test();
		$passedTests++;
	} catch (AssertionError $e) {
		echo PHP_EOL, "*** ERROR ***", PHP_EOL;
		echo "Test not passed: $currentGroup $description", PHP_EOL;
		$failedTests++;
		echo $e->getMessage(), PHP_EOL;
		$error = $e->getTrace()[1];
		echo $error['file'], ':', $error['line'], PHP_EOL, PHP_EOL;
	}
}

function response() {
	global $passedTests, $failedTests, $skippedTests, $totalTests;
	echo PHP_EOL;
	if ($skippedTests) echo "Skipped tests : $skippedTests/$totalTests", PHP_EOL;
	if ($passedTests ) echo "Passed tests  : $passedTests/$totalTests", PHP_EOL;
	if ($failedTests ) echo "Failed tests  : $failedTests/$totalTests", PHP_EOL;
}

function expectEquals($exp1, $exp2) {
	if ($exp1 !== $exp2) {
		$value1 = '(' . _type_( $exp1 ) . ') ' . var_export( $exp1, true );
		$value2 = '(' . _type_( $exp2 ) . ') ' . var_export( $exp2, true );
		$msg = "ERROR: expected: $value2, actual: $value1.";
		if ( $exp1 == $exp2 ) {
			_type_( $exp1 ) . ' != ' . _type_( $exp2 ) . PHP_EOL;
		}
		throw new AssertionError($msg);
	}
}

function expect($actual, Closure $matcher) {
	$matcher($actual);
}

$skipNext = false;
$skipReason = '';
$skipAll  = false;
$skippedInModule = 0;
$skippedAllReason = '';

function skip(string $reason) {
	global $skipNext,$skipReason;
	$skipNext = true;
	$skipReason = $reason;
}

function skipAll(string $reason) {
	global $skipAll, $skippedAllReason;
	$skipAll = true;
	$skippedAllReason = $reason;
}

// Matchers

function equals($expected) {
	return function ($actual) use ($expected) {
		expectEquals($actual, $expected);
	};
}

function is($expected) {
	return function ($actual) use ($expected) {
		expectEquals($actual, $expected);
	};
}

function isA( string $className ) {
	return function ( $actual ) use ( $className ) {
		if ( ! is_a( $actual, $className )) {
			throw new AssertionError( "Object is not a $className");
		}
	};
}

function throws() {
	return function(Closure $fn) {
		try {
			$fn();
			throw new TestDoesNotThrowException('Test should throw');
		} catch (TestDoesNotThrowException $e) {
			throw $e;
		} catch (Throwable $e) {
			// Test passed
		}
	};
}

function throwsA( string $throwableType ) {
	return function( Closure $fn ) use ($throwableType) {
		try {
			$fn();
		} catch (Throwable $e) {
			if ( is_a( $e, $throwableType ) ) {
				// Test passed
			} else {
				throw new Exception( "Test does not throw $throwableType, but " . _type_( $e ) );
			}
		}
	};
	// return function(Closure $fn) {
	// 	try {
	// 		$fn();
	// 		throw new TestDoesNotThrowException('Test should throw');
	// 	} catch (TestDoesNotThrowException $e) {
	// 		throw $e;
	// 	} catch (Throwable $e) {
	// 		// Test passed
	// 	}
	// };
}

function _type_($x) {
	$type = gettype($x);
	return $type == 'object' ? get_class($x) : $type;
}

function i( $x ) {
	print_r( $x );
	return $x;
}

class TestDoesNotThrowException extends Exception {}

runAll(__DIR__ . '/../..');
response();