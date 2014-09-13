#File

[![Build Status](https://travis-ci.org/asgardphp/file.svg?branch=master)](https://travis-ci.org/asgardphp/file)

File is a package to work with the file system and it provides a class to manipulate files as objects.

- [Installation](#installation)
- [FileSystem](#filesystem)
- [File](#file)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/file 0.*

<a name="filesystem"></a>
##FileSystem

###$mode

When a method takes a parameter $mode, you can use different values:

	$mode = \Asgard\File\FileSystem::OVERRIDE; #override existing files
	$mode = \Asgard\File\FileSystem::RENAME; #rename new files
	$mode = \Asgard\File\FileSystem::IGNORE; #ignore existing files
	$mode = \Asgard\File\FileSystem::MERGEDIR; #merge directories
	$mode = \Asgard\File\FileSystem::MERGEDIR | \Asgard\File\FileSystem::OVERRIDE; #merge directories but override existing files
	$mode = \Asgard\File\FileSystem::MERGEDIR | \Asgard\File\FileSystem::RENAME; #merge directories but rename new files
	$mode = \Asgard\File\FileSystem::MERGEDIR | \Asgard\File\FileSystem::IGNORE; #merge directories but ignore existing files

###Methods

Get relative path from a file to another:

	\Asgard\File\FileSystem::relativeTo($from, $to);

Get a new filename if the existing one is already taken:

	$filename = \Asgard\File\FileSystem::getNewFilename($filename);

Rename a file or directory:

	$path = \Asgard\File\FileSystem::rename($src, $dst, $mode);

returns the new path if successful, otherwise false.

Copy a file or directory:

	$path = \Asgard\File\FileSystem::copy($src, $dst, $mode);

returns the new path if successful, otherwise false.

Delete a file or directory:

	\Asgard\File\FileSystem::delete($path);

returns true is successful, otherwise false.

Create a new directory:

	\Asgard\File\FileSystem::mkdir($path);

returns true is successful, otherwise false.

Write content into a file:

	\Asgard\File\FileSystem::write($path, $content, $mode);

returns true is successful, otherwise false.

<a name="file"></a>
##File

Instance:

	$file = new \Asgard\File\File('/path/to/file.txt');

Set source:

	$file->setSrc('/path/to/another/file.txt');

Set file name:

	$file->setName('file2.txt');

Get file name:

	$file->getName();

Check if the file was just uploaded:

	$file->isUploaded();

Get the file size in bytes:

	$file->size();

Get the file type:

	$file->type();

Get the file extension:

	$file->extension();

Check if the file exists:

	$file->exists();

Get the file source:

	$file->src();

Get the relative path to another file or directory:

	$file->relativeTo('/another/file.jpg');

Move the to another directory:

	$file->moveToDir('/a/dir/', $mode);

Check if the file is in a directory

	$file->isIn('/a/dir/');

Check if the file is at a specific path:

	$file->isAt('/path/to/file.txt');

Rename the file:

	$file->rename('/anoter/path/to/file.txt', $mode);

Delete the file:

	$file->delete();

Copy the file:

	$file->copy('/path/to/copy.txt', $mode);

###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)