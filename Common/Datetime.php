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
	 * @param DateTimeZone|string $tz
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
		return static::createFromCarbon($this->carbon);
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
		if(!$this->carbon instanceof \__PHP_Incomplete_Class)
			return static::createFromCarbon($this->carbon->__wakeup());
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
		return static::createFromCarbon($this->carbon->__get($name));
	}

	/**
	 * {@inheritDoc}
	 */
	public function __isset($name) {
		return static::createFromCarbon($this->carbon->__isset($name));
	}

	/**
	 * {@inheritDoc}
	 */
	public function __set($name, $value) {
		return static::createFromCarbon($this->carbon->__set($name, $value));
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
		return static::createFromCarbon($this->carbon->setTimezone($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function formatLocalized($format) {
		return static::createFromCarbon($this->carbon->formatLocalized($format));
	}

	/**
	 * {@inheritDoc}
	 */
	public function __toString() {
		return static::createFromCarbon($this->carbon->__toString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toDateString() {
		return static::createFromCarbon($this->carbon->toDateString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toFormattedDateString() {
		return static::createFromCarbon($this->carbon->toFormattedDateString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toTimeString() {
		return static::createFromCarbon($this->carbon->toTimeString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toDateTimeString() {
		return static::createFromCarbon($this->carbon->toDateTimeString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toDayDateTimeString() {
		return static::createFromCarbon($this->carbon->toDayDateTimeString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toAtomString() {
		return static::createFromCarbon($this->carbon->toAtomString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toCookieString() {
		return static::createFromCarbon($this->carbon->toCookieString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toIso8601String() {
		return static::createFromCarbon($this->carbon->toIso8601String());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc822String() {
		return static::createFromCarbon($this->carbon->toRfc822String());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc850String() {
		return static::createFromCarbon($this->carbon->toRfc850String());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc1036String() {
		return static::createFromCarbon($this->carbon->toRfc1036String());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc1123String() {
		return static::createFromCarbon($this->carbon->toRfc1123String());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc2822String() {
		return static::createFromCarbon($this->carbon->toRfc2822String());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRfc3339String() {
		return static::createFromCarbon($this->carbon->toRfc3339String());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toRssString() {
		return static::createFromCarbon($this->carbon->toRssString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toW3cString() {
		return static::createFromCarbon($this->carbon->toW3cString());
	}

	/**
	 * {@inheritDoc}
	 */
	public function eq(DatetimeInterface $dt) {
		return static::createFromCarbon($this->carbon->eq($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function ne(DatetimeInterface $dt) {
		return static::createFromCarbon($this->carbon->ne($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function gt(DatetimeInterface $dt) {
		return static::createFromCarbon($this->carbon->gt($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function gte(DatetimeInterface $dt) {
		return static::createFromCarbon($this->carbon->gte($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function lt(DatetimeInterface $dt) {
		return static::createFromCarbon($this->carbon->lt($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function lte(DatetimeInterface $dt) {
		return static::createFromCarbon($this->carbon->lte($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	*/
	public function between(DatetimeInterface $dt1, DatetimeInterface $dt2, $equal=true) {
		return static::createFromCarbon($this->carbon->between($dt1->getCarbon(), $dt2->getCarbon(), $equal));
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
		return static::createFromCarbon($this->carbon->isWeekday());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isWeekend() {
		return static::createFromCarbon($this->carbon->isWeekend());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isYesterday() {
		return static::createFromCarbon($this->carbon->isYesterday());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isToday() {
		return static::createFromCarbon($this->carbon->isToday());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isTomorrow() {
		return static::createFromCarbon($this->carbon->isTomorrow());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isFuture() {
		return static::createFromCarbon($this->carbon->isFuture());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isPast() {
		return static::createFromCarbon($this->carbon->isPast());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isLeapYear() {
		return static::createFromCarbon($this->carbon->isLeapYear());
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSameDay(DatetimeInterface $dt) {
		return static::createFromCarbon($this->carbon->isSameDay($dt->getCarbon()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addYears($value) {
		return static::createFromCarbon($this->carbon->addYears($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addYear() {
		return static::createFromCarbon($this->carbon->addYear());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subYear() {
		return static::createFromCarbon($this->carbon->subYear());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subYears($value) {
		return static::createFromCarbon($this->carbon->subYears($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMonths($value) {
		return static::createFromCarbon($this->carbon->addMonths($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMonth() {
		return static::createFromCarbon($this->carbon->addMonth());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMonth() {
		return static::createFromCarbon($this->carbon->subMonth());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMonths($value) {
		return static::createFromCarbon($this->carbon->subMonths($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDays($value) {
		return static::createFromCarbon($this->carbon->addDays($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDay() {
		return static::createFromCarbon($this->carbon->addDay());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subDay() {
		return static::createFromCarbon($this->carbon->subDay());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subDays($value) {
		return static::createFromCarbon($this->carbon->subDays($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeekdays($value) {
		return static::createFromCarbon($this->carbon->addWeekdays($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeekday() {
		return static::createFromCarbon($this->carbon->addWeekday());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeekday() {
		return static::createFromCarbon($this->carbon->subWeekday());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeekdays($value) {
		return static::createFromCarbon($this->carbon->subWeekdays($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeeks($value) {
		return static::createFromCarbon($this->carbon->addWeeks($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeek() {
		return static::createFromCarbon($this->carbon->addWeek());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeek() {
		return static::createFromCarbon($this->carbon->subWeek());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeeks($value) {
		return static::createFromCarbon($this->carbon->subWeeks($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addHours($value) {
		return static::createFromCarbon($this->carbon->addHours($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addHour() {
		return static::createFromCarbon($this->carbon->addHour());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subHour() {
		return static::createFromCarbon($this->carbon->subHour());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subHours($value) {
		return static::createFromCarbon($this->carbon->subHours($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMinutes($value) {
		return static::createFromCarbon($this->carbon->addMinutes($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMinute() {
		return static::createFromCarbon($this->carbon->addMinute());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMinute() {
		return static::createFromCarbon($this->carbon->subMinute());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMinutes($value) {
		return static::createFromCarbon($this->carbon->subMinutes($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addSeconds($value) {
		return static::createFromCarbon($this->carbon->addSeconds($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function addSecond() {
		return static::createFromCarbon($this->carbon->addSecond());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subSecond() {
		return static::createFromCarbon($this->carbon->subSecond());
	}

	/**
	 * {@inheritDoc}
	 */
	public function subSeconds($value) {
		return static::createFromCarbon($this->carbon->subSeconds($value));
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInYears(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInYears($dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInMonths(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInMonths($dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInWeeks(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInWeeks($dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInDays(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInDays($dt->getCarbon(), $abs));
	}

	 /**
	 * {@inheritDoc}
	 */
	 public function diffInDaysFiltered(Closure $callback, DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInDaysFiltered($callback, $dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	 public function diffInWeekdays(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInWeekdays($dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	 public function diffInWeekendDays(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInWeekendDays($dt->getCarbon(), $abs));
	}

	/**
	 */
	public function diffInHours(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInHours($dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInMinutes(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInMinutes($dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInSeconds(DatetimeInterface $dt=null, $abs=true) {
		return static::createFromCarbon($this->carbon->diffInSeconds($dt->getCarbon(), $abs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffForHumans(DatetimeInterface $other=null) {
		return static::createFromCarbon($this->carbon->diffForHumans($other));
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
		return static::createFromCarbon($this->carbon->isBirthday($dt->getCarbon()));
	}
}