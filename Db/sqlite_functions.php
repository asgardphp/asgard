<?php
$pdo->sqliteCreateFunction('concat', function() {
	return implode('', func_get_args());
});

$pdo->sqliteCreateFunction('md5', function($a) {
	return md5($a);
}, 1);

#add more..
#http://dev.mysql.com/doc/refman/5.0/en/func-op-summary-ref.html