function truncateString(str, limit) {
  // Check if the string length exceeds the limit
  if (str.length > limit) {
    return str.substring(0, limit) + "...";
  } else {
    return str; // Return the original string if it's shorter than the limit
  }
}

// Function to get cookie by name
function getCookie(name) {
  let match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
  if (match) {
    return match[2];
  }
  return null;
}
