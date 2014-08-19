/**
 * Sentence case a string.
 *
 * @param  {String} str
 * @return {String}
 */
module.exports = function (str) {
  if (str == null) {
    return '';
  }

  return String(str)
    // Add camel case support.
    .replace(/([a-z])([A-Z0-9])/g, '$1 $2')
    // Remove every non-word character and replace with a period.
    .replace(/[^a-zA-Z0-9]+/g, ' ')
    // Trim whitespace from the string.
    .replace(/^ | $/g, '')
    // Finally lower case the entire string.
    .toLowerCase();
};
