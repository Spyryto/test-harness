
# Test harness

This low-code, one-file test harness lets you:

- Write readable tests
- Run them quickly
- Easily create your matchers

## Examples

```php
<?php

describe ("expectEquals");

it ("works with simple values", function () {
	expect (1 + 1, is (2));
});
```
This will give you:

```
testing ...\test1.test.php

Passed tests  : 1/1
```


## How to configure an automatic task

### Add the task

Add the following task to your `tasks.json` in your `.vscode` folder:

```json
"tasks":
	...
	[
		{
			"label": "tests",
			"type": "shell",
			"command": "php test-harness/src/tests.php",
			"group": {
				"kind": "test",
				"isDefault": true
			}
		},
	]
	...
```

If you have any Composer package, add the autoloader path:

```json
"tasks":
	...
	[
		{
			"label": "tests",
			"type": "shell",
			"command": "php test-harness/src/tests.php prepend=src/vendor/autoload.php",
			"group": {
				"kind": "test",
				"isDefault": true
			}
		},
	]
	...
```

### Run the tests

Press `F1`, type `test`, select this action:
`Tasks: Run test task`.

You can [configure a shotcut](https://code.visualstudio.com/docs/getstarted/keybindings#_keyboard-shortcuts-editor) for that.


## How to run from a single file

Put the `tests.php` file anywhere in your project.

Change the second-to-last line from

```php
runAll(__DIR__ . '/../..');
```

to

```php
runAll(__DIR__);
```

Then, on your command line:

```bash
php test.php
```

This will run all the `*.test.php` files in the folder you put the `test.php`
in, and in all  its subfolders.