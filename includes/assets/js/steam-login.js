jQuery(document).ready(function ($) {
  $("#steam-login-button").on("click", function (e) {
    e.preventDefault();
    const callbackUrl = steamLogin.callback_url;
    window.location.href =
      "https://steamcommunity.com/openid/login?" +
      "openid.ns=http://specs.openid.net/auth/2.0" +
      "&openid.mode=checkid_setup" +
      "&openid.return_to=" +
      callbackUrl +
      "&openid.realm=" +
      window.location.origin +
      "&openid.identity=http://specs.openid.net/auth/2.0/identifier_select" +
      "&openid.claimed_id=http://specs.openid.net/auth/2.0/identifier_select";
  });

  // Function to get cookie by name
  function getCookie(name) {
    let match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
    if (match) {
      return match[2];
    }
    return null;
  }

  // Function to delete a cookie by name
  function deleteCookie(name) {
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
  }

  // Check if the user is logged in by looking for the cookies
  var steamUsername = getCookie("steam_username");
  var steamAvatar = getCookie("steam_avatar");
  var steamID = getCookie("steam_steamID");

  // If the user is logged in (cookies exist), hide the login button and show logout button
  if (steamUsername && steamAvatar && steamID) {
    $("#steam-login-button").hide(); // Hide the login button
    $("#steam-logout-button").show(); // Show the logout button
    steamUsername = decodeURIComponent(steamUsername);
    steamAvatar = decodeURIComponent(steamAvatar);

    // Update the frontend with decoded data
    $("#steam-username").text(steamUsername);
    $("#steam-avatar").attr("src", steamAvatar); // Make sure this is the correct URL
    // $("#steam-id span").text(steamID);
  } else {
    $("#steam-username").hide();
    $("#steam-avatar").hide();
    $("#steam-id span").hide();
    $("#steam-logout-button").hide(); // Hide the logout button if not logged in
  }

  // Handle logout
  $("#steam-logout-button").on("click", function () {
    // Delete cookies
    deleteCookie("steam_username");
    deleteCookie("steam_avatar");
    deleteCookie("steam_steamID");

    // Reload the page to update the UI
    location.reload();
  });
});
