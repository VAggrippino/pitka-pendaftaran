function toggleField(selector, options) {
  if (selector.value === options.value) {
    document.getElementById(options.field).disabled = false;
  } else {
    document.getElementById(options.field).disabled = true;
  }
}