


	function ability_modifier (score) {
		let mod = Math.floor((score - 10) / 2);
		return (mod > 0 ? "+" : "") + mod;
	}
	
	function skill_save_modifier (score, proficiency, bonus) {
		let mod = Math.floor((score - 10) / 2)+(proficiency*bonus);
		return (mod > 0 ? "+" : "") + mod;
	}
