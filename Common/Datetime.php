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
	 * @param DateTimeZone $tz
	 */
	public function __construct($time=null, $tz=null) {
		if($time===null)
			$time = 'now';
		parent::__construct($time, $tz); #need to initialize parent \DateTime as well or comparaison will fail in PHP internals
		return $this->carbon = new Carbon($time, $tz);
	}

	/**
	 * Return the carbon instance equivalent.
	 * @param  DatetimeInterface $dt
	 * @return Carbon
	 */
	public function getCarbon(DatetimeInterface $dt) {
		if($dt instanceof Carbon)
			return $dt;
		else
			return new Carbon($dt->getTimestamp(), $dt->getTimezone());
	}

	/**
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
		return $this->carbon->__wakeup();
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
		return $this->carbon->copy();
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
		return $this->carbon->year($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function month($value) {
		return $this->carbon->month($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function day($value) {
		return $this->carbon->day($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDate($year, $month, $day) {
		return $this->carbon->setDate($year, $month, $day);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hour($value) {
		return $this->carbon->hour($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function minute($value) {
		return $this->carbon->minute($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function second($value) {
		return $this->carbon->second($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTime($hour, $minute, $second=0) {
		return $this->carbon->setTime($hour, $minute, $second);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDateTime($year, $month, $day, $hour, $minute, $second=0) {
		return $this->carbon->setDateTime($year, $month, $day, $hour, $minute, $second);
	}

	/**
	 * {@inheritDoc}
	 */
	public function timestamp($value) {
		return $this->carbon->timestamp($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function timezone($value) {
		return $this->carbon->timezone($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function tz($value) {
		return $this->carbon->tz($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTimezone($value) {
		return $this->carbon->setTimezone($value);
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
		return $this->carbon->eq($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function ne(DatetimeInterface $dt) {
		return $this->carbon->ne($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function gt(DatetimeInterface $dt) {
		return $this->carbon->gt($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function gte(DatetimeInterface $dt) {
		return $this->carbon->gte($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lt(DatetimeInterface $dt) {
		return $this->carbon->lt($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lte(DatetimeInterface $dt) {
		return $this->carbon->lte($dt);
	}

	/**
	 * {@inheritDoc}
	*/
	public function between(DatetimeInterface $dt1, DatetimeInterface $dt2, $equal=true) {
		return $this->carbon->between($dt1, $dt2, $equal);
	}

	/**
	 * {@inheritDoc}
	 */
	public function min(DatetimeInterface $dt=null) {
		return $this->carbon->min($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function max(DatetimeInterface $dt=null) {
		return $this->carbon->max($dt);
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
		return $this->carbon->isSameDay($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addYears($value) {
		return $this->carbon->addYears($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addYear() {
		return $this->carbon->addYear();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subYear() {
		return $this->carbon->subYear();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subYears($value) {
		return $this->carbon->subYears($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMonths($value) {
		return $this->carbon->addMonths($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMonth() {
		return $this->carbon->addMonth();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMonth() {
		return $this->carbon->subMonth();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMonths($value) {
		return $this->carbon->subMonths($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDays($value) {
		return $this->carbon->addDays($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addDay() {
		return $this->carbon->addDay();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subDay() {
		return $this->carbon->subDay();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subDays($value) {
		return $this->carbon->subDays($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeekdays($value) {
		return $this->carbon->addWeekdays($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeekday() {
		return $this->carbon->addWeekday();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeekday() {
		return $this->carbon->subWeekday();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeekdays($value) {
		return $this->carbon->subWeekdays($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeeks($value) {
		return $this->carbon->addWeeks($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addWeek() {
		return $this->carbon->addWeek();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeek() {
		return $this->carbon->subWeek();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subWeeks($value) {
		return $this->carbon->subWeeks($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addHours($value) {
		return $this->carbon->addHours($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addHour() {
		return $this->carbon->addHour();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subHour() {
		return $this->carbon->subHour();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subHours($value) {
		return $this->carbon->subHours($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMinutes($value) {
		return $this->carbon->addMinutes($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addMinute() {
		return $this->carbon->addMinute();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMinute() {
		return $this->carbon->subMinute();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subMinutes($value) {
		return $this->carbon->subMinutes($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addSeconds($value) {
		return $this->carbon->addSeconds($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addSecond() {
		return $this->carbon->addSecond();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subSecond() {
		return $this->carbon->subSecond();
	}

	/**
	 * {@inheritDoc}
	 */
	public function subSeconds($value) {
		return $this->carbon->subSeconds($value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInYears(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInYears($dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInMonths(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInMonths($dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInWeeks(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInWeeks($dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInDays(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInDays($dt, $abs);
	}

	 /**
	 * {@inheritDoc}
	 */
	 public function diffInDaysFiltered(Closure $callback, DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInDaysFiltered($callback, $dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	 public function diffInWeekdays(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInWeekdays($dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	 public function diffInWeekendDays(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInWeekendDays($dt, $abs);
	}

	/**
	 */
	public function diffInHours(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInHours($dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInMinutes(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInMinutes($dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffInSeconds(DatetimeInterface $dt=null, $abs=true) {
		return $this->carbon->diffInSeconds($dt, $abs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function diffForHumans(DatetimeInterface $other=null) {
		return $this->carbon->diffForHumans($other);
	}

	/**
	 * {@inheritDoc}
	 */
	public function startOfDay() {
		return $this->carbon->startOfDay();
	}

	/**
	 * {@inheritDoc}
	 */
	public function endOfDay() {
		return $this->carbon->endOfDay();
	}

	/**
	 * {@inheritDoc}
	 */
	public function startOfMonth() {
		return $this->carbon->startOfMonth();
	}

	/**
	 * {@inheritDoc}
	 */
	public function endOfMonth() {
		return $this->carbon->endOfMonth();
	}

	/**
	 * {@inheritDoc}
	 */
	public function startOfYear() {
		return $this->carbon->startOfYear();
	}

	/**
	 * {@inheritDoc}
	 */
	 public function endOfYear() {
		return $this->carbon->endOfYear();
	}

	/**
	 * {@inheritDoc}
	 */
	 public function startOfDecade() {
		return $this->carbon->startOfDecade();
	}

	/**
	 * {@inheritDoc}
	 */
	 public function endOfDecade() {
		return $this->carbon->endOfDecade();
	}

	/**
	 * {@inheritDoc}
	 */
	 public function startOfCentury() {
		return $this->carbon->startOfCentury();
	}

	/**
	 * {@inheritDoc}
	 */
	 public function endOfCentury() {
		return $this->carbon->endOfCentury();
	}

	/**
	 */
	 public function startOfWeek() {
		return $this->carbon->startOfWeek();
	}

	 /**
	 * {@inheritDoc}
	 */
	 public function endOfWeek() {
		return $this->carbon->endOfWeek();
	}

	/**
	 * {@inheritDoc}
	 */
	public function next($dayOfWeek=null) {
		return $this->carbon->next($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function previous($dayOfWeek=null) {
		return $this->carbon->previous($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function firstOfMonth($dayOfWeek=null) {
		return $this->carbon->firstOfMonth($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastOfMonth($dayOfWeek=null) {
		return $this->carbon->lastOfMonth($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function nthOfMonth($nth, $dayOfWeek) {
		return $this->carbon->nthOfMonth($nth, $dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function firstOfQuarter($dayOfWeek=null) {
		return $this->carbon->firstOfQuarter($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastOfQuarter($dayOfWeek=null) {
		return $this->carbon->lastOfQuarter($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function nthOfQuarter($nth, $dayOfWeek) {
		return $this->carbon->nthOfQuarter($nth, $dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function firstOfYear($dayOfWeek=null) {
		return $this->carbon->firstOfYear($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastOfYear($dayOfWeek=null) {
		return $this->carbon->lastOfYear($dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function nthOfYear($nth, $dayOfWeek) {
		return $this->carbon->nthOfYear($nth, $dayOfWeek);
	}

	/**
	 * {@inheritDoc}
	 */
	public function average(DatetimeInterface $dt=null) {
		return $this->carbon->average($dt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function isBirthday(DatetimeInterface $dt) {
		return $this->carbon->isBirthday($dt);
	}
}