function fetchSteamMarketData(cat) {
  const $skinsContainer = $("#skins-container");
  const numOfResults = steamListingData.count;
  $.ajax({
    url: `https://steamcommunity.com/market/search/render/?appid=730&norender=1&count=${numOfResults}`,
    method: "GET",
    success: function (response) {
      try {
        const skinsData = response.results;

        if (!skinsData || skinsData.length === 0) {
          $skinsContainer.html("<p>No skins found.</p>");
          return;
        }

        $skinsContainer.empty(); // Clear shimmer placeholders

        // Loop through the JSON data to dynamically add skins
        $.each(skinsData, function (index, skin) {
          const itemName = skin?.name || ""; // Fallback to empty string
          if (itemName.includes(cat)) {
            const skinCard = `
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="https://steamcommunity.com/market/listings/730/${
                              skin.asset_description?.market_hash_name || "#"
                            }" target="_blank" class="steam-link">
                                <div class="skin-card">
                                    <img src="https://community.cloudflare.steamstatic.com/economy/image/${
                                      skin.asset_description?.icon_url || ""
                                    }" alt="${itemName}">
                                    <div class="skin-name">${truncateString(
                                      itemName,
                                      13
                                    )}</div>
                                    <div class="skin-price">${
                                      skin.sell_price_text || "N/A"
                                    }</div>
                                </div>
                            </a>
                        </div>
                    `;
            $skinsContainer.append(skinCard);
          }
        });
      } catch (error) {
        console.error("Error processing skins data:", error);
        $skinsContainer.html("<p>Error: " + error.message + "</p>");
      }
    },
    error: function () {
      $skinsContainer.html(
        "<p>Error: Failed to fetch data from Steam Market API</p>"
      );
    },
  });
}

function truncateString(str, limit) {
  // Check if the string length exceeds the limit
  if (str.length > limit) {
    return str.substring(0, limit) + "...";
  } else {
    return str; // Return the original string if it's shorter than the limit
  }
}

// Call the function on page load
$(document).ready(function () {
  const category = steamListingData.category;
  fetchSteamMarketData(category);
});
