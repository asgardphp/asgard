<?php
namespace Asgard\Common;

use Carbon\Carbon;

/**
 * Datetime.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Datetime extends \DateTime implements DatetimeInterface {
	/**
	 * Carbon instance.
	 * @var Carbon
	 */
	protected $carbon;

	/**
	 * Constructor.
	 * @param string       $time
	 * @param \DateTimeZone|string $tz
	 */
	public function __construct($time=null, $tz=null) {
		if($time===null)
			$time = 'now';
		$tz = $this->safeCreateDateTimeZone($tz);
		parent::__construct($time, $tz); #need to initialize parent \DateTime as well or comparaison will fail in PHP internals
		$this->carbon = new Carbon($time, $tz);
	}

	protected static function safeCreateDateTimeZone($object) {
		if($object instanceof \DateTimeZone || $object === null)
			return $object;
		$tz = timezone_open((string)$object);
		if($tz === false)
			throw new \InvalidArgumentException('Unknown or bad timezone ('.$object.')');
		return $tz;
	}

	protected function update() {
		$this->setTimestamp($this->carbon->getTimestamp());
		$this->setTimezone($this->carbon->getTimezone());
	}

	/**
	 * Object cloning.
	 */
	public function __clone() {
		$this->carbon = clone $this->carbon;
	}

	/**
	 * Create a new Datetime object from a carbon object.
	 * @param  \Carbon\Carbon $carbon
	 * @return Datetime
	 */
	public static function createFromCarbon(\Carbon\Carbon $carbon) {
		$dt = new static;
		$dt->setCarbon($carbon);
		return $dt;
	}

	/**
	 * Return the carbon instance equivalent.
	 * @return Carbon
	 */
	public function getCarbon() {
		return $this->carbon;
	}

	/**
	 * Return the carbon instance equivalent.
	 * @param \Carbon\Carbon $carbon
	 */
	public function setCarbon(\Carbon\Carbon $carbon) {
		$this->carbon = $carbon;
	}

	/**)
	 * {@inheritDoc}
	 */
	public function diff($datetime2, $absolute=null) {
		parent::diff($datetime2, $absolute);
		return $this->carbon->diff($datetime2, $absolute);
	}

	/**
	 * {@inheritDoc}
	 */
	public function sub($interval) {
		parent::sub($interval);
		return $this->carbon->sub($interval);
	}

	/**
	 * {@inheritDoc}
	 */
	public function format($format) {
		parent::format($format);
		return $this->carbon->format($format);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOffset() {
		return $this->carbon->getOffset();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTimestamp() {
		return $this->carbon->getTimestamp();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTimezone() {
		return $this->carbon->getTimezone();
	}

	/**
	 * {@inheritDoc}
	 */
	public function __wakeup() {
		parent::__wakeup();
		if(!$this->carbon instanceof \__PHP_Incomplete_Class && $carbon=$this->carbon->__wakeup())
			return static::createFromCarbon($carbon);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function instance(\DateTime $dt) {
		return new static($dt->format('Y-m-d H:i:s.u'), $dt->getTimeZone());
	}
	
	/**
	 * {@inheritDoc}
	 */
	public static function parse($time = null, $tz = null) {
		return new static($time, $tz);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function now($tz = null) {
		return new static(null, $tz);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function today($tz = null) {
		return static::now($tz)->startOfDay();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function tomorrow($tz = null) {
		return static::today($tz)->addDay();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function yesterday($tz = null) {
		return static::today($tz)->subDay();
	}

	/**
	 * {@inheritDoc}
	 */
	public static function maxValue() {
		return static::createFromTimestamp(PHP_INT_MAX);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function minValue() {
		return static::createFromTimestamp(~PHP_INT_MAX);
	}

	/**
	 * {@inheritDoc}
	 * @link https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
	 */
	public static function create($year=null, $month=null, $day=null, $hour=null, $minute=null, $second=null, $tz=null) {
		$year = ($year === null) ? date('Y') : $year;
		$month = ($month === null) ? date('n') : $month;
		$day = ($day === null) ? date('j') : $day;

		if ($hour === null) {
			$hour = date('G');
			$minute = ($minute === null) ? date('i') : $minute;
			$second = ($second === null) ? date('s') : $second;
		}
		else {
			$minute = ($minute === null) ? 0 : $minute;
			$second = ($second === null) ? 0 : $second;
		}

        return static::createFromFormat('Y-n-j G:i:s', sprintf('%s-%s-%s %s:%02s:%02s', $year, $month, $day, $hour, $minute, $second), $tz);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function createFromDate($year=null, $month=null, $day=null, $tz=null) {
		return static::create($year, $month, $day, null, null, null, $tz);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function createFromTime($hour=null, $minute=null, $second=null, $tz=null) {
		return static::create(null, null, null, $hour, $minute, $second, $tz);
	}

	/**
	 * {@inheritDoc}
	 * @link https://github.com/briannesbitt/Carbon/blob/master/src/Carbon/Carbon.php
	 */
	public static function createFromFormat($format, $time, $tz=null) {
		if ($tz !== null)
			$dt = parent::createFromFormat($format, $time, static::safeCreateDateTimeZone($tz));
		else
			$dt = parent::createFromFormat($format, $time);

		if ($dt instanceof \DateTime)
			return static::instance($dt);

		$errors = static::getLastErrors();
		throw new \InvalidArgumentException(implode(PHP_EOL, $errors['errors']));
	}

	/**
	 * {@inheritDoc}
	 */
	public static function createFromTimestamp($timestamp, $tz=null) {
		return static::now($tz)->setTimestamp($timestamp);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function createFromTimestampUTC($timestamp) {
		return new static('@'.$timestamp);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function resetToStringFormat() {
		static::setToStringFormat(self::DEFAULT_TO_STRING_FORMAT);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function setToStringFormat($format) {
		static::$toStringFormat = $format;
	}

	/**
	 * {@inheritDoc}
	 */
	public function copy() {
		return static::createFromCarbon($this->carbon->copy());
	}

	/**
	 * {@inheritDoc}
	 */
	public function __get($name) {
		return $this->carbon->__get($name);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __isset($name) {
		return $this->carbon->__isset($name);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __set($name, $value) {
		return $this->carbon->__set($name, $value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function year($value) {
		return static::createFromCarbon($this->carbon->year($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function month($value) {
		return static::createFromCarbon($this->carbon->month($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function day($value) {
		return static::createFromCarbon($this->carbon->day($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDate($year, $month, $day) {
		return static::createFromCarbon($this->carbon->setDate($year, $month, $day));
	}

	/**
	 * {@inheritDoc}
	 */
	public function hour($value) {
		return static::createFromCarbon($this->carbon->hour($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function minute($value) {
		return static::createFromCarbon($this->carbon->minute($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function second($value) {
		return static::createFromCarbon($this->carbon->second($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTime($hour, $minute, $second=0) {
		return static::createFromCarbon($this->carbon->setTime($hour, $minute, $second));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDateTime($year, $month, $day, $hour, $minute, $second=0) {
		return static::createFromCarbon($this->carbon->setDateTime($year, $month, $day, $hour, $minute, $second));
	}

	/**
	 * {@inheritDoc}
	 */
	public function timestamp($value) {
		return static::createFromCarbon($this->carbon->timestamp($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function timezone($value) {
		return static::createFromCarbon($this->carbon->timezone($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function tz($value) {
		return static::createFromCarbon($this->carbon->tz($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTimezone($value) {
		$this->carbon->setTimezone($value);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function formatLocalized($format) {
		return $this->carbon->formatLocalized($format);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString() {
		return $this->carbon->__toString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toDateString() {
		return $this->carbon->toDateString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toFormattedDateString() {
		return $this->carbon->toFormattedDateString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toTimeString() {
		return $this->carbon->toTimeString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toDateTimeString() {
		return $this->carbon->toDateTimeString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toDayDateTimeString() {
		return $this->carbon->toDayDateTimeString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toAtomString() {
		return $this->carbon->toAtomString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toCookieString() {
		return $this->carbon->toCookieString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toIso8601String() {
		return $this->carbon->toIso8601String();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc822String() {
		return $this->carbon->toRfc822String();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc850String() {
		return $this->carbon->toRfc850String();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc1036String() {
		return $this->carbon->toRfc1036String();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc1123String() {
		return $this->carbon->toRfc1123String();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc2822String() {
		return $this->carbon->toRfc2822String();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc3339String() {
		return $this->carbon->toRfc3339String();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRssString() {
		return $this->carbon->toRssString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toW3cString() {
		return $this->carbon->toW3cString();
	}

	/**
	 * {@inheritDoc}
	 */
	public function eq(DatetimeInterface $dt) {
		return $this->carbon->eq($dt->getCarbon());
	}

	/**
	 * {@inheritDoc}
	 */
	public function ne(DatetimeInterface $dt) {
		return $this->carbon->ne($dt->getCarbon());
	}

	/**
	 * {@inheritDoc}
	 */
	public function gt(DatetimeInterface $dt) {
		return $this->carbon->gt($dt->getCarbon());
	}

	/**
	 * {@inheritDoc}
	 */
	public function gte(DatetimeInterface $dt) {
		return $this->carbon->gte($dt->getCarbon());
	}

	/**
	 * {@inheritDoc}
	 */
	public function lt(DatetimeInterface $dt) {
		return $this->carbon->lt($dt->getCarbon());
	}

	/**
	 * {@inheritDoc}
	 */
	public function lte(DatetimeInterface $dt) {
		return $this->carbon->lte($dt->getCarbon());
	}

	/**
	 * {@inheritDoc}
	*/
	public function between(DatetimeInterface $dt1, DatetimeInterface $dt2, $equal=true) {
		return $this->carbon->between($dt1->getCarbon(), $dt2->getCarbon(), $equal);
	}

	/**
	 * {@inheritDoc}
	 */
	public function min(DatetimeInterface $dt=null) {
		return static::createFromCarbon($this->carbon->min($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function max(DatetimeInterface $dt=null) {
		return static::createFromCarbon($this->carbon->max($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function isWeekday() {
		return $this->carbon->isWeekday();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isWeekend() {
		return $this->carbon->isWeekend();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isYesterday() {
		return $this->carbon->isYesterday();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isToday() {
		return $this->carbon->isToday();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isTomorrow() {
		return $this->carbon->isTomorrow();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFuture() {
		return $this->carbon->isFuture();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isPast() {
		return $this->carbon->isPast();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLeapYear() {
		return $this->carbon->isLeapYear();
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSameDay(DatetimeInterface $dt) {
		return $this->carbon->isSameDay($dt->getCarbon());
	}

	/**
	 * {@inheritDoc}
	 */
	public function addYears($value) {
		$this->carbon->addYears($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addYear() {
		$this->carbon->addYear();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subYear() {
		$this->carbon->subYear();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subYears($value) {
		$this->carbon->subYears($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMonths($value) {
		$this->carbon->addMonths($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMonth() {
		$this->carbon->addMonth();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMonth() {
		$this->carbon->subMonth();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMonths($value) {
		$this->carbon->subMonths($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDays($value) {
		$this->carbon->addDays($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDay() {
		$this->carbon->addDay();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subDay() {
		$this->carbon->subDay();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subDays($value) {
		$this->carbon->subDays($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeekdays($value) {
		$this->carbon->addWeekdays($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeekday() {
		$this->carbon->addWeekday();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeekday() {
		$this->carbon->subWeekday();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeekdays($value) {
		$this->carbon->subWeekdays($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeeks($value) {
		$this->carbon->addWeeks($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeek() {
		$this->carbon->addWeek();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeek() {
		$this->carbon->subWeek();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeeks($value) {
		$this->carbon->subWeeks($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addHours($value) {
		$this->carbon->addHours($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addHour() {
		$this->carbon->addHour();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subHour() {
		$this->carbon->subHour();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subHours($value) {
		$this->carbon->subHours($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMinutes($value) {
		$this->carbon->addMinutes($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMinute() {
		$this->carbon->addMinute();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMinute() {
		$this->carbon->subMinute();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMinutes($value) {
		$this->carbon->subMinutes($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addSeconds($value) {
		$this->carbon->addSeconds($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addSecond() {
		$this->carbon->addSecond();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subSecond() {
		$this->carbon->subSecond();
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function subSeconds($value) {
		$this->carbon->subSeconds($value);
		$this->update();
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInYears(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInYears($dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInMonths(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInMonths($dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInWeeks(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInWeeks($dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInDays(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInDays($dt->getCarbon(), $abs);
	}

	 /**
	 * {@inheritDoc}
	 */
	 public function diffInDaysFiltered(Closure $callback, DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInDaysFiltered($callback, $dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	 public function diffInWeekdays(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInWeekdays($dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	 public function diffInWeekendDays(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInWeekendDays($dt->getCarbon(), $abs);
	}

	/**
	 */
	public function diffInHours(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInHours($dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInMinutes(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInMinutes($dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInSeconds(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInSeconds($dt->getCarbon(), $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffForHumans(DatetimeInterface $other=null) {
		return $this->carbon->diffForHumans($other->getCarbon());
	}

	/**
	 * {@inheritDoc}
	 */
	public function startOfDay() {
		return static::createFromCarbon($this->carbon->startOfDay());
	}

	/**
	 * {@inheritDoc}
	 */
	public function endOfDay() {
		return static::createFromCarbon($this->carbon->endOfDay());
	}

	/**
	 * {@inheritDoc}
	 */
	public function startOfMonth() {
		return static::createFromCarbon($this->carbon->startOfMonth());
	}

	/**
	 * {@inheritDoc}
	 */
	public function endOfMonth() {
		return static::createFromCarbon($this->carbon->endOfMonth());
	}

	/**
	 * {@inheritDoc}
	 */
	public function startOfYear() {
		return static::createFromCarbon($this->carbon->startOfYear());
	}

	/**
	 * {@inheritDoc}
	 */
	 public function endOfYear() {
		return static::createFromCarbon($this->carbon->endOfYear());
	}

	/**
	 * {@inheritDoc}
	 */
	 public function startOfDecade() {
		return static::createFromCarbon($this->carbon->startOfDecade());
	}

	/**
	 * {@inheritDoc}
	 */
	 public function endOfDecade() {
		return static::createFromCarbon($this->carbon->endOfDecade());
	}

	/**
	 * {@inheritDoc}
	 */
	 public function startOfCentury() {
		return static::createFromCarbon($this->carbon->startOfCentury());
	}

	/**
	 * {@inheritDoc}
	 */
	 public function endOfCentury() {
		return static::createFromCarbon($this->carbon->endOfCentury());
	}

	/**
	 */
	 public function startOfWeek() {
		return static::createFromCarbon($this->carbon->startOfWeek());
	}

	 /**
	 * {@inheritDoc}
	 */
	 public function endOfWeek() {
		return static::createFromCarbon($this->carbon->endOfWeek());
	}

	/**
	 * {@inheritDoc}
	 */
	public function next($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->next($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function previous($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->previous($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function firstOfMonth($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->firstOfMonth($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastOfMonth($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->lastOfMonth($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function nthOfMonth($nth, $dayOfWeek) {
		return static::createFromCarbon($this->carbon->nthOfMonth($nth, $dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function firstOfQuarter($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->firstOfQuarter($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastOfQuarter($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->lastOfQuarter($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function nthOfQuarter($nth, $dayOfWeek) {
		return static::createFromCarbon($this->carbon->nthOfQuarter($nth, $dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function firstOfYear($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->firstOfYear($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastOfYear($dayOfWeek=null) {
		return static::createFromCarbon($this->carbon->lastOfYear($dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function nthOfYear($nth, $dayOfWeek) {
		return static::createFromCarbon($this->carbon->nthOfYear($nth, $dayOfWeek));
	}

	/**
	 * {@inheritDoc}
	 */
	public function average(DatetimeInterface $dt=null) {
		return static::createFromCarbon($this->carbon->average($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function isBirthday(DatetimeInterface $dt) {
		return $this->carbon->isBirthday($dt->getCarbon());
	}
}