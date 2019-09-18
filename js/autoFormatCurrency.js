const currency_field = document.querySelector('input[data-type="currency"]');

currency_field.addEventListener( 'keyup', formatCurrency );
currency_field.addEventListener( 'blur', formatCurrency );

document.querySelector('form').addEventListener( 'submit', (e) => {
  // Process all currency input fields
  const currency_fields = document.querySelectorAll('input[data-type="currency"]');
  currency_fields.forEach((field) => {
    field.value = field.value.replace(/[^\d.]/g, '');
  });
});

function formatCurrency(e) {
  const input = e.target;
  const event_type = e.type;
  
  // If it's empty, don't do anything
  if (input.value === '') return;
  const blur = event_type === 'blur';
  const original_length = input.value.length;
  const decimal_position = input.value.indexOf('.');
  let cursor_position = input.selectionStart;
  let integer;
  let decimal;
  let formatted;
  
  if (decimal_position >= 0) {
    // Separate the integer and decimal parts and remove
    // invalid characters
    integer = formatNumber( input.value.substring(0, decimal_position) );
    decimal = formatNumber( input.value.substring(decimal_position) );
    
    // When we exit the field, make sure there are at least
    // 2 digits after the decimal place
    if (blur) decimal += '00';
    formatted = `${integer}.${decimal.substring(0,2)}`;
    
  } else {
    // Remove invalid characters
    integer = formatNumber( input.value );
    formatted = `${integer}`;
    if (blur) formatted += '.00';
  }
  input.value = formatted;
  
  // Put the text cursor back in the correct position
  const updated_length = formatted.length;
  cursor_position = updated_length - original_length + cursor_position;
}

// Group digits using a Unicode "thin space"
// ref: https://en.wikipedia.org/wiki/Decimal_separator#Digit_grouping
function formatNumber(n) {
  return n.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '\u2009');
}