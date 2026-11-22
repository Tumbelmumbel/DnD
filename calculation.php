<?php
declare(strict_types=1);

	function ability_modifier ($score) {
	    $mod = floor(($score-10)/2);
		return ($mod > 0 ? "+" :"") . $mod;
	}

?>