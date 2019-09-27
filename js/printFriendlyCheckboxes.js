const realCheckboxes = document.querySelectorAll('input[type="checkbox"],input[type="radio"]');
realCheckboxes.forEach((checkbox) => {
  checkbox.classList.add('noprint');
  const fakeCheckbox = document.createElement('div');
  fakeCheckbox.classList.add('onlyprint', 'fake-checkbox');
  checkbox.parentNode.appendChild(fakeCheckbox);
});