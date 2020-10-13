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

function describe (...$args) {
	global $currentGroup;
	$currentGroup = join (' ', $args);
}

function it (string $description, Closure $test) {
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
		echo $e->getMessage(), PHP_EOL, PHP_EOL;
		echo "Trace:", PHP_EOL;
		printTrace($e);
		echo PHP_EOL;
		// echo $e->getTraceAsString(), PHP_EOL, PHP_EOL;
		// $error = $e->getTrace()[1];
		// echo $error['file'], ':', $error['line'], PHP_EOL, PHP_EOL;
	}
}

function printTrace (Throwable $exception)
{
	foreach ($exception->getTrace() as $key => $call) {
		if ($call['file'] != __FILE__) {
			printf ("* %s(%s) \n", $call['file'], $call['line'] );
		}
	}
}

function response () {
	global $passedTests, $failedTests, $skippedTests, $totalTests, $allFine;
	echo PHP_EOL;
	if ($skippedTests) echo "Skipped tests : $skippedTests/$totalTests", PHP_EOL;
	if ($passedTests ) echo "Passed tests  : $passedTests/$totalTests", PHP_EOL;
	if ($failedTests ) echo "Failed tests  : $failedTests/$totalTests", PHP_EOL;
	$allFine = $failedTests == 0;
}

function expectEquals ($exp1, $exp2) {
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

function expect ($actual, Closure $matcher = null)
{
	if (func_num_args() === 1):
		expectEquals ($actual, true);
		return;
	endif;
	$matcher ($actual);
}

$skipNext = false;
$skipReason = '';
$skipAll  = false;
$skippedInModule = 0;
$skippedAllReason = '';
$allFine = false;

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

function cleanup (Closure $cleanup_function)
{
	register_shutdown_function($cleanup_function);
}

/////[   Matchers   ]///////////////////////////////////////////////////////////

function equals ($expected) {
	return function ($actual) use ($expected) {
		expectEquals($actual, $expected);
	};
}

function is ($expected) {
	return function ($actual) use ($expected) {
		expectEquals($actual, $expected);
	};
}

function isA (string $className) {
	return function ( $actual ) use ( $className ) {
		$actual_type = _type_ ($actual);
		if ( ! is_a ($actual, $className) &&
			$actual_type !== $className):
			throw new AssertionError
			(
				"Object is not a $className, it is a $actual_type"
			);
		endif;
	};
}

function throws () {
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

function throwsA (string $throwableType) {
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
}

/////[   Aliases   ]////////////////////////////////////////////////////////////

function expect equals ($exp1, $exp2)
{
	expectEquals ($exp1, $exp2);
}

function skip all (string $reason)
{
	return skipAll ($reason);
}

function is a (string $className)
{
	return isA ($className);
}

function throws a (string $throwableType)
{
	return throwsA ($throwableType);
}

/////[   Utilities   ]//////////////////////////////////////////////////////////

function _type_($x) {
	$type = gettype($x);
	return $type == 'object' ? get_class($x) : $type;
}

function i( $x ) {
	print_r( $x );
	return $x;
}

function arguments ()
{
	if (PHP_SAPI == 'cli'):
		$pairs = array_slice ($GLOBALS ['argv'], 1);
		$arguments = [];
		foreach ($pairs as $pair):
			list ($name, $value) = explode ('=', $pair);
			$arguments [$name] = $value;
		endforeach;
		return $arguments;
	else:
		return $_GET;
	endif;
}

function checkCommandLineOptions()
{
	foreach (arguments () as $name => $values):
		if (in_array ($name, ['prepend'])):
			$argsList = $values ? explode (',', $values) : [];
			call_user_func_array ($name, $argsList);
		else:
			die ("Invalid option '$name'.");
		endif;
	endforeach;
}

function prepend(...$files)
{
	foreach ($files as $file):
		$dir path = __DIR__ . '/../..';
		$file path = ltrim ($file, '/\\');
		$include file = realpath ("$dir path/$file path");
		include $include file;
	endforeach;
}

/////[   Errors   ]/////////////////////////////////////////////////////////////

class TestDoesNotThrowException extends Exception {}

/////[   Main   ]/////////////////////////////////////////////////////////////

if (PHP_SAPI !== 'cli') {
	echo '<pre>';
	register_shutdown_function (function () {
		global $allFine;
		if ($allFine) {
			echo '</pre><style> body { background: #00ff0022 }</style>';
		} else {
			echo '</pre><style> body { background: #ff000022 }</style>';
		}
	});
}

checkCommandLineOptions();
runAll(__DIR__ . '/../..');
response();