jQuery(document).ready(function ($) {
  // Add new location
  $("#add-location").on("click", function () {
    var template = $("#location-row-template").html();
    var index = $(".location-row").length;
    template = template.replace(/\{\{INDEX\}\}/g, index);
    $("#gdh-locations-container").append(template);
  });

  // Remove location
  $(document).on("click", ".remove-location", function () {
    $(this).closest(".location-row").remove();
  });

  // Toggle redirect URL field
  $(document).on("change", ".redirect-toggle", function () {
    var redirectField = $(this)
      .closest(".location-row")
      .find(".redirect-url-field");
    if ($(this).is(":checked")) {
      redirectField.show();
    } else {
      redirectField.hide();
    }
  });
});
