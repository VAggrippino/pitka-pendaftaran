function showOtherField(trigger, value, fieldId) {
  const otherField = document.getElementById(fieldId);
  if ( value === trigger.value ) {
    otherField.classList.remove(`hidden`);
    otherField.querySelectorAll(`input`).forEach((field) => {
      field.disabled = false;
    });
  } else {
    otherField.classList.add(`hidden`);
    otherField.querySelectorAll(`input`).forEach((field) => {
      field.disabled = true;
    });
  }
}