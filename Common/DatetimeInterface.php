<?php
namespace Asgard\Common;

use Carbon\Carbon;

/**
 * Date.
 * API from Carbon\Carbon.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface DatetimeInterface {
	#START: \DateTimeInterface, only in PHP >= 5.5.0
	
	/**
	 * Return the difference.
	 * @param  $datetime2
	 * @param  boolean    $absolute
	 * @return \DateInterval
	 */
	public function diff($datetime2, $absolute);
	/**
	 *  Subtracts an amount of days, months, years, hours, minutes and seconds from a DateTime object.
	 * @param  $interval
	 * @return \DateTime
	 */
	public function sub($interval);
	/**
	 * Return a formatted string.
	 * @param  string $format
	 * @return string
	 */
	public function format($format);
	/**
	 * Return the offset.
	 * @return int
	 */
	public function getOffset();
	/**
	 * Return the timestamp.
	 * @return int
	 */
	public function getTimestamp();
	/**
	 * Return the timezone.
	 * @return \DateTimeZone
	 */
	public function getTimezone();
	/**
	 * __wakeup magic method.
	 */
	public function __wakeup();

	#END \DateTimeInterface

	/**
	 * Create a DatetimeInterface instance from a DateTime one
	 *
	 * @param \DateTime $dt
	 *
	 * @return static
	 */
	public static function instance(\DateTime $dt);
	
	/**
	 * Create a DatetimeInterface instance from a string.  This is an alias for the
	 * constructor that allows better fluent syntax as it allows you to do
	 * DatetimeInterface::parse('Monday next week')->fn() rather than
	 * (new Datetime('Monday next week'))->fn()
	 *
	 * @param string              $time
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function parse($time=null, $tz=null);

	/**
	 * Get a DatetimeInterface instance for the current date and time
	 *
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function now($tz=null);

	/**
	 * Create a DatetimeInterface instance for today
	 *
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function today($tz=null);

	/**
	 * Create a DatetimeInterface instance for tomorrow
	 *
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function tomorrow($tz=null);

	/**
	 * Create a DatetimeInterface instance for yesterday
	 *
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function yesterday($tz=null);

	/**
	 * Create a DatetimeInterface instance for the greatest supported date.
	 *
	 * @return static
	 */
	public static function maxValue();

	/**
	 * Create a DateTimeInterface instance for the lowest supported date.
	 *
	 * @return static
	 */
	public static function minValue();

	/**
	 * Create a new DatetimeInterface instance from a specific date and time.
	 *
	 * If any of $year, $month or $day are set to null their now() values
	 * will be used.
	 *
	 * If $hour is null it will be set to its now() value and the default values
	 * for $minute and $second will be their now() values.
	 * If $hour is not null then the default values for $minute and $second
	 * will be 0.
	 *
	 * @param integer             $year
	 * @param integer             $month
	 * @param integer             $day
	 * @param integer             $hour
	 * @param integer             $minute
	 * @param integer             $second
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function create($year=null, $month=null, $day=null, $hour=null, $minute=null, $second=null, $tz=null);

	/**
	 * Create a DatetimeInterface instance from just a date. The time portion is set to now.
	 *
	 * @param integer             $year
	 * @param integer             $month
	 * @param integer             $day
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function createFromDate($year=null, $month=null, $day=null, $tz=null);

	/**
	 * Create a DatetimeInterface instance from just a time. The date portion is set to today.
	 *
	 * @param integer             $hour
	 * @param integer             $minute
	 * @param integer             $second
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function createFromTime($hour=null, $minute=null, $second=null, $tz=null);

	/**
	 * Create a DatetimeInterface instance from a specific format
	 *
	 * @param string              $format
	 * @param string              $time
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 *
	 * @throws InvalidArgumentException
	 */
	public static function createFromFormat($format, $time, $tz=null);

	/**
	 * Create a DatetimeInterface instance from a timestamp
	 *
	 * @param integer             $timestamp
	 * @param \DateTimeZone|string $tz
	 *
	 * @return static
	 */
	public static function createFromTimestamp($timestamp, $tz=null);

	/**
	 * Create a DatetimeInterface instance from an UTC timestamp
	 *
	 * @param integer $timestamp
	 *
	 * @return static
	 */
	public static function createFromTimestampUTC($timestamp);

	/**
	 * Reset the format used to the default when type juggling a DatetimeInterface instance to a string
	 *
	 */
	public static function resetToStringFormat();

	/**
	 * Set the default format used when type juggling a DatetimeInterface instance to a string
	 *
	 * @param string $format
	 */
	public static function setToStringFormat($format);

	/**
	 * Get a copy of the instance
	 *
	 * @return static
	 */
	public function copy();

	/**
	 * Get a part of the DatetimeInterface object
	 *
	 * @param string $name
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return string|integer|\DateTimeZone
	 */
	public function __get($name);

	/**
	 * Check if an attribute exists on the object
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function __isset($name);

	/**
	 * Set a part of the DatetimeInterface object
	 *
	 * @param string                      $name
	 * @param string|integer|DateTimeZone $value
	 *
	 * @throws InvalidArgumentException
	 */
	public function __set($name, $value);

	/**
	 * Set the instance's year
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function year($value);

	/**
	 * Set the instance's month
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function month($value);

	/**
	 * Set the instance's day
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function day($value);

	/**
	 * Set the date all together
	 *
	 * @param integer $year
	 * @param integer $month
	 * @param integer $day
	 *
	 * @return static
	 */
	public function setDate($year, $month, $day);

	/**
	 * Set the instance's hour
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function hour($value);

	/**
	 * Set the instance's minute
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function minute($value);

	/**
	 * Set the instance's second
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function second($value);

	/**
	 * Set the time all together
	 *
	 * @param integer $hour
	 * @param integer $minute
	 * @param integer $second
	 *
	 * @return static
	 */
	public function setTime($hour, $minute, $second);

	/**
	 * Set the date and time all together
	 *
	 * @param integer $year
	 * @param integer $month
	 * @param integer $day
	 * @param integer $hour
	 * @param integer $minute
	 * @param integer $second
	 *
	 * @return static
	 */
	public function setDateTime($year, $month, $day, $hour, $minute, $second);

	/**
	 * Set the instance's timestamp
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function timestamp($value);

	/**
	 * Alias for setTimezone()
	 *
	 * @param DateTimeZone|string $value
	 *
	 * @return static
	 */
	public function timezone($value);

	/**
	 * Alias for setTimezone()
	 *
	 * @param DateTimeZone|string $value
	 *
	 * @return static
	 */
	public function tz($value);

	/**
	 * Set the instance's timezone from a string or object
	 *
	 * @param DateTimeZone|string $value
	 *
	 * @return static
	 */
	public function setTimezone($value);

	/**
	 * Format the instance with the current locale.  You can set the current
	 * locale using setlocale() http://php.net/setlocale.
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function formatLocalized($format);

	/**
	 * Format the instance as a string using the set format
	 *
	 * @return string
	 */
	public function __toString();

	/**
	 * Format the instance as date
	 *
	 * @return string
	 */
	public function toDateString();

	/**
	 * Format the instance as a readable date
	 *
	 * @return string
	 */
	public function toFormattedDateString();

	/**
	 * Format the instance as time
	 *
	 * @return string
	 */
	public function toTimeString();

	/**
	 * Format the instance as date and time
	 *
	 * @return string
	 */
	public function toDateTimeString();

	/**
	 * Format the instance with day, date and time
	 *
	 * @return string
	 */
	public function toDayDateTimeString();

	/**
	 * Format the instance as ATOM
	 *
	 * @return string
	 */
	public function toAtomString();

	/**
	 * Format the instance as COOKIE
	 *
	 * @return string
	 */
	public function toCookieString();

	/**
	 * Format the instance as ISO8601
	 *
	 * @return string
	 */
	public function toIso8601String();

	/**
	 * Format the instance as RFC822
	 *
	 * @return string
	 */
	public function toRfc822String();

	/**
	 * Format the instance as RFC850
	 *
	 * @return string
	 */
	public function toRfc850String();

	/**
	 * Format the instance as RFC1036
	 *
	 * @return string
	 */
	public function toRfc1036String();

	/**
	 * Format the instance as RFC1123
	 *
	 * @return string
	 */
	public function toRfc1123String();

	/**
	 * Format the instance as RFC2822
	 *
	 * @return string
	 */
	public function toRfc2822String();

	/**
	 * Format the instance as RFC3339
	 *
	 * @return string
	 */
	public function toRfc3339String();

	/**
	 * Format the instance as RSS
	 *
	 * @return string
	 */
	public function toRssString();

	/**
	 * Format the instance as W3C
	 *
	 * @return string
	 */
	public function toW3cString();

	/**
	 * Determines if the instance is equal to another
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return boolean
	 */
	public function eq(DatetimeInterface $dt);

	/**
	 * Determines if the instance is not equal to another
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return boolean
	 */
	public function ne(DatetimeInterface $dt);

	/**
	 * Determines if the instance is greater (after) than another
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return boolean
	 */
	public function gt(DatetimeInterface $dt);

	/**
	 * Determines if the instance is greater (after) than or equal to another
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return boolean
	 */
	public function gte(DatetimeInterface $dt);

	/**
	 * Determines if the instance is less (before) than another
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return boolean
	 */
	public function lt(DatetimeInterface $dt);

	/**
	 * Determines if the instance is less (before) or equal to another
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return boolean
	 */
	public function lte(DatetimeInterface $dt);

	/**
	* Determines if the instance is between two others
	*
	* @param  DatetimeInterface  $dt1
	* @param  DatetimeInterface  $dt2
	* @param  boolean $equal  Indicates if a > and < comparison should be used or <= or >=
	*
	* @return boolean
	*/
	public function between(DatetimeInterface $dt1, DatetimeInterface $dt2, $equal);

	/**
	 * Get the minimum instance between a given instance (default now) and the current instance.
	 * @param DatetimeInterface $dt
	 *
	 * @return static
	 */
	public function min(DatetimeInterface $dt);

	/**
	 * Get the maximum instance between a given instance (default now) and the current instance.
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return static
	 */
	public function max(DatetimeInterface $dt);

	/**
	 * Determines if the instance is a weekday
	 *
	 * @return boolean
	 */
	public function isWeekday();

	/**
	 * Determines if the instance is a weekend day
	 *
	 * @return boolean
	 */
	public function isWeekend();

	/**
	 * Determines if the instance is yesterday
	 *
	 * @return boolean
	 */
	public function isYesterday();

	/**
	 * Determines if the instance is today
	 *
	 * @return boolean
	 */
	public function isToday();

	/**
	 * Determines if the instance is tomorrow
	 *
	 * @return boolean
	 */
	public function isTomorrow();

	/**
	 * Determines if the instance is in the future, ie. greater (after) than now
	 *
	 * @return boolean
	 */
	public function isFuture();

	/**
	 * Determines if the instance is in the past, ie. less (before) than now
	 *
	 * @return boolean
	 */
	public function isPast();

	/**
	 * Determines if the instance is a leap year
	 *
	 * @return boolean
	 */
	public function isLeapYear();

	/**
	 * Checks if the passed in date is the same day as the instance current day.
	 *
	 * @param  DatetimeInterface  $dt
	 * @return boolean
	 */
	public function isSameDay(DatetimeInterface $dt);

	/**
	 * Add years to the instance. Positive $value travel forward while
	 * negative $value travel into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addYears($value);

	/**
	 * Add a year to the instance
	 *
	 * @return static
	 */
	public function addYear();

	/**
	 * Remove a year from the instance
	 *
	 * @return static
	 */
	public function subYear();

	/**
	 * Remove years from the instance.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subYears($value);

	/**
	 * Add months to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addMonths($value);

	/**
	 * Add a month to the instance
	 *
	 * @return static
	 */
	public function addMonth();

	/**
	 * Remove a month from the instance
	 *
	 * @return static
	 */
	public function subMonth();

	/**
	 * Remove months from the instance
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subMonths($value);

	/**
	 * Add days to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addDays($value);

	/**
	 * Add a day to the instance
	 *
	 * @return static
	 */
	public function addDay();

	/**
	 * Remove a day from the instance
	 *
	 * @return static
	 */
	public function subDay();

	/**
	 * Remove days from the instance
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subDays($value);

	/**
	 * Add weekdays to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addWeekdays($value);

	/**
	 * Add a weekday to the instance
	 *
	 * @return static
	 */
	public function addWeekday();

	/**
	 * Remove a weekday from the instance
	 *
	 * @return static
	 */
	public function subWeekday();

	/**
	 * Remove weekdays from the instance
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subWeekdays($value);

	/**
	 * Add weeks to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addWeeks($value);

	/**
	 * Add a week to the instance
	 *
	 * @return static
	 */
	public function addWeek();

	/**
	 * Remove a week from the instance
	 *
	 * @return static
	 */
	public function subWeek();

	/**
	 * Remove weeks to the instance
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subWeeks($value);

	/**
	 * Add hours to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addHours($value);

	/**
	 * Add an hour to the instance
	 *
	 * @return static
	 */
	public function addHour();

	/**
	 * Remove an hour from the instance
	 *
	 * @return static
	 */
	public function subHour();

	/**
	 * Remove hours from the instance
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subHours($value);

	/**
	 * Add minutes to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addMinutes($value);

	/**
	 * Add a minute to the instance
	 *
	 * @return static
	 */
	public function addMinute();

	/**
	 * Remove a minute from the instance
	 *
	 * @return static
	 */
	public function subMinute();

	/**
	 * Remove minutes from the instance
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subMinutes($value);

	/**
	 * Add seconds to the instance. Positive $value travels forward while
	 * negative $value travels into the past.
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function addSeconds($value);

	/**
	 * Add a second to the instance
	 *
	 * @return static
	 */
	public function addSecond();

	/**
	 * Remove a second from the instance
	 *
	 * @return static
	 */
	public function subSecond();

	/**
	 * Remove seconds from the instance
	 *
	 * @param integer $value
	 *
	 * @return static
	 */
	public function subSeconds($value);

	/**
	 * Get the difference in years
	 *
	 * @param DatetimeInterface  $dt
	 * @param boolean $abs Get the absolute of the difference
	 *
	 * @return integer
	 */
	public function diffInYears(DatetimeInterface $dt, $abs);

	/**
	 * Get the difference in months
	 *
	 * @param DatetimeInterface  $dt
	 * @param boolean $abs Get the absolute of the difference
	 *
	 * @return integer
	 */
	public function diffInMonths(DatetimeInterface $dt, $abs);

	/**
	 * Get the difference in weeks
	 *
	 * @param DatetimeInterface  $dt
	 * @param boolean $abs Get the absolute of the difference
	 *
	 * @return integer
	 */
	public function diffInWeeks(DatetimeInterface $dt, $abs);

	/**
	 * Get the difference in days
	 *
	 * @param DatetimeInterface  $dt
	 * @param boolean $abs Get the absolute of the difference
	 *
	 * @return integer
	 */
	public function diffInDays(DatetimeInterface $dt, $abs);

	 /**
	  * Get the difference in days using a filter closure
	  *
	  * @param Closure $callback
	  * @param DatetimeInterface  $dt
	  * @param boolean $abs      Get the absolute of the difference
	  *
	  * @return int
	  */
	 public function diffInDaysFiltered(Closure $callback, DatetimeInterface $dt, $abs);

	 /**
	  * Get the difference in weekdays
	  *
	  * @param DatetimeInterface  $dt
	  * @param boolean $abs Get the absolute of the difference
	  *
	  * @return int
	  */
	 public function diffInWeekdays(DatetimeInterface $dt, $abs);

	 /**
	  * Get the difference in weekend days using a filter
	  *
	  * @param DatetimeInterface  $dt
	  * @param boolean $abs Get the absolute of the difference
	  *
	  * @return int
	  */
	 public function diffInWeekendDays(DatetimeInterface $dt, $abs);

	/**
	 * Get the difference in hours
	 *
	 * @param DatetimeInterface  $dt
	 * @param boolean $abs Get the absolute of the difference
	 *
	 * @return integer
	 */
	public function diffInHours(DatetimeInterface $dt, $abs);

	/**
	 * Get the difference in minutes
	 *
	 * @param DatetimeInterface  $dt
	 * @param boolean $abs Get the absolute of the difference
	 *
	 * @return integer
	 */
	public function diffInMinutes(DatetimeInterface $dt, $abs);

	/**
	 * Get the difference in seconds
	 *
	 * @param DatetimeInterface  $dt
	 * @param boolean $abs Get the absolute of the difference
	 *
	 * @return integer
	 */
	public function diffInSeconds(DatetimeInterface $dt, $abs);

	/**
	 * Get the difference in a human readable format.
	 *
	 * When comparing a value in the past to default now:
	 * 1 hour ago
	 * 5 months ago
	 *
	 * When comparing a value in the future to default now:
	 * 1 hour from now
	 * 5 months from now
	 *
	 * When comparing a value in the past to another value:
	 * 1 hour before
	 * 5 months before
	 *
	 * When comparing a value in the future to another value:
	 * 1 hour after
	 * 5 months after
	 *
	 * @param DatetimeInterface $other
	 *
	 * @return string
	 */
	public function diffForHumans(DatetimeInterface $other);

	/**
	 * Resets the time to 00:00:00
	 *
	 * @return static
	 */
	public function startOfDay();

	/**
	 * Resets the time to 23:59:59
	 *
	 * @return static
	 */
	public function endOfDay();

	/**
	 * Resets the date to the first day of the month and the time to 00:00:00
	 *
	 * @return static
	 */
	public function startOfMonth();

	/**
	 * Resets the date to end of the month and time to 23:59:59
	 *
	 * @return static
	 */
	public function endOfMonth();

	 /**
	  * Resets the date to the first day of the year and the time to 00:00:00
	  *
	  * @return static
	  */
	public function startOfYear();

	 /**
	  * Resets the date to end of the year and time to 23:59:59
	  *
	  * @return static
	  */
	 public function endOfYear();

	 /**
	  * Resets the date to the first day of the decade and the time to 00:00:00
	  *
	  * @return static
	  */
	 public function startOfDecade();

	 /**
	  * Resets the date to end of the decade and time to 23:59:59
	  *
	  * @return static
	  */
	 public function endOfDecade();

	 /**
	  * Resets the date to the first day of the century and the time to 00:00:00
	  *
	  * @return static
	  */
	 public function startOfCentury();

	/**
	  * Resets the date to end of the century and time to 23:59:59
	  *
	  * @return static
	  */
	 public function endOfCentury();

	/**
	 * Resets the date to the first day of the ISO-8601 week (Monday) and the time to 00:00:00
	 *
	 * @return static
	 */
	 public function startOfWeek();

	 /**
	  * Resets the date to end of the ISO-8601 week (Sunday) and time to 23:59:59
	  *
	  * @return static
	  */
	 public function endOfWeek();

	/**
	 * Modify to the next occurance of a given day of the week.
	 * If no dayOfWeek is provided, modify to the next occurance
	 * of the current day of the week.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function next($dayOfWeek);

	/**
	 * Modify to the previous occurance of a given day of the week.
	 * If no dayOfWeek is provided, modify to the previous occurance
	 * of the current day of the week.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function previous($dayOfWeek);

	/**
	 * Modify to the first occurance of a given day of the week
	 * in the current month. If no dayOfWeek is provided, modify to the
	 * first day of the current month.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function firstOfMonth($dayOfWeek);

	/**
	 * Modify to the last occurance of a given day of the week
	 * in the current month. If no dayOfWeek is provided, modify to the
	 * last day of the current month.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function lastOfMonth($dayOfWeek);

	/**
	 * Modify to the given occurance of a given day of the week
	 * in the current month. If the calculated occurance is outside the scope
	 * of the current month, then return false and no modifications are made.
	 * Use the supplied consts to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $nth
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function nthOfMonth($nth, $dayOfWeek);

	/**
	 * Modify to the first occurance of a given day of the week
	 * in the current quarter. If no dayOfWeek is provided, modify to the
	 * first day of the current quarter.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function firstOfQuarter($dayOfWeek);

	/**
	 * Modify to the last occurance of a given day of the week
	 * in the current quarter. If no dayOfWeek is provided, modify to the
	 * last day of the current quarter.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function lastOfQuarter($dayOfWeek);

	/**
	 * Modify to the given occurance of a given day of the week
	 * in the current quarter. If the calculated occurance is outside the scope
	 * of the current quarter, then return false and no modifications are made.
	 * Use the supplied consts to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $nth
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function nthOfQuarter($nth, $dayOfWeek);

	/**
	 * Modify to the first occurance of a given day of the week
	 * in the current year. If no dayOfWeek is provided, modify to the
	 * first day of the current year.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function firstOfYear($dayOfWeek);

	/**
	 * Modify to the last occurance of a given day of the week
	 * in the current year. If no dayOfWeek is provided, modify to the
	 * last day of the current year.  Use the supplied consts
	 * to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function lastOfYear($dayOfWeek);

	/**
	 * Modify to the given occurance of a given day of the week
	 * in the current year. If the calculated occurance is outside the scope
	 * of the current year, then return false and no modifications are made.
	 * Use the supplied consts to indicate the desired dayOfWeek, ex. static::MONDAY.
	 *
	 * @param int $nth
	 * @param int $dayOfWeek
	 *
	 * @return mixed
	 */
	public function nthOfYear($nth, $dayOfWeek);

	/**
	 * Modify the current instance to the average of a given instance (default now) and the current instance.
	 *
	 * @param DatetimeInterface $dt
	 *
	 * @return static
	 */
	public function average(DatetimeInterface $dt);

	/**
	 * Check if its the birthday. Compares the date/month values of the two dates.
	 * @param  DatetimeInterface  $dt
	 * @return boolean  
	 */
	public function isBirthday(DatetimeInterface $dt);
}