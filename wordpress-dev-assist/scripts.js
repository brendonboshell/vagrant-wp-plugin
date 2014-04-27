jQuery(document).ready(function () {
    var $ = jQuery,
        $panel,
        version,
        $buttons_ul,
        $button,
        $button_a,
        versionSwitchClickHandler;
        
    if (typeof bbpp_wda_params === "undefined") {
        return;
    }
        
    versionSwitchClickHandler = function ($button, version) {
        return function () {
            $button.addClass("bbpp-wda-button-loading");
            $.get("/", { "bbpp-wda-version": version }, function (data) {
               window.location.reload(true);
            });
        };
    };
    
    $panel = $(document.createElement("div"));
    $panel.addClass("bbpp-wda-panel");
    
    $buttons_ul = $(document.createElement("ul"));
    $buttons_ul.addClass("bbpp-wda-buttons");
    
    for (i = 0; i < bbpp_wda_params.wp_versions.length; i++) {
        version = bbpp_wda_params.wp_versions[i];
        $button = $(document.createElement("li"));
        $button_a = $(document.createElement("a"));
        $button_a.text(version);
        $button_a.click(versionSwitchClickHandler($button_a, version));
        $button.append($button_a);
        $buttons_ul.append($button);
    }
    
    $panel.append($buttons_ul);
    
    $("body").append($panel);
    $("body").css("margin-bottom", "70px");
});