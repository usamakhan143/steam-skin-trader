function fetchSteamUserItems() {
  const $item = $("#user-items");
  let steamId = getCookie("steam_steamID");
  let baseUrl =
    window.location.host === "localhost"
      ? `${window.location.origin}/wpplugindev/`
      : window.location.origin;

  $.ajax({
    url: `${baseUrl}wp-json/custom-proxy/v1/steam-user-items?userid=${steamId}`,
    method: "GET",
    success: function (response) {
      try {
        const skinsData = response.assets;

        if (!skinsData || skinsData.length === 0) {
          $item.html("<p>No items found.</p>");
          return;
        }

        $item.empty(); // Clear shimmer placeholders

        // Loop through the JSON data to dynamically add skins
        $.each(skinsData, function (index, skin) {
          const itemDetails = response.descriptions.find(
            (desc) => desc.classid === skin.classid
          );
          if (itemDetails) {
            const skinCard = `
                            <div class="col-6 col-md-4 col-lg-2">
                                    <div class="skin-card">
                                        <img src="https://steamcommunity-a.akamaihd.net/economy/image/${
                                          itemDetails.icon_url || ""
                                        }" alt="${itemDetails.name}">
                                        <div class="skin-name" title="${
                                          itemDetails.name
                                        }">${truncateString(
              itemDetails.name,
              13
            )}</div>
                                    </div>
                            </div>
                        `;
            $item.append(skinCard);
          }
        });
      } catch (error) {
        console.error("Error in processing items:", error);
        $item.html("<p>Error: " + error.message + "</p>");
      }
    },
    error: function () {
      $item.html("<p>No items found.</p>");
    },
  });
}

fetchSteamUserItems();
