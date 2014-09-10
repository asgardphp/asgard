<?php
namespace Asgard\Debug;

#using static calls to make debugging available anywhere in the code

/**
 * Debugging utils.
 */
class Debug {
	/**
	 * Url object to link to javascript files.
	 * @var \Asgard\Http\URL
	 */
	protected static $url;

	/**
	 * Create the backtrace and forward it to dWithTrace.
	 */
	public static function d() {
		$args = func_get_args();
		static::dWithTrace(array_merge([debug_backtrace()], $args));
	}

	/**
	 * Print out the backtrace and the given arguments.
	 * @param  array  $trace
	 */
	public static function dWithTrace(array $trace) {
		#clear all active buffers
		while(ob_get_length())
			ob_end_clean();
		
		if(php_sapi_name() != 'cli')
			echo '<pre>';
		foreach(array_slice(func_get_args(), 1) as $arg)
			var_dump($arg);
		if(php_sapi_name() != 'cli')
			echo '</pre>';
		
		die(static::getReport($trace));
	}

	/**
	 * Set the URL dependency.
	 * @param \Asgard\Http\URL $url
	 */
	public static function setURL($url) {
		static::$url = $url;
	}

	/**
	 * Return the HTML or CLI debug report.
	 * @param  array  $backtrace
	 * @return string
	 */
	public static function getReport(array $backtrace) {
		$request = \Asgard\Http\Request::singleton();

		$r = '';
		if(php_sapi_name() === 'cli')
			$r .= static::getCLIBacktrace($backtrace);
		else {
			$r .= static::getHTMLBacktrace($request, $backtrace);
			$r .= static::getHTMLRequest($request);
		}
		return $r;
	}
	
	/**
	 * Format the backtrace in HTML.
	 * @param  \Asgard\Http\Request $request
	 * @param  array                $backtrace
	 * @return string
	 */
	public static function getHTMLBacktrace(\Asgard\Http\Request $request, $backtrace=null) {
		if(!$backtrace)
			$backtrace = debug_backtrace();

		$r = '<p><b>Backtrace</b></p>'."\n";

		#Javascript
		$jquery = $request->url->to('js/jquery.js');
		$r .= <<<EOT
<script src="$jquery"></script>
<style>
pre { display:inline; }
.toggle { cursor:pointer; }
.current_line { display:inline-block; }
</style>
<script>
$(function(){
	$('.toggle').click(function(e) {
		if($(e.currentTarget).parent().find('div').first().css('display') == 'block') {
			$(e.currentTarget).parent().find('div').first().css('display', 'none');
			$(e.currentTarget).find('span').text('+');
		}
		else {
			$(e.currentTarget).parent().find('div').first().css('display', 'block');
			$(e.currentTarget).find('span').text('-');
		}
	});

	$('li pre').each(function() {
		var e = $(this);
		var isShort = true;
		var longText = e.text();
		var shortText = longText.split("\\n")[0];
		if(shortText.length < longText.length)
			shortText += '...';
		e.text(shortText);
		e.click(function() {
			if(isShort)
				e.text(longText);
			else
				e.text(shortText);
			isShort = !isShort;
		});
	});
});
</script>
EOT;

		#Backtrace
		for($i=0; $i<count($backtrace); $i++) {
			$trace = $backtrace[$i];
			if(isset($backtrace[$i+1]))
				$next = $backtrace[$i+1];
			else
				$next = $backtrace[count($backtrace)-1];
			
			#Links
			if(isset($trace['file'])) {
				$url = static::$url;
				$url = str_replace('%file%', $trace['file'], $url);
				$url = str_replace('%line%', $trace['line'], $url);
				$r .= '<a href="'.$url.'">'.$trace['file'].'</a> ('.$trace['line'].')';
			}

			#Class
			if(isset($next['class']))
				$r .= ' at '.$next['class'].(isset($next['function']) ? $next['type'].$next['function'].'()':'');

			#Function
			elseif(isset($next['function']))
				$r .= ' at '.$next['function'];
			$r .= "<br>\n";

			#Arguments
			if(isset($next['args']) && count($next['args']) > 0) {
				$r .= '<div><span class="toggle"><span>+</span>Args:</span>'."<br>\n";
				$r .= '<div style="display:none"><ul>';
				foreach($next['args'] as $arg) {
					$r .= '<li>';
					$r .= '<pre>'.static::var_dump_to_string($arg).'</pre>';
					$r .= "</li>\n";
				}
				$r .= '</ul></div>';
				$r .= '</div>';
			}
			
			#Code snippet
			if(isset($trace['line'])) {
				$start = $trace['line']-5-1;
				$start = $start < 1 ? 1:$start;
				$pos = $trace['line']-$start;

				if(file_exists($trace['file']))
					$r .= static::getCode($trace['file'], $start, 11, $pos);
			}
			
			$r .= '<hr/>';
		}

		return $r;
	}

	/**
	 * Return the code extract.
	 * @param  string  $file   file path
	 * @param  integer $offset from line
	 * @param  integer $limit  number of lines
	 * @param  integer $pos    line position
	 * @return string
	 */
	protected static function getCode($file, $offset, $limit, $pos) {
		ob_start();
		highlight_string(file_get_contents($file));
		$code = ob_get_contents();
		ob_end_clean();
		$code = explode('<br />', $code);
		$code = array_slice($code, $offset, $limit);
		
		if($code) {
			$r = '<div><span class="toggle"><span>+</span>Code:</span>'."<br>\n";
			$r .= '<div style="display:none"><code>';
			foreach($code as $k=>$line) {
				if($pos == $k+1) {
					$r .= '<span style="float:left; display:inline-block; width:50px; color:#000">'.($offset+$k+1).'</span>
					<div class="current_line" style="display:inline-block; background-color:#ccc;">'.$line.'</div><br>';
				}
				else
					$r .= '<span style="float:left; display:inline-block; width:50px; color:#000">'.($offset+$k+1).'</span>'.$line.'<br>';
			}
			$r .= '</code></div></div>';
			return $r;
		}
	}
	
	/**
	 * Return the backtrace formatted for CLI.
	 * @param  array $backtrace
	 * @return string
	 */
	public static function getCLIBacktrace($backtrace=null) {
		if(!$backtrace)
			$backtrace = debug_backtrace();
		
		$r = '';
		for($i=0; $i<count($backtrace); $i++) {
			$trace = $backtrace[$i];
			
			if(isset($trace['file']))
				$r .= $trace['file'].':'.$trace['line']."\n";
		}

		return $r;
	}

	/**
	 * Return the request formatted for HTML.
	 * @param  \Asgard\Http\Request $r
	 * @return string
	 */
	public static function getHTMLRequest(\Asgard\Http\Request $r) {
		$res = '<b>Request</b><br>';
		$res .= '<div>';
		$res .= static::inputs($r, 'get', 'GET');
		$res .= static::inputs($r, 'post', 'POST');
		$res .= static::inputs($r, 'file', 'FILES');
		$res .= static::inputs($r, 'cookie', 'COOKIES');
		$res .= static::inputs($r, 'session', 'SESSION');
		$res .= static::inputs($r, 'server', 'SERVER');
		$res .= '</div>';
		return $res;
	}

	/**
	 * Return the inputs formatted for HTML.
	 * @param  \Asgard\Http\Request $r
	 * @param  string $input        request input name
	 * @param  string $name         php input name
	 * @return string
	 */
	protected static function inputs(\Asgard\Http\Request $r, $input, $name) {
		if($r->$input->size()) {
			$res = '<div><span class="toggle"><span>+</span>'.$name.':</span>';
			$res .= '<div style="display:none"><ul>';
			foreach($r->get->all() as $k=>$v) {
				$res .= '<li>'.$k.': ';
				$str = static::var_dump_to_string($v);
				$res .= '<pre>'.$str.'</pre>';
				$res .= '</li>';
			}
			$res .= '</ul></div></div>';
			return $res;
		}
	}

	/**
	 * Return var_dump output.
	 * @param  mixed $var
	 * @return string
	 */
	protected static function var_dump_to_string($var) {
		if(is_string($var))
			return $var;

		ob_start();
		var_dump($var);
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}
}