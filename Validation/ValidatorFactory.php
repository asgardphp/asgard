<?php
namespace Asgard\Validation;

/**
 * Validator factory.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ValidatorFactory implements ValidatorFactoryInterface {
	/**
	 * Rules registry.
	 * @var RulesRegistry
	 */
	protected $rulesRegistry;
	/**
	 * Translator.
	 * @var \Symfony\Component\Translation\TranslatorInterface
	 */
	protected $translator;

	/**
	 * Constructor.
	 * @param RulesRegistry $rulesRegistry
	 */
	public function __construct(RulesRegistry $rulesRegistry=null, \Symfony\Component\Translation\TranslatorInterface $translator=null) {
		$this->rulesRegistry = $rulesRegistry;
		$this->translator = $translator;
	}

	/**
	 * {@inheritDoc}
	 * @return TemplateEngine
	 */
	public function create() {
		return (new Validator($this->rulesRegistry))->setTranslator($this->translator);
	}
}