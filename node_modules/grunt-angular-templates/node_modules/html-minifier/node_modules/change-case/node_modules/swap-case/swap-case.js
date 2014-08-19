/**
 * Swap the case of a string.
 *
 * @param  {String} string
 * @return {String}
 */
module.exports = function (string) {
  if (string == null) {
    return '';
  }

  return String(string).replace(/\w/g, function (c) {
    var u = c.toUpperCase();

    return c === u ? c.toLowerCase() : u;
  });
};
