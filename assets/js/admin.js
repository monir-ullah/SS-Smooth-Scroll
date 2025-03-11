jQuery(document).ready(function ($) {
  console.log("Admin.js loaded successfully!");
  function updateSetting(field, value) {
    $.post(
      ss_smooth_scroll_ajax.ajaxurl,
      {
        action: "save_smooth_scroll_settings",
        security: ss_smooth_scroll_ajax.security,
        field: field,
        value: value,
      },
      function (response) {
        try {
          const result =
            typeof response === "string" ? JSON.parse(response) : response;
          if (result.success) {
            // Remove existing toast if any
            $("#toast").remove();
            // Create and append new toast with initial opacity 0
            const $toast = $(
              '<div id="toast" style="position: fixed; bottom: 20px; right: 20px; background: #323232; color: #fff; padding: 20px; border-radius: 10px; font-size: 18px; font-weight: bold; z-index: 1000; opacity: 0;">✅ Settings saved! 🎉</div>'
            );
            $("body").append($toast);
            // Fade in
            $toast.animate({ opacity: 1 }, 300, function () {
              // Wait for 1.4 seconds then fade out
              setTimeout(function () {
                $toast.animate({ opacity: 0 }, 300, function () {
                  $toast.remove();
                });
              }, 1400);
            });
          } else {
            console.error("Failed to save settings:", result.message);
          }
        } catch (e) {
          console.error("Error processing response:", e);
        }
      }
    ).fail(function (xhr, status, error) {
      console.error("Ajax request failed:", error);
    });
  }

  let isChecked = $("#ss_smooth_scroll_enabled").prop("checked");
  $("#ss_smooth_scroll_speed").prop("disabled", !isChecked);

  $("#ss_smooth_scroll_enabled").on("change", function () {
    let isChecked = $(this).prop("checked") ? "yes" : "no";
    updateSetting("ss_smooth_scroll_enabled", isChecked);
    $("#ss_smooth_scroll_speed").prop("disabled", isChecked !== "yes");
  });

  let timeout;
  $("#ss_smooth_scroll_speed").on("change", function () {
    let speed = $(this).val();
    updateSetting("ss_smooth_scroll_speed", speed);
  });
});
