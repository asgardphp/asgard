#Validation

[![Build Status](https://travis-ci.org/asgardphp/validation.svg?branch=master)](https://travis-ci.org/asgardphp/validation)

- [Installation](#installation)
- [Validator](#validator)
- [Error report](#errors)
- [Error messages](#messages)
- [Input bag](#input)
- [Rules](#rules)

<a name="installation"></a>
##Installation
**If you are working on an Asgard project you don't need to install this library as it is already part of the standard libraries.**

	composer require asgard/validation 0.*

<a name="validator"></a>
##Validator
###Usage in the Asgard Framework

	#rulesRegistry instance
	$rulesRegistry = $container['rulesRegistry'];

	#validator instance
	$validator = $container->make('validator');
	
The [container](http://asgardphp.com/docs/container) is often accessible as a parameter or through a [ContainerAware](http://asgardphp.com/docs/container#containeraware) object. You can also use the [singleton](http://asgardphp.com/docs/container#usage-outside) but it is not recommended.

###Usage outside the Asgard Framework

	use Asgard\Validation\Validator;
	use Asgard\Validation\RulesRegistry;
	$rulesRegistry = new RulesRegistry();
	$validator = new Validator();
	$validator->setRegistry($rulesRegistry);

Instantiating the rulesRegistry is optional. If not, the validator will automatically use the RulesRegistry singleton.

###Adding rules
Adding multiple rules at once:

	$validator->rules([
		'min' => 5,
		'max' => 10,
	]);

If you do not give any parameter, it defaults to true, for example:

	$validator->rules(['required']);
	#or chaining:
	$validator->min(5)->max(10);

You can also initialize the validator with static calls:

	Validator::min(5)->max(10); #returns object Validator

###Validating attributes
Sometimes you validate arrays and need to use specific rules for some attributes.

Adding multiple rules at once for an attribute:

	$validator->attribute('attr', ['min' => 5, 'max' => 10]);
	#chaining:
	$validator->attribute('attr')->min(5)->max(10);

Using a new validator for an attribute:

	$v = Validator::min(5)->max(10);
	Validator::attribute('attr', $v)

For a nested attribute, use dots:

	$validator->attribute('attr.subattr.subsubattr', ['min' => 5, 'max' => 10]);
	$validator->valid(['attr'=>['subattr'=>['subsubattr'=>7]]]); #returns true

###Testing for input validity
	Validator::min(5)->valid(3); #returns false, below 5
	Validator::min(5)->valid(7); #returns true, above 5

With attributes:

	$v = Validator::attribute('attr')->min(5);
	$v->valid(['attr'=>2]); #returns false
	$v->valid(['attr'=>7]); #returns true

###Validating an array of inputs
If you want to validate all elements of an array:

	Validator::ruleEach('min', 5)->valid([4,5,6,7,8]); #returns false because of 4

By using rule instead of ruleEach, it would try to compare the array itself with 5.

###Short syntax

	Validator::rule('lengthbetween:1,3|contains:c')->valid('ac'); #returns true
	Validator::rule('lengthbetween:1,3|contains:c')->valid('aaaaac'); #returns false
	Validator::rule('lengthbetween:1,3|contains:c')->valid('aa'); #returns false

###Throwing exceptions

	Validator::min(5)->assert(3); #throws \Asgard\Validation\ValidatorException

You can access the errors through:

	try {
		Validator::min(5)->assert(3);
	} catch(\Asgard\Validation\ValidatorException $e) {
		$e->errors(); #returns a Asgard\Validation\ValidatorException object
	}

###Required input
To make an input required, use the rule "required":

	Validation::required()->valid(null) #returns false
	Validation::required()->valid(5) #returns true

And this will return true:

	Validation::equal('123')->valid(null) #returns true
	Validation::equal('123')->valid('') #returns true

Because if the input is empty and not required, it is not be checked against any rule.

However you may sometimes consider other types of inputs as empty. For example an empty array. In order to do that, use isNull:

	Validation::isNull(function($arr) { return count($arr)==0; })->valid([]); #returns true, because the input is empty and not required
	Validation::contains(1)->valid([]); #returns false

Sometimes the requirement depends on conditions. For example, a payment may be required if the amount is over 400.

	$v = Validator::attribute('payment', ['required' => function($input, $parent, $validator) {
		return $parent->attribute('amount') >= 400;
	}]);
	$v->valid(['amount'=>300]); #true
	$v->valid(['amount'=>500]); #false

###Getting the error report

	$report = Validator::min(5)->errors(3); #returns a report (Asgard\Validation\ValidatorException)

###Adding parameters to the validator

	$v->set('form', $form);

You can access the parameter in the rule function:

	//...
		public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
			$form = $validator->get('form');
			//...
		}
	//...

If an attribute validator does not have a parameter, it will ask its parents.

<a name="errors"></a>
##Error report
Error reports contain error messages for all rules or attributes that failed. Attributes included in a report have their own nested report.

To get all error messages of a report (without nested ones):

	$errors = $report->errors();

To get the main error message:

	$error = $report->error();

To get the error message of a specific rule:

	$error = $report->error('min');

To get the first message error of an attribute:

	$attrError = $report->first('attr');

To get a list of attributes that failed:

	$failed = $report->failed();

To navigate through the report:

	$r = $report->attribute('attr'); #returns the attribute report
	$r->errors(); #returns all its errors
	$r->error('min'); #returns specific rule error

In case you need to build your own report from differences sources, set an attribute report:

	$attrReport = new Report(['min' => 'attr is to high.']);
	$report->attribute('attr', $attrReport);

To get all nested attributes reports:

	$errors = $report->attributes();

To check if a report has any error:

	$report->hasError(); #returns true or false

If a validator has multiple rules with the same name, the report will add an increasing integer to each name:

	Validator::contains('a')->contains('b')->errors('c')->errors();
	# returns
	[
		'contains' => '"c" must contain "a".',
		'contains-1' => '"c" must contain "b".',
	]

To access a nested attribute, use dots:

	$report->attribute('attr.substtr.subsubsttr')

<a name="messages"></a>
##Error messages

###Custom messages
You can specify one or multiple messages for a validator rules:

	$validator->ruleMessages([
		'min' => 'Must be over :min!',
		'max' => 'Must be below :max!',
	]);
	#or
	$validator->ruleMessage('min', 'Must be over :min!');

Or specify a message for rules in the RulesRegitry:

	$registry->messages([
		'min' => 'Must be over :min!',
		'max' => 'Must be below :max!',
	]);
	#or
	$registry->message('min', 'Must be over :min!');

If you did not do any of the two above, the validator will take the default rule message by calling the method getMessage() of the rule:

	public function getMessage() {
		return ':attribute must be greater than :min.';
	}

If the rule does not have a its own message, the validator will get the default message error that you can set with:

	$validator->setDefaultMessage(':attribute is wrong!');

Finally, if any of the above is available, it will return the message: ":attribute is not valid."

###Message parameters
As you have seen, messages have parameters like :attribute, :input, etc.

* The parameter :attribute is either the name of the attribute or the input itself.
* The parameter :input is available when the input is a string or numeric.

Then comes rules specific parameters. Any rule with member variables, can use them as parameters in the error message. For example the rule Asgard\Validation\Rules\Min has a variable "min". Hence, you can use the parameter :min in its message: ":attribute must be greater than :min."

<a name="input"></a>
##Input Bag
All rules receive the raw input and the parent input bag. You can use the input bag object to navigate through the whole input:

	//...
		public function validate($input, \Asgard\Validation\InputBag $parentInput, \Asgard\Validation\Validator $validator) {
			return $input == $parentInput->attribute('^.confirm')->input();
		}
	//..

The sign ^ is used to go to the parent, and go down the tree with the list of attributes separated with a dot.
For example, with the input ['password'=>'abc', 'confirm'=>'zyx'], and validating attribute "password", the previous function will access to the value of the "confirm" attribute.

You can check that an attribute exists with:

	$parentInput->hasAttribute('^.confirm');

<a name="rules"></a>
##Rules
###Creating new rules
You can create new rules through the rulesregistry. Either use the default instance or a new instance:
	
	$rr = RulesRegistry::instance();

or

	$rr = new RulesRegistry();
	$validator = new Validator();
	$validator->setRegistry($rr);

If a validator doesn't haven its own rulesregistry, it will ask its parent, like here:

	Validator::attribute('attr', Validator::min(5));

The attribute validator will ask the main validator for the rulesregistry. If the parent doesn't have one, it will use the default RulesRegistry instance.

###Registering new rules

	$rr->register('customrule', function($input) { /*...*/ });
	$rr->register('customrule', 'Namespace\Rules\Customrule');
	$rr->register('customrule', new \Namespace\Rules\Customrule(/* params */));

###Registering namespaces
Let's say you want to add multiple rules, all in the same namespace. You can do:

	$rr->registerNamespace('Namespace\Rules');

When looking for a rule (like "customrule"), the rulesregistry will check if the class Namespace\Rules\Customrule exists.

If a rule is not found, it will throw an exception.

###List of existing rules
**All**: must validate all validators passed as parameters

	Validator::all(v::contains('a'), v::contains('b'));

**Any**: must validate any of the rules passed as parameters

	Validator::any(v::contains('a'), v::contains('b'));

**Callback**: use a custom rule in a lambda function

	Validator::callback(function($input) { return $input == 5; });
	#or
	Validator::rule(function($input) { return $input == 5; })

**Contains**: the string input must contain the substring passed as parameter

	Validator::contains('a')

**Date**: must be a valid date (xxxx-xx-xx)

	Validator::date()

**Each**: each attribute of the input must be valid with the validator passed as parameter

	Validator::each(v::min(5))->valid([4, 5, 6, 7]) #returns false because of 4

**Email**: the input must be an email address

	Validator::email()

**Equal**: the input must be equal to the value passed as parameter

	Validator::equal(12345)

**Int**: the input must be an integer

	Validator::int()

**Isinstanceof**: the input must be an instance of..

	Validator::isinstanceof('Namespace\Class')

**Length**: the input must have "length" characters.

	Validator::length(10)

**Lengthbetween**: the input length must be between min and max

	Validator:lengthbetween(10, 20)

**Min**: the input must be greater than min
	
	Validator::min(5)

**Max**: the input must be less than max
	
	Validator::max(10)

**Regex**: the input must match the given pattern
	
	Validator::regex('/^[a-z]+$/')

**Required**
	
	Validator::required()

**Same**: the input attribute must be equal to another attribute
	
	Validator::same('^.confirm')


###Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

### License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)