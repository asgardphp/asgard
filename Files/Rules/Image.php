<?php
namespace Asgard\Files\Rules;

class Image extends \Asgard\Validation\Rule {
	protected $handleEach = true;

	public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
		if(!$input instanceof \Asgard\Files\Libs\EntityFile || $input->get(null, true) === null)
			return;
		$finfo = \finfo_open(FILEINFO_MIME);
		$mime = \finfo_file($finfo, $input->get(null, true));
		\finfo_close($finfo);
		list($mime) = explode(';', $mime);
		return in_array($mime, array('image/jpeg', 'image/png', 'image/gif'));
	}

	public function getMessage() {
		return 'The file :attribute must be an image (jpg, png or gif).';
	}
}