<?php
namespace Coxis\Core;

class Error {
	public static function report($msg, $backtrace=null) {
		set_error_handler(function(){});
	
		if(ob_get_length() > 0)
			ob_end_clean();
	
		\Response::setCode(500);
				
		ob_start();
		
		if($msg) {
			echo '<b>Message</b><br/>'."\n";
			echo $msg."<br/>\n<br/>\n";
		}
		static::print_backtrace($msg, $backtrace);
	
		$result = ob_get_contents();
		ob_end_clean();
		
		\Coxis\Utils\Log::add('errors/log.html', $result);
		
		if(\Config::get('error_display') || \Config::get('error_display') === null)
			return \Response::setHeader('Content-Type', 'text/html')->setContent($result);
		else
			return \Response::setHeader('Content-Type', 'text/html')->setContent('<h1>Error</h1>Oops, something went wrong. Please report it to the administrator.');
	}
	
	public static function print_backtrace($msg='', $backtrace=null) {
		if(!$backtrace)
			$backtrace = debug_backtrace();
			
		if(php_sapi_name() == 'cli') {
			for($i=0; $i<sizeof($backtrace); $i++) {
				$trace = $backtrace[$i];
				if(isset($backtrace[$i+1]))
					$next = $backtrace[$i+1];
				else
					$next = $backtrace[sizeof($backtrace)-1];
				
				if(isset($trace['file']))
					echo 'File:  '.$trace['file'].' ('.$trace['line'].')'."\n";
			}
		}
		else {
			echo '<b>Backtrace</b><br/>'."\n";
			?>
			<script src="<?php echo \URL::to('js/jquery.js') ?>"></script>
			<style>
			.spanargs {
				cursor:pointer;
			}
			.current_line {
				display:inline-block;
			}
			</style>
			<script>
			$(function(){
				$('.spanargs').unbind("click").click(function(e){//todo WTF?
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
					var shortText = longText.split("\n")[0];
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
			<?php
			for($i=0; $i<sizeof($backtrace); $i++) {
				$trace = $backtrace[$i];
				if(isset($backtrace[$i+1]))
					$next = $backtrace[$i+1];
				else
					$next = $backtrace[sizeof($backtrace)-1];
				
				if(isset($trace['file']))
					echo 'File:  <a href="code:'.$trace['file'].':'.$trace['line'].'">'.$trace['file'].'</a> ('.$trace['line'].')'."<br/>\n";
				if(isset($next['class']))
					echo 'At: '.$next['class'].$next['type'].$next['function']."()<br/>\n";
				else
					echo 'At: '.$next['function']."()<br/>\n";

				if(sizeof($next['args']) > 0) {
					echo '<div><span class="spanargs"><span>+</span>Args:</span>'."<br/>\n";
					echo '<div style="display:none"><ul>';
					foreach($next['args'] as $arg) {
						echo '<li>';
						if(is_array($arg))
							$str = \Coxis\Utils\Tools::var_dump_to_string($arg);
						elseif(is_string($arg))
							$str = $arg;
						else
							$str = \Coxis\Utils\Tools::var_dump_to_string($arg);
						echo '<pre>'.$str.'</pre>';
						echo "</li>\n";
					}
					echo '</ul></div>';
					echo '</div>';
				}
				
				if(isset($trace['line'])) {
					$start = $trace['line']-3;
					if($start < 1)
						$start = 1;
					$end = $trace['line']+3;
					
					$pos = $trace['line']-$start;
					
					$code = '';

					ob_start();
					highlight_string(file_get_contents($trace['file']));
					$code = ob_get_contents();

					ob_end_clean();
					$code = explode('<br />', $code);
					$code = array_slice($code, $start, 7);
					
					if($code) {
						echo '<div><span class="spanargs"><span>+</span>Code:</span>'."<br/>\n";
						echo '<div style="display:none">';
						echo '<code>';
						foreach($code as $k=>$line)
							if($pos == $k+1)
								echo '<span style="float:left; display:inline-block; width:50px; color:#000">'.($start++).'</span>'.'<div class="current_line" style="display:inline-block; background-color:#ccc;">'.$line.'</div><br>';
							else
								echo '<span style="float:left; display:inline-block; width:50px; color:#000">'.($start++).'</span>'.$line.'<br>';
						echo '</code>';
						echo '</div>';
						echo '</div>';
					}
				}
				
				echo "<br/>\n";
				echo '<hr/>';
				echo "<br/>\n";
			}
		}
	}
}
