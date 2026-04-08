(function () {
  const fallbackConfig = {
    plugins: [],
    removePlugins: [],
    toolbar: {
      items: ['heading', '|', 'bold', 'italic', 'underline', 'strikethrough', 'sourceEditing'],
    },
  };

  window.formidableCkeditorConfigs = window.formidableCkeditorConfigs || {};
  window.formidableCkeditorConfigs.label =
    window.formidableCkeditorConfigs.label || fallbackConfig;
  window.formidableCkeditorConfigs.tooltip =
    window.formidableCkeditorConfigs.tooltip || fallbackConfig;
})();
