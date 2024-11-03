function setGeoLocation(geo) {
  jQuery.ajax({
    url: geoHeadingsAjax.ajaxurl,
    type: "POST",
    data: {
      action: "set_geo_cookie",
      geo: geo,
      nonce: geoHeadingsAjax.nonce,
    },
    success: function (response) {
      if (response.success) {
        if (response.data.redirect) {
          window.location.href = response.data.url;
        } else {
          location.reload();
        }
      } else {
        console.error("Failed to set geo location");
      }
    },
    error: function (xhr, status, error) {
      console.error("Ajax request failed:", error);
    },
  });
}
