<?php
namespace Asgard\File\Rule;

/**
 * Check that the file is an image.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Image extends \Asgard\Validation\Rule {
	/**
	 * {@inherits}
	 */
	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\ValidatorInterface $validator) {
		if(!$input instanceof \Asgard\File\File)
			return;
		$finfo = \finfo_open(FILEINFO_MIME);
		$mime = \finfo_file($finfo, $input->src());
		\finfo_close($finfo);
		list($mime) = explode(';', $mime);
		return in_array($mime, ['image/jpeg', 'image/png', 'image/gif']);
	}

	/**
	 * {@inherits}
	 */
	public function getMessage() {
		return 'The file :attribute must be an image (jpg, png or gif).';
	}
}