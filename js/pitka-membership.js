const input = document.querySelector('.pitka-membership--filter--search');

if ( input !== null ) {
	function memberTableSearch(e) {
		const input = document.querySelector('.pitka-membership--filter--search');
		const memberTable = document.querySelector('.pitka-membership--table');
		const rows = Array.from(memberTable.querySelectorAll('tr:not(:first-child)'));

		rows.forEach((row) => {
			const cells = Array.from(row.getElementsByTagName('td'));
			const matching = cells.filter((cell) => {
				const toMatch = input.value.toLowerCase();
				const toBeMatched = cell.innerText.toLowerCase();
				return toBeMatched.indexOf(toMatch) !== -1;
			});
			if (matching.length === 0) {
				row.classList.add('hidden');
			} else {
				row.classList.remove('hidden');
			}
		});
	}

	input.addEventListener('keyup', memberTableSearch);
}