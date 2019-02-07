(function () {
    'use strict';

    $cms.views.AddonInstallConfirmScreen = AddonInstallConfirmScreen;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function AddonInstallConfirmScreen() {
        AddonInstallConfirmScreen.base(this, 'constructor', arguments);
    }

    $util.inherits(AddonInstallConfirmScreen, $cms.View);

    // Templates:
    // ADDON_SCREEN.tpl
    // - ADDON_SCREEN_ADDON.tpl
    $cms.views.AddonScreen = AddonScreen;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function AddonScreen() {
        AddonScreen.base(this, 'constructor', arguments);
    }

    $util.inherits(AddonScreen, $cms.View, /**@lends AddonScreen#*/{
        events: function () {
            return {
                'click .js-click-check-uninstall-all': 'checkUninstallAll',
            };
        },

        checkUninstallAll: function () {
            var checkboxes = this.$$('input[type="checkbox"][name^="uninstall_"]');

            checkboxes.forEach(function (el) {
                el.checked = true;
            });
        }
    });
}());
