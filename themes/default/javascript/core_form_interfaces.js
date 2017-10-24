'use strict';

(function ($cms) {
    'use strict';

    // Templates:
    // POSTING_FORM.tpl
    // - POSTING_FIELD.tpl
    $cms.views.PostingForm = PostingForm;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function PostingForm(params) {
        PostingForm.base(this, 'constructor', arguments);

        if (params.jsFunctionCalls != null) {
            $cms.executeJsFunctionCalls(params.jsFunctionCalls);
        }
    }

    $cms.inherits(PostingForm, $cms.View, {
        events: function () {
            return {
                'submit .js-submit-modsec-workaround': 'submitWithWorkaround',
                'click .js-click-pf-toggle-subord-fields': 'toggleSubordFields',
                'keypress .js-keypress-pf-toggle-subord-fields': 'toggleSubordFields'
            };
        },

        submitWithWorkaround: function (e, form) {
            e.preventDefault();
            $cms.form.modSecurityWorkaround(form);
        },

        toggleSubordFields: function (e, target) {
            toggleSubordinateFields(target.parentNode.querySelector('img'), 'fes_attachments_help');
        }
    });

    $cms.views.FormStandardEnd = FormStandardEnd;
    /**
     * @memberof $cms.views
     * @class FormStandardEnd
     * @extends $cms.View
     */
    function FormStandardEnd(params) {
        FormStandardEnd.base(this, 'constructor', arguments);

        this.backUrl = strVal(params.backUrl);
        this.cancelUrl = strVal(params.cancelUrl);
        this.analyticEventCategory = params.analyticEventCategory;
        this.form = $cms.dom.closest(this.el, 'form');
        this.btnSubmit = this.$('#submit_button');

        window.formPreviewUrl = strVal(params.previewUrl);
        window.separatePreview = Boolean(params.separatePreview);
        window.analyticEventCategory = params.analyticEventCategory;

        if (params.forcePreviews) {
            $cms.dom.hide(this.btnSubmit);
        }

        if (params.jsFunctionCalls != null) {
            $cms.executeJsFunctionCalls(params.jsFunctionCalls);
        }

        if (!params.secondaryForm) {
            this.fixFormEnterKey();
        }

        if (params.supportAutosave && params.formName) {
            setTimeout(function () {
                if (window.initFormSaving !== undefined) {
                    initFormSaving(params.formName);
                }
            }, 3000/*Let CKEditor load*/);
        }
    }

    $cms.inherits(FormStandardEnd, $cms.View, /**@lends FormStandardEnd#*/{
        events: function () {
            return {
                'click .js-click-do-form-cancel': 'doFormCancel',
                'click .js-click-do-form-preview': 'doFormPreview',
                'click .js-click-do-form-submit': 'doStandardFormSubmit',
                'click .js-click-btn-go-back': 'goBack'
            };
        },

        doFormCancel: function (e) {
            var that = this;
            $cms.ui.confirm(
                '{!Q_SURE;*}',
                function (result) {
                    if (result) {
                        window.location = that.cancelUrl;
                    }
                }
            );
        },

        doFormPreview: function (e) {
            var form = this.form;

            $cms.form.doFormPreview(form, window.formPreviewUrl, window.separatePreview).then(function (bool) {
                if (bool) {
                    form.submit();
                }
            });
        },

        doStandardFormSubmit: function (e) {
            $cms.form.doFormSubmit(this.form, this.analyticEventCategory);
        },

        goBack: function (e, btn) {
            if (btn.form.method.toLowerCase() === 'get') {
                window.location = this.backUrl;
            } else {
                btn.form.action = this.backUrl;
                $cms.dom.submit(btn.form);
            }
        },

        fixFormEnterKey: function () {
            var form = this.form,
                submitBtn = document.getElementById('submit_button'),
                inputs = form.getElementsByTagName('input'),
                type, types = ['text', 'password', 'color', 'email', 'number', 'range', 'search',  'tel', 'url'];

            for (var i = 0; i < inputs.length; i++) {
                type = inputs[i].type;
                if (types.includes(type)) {
                    $cms.dom.on(inputs[i], 'keypress', function (event) {
                        if ($cms.dom.keyPressed(event, 'Enter')) {
                            $cms.dom.trigger(submitBtn, 'click');
                        }
                    });
                }
            }
        }
    });

    $cms.views.FromScreenInputUpload = FromScreenInputUpload;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function FromScreenInputUpload(params) {
        FromScreenInputUpload.base(this, 'constructor', arguments);

        if (params.plupload && !$cms.$IS_HTTPAUTH_LOGIN() && $cms.$CONFIG_OPTION('complex_uploader')) {
            $cms.requireJavascript('plupload').then(function () {
                window.preinitFileInput('upload', params.name, null, params.filter);
            });
        }

        if (params.syndicationJson != null) {
            $cms.requireJavascript('editing').then(function () {
                window.showUploadSyndicationOptions(params.name, params.syndicationJson);
            });
        }
    }

    $cms.inherits(FromScreenInputUpload, $cms.View);

    $cms.views.FormScreenInputPermission = FormScreenInputPermission;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function FormScreenInputPermission(params) {
        FormScreenInputPermission.base(this, 'constructor', arguments);

        this.groupId = params.groupId;
        this.prefix = 'access_' + this.groupId;
        var prefix = this.prefix;

        if (!params.allGlobal) {
            var list = document.getElementById(prefix + '_presets');
            // Test to see what we wouldn't have to make a change to get - and that is what we're set at
            if (!copyPermissionPresets(prefix, '0', true)) {
                list.selectedIndex = list.options.length - 4;
            } else if (!copyPermissionPresets(prefix, '1', true)) {
                list.selectedIndex = list.options.length - 3;
            } else if (!copyPermissionPresets(prefix, '2', true)) {
                list.selectedIndex = list.options.length - 2;
            } else if (!copyPermissionPresets(prefix, '3', true)) {
                list.selectedIndex = list.options.length - 1;
            }
        }
    }

    $cms.inherits(FormScreenInputPermission, $cms.View, {
        events: function () {
            return {
                'click .js-click-copy-perm-presets': 'copyPresets',
                'change .js-change-copy-perm-presets': 'copyPresets',
                'click .js-click-perm-repeating': 'permissionRepeating'
            };
        },

        copyPresets: function (e, select) {
            copyPermissionPresets(this.prefix, select.options[select.selectedIndex].value);
            cleanupPermissionList(this.prefix);
        },

        permissionRepeating: function (e, button) {
            var name = this.prefix,
                oldPermissionCopying = window.permissionCopying,
                tr = button.parentNode.parentNode,
                trs = tr.parentNode.getElementsByTagName('tr');

            if (window.permissionCopying) { // Undo current copying
                document.getElementById('copy_button_' + window.permissionCopying).style.textDecoration = 'none';
                window.permissionCopying = null;
            }

            if (oldPermissionCopying !== name) { // Starting a new copying session
                button.style.textDecoration = 'blink';
                window.permissionCopying = name;
                $cms.ui.alert('{!permissions:REPEAT_PERMISSION_NOTICE;^}');
                for (var j = 0; j < trs.length; j++) {
                    if (trs[j] !== tr) {
                        $cms.dom.on(trs[j], 'click', copyPermissionsFunction(trs[j], tr));
                    }
                }
            }

            function copyPermissionsFunction(toRow, fromRow) {
                return function () {
                    var inputsTo = toRow.getElementsByTagName('input');
                    var inputsFrom = fromRow.getElementsByTagName('input');
                    for (var i = 0; i < inputsTo.length; i++) {
                        inputsTo[i].checked = inputsFrom[i].checked;
                    }
                    var selectsTo = toRow.getElementsByTagName('select');
                    var selectsFrom = fromRow.getElementsByTagName('select');
                    for (var i = 0; i < selectsTo.length; i++) {
                        while (selectsTo[i].options.length > 0) {
                            selectsTo[i].remove(0);
                        }
                        for (var j = 0; j < selectsFrom[i].options.length; j++) {
                            selectsTo[i].add(selectsFrom[i].options[j].cloneNode(true), null);
                        }
                        selectsTo[i].selectedIndex = selectsFrom[i].selectedIndex;
                        selectsTo[i].disabled = selectsFrom[i].disabled;
                    }
                }
            }
        }
    });

    $cms.views.FormScreenInputPermissionOverride = FormScreenInputPermissionOverride;
    /**
     * @memberof $cms.views
     * @class
     * @extends $cms.View
     */
    function FormScreenInputPermissionOverride(params) {
        FormScreenInputPermissionOverride.base(this, 'constructor', arguments);

        var prefix = 'access_' + params.groupId,
            defaultAccess = intVal(params.defaultAccess);

        this.groupId = params.groupId;
        this.prefix = prefix;

        setupPrivilegeOverrideSelector(prefix, defaultAccess, params.privilege, params.title, !!params.allGlobal);

        if (!params.allGlobal) {
            var list = document.getElementById(prefix + '_presets');
            // Test to see what we wouldn't have to make a change to get - and that is what we're set at
            if (!copyPermissionPresets(prefix, '0', true)) {
                list.selectedIndex = list.options.length - 4;
            } else if (!copyPermissionPresets(prefix, '1', true)) {
                list.selectedIndex = list.options.length - 3;
            } else if (!copyPermissionPresets(prefix, '2', true)) {
                list.selectedIndex = list.options.length - 2;
            } else if (!copyPermissionPresets(prefix, '3', true)) {
                list.selectedIndex = list.options.length - 1;
            }
        }
    }

    $cms.inherits(FormScreenInputPermissionOverride, $cms.View, /**@lends FormScreenInputPermissionOverride#*/{
        events: function () {
            return {
                'click .js-click-perms-overridden': 'permissionsOverridden',
                'change .js-change-perms-overridden': 'permissionsOverridden',
                'mouseover .js-mouseover-show-perm-setting': 'showPermissionSetting'
            };
        },

        permissionsOverridden: function () {
            permissionsOverridden(this.prefix);
        },

        showPermissionSetting: function (e, select) {
            if (select.options[select.selectedIndex].value === '-1') {
                showPermissionSetting(select);
            }
        }
    });
    
    $cms.templates.formScreenInputPassword = function (params, container) {
        var value = strVal(params.value),
            name = strVal(params.name);

        if ((value === '') && (name === 'edit_password')) {
            // LEGACY Work around annoying Firefox bug. It ignores autocomplete="off" if a password was already saved somehow
            setTimeout(function () {
                $cms.dom.$('#' + name).value = '';
            }, 300);
        }

        $cms.dom.on(container, 'mouseover', '.js-mouseover-activate-password-strength-tooltip', function (e, el) {
            if (el.parentNode.title !== undefined) {
                el.parentNode.title = '';
            }
            $cms.ui.activateTooltip(el, e, '{!PASSWORD_STRENGTH;^}', 'auto');
        });

        $cms.dom.on(container, 'change', '.js-input-change-check-password-strength', function (e, input) {
            if (input.name.includes('2') || input.name.includes('confirm')) {
                return;
            }

            var _ind = $cms.dom.$('#password_strength_' + input.id);
            if (!_ind) {
                return;
            }
            var ind = _ind.querySelector('div');
            var post = 'password=' + encodeURIComponent(input.value);
            if (input.form && (input.form.elements.username !== undefined)) {
                post += '&username=' + input.form.elements['username'].value;
            } else {
                if (input.form && input.form.elements.edit_username !== undefined) {
                    post += '&username=' + input.form.elements['edit_username'].value;
                }
            }

            $cms.loadSnippet('password_strength', post, true).then(function (strength) {
                strength = Number(strength);
                strength *= 2;
                if (strength > 10) {  // Normally too harsh!
                    strength = 10;
                }
                ind.style.width = (strength * 10) + 'px';
                if (strength >= 6) {
                    ind.style.backgroundColor = 'green';
                } else if (strength < 4) {
                    ind.style.backgroundColor = 'red';
                } else {
                    ind.style.backgroundColor = 'orange';
                }

                ind.parentNode.style.display = (input.value.length === 0) ? 'none' : 'block';
            });
        });
    };

    $cms.templates.comcodeEditor = function (params, container) {
        var postingField = strVal(params.postingField);

        $cms.dom.on(container, 'click', '.js-click-do-input-font-posting-field', function () {
            doInputFont(postingField);
        });
    };

    $cms.templates.form = function (params, container) {
        var skippable =  strVal(params.skippable);

        $cms.dom.on(container, 'click', '.js-click-btn-skip-step', function () {
            $cms.dom.$('#' + skippable).value = '1';
        });

        $cms.dom.on(container, 'submit', '.js-submit-modesecurity-workaround', function (e, form) {
            e.preventDefault();
            $cms.form.modSecurityWorkaround(form);
        });
    };
    
    $cms.templates.formScreen = function (params, container) {
        if (params.jsFunctionCalls != null) {
            $cms.executeJsFunctionCalls(params.jsFunctionCalls);
        }
        
        tryToSimplifyIframeForm();

        if (params.iframeUrl) {
            setInterval(function () {
                $cms.dom.resizeFrame('iframe_under');
            }, 1500);

            $cms.dom.on(container, 'click', '.js-checkbox-will-open-new', function (e, checkbox) {
                var form = $cms.dom.$(container, '#main_form');

                form.action = checkbox.checked ? params.url : params.iframeUrl;
                form.elements.opens_below.value = checkbox.checked ? '0' : '1';
                form.target = checkbox.checked ? '_blank' : 'iframe_under';
            });
        }

        $cms.dom.on(container, 'click', '.js-btn-skip-step', function () {
            $cms.dom.$('#' + params.skippable).value = '1';
        });
    };

    $cms.templates.formScreenField_input = function (params) {
        var el = $cms.dom.$('#form_table_field_input__' + params.randomisedId);
        if (el) {
            $cms.form.setUpChangeMonitor(el.parentElement);
        }
    };

    $cms.templates.formScreenFieldDescription = function formScreenFieldDescription(params, img) {
    };

    $cms.templates.formScreenInputLine = function formScreenInputLine(params) {
        $cms.requireJavascript(['jquery', 'jquery_autocomplete']).then(function () {
            setUpComcodeAutocomplete(params.name, !!params.wysiwyg);
        });
    };

    $cms.templates.formScreenInputCombo = function formScreenInputCombo(params, container) {
        var name = strVal(params.name),
            comboInput = $cms.dom.$('#' + name),
            fallbackList = $cms.dom.$('#' + name + '_fallback_list');

        if (window.HTMLDataListElement === undefined) {
            comboInput.classList.remove('input_line_required');
            comboInput.classList.add('input_line');
        }

        if (fallbackList) {
            fallbackList.disabled = (comboInput.value !== '');

            $cms.dom.on(container, 'keyup', '.js-keyup-toggle-fallback-list', function () {
                fallbackList.disabled = (comboInput.value !== '');
            });
        }
    };

    $cms.templates.formScreenInputUsernameMulti = function formScreenInputUsernameMulti(params, container) {
        $cms.dom.on(container, 'focus', '.js-focus-update-ajax-member-list', function (e, input) {
            if (input.value === '') {
                $cms.form.updateAjaxMemberList(input, null, true, e);
            }
        });

        $cms.dom.on(container, 'keyup', '.js-keyup-update-ajax-member-list', function (e, input) {
            $cms.form.updateAjaxMemberList(input, null, false, e);
        });

        $cms.dom.on(container, 'change', '.js-change-ensure-next-field', function (e, input) {
            ensureNextField(input)
        });

        $cms.dom.on(container, 'keypress', '.js-keypress-ensure-next-field', function (e, input) {
            ensureNextField(input)
        });
    };

    $cms.templates.formScreenInputUsername = function formScreenInputUsername(params, container) {
        $cms.dom.on(container, 'focus', '.js-focus-update-ajax-member-list', function (e, input) {
            if (input.value === '') {
                $cms.form.updateAjaxMemberList(input, null, true, e);
            }
        });

        $cms.dom.on(container, 'keyup', '.js-keyup-update-ajax-member-list', function (e, input) {
            $cms.form.updateAjaxMemberList(input, null, false, e);
        });
    };

    $cms.templates.formScreenInputLineMulti = function (params, container) {
        $cms.dom.on(container, 'keypress', '.js-keypress-ensure-next-field', function (e, input) {
            _ensureNextField(e, input);
        });
    };

    $cms.templates.formScreenInputHugeComcode = function formScreenInputHugeComcode(params) {
        var required = strVal(params.required),
            textarea = $cms.dom.$id(params.name),
            input = $cms.dom.$id('form_table_field_input__' + params.randomisedId);

        if (required.includes('wysiwyg') && wysiwygOn()) {
            textarea.readOnly = true;
        }

        if (input) {
            $cms.form.setUpChangeMonitor(input.parentElement);
        }

        if (!$cms.$MOBILE()) {
            $cms.manageScrollHeight(textarea);
        }

        $cms.requireJavascript(['jquery', 'jquery_autocomplete']).then(function () {
            setUpComcodeAutocomplete(params.name, required.includes('wysiwyg'));
        });
    };

    $cms.templates.formScreenInputAuthor = function formScreenInputAuthor(params, container) {
        $cms.dom.on(container, 'keyup', '.js-keyup-update-ajax-author-list', function (e, target) {
            $cms.form.updateAjaxMemberList(target, 'author', false, e);
        });
    };

    $cms.templates.formScreenInputColour = function (params) {
        var label = params.rawField ? ' ' : params.prettyName;

        makeColourChooser(params.name, params.default, '', params.tabindex, label, 'input_colour' + params._required);
        doColorChooser();
    };

    $cms.templates.formScreenInputTreeList = function formScreenInputTreeList(params, container) {
        var name = strVal(params.name),
            hook = $cms.filter.url(params.hook),
            rootId = $cms.filter.url(params.rootId),
            opts = $cms.filter.url(params.options),
            multiSelect = !!params.multiSelect && (params.multiSelect !== '0');

        $cms.requireJavascript('tree_list').then(function () {
            $cms.ui.createTreeList(params.name, 'data/ajax_tree.php?hook=' + hook + $cms.$KEEP(), rootId, opts, multiSelect, params.tabIndex, false, !!params.useServerId);
        });

        $cms.dom.on(container, 'change', '.js-input-change-update-mirror', function (e, input) {
            var mirror = document.getElementById(name + '_mirror');
            if (mirror) {
                $cms.dom.toggle(mirror.parentElement, !!input.selectedTitle);
                $cms.dom.html(mirror, input.selectedTitle ? $cms.filter.html(input.selectedTitle) : '{!NA_EM;}');
            }
        });
    };

    $cms.templates.formScreenInputPermissionMatrix = function (params) {
        var container = this;
        window.permServerid = params.serverId;

        $cms.dom.on(container, 'click', '.js-click-permissions-toggle', function (e, clicked) {
            permissionsToggle(clicked.parentNode)
        });

        function permissionsToggle(cell) {
            var index = cell.cellIndex,
                table = cell.parentNode.parentNode;

            if (table.localName !== 'table') {
                table = table.parentNode;
            }

            var stateList = null,
                stateCheckbox = null;

            for (var i = 0; i < table.rows.length; i++) {
                if (i >= 1) {
                    var cell2 = table.rows[i].cells[index];
                    var input = cell2.querySelector('input');
                    if (input) {
                        if (!input.disabled) {
                            if (stateCheckbox == null) {
                                stateCheckbox = input.checked;
                            }
                            input.checked = !stateCheckbox;
                        }
                    } else {
                        input = cell2.querySelector('select');
                        if (stateList == null) {
                            stateList = input.selectedIndex;
                        }
                        input.selectedIndex = ((stateList != input.options.length - 1) ? (input.options.length - 1) : (input.options.length - 2));
                        input.disabled = false;

                        permissionsOverridden(table.rows[i].id.replace(/_privilege_container$/, ''));
                    }
                }
            }
        }
    };

    $cms.templates.formScreenFieldsSetItem = function formScreenFieldsSetItem(params) {
        var el = $cms.dom.$('#form_table_field_input__' + params.name);

        if (el) {
            $cms.form.setUpChangeMonitor(el.parentElement);
        }
    };

    $cms.templates.formScreenFieldSpacer = function (params, container) {
        var title = $cms.filter.id(params.title),
            sectionHidden = Boolean(params.sectionHidden);

        $cms.dom.on(container, 'click', '.js-click-toggle-subord-fields', function (e, clicked) {
            toggleSubordinateFields(clicked.parentNode.querySelector('img'), 'fes' + title + '_help');
        });

        $cms.dom.on(container, 'keypress', '.js-keypress-toggle-subord-fields', function (e, pressed) {
            toggleSubordinateFields(pressed.parentNode.querySelector('img'), 'fes' + title + '_help');
        });

        $cms.dom.on(container, 'click', '.js-click-geolocate-address-fields', function () {
            geolocateAddressFields();
        });

        if (title && sectionHidden) {
            $cms.dom.trigger('#fes' + title, 'click');
        }
    };

    $cms.templates.formScreenInputTick = function (params, el) {
        if (params.name === 'validated') {
            $cms.dom.on(el, 'click', function () {
                el.previousElementSibling.className = 'validated_checkbox' + (el.checked ? ' checked' : '');
            });
        }

        if (params.name === 'delete') {
            assignTickDeletionConfirm(params.name);
        }

        function assignTickDeletionConfirm(name) {
            var el = document.getElementById(name);

            el.onchange = function () {
                if (this.checked) {
                    $cms.ui.confirm(
                        '{!ARE_YOU_SURE_DELETE;^}',
                        function (result) {
                            if (result) {
                                var form = el.form;
                                if (!form.action.includes('_post')) { // Remove redirect if redirecting back, IF it's not just deleting an on-page post (Wiki+)
                                    form.action = form.action.replace(/([&\?])redirect=[^&]*/, '$1');
                                }
                            } else {
                                el.checked = false;
                            }
                        }
                    );
                }
            }
        }
    };

    $cms.templates.formScreenInputCaptcha = function formScreenInputCaptcha(params, container) {
        if ($cms.$CONFIG_OPTION('js_captcha')) {
            $cms.dom.html($cms.dom.$('#captcha_spot'), params.captcha);
        } else {
            window.addEventListener('pageshow', function () {
                $cms.dom.$('#captcha_readable').src += '&r=' + $cms.random(); // Force it to reload latest captcha
            });
        }

        $cms.dom.on(container, 'click', '.js-click-play-self-audio-link', function (e, link) {
            e.preventDefault();
            $cms.playSelfAudioLink(link);
        });
    };

    $cms.templates.formScreenInputList = function formScreenInputList(params, selectEl) {
        var select2Options;

        if (params.inlineList) {
            return;
        }

        select2Options = {
            dropdownAutoWidth: true,
            formatResult: (params.images === undefined) ? formatSelectSimple : formatSelectImage,
            containerCssClass: 'wide_field'
        };

        if (window.jQuery && (window.jQuery.fn.select2 != null) && (selectEl.options.length > 20)/*only for long lists*/ && (!$cms.dom.html(selectEl.options[1]).match(/^\d+$/)/*not for lists of numbers*/)) {
            window.jQuery(selectEl).select2(select2Options);
        }

        function formatSelectSimple(opt) {
            if (!opt.id) { // optgroup
                return opt.text;
            }
            return '<span title="' + $cms.filter.html(opt.element[0].title) + '">' + $cms.filter.html(opt.text) + '</span>';
        }
        
        function formatSelectImage(opt) {
            if (!opt.id) {
                return opt.text; // optgroup
            }
            
            var imageSources = JSON.parse(strVal(params.imageSources, '{}'));

            for (var imageName in imageSources) {
                if (opt.id === imageName) {
                    return '<span class="vertical_alignment inline_lined_up"><img style="width: 24px;" src="' + imageSources[imageName] + '" \/> ' + $cms.filter.html(opt.text) + '</span>';
                }
            }

            return $cms.filter.html(opt.text);
        }
    };

    $cms.templates.formScreenFieldsSet = function (params) {
        standardAlternateFieldsWithin(params.setName, !!params.required, params.defaultSet);
    };

    $cms.templates.formScreenInputThemeImageEntry = function (params) {
        var name = $cms.filter.id(params.name),
            code = $cms.filter.id(params.code),
            stem = name + '_' + code,
            el = document.getElementById('w_' + stem),
            img = el.querySelector('img'),
            input = document.getElementById('j_' + stem),
            label = el.querySelector('label'),
            form = input.form;

        el.onkeypress = function (event) {
            if ($cms.dom.keyPressed(event, 'Enter')) {
                return clickFunc(event);
            }
        };

        function clickFunc(event) {
            choosePicture('j_' + stem, img, name, event);

            if (window.mainFormVerySimple !== undefined) {
                $cms.dom.submit(form);
            }
        }
        
        $cms.dom.on(img, 'keypress', clickFunc);
        $cms.dom.on(img, 'click', clickFunc);
        $cms.dom.on(el, 'click', clickFunc);

        label.className = 'js_widget';

        $cms.dom.on(input, 'click', function () {
            if (this.disabled) {
                return;
            }

            deselectAltUrl(this.form);

            if (window.mainFormVerySimple !== undefined) {
                $cms.dom.submit(this.form);
            }
        });

        function deselectAltUrl(form) {
            if (form.elements['alt_url'] != null) {
                form.elements['alt_url'].value = '';
            }
        }

    };

    $cms.templates.formScreenInputHuge_input = function (params) {
        var textArea = document.getElementById(params.name),
            el = $cms.dom.$('#form_table_field_input__' + params.randomisedId);

        if (el) {
            $cms.form.setUpChangeMonitor(el.parentElement);
        }

        if (!$cms.$MOBILE()) {
            $cms.manageScrollHeight(textArea);

            $cms.dom.on(textArea, 'change keyup', function () {
                $cms.manageScrollHeight(textArea);
            });
        }
    };

    $cms.templates.formScreenInputHugeList_input = function (params) {
        var el = $cms.dom.$('#form_table_field_input__' + params.randomisedId);

        if (!params.inlineList && el) {
            $cms.form.setUpChangeMonitor(el.parentElement);
        }
    };

    $cms.templates.previewScript = function (params, container) {
        var inner = $cms.dom.$(container, '.js-preview-box-scroll');

        if (inner) {
            $cms.dom.on(inner, $cms.browserMatches('gecko')/*LEGACY*/ ? 'DOMMouseScroll' : 'mousewheel', function (event) {
                inner.scrollTop -= event.wheelDelta ? event.wheelDelta : event.detail;
                event.preventDefault();
            });
        }

        $cms.dom.on(container, 'click', '.js-click-preview-mobile-button', function (event, el) {
            el.form.action = el.form.action.replace(/keep_mobile=\d/g, 'keep_mobile=' + (el.checked ? '1' : '0'));
            if (window.parent) {
                try {
                    window.parent.scrollTo(0, $cms.dom.findPosY(window.parent.document.getElementById('preview_iframe')));
                } catch (e) {}
                window.parent.mobileVersionForPreview = !!el.checked;
                $cms.dom.trigger(window.parent.document.getElementById('preview_button'), 'click');
                return;
            }

            $cms.dom.submit(el.form);
        });
    };

    $cms.templates.postingField = function postingField(params/* NB: mutiple containers */) {
        var id = strVal(params.id),
            name = strVal(params.name),
            initDragDrop = !!params.initDragDrop,
            postEl = $cms.dom.$('#' + name),
            // Container elements:
            labelRow = $cms.dom.$('#field-' + id +'-label'),
            inputRow = $cms.dom.$('#field-' + id +'-input'),
            attachmentsUiRow = $cms.dom.$('#field-' + id +'-attachments-ui'),
            attachmentsUiInputRow = $cms.dom.$('#field-' + id +'-attachments-ui-input');

        if (params.class.includes('wysiwyg')) {
            if (window.wysiwygOn && wysiwygOn()) {
                postEl.readOnly = true; // Stop typing while it loads

                setTimeout(function () {
                    if (postEl.value === postEl.defaultValue) {
                        postEl.readOnly = false; // Too slow, maybe WYSIWYG failed due to some network issue
                    }
                }, 3000);
            }

            if (params.wordCounter !== undefined) {
                setupWordCounter($cms.dom.$('#post'), $cms.dom.$('#word_count_' + params.wordCountId));
            }
        }

        if (!$cms.$MOBILE()) {
            $cms.manageScrollHeight(postEl);
        }

        $cms.requireJavascript(['jquery', 'jquery_autocomplete']).then(function () {
            setUpComcodeAutocomplete(name, true);
        });

        if (initDragDrop) {
            $cms.requireJavascript('plupload').then(function () {
                window.initialiseHtml5DragdropUpload('container_for_' + name, name);
            });
        }

        $cms.dom.on(labelRow, 'click', '.js-click-toggle-wysiwyg', function () {
            toggleWysiwyg(name);
        });

        $cms.dom.on(labelRow, 'click', '.js-link-click-open-field-emoticon-chooser-window', function (e, link) {
            var url = $cms.maintainThemeInLink(link.href);
            $cms.ui.open(url, 'field_emoticon_chooser', 'width=300,height=320,status=no,resizable=yes,scrollbars=no');
        });

        $cms.dom.on(inputRow, 'click', '.js-link-click-open-site-emoticon-chooser-window', function (e, link) {
            var url = $cms.maintainThemeInLink(link.href);
            $cms.ui.open(url, 'site_emoticon_chooser', 'width=300,height=320,status=no,resizable=yes,scrollbars=no');
        });
    };

    $cms.templates.previewScriptCode = function (params) {
        var newPostValue = strVal(params.newPostValue),
            newPostValueHtml = strVal(params.newPostValueHtml),
            mainWindow = $cms.getMainCmsWindow();

        var post = mainWindow.document.getElementById('post');

        // Replace Comcode
        var oldComcode = mainWindow.getTextbox(post);
        mainWindow.setTextbox(post, newPostValue.replace(/&#111;/g, 'o').replace(/&#79;/g, 'O'), newPostValueHtml);

        // Turn main post editing back on
        if (window.wysiwygSetReadonly !== undefined) {
            wysiwygSetReadonly('post', false);
        }

        // Remove attachment uploads
        var inputs = post.form.elements, uploadButton,
            i, doneOne = false;
        
        for (i = 0; i < inputs.length; i++) {
            if (((inputs[i].type === 'file') || ((inputs[i].type === 'text') && (inputs[i].disabled))) && (inputs[i].value !== '') && (inputs[i].name.match(/file\d+/))) {
                if (inputs[i].pluploadObject !== undefined) {
                    if ((inputs[i].value !== '-1') && (inputs[i].value !== '')) {
                        if (!doneOne) {
                            if (!oldComcode.includes('attachment_safe')) {
                                $cms.ui.alert('{!javascript:ATTACHMENT_SAVED;^}');
                            } else {
                                if (!mainWindow.$cms.form.isWysiwygField(post)) {// Only for non-WYSIWYG, as WYSIWYG has preview automated at same point of adding
                                    $cms.ui.alert('{!javascript:ATTACHMENT_SAVED;^}');
                                }
                            }
                        }
                        doneOne = true;
                    }
                    
                    uploadButton = mainWindow.document.getElementById('uploadButton_' + inputs[i].name);
                    if (uploadButton) {
                        uploadButton.disabled = true;
                    }
                    inputs[i].value = '-1';
                } else {
                    try {
                        inputs[i].value = '';
                    } catch (e) {}
                }
                if (inputs[i].form.elements['hidFileID_' + inputs[i].name] !== undefined) {
                    inputs[i].form.elements['hidFileID_' + inputs[i].name].value = '';
                }
            }
        }
    };

    $cms.templates.blockHelperDone = function (params) {
        var targetWin = window.opener ? window.opener : window.parent,
            element = targetWin.document.getElementById(params.fieldName);
        
        if (!element) {
            targetWin = targetWin.frames['iframe_page'];
            element = targetWin.document.getElementById(params.fieldName);
        }
        
        var block = strVal(params.block),
            tagContents = strVal(params.tagContents),
            comcode = strVal(params.comcode),
            comcodeSemihtml = strVal(params.comcodeSemihtml),
            isWysiwyg = targetWin.$cms.form.isWysiwygField(element),
            loadingSpace = document.getElementById('loading_space'),
            attachedEventAction = false;
        
        window.returnValue = comcode;

        if ((block === 'attachment_safe') && /^new_\d+$/.test(tagContents)) {
            // WYSIWYG-editable attachments must be synched
            var field = 'file' + tagContents.substr(4),
                uploadEl = targetWin.document.getElementById(field);
            
            if (!uploadEl) {
                uploadEl = targetWin.document.getElementById('hidFileID_' + field);
            }
            
            if ((uploadEl.pluploadObject != null) && isWysiwyg) {
                var ob = uploadEl.pluploadObject;
                if (Number(ob.state) === Number(targetWin.plupload.STARTED)) {
                    ob.bind('UploadComplete', function () {
                        setTimeout(dispatchBlockHelper, 100); // Give enough time for everything else to update
                    });
                    ob.bind('Error', shutdownOverlay);

                    // Keep copying the upload indicator
                    var progress = $cms.dom.html(targetWin.document.getElementById('fsUploadProgress_' + field));
                    setInterval(function () {
                        if (progress !== '') {
                            $cms.dom.html(loadingSpace, progress);
                            loadingSpace.className = 'spaced flash';
                        }
                    }, 100);

                    attachedEventAction = true;
                }
            }
        }

        if (!attachedEventAction) {
            setTimeout(dispatchBlockHelper, 1000); // Delay it, so if we have in a faux popup it can set up fauxClose
        }

        function shutdownOverlay() {
            setTimeout(function () { // Close master window in timeout, so that this will close first (issue on Firefox) / give chance for messages
                if (window.fauxClose !== undefined) {
                    window.fauxClose();
                } else {
                    window.close();
                }
            }, 200);
        }

        function dispatchBlockHelper() {
            var saveToId = strVal(params.saveToId),
                toDelete = Boolean(params.delete);
            
            if (saveToId !== '') {
                var ob = targetWin.wysiwygEditors[element.id].document.$.getElementById(saveToId);

                if (toDelete) {
                    ob.parentNode.removeChild(ob);
                } else {
                    var inputContainer = document.createElement('div');
                    $cms.dom.html(inputContainer, comcodeSemihtml.replace(/^\s*/, ''));
                    ob.parentNode.replaceChild(inputContainer.firstElementChild, ob);
                }

                targetWin.wysiwygEditors[element.id].updateElement();

                shutdownOverlay();
                return;
            }
            
            var message = '';
            if (comcode.includes('[attachment') && comcode.includes('[attachment_safe') && !isWysiwyg) {
                message = '{!comcode:ADDED_COMCODE_ONLY_SAFE_ATTACHMENT;^}';
            }

            // We define as a temporary global method so we can clone out the tag if needed (e.g. for multiple attachment selections)
            targetWin.insertComcodeTag = function insertComcodeTag(repFrom, repTo, ret, callback) {
                ret = Boolean(ret);
                
                var newComcodeSemihtml = comcodeSemihtml,
                    newComcode = comcode;
                
                if (repFrom != null) {
                    for (var i = 0; i < repFrom.length; i++) {
                        newComcodeSemihtml = newComcodeSemihtml.replace(repFrom[i], repTo[i]);
                        newComcode = newComcode.replace(repFrom[i], repTo[i]);
                    }
                }

                if (ret) {
                    if (callback != null) {
                        callback();
                    }
                    return [newComcodeSemihtml, newComcode];
                }

                var promise = Promise.resolve();
                if (!element.value.includes(comcodeSemihtml) || !comcode.includes('[attachment')) { // Don't allow attachments to add twice
                    promise = targetWin.insertTextbox(element, newComcode, true, newComcodeSemihtml);
                }
                
                promise.then(function () {
                    if (callback != null) {
                        callback();
                    }
                });
            };

            var promise = Promise.resolve();
            if (params.prefix !== undefined) {
                promise = targetWin.insertTextbox(element, params.prefix, true, '');
            }
            promise.then(function () {
                targetWin.insertComcodeTag(null, null, false, function () {
                    if (message !== '') {
                        $cms.ui.alert(message).then(function () {
                            shutdownOverlay();
                        });
                    } else {
                        shutdownOverlay();
                    }
                });
            });
        }
    };

    $cms.templates.formScreenInputUploadMulti = function formScreenInputUploadMulti(params, container) {
        var nameStub = strVal(params.nameStub),
            index = strVal(params.i),
            syndicationJson = strVal(params.syndicationJson);

        if (params.syndicationJson !== undefined) {
            $cms.requireJavascript('editing').then(function () {
                window.showUploadSyndicationOptions(nameStub, syndicationJson);
            });
        }

        if (params.plupload && !$cms.$IS_HTTPAUTH_LOGIN() && $cms.$CONFIG_OPTION('complex_uploader')) {
            window.preinitFileInput('upload_multi', nameStub + '_' + index, null, params.filter);
        }

        $cms.dom.on(container, 'change', '.js-input-change-ensure-next-field-upload', function (e, input) {
            if (!$cms.dom.keyPressed(e, 'Tab')) {
                ensureNextFieldUpload(input);
            }
        });

        $cms.dom.on(container, 'click', '.js-click-clear-name-stub-input', function (e) {
            var input = $cms.dom.$('#' + nameStub + '_' + index);
            $cms.dom.changeVal(input, '');
        });


        function ensureNextFieldUpload(thisField) {
            var mid = thisField.name.lastIndexOf('_'),
                nameStub = thisField.name.substring(0, mid + 1),
                thisNum = thisField.name.substring(mid + 1, thisField.name.length) - 0,
                nextNum = thisNum + 1,
                nextField = document.getElementById('multi_' + nextNum),
                name = nameStub + nextNum,
                thisId = thisField.id;

            if (!nextField) {
                nextNum = thisNum + 1;
                thisField = document.getElementById(thisId);
                nextField = document.createElement('input');
                nextField.className = 'input_upload';
                nextField.setAttribute('id', 'multi_' + nextNum);
                nextField.addEventListener('change', _ensureNextFieldUpload);
                nextField.setAttribute('type', 'file');
                nextField.name = nameStub + nextNum;
                thisField.parentNode.appendChild(nextField);
            }

            function _ensureNextFieldUpload(event) {
                if (!$cms.dom.keyPressed(event, 'Tab')) {
                    ensureNextFieldUpload(this);
                }
            }
        }
    };

    $cms.templates.formScreenInputRadioList = function (params) {
        if (params.name === undefined) {
            return;
        }

        if (params.code !== undefined) {
            choosePicture('j_' + $cms.filter.id(params.name) + '_' + $cms.filter.id(params.code), null, params.name, null);
        }

        if (params.name === 'delete') {
            assignRadioDeletionConfirm(params.name);
        }

        function assignRadioDeletionConfirm(name) {
            for (var i = 1; i < 3; i++) {
                var e = document.getElementById('j_' + name + '_' + i);
                if (e) {
                    e.onchange = function () {
                        if (this.checked) {
                            $cms.ui.confirm(
                                '{!ARE_YOU_SURE_DELETE;^}',
                                function (result) {
                                    var e = document.getElementById('j_' + name + '_0');
                                    if (e) {
                                        if (result) {
                                            var form = e.form;
                                            form.action = form.action.replace(/([&\?])redirect=[^&]*/, '$1');
                                        } else {
                                            e.checked = true; // Check first radio
                                        }
                                    }
                                }
                            );
                        }
                    }
                }
            }
        }
    };

    $cms.templates.formScreenInputMultiList = function formScreenInputMultiList(params, container) {
        $cms.dom.on(container, 'keypress', '.js-keypress-input-ensure-next-field', function (e, input) {
            _ensureNextField(e, input)
        });
    };

    $cms.templates.formScreenInputTextMulti = function formScreenInputTextMulti(params, container) {
        $cms.dom.on(container, 'keypress', '.js-keypress-textarea-ensure-next-field', function (e, textarea) {
            if (!$cms.dom.keyPressed(e, 'Tab')) {
                ensureNextField(textarea);
            }
        });
    };

    $cms.templates.formScreenInputRadioListComboEntry = function formScreenInputRadioListComboEntry(params, container) {
        var nameId = $cms.filter.id(params.name);

        toggleOtherCustomInput();
        $cms.dom.on(container, 'change', '.js-change-toggle-other-custom-input', function () {
            toggleOtherCustomInput();
        });

        function toggleOtherCustomInput() {
            $cms.dom.$('#j_' + nameId + '_other_custom').disabled = !$cms.dom.$('#j_' + nameId + '_other').checked;
        }
    };

    $cms.templates.formScreenInputVariousTricks = function formScreenInputVariousTricks(params, container) {
        var customName = strVal(params.customName);

        if (customName && !params.customAcceptMultiple) {
            var el = document.getElementById(params.customName + '_value');
            $cms.dom.trigger(el, 'change');
        }

        $cms.dom.on(container, 'click', '.js-click-checkbox-toggle-value-field', function (e, checkbox) {
            document.getElementById(customName + '_value').disabled = !checkbox.checked;
        });

        $cms.dom.on(container, 'change', '.js-change-input-toggle-value-checkbox', function (e, input) {
            document.getElementById(customName).checked = (input.value !== '');
            input.disabled = (input.value === '');
        });

        $cms.dom.on(container, 'keypress', '.js-keypress-input-ensure-next-field', function (e, input) {
            _ensureNextField(e, input);
        });
    };

    $cms.templates.formScreenInputText = function formScreenInputText(params) {
        if (params.required.includes('wysiwyg')) {
            if ((window.wysiwygOn) && (wysiwygOn())) {
                document.getElementById(params.name).readOnly = true;
            }
        }

        if (!$cms.$MOBILE()) {
            $cms.manageScrollHeight(document.getElementById(params.name));
        }
    };

    $cms.templates.formScreenInputTime = function formScreenInputTime(params) {
        // Uncomment if you want to force jQuery-UI inputs even when there is native browser input support
        //window.jQuery('#' + params.name).inputTime({});
    };
    
    /**
     * Marking things (to avoid illegally nested forms)
     * @param form
     * @param prefix
     * @returns {boolean}
     */
    $cms.form.addFormMarkedPosts = function addFormMarkedPosts(form, prefix) {
        prefix = strVal(prefix);

        var get = form.method.toLowerCase() === 'get',
            i;

        if (get) {
            for (i = 0; i < form.elements.length; i++) {
                if ((new RegExp('&' + prefix + '\d+=1$', 'g')).test(form.elements[i].name)) {
                    form.elements[i].parentNode.removeChild(form.elements[i]);
                }
            }
        } else {
            // Strip old marks out of the URL
            form.action = form.action.replace('?', '&')
                .replace(new RegExp('&' + prefix + '\d+=1$', 'g'), '')
                .replace('&', '?'); // will just do first due to how JS works
        }

        var checkboxes = $cms.dom.$$('input[type="checkbox"][name^="' + prefix + '"]:checked'),
            append = '';

        for (i = 0; i < checkboxes.length; i++) {
            append += (((append === '') && !form.action.includes('?') && !form.action.includes('/pg/') && !get) ? '?' : '&') + checkboxes[i].name + '=1';
        }

        if (get) {
            var bits = append.split('&');
            for (i = 0; i < bits.length; i++) {
                if (bits[i] !== '') {
                    $cms.dom.append(form, $cms.dom.create('input', {
                        name: bits[i].substr(0, bits[i].indexOf('=1')),
                        type: 'hidden',
                        value: '1'
                    }));
                }
            }
        } else {
            form.action += append;
        }

        return append !== '';
    };
    
    /**
     * @memberof $cms.form
     * @param form
     * @returns {boolean}
     */
    $cms.form.modSecurityWorkaround = function modSecurityWorkaround(form) {
        var tempForm = document.createElement('form');
        tempForm.method = 'post';

        if (form.target) {
            tempForm.target = form.target;
        }
        tempForm.action = form.action;

        var data = $cms.dom.serialize(form);
        data = _modSecurityWorkaround(data);

        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_data';
        input.value = data;
        tempForm.appendChild(input);

        if (form.elements['csrf_token']) {
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = form.elements.csrf_token.value;
            tempForm.appendChild(csrfInput);
        }

        tempForm.style.display = 'none';
        document.body.appendChild(tempForm);

        setTimeout(function () {
            $cms.dom.submit(tempForm);
            tempForm.parentNode.removeChild(tempForm);
        });

        return false;
    };


    /**
     * @memberof $cms.form
     * @param data
     * @returns {string}
     */
    $cms.form.modSecurityWorkaroundAjax = function modSecurityWorkaroundAjax(data) {
        return '_data=' + encodeURIComponent(_modSecurityWorkaround(data));
    };

    function _modSecurityWorkaround(data) {
        data = strVal(data);

        var remapper = {
                '\\': '<',
                '/': '>',
                '<': '\'',
                '>': '"',
                '\'': '/',
                '"': '\\',
                '%': '&',
                '&': '%',
                '@': ':',
                ':': '@'
            },
            out = '',
            character;
        
        for (var i = 0; i < data.length; i++) {
            character = data[i];
            if (remapper[character] !== undefined) {
                out += remapper[character];
            } else {
                out += character;
            }
        }
        
        return out;
    }

    /* Set up a word count for a form field */
    function setupWordCounter(post, countElement) {
        setInterval(function () {
            if ($cms.form.isWysiwygField(post)) {
                try {
                    var textValue = window.CKEDITOR.instances[post.name].getData();
                    var matches = textValue.replace(/<[^<|>]+?>|&nbsp;/gi, ' ').match(/\b/g);
                    var count = 0;
                    if (matches) count = matches.length / 2;
                    $cms.dom.html(countElement, '{!WORDS;^}'.replace('\\{1\\}', count));
                }
                catch (e) {
                }
            }
        }, 1000);
    }

    function permissionsOverridden(select) {
        var element = document.getElementById(select + '_presets');
        if (element.options[0].id !== select + '_custom_option') {
            var newOption = document.createElement('option');
            $cms.dom.html(newOption, '{!permissions:PINTERFACE_LEVEL_CUSTOM;^}');
            newOption.id = select + '_custom_option';
            newOption.value = '';
            element.insertBefore(newOption, element.options[0]);
        }
        element.selectedIndex = 0;
    }

    function tryToSimplifyIframeForm() {
        var iframe = document.getElementById('iframe_under'),
            formCatSelector = document.getElementById('main_form'),
            elements, i, element,
            count = 0, found, foundButton;
        
        if (!formCatSelector) {
            return;
        }

        elements = $cms.dom.$$(formCatSelector, 'input, button, select, textarea');
        for (i = 0; i < elements.length; i++) {
            element = elements[i];
            if (((element.localName === 'input') && (element.type !== 'hidden') && (element.type !== 'button') && (element.type !== 'image') && (element.type !== 'submit')) || (element.localName === 'select') || (element.localName === 'textarea')) {
                found = element;
                count++;
            }
            if (((element.localName === 'input') && ((element.type === 'button') || (element.type === 'image') || (element.type === 'submit'))) || (element.localName === 'button')) {
                foundButton = element;
            }
        }

        if ((count === 1) && (found.localName === 'select')) {
            $cms.dom.on(found, 'change', foundChangeHandler);
            
            if ((found.getAttribute('size') > 1) || (found.multiple)) {
                $cms.dom.on(found, 'click', foundChangeHandler);
            }
            if (iframe) {
                foundButton.style.display = 'none';
            }
        }
        
        function foundChangeHandler() {
            if (iframe) {
                if (iframe.contentDocument && (iframe.contentDocument.getElementsByTagName('form').length !== 0)) {
                    $cms.ui.confirm(
                        '{!Q_SURE_LOSE;^}'
                    ).then(function (result) {
                        if (result) {
                            _simplifiedFormContinueSubmit(iframe, formCatSelector);
                        }
                    });

                    return null;
                }
            }

            _simplifiedFormContinueSubmit(iframe, formCatSelector);

            return null;
        }
    }

    function _simplifiedFormContinueSubmit(iframe, formCatSelector) {
        $cms.form.checkForm(formCatSelector, false).then(function (valid) {
            if (valid) {
                if (iframe) {
                    $cms.dom.animateFrameLoad(iframe, 'iframe_under');
                }
                $cms.dom.submit(formCatSelector);
            }
        });
    }

    /* Geolocation for address fields */
    function geolocateAddressFields() {
        if (!navigator.geolocation) {
            return;
        }
        try {
            navigator.geolocation.getCurrentPosition(function (position) {
                var fields = [
                    '{!cns_special_cpf:SPECIAL_CPF__cms_street_address;^}',
                    '{!cns_special_cpf:SPECIAL_CPF__cms_city;^}',
                    '{!cns_special_cpf:SPECIAL_CPF__cms_county;^}',
                    '{!cns_special_cpf:SPECIAL_CPF__cms_state;^}',
                    '{!cns_special_cpf:SPECIAL_CPF__cms_post_code;^}',
                    '{!cns_special_cpf:SPECIAL_CPF__cms_country;^}'
                ];

                var geocodeUrl = '{$FIND_SCRIPT;,geocode}';
                geocodeUrl += '?latitude=' + encodeURIComponent(position.coords.latitude) + '&longitude=' + encodeURIComponent(position.coords.longitude);
                geocodeUrl += $cms.keepStub();

                $cms.doAjaxRequest(geocodeUrl).then(function (xhr) {
                    var parsed = JSON.parse(xhr.responseText);
                    if (parsed === null) {
                        return;
                    }
                    var labels = document.getElementsByTagName('label'), label, fieldName, field;
                    for (var i = 0; i < labels.length; i++) {
                        label = $cms.dom.html(labels[i]);
                        for (var j = 0; j < fields.length; j++) {
                            if (fields[j].replace(/^.*: /, '') === label) {
                                if (parsed[j + 1] === null) parsed[j + 1] = '';

                                fieldName = labels[i].getAttribute('for');
                                field = document.getElementById(fieldName);
                                if (field.localName === 'select') {
                                    field.value = parsed[j + 1];
                                    if (jQuery.fn.select2 !== undefined) {
                                        jQuery(field).trigger('change');
                                    }
                                } else {
                                    field.value = parsed[j + 1];
                                }
                            }
                        }
                    }
                });
            });
        } catch (ignore) {}
    }

    // Hide a 'tray' of trs in a form
    function toggleSubordinateFields(pic, helpId) {
        var fieldInput = $cms.dom.parent(pic, '.form_table_field_spacer'),
            next = fieldInput.nextElementSibling,
            newDisplayState, newDisplayState2;

        if (!next) {
            return;
        }

        while (next.classList.contains('field_input')) { // Sometimes divs or whatever may have errornously been put in a table by a programmer, skip past them
            next = next.nextElementSibling;
            if (!next || next.classList.contains('form_table_field_spacer')) { // End of section, so no need to keep going
                next = null;
                break;
            }
        }

        if ((!next && (pic.src.includes('expand'))) || (next && (next.style.display === 'none'))) {/* Expanding now */
            pic.src = pic.src.includes('themewizard.php') ? pic.src.replace('expand', 'contract') : $cms.img('{$IMG;,1x/trays/contract}');
            if (pic.srcset !== undefined) {
                pic.srcset.includes('themewizard.php') ? pic.srcset.replace('expand', 'contract') : ($cms.img('{$IMG;,2x/trays/contract}') + ' 2x');
            }
            pic.alt = '{!CONTRACT;^}';
            pic.title = '{!CONTRACT;^}';
            newDisplayState = ''; // default state from CSS
            newDisplayState2 = ''; // default state from CSS
        } else { /* Contracting now */
            pic.src = pic.src.includes('themewizard.php') ? pic.src.replace('contract', 'expand') : $cms.img('{$IMG;,1x/trays/expand}');
            if (pic.srcset !== undefined) {
                pic.srcset = pic.src.includes('themewizard.php') ? pic.srcset.replace('contract', 'expand') : ($cms.img('{$IMG;,2x/trays/expand}') + ' 2x');
            }
            pic.alt = '{!EXPAND;^}';
            pic.title = '{!EXPAND;^}';
            newDisplayState = 'none';
            newDisplayState2 = 'none';
        }

        // Hide everything until we hit end of section
        var count = 0;
        while (fieldInput.nextElementSibling) {
            fieldInput = fieldInput.nextElementSibling;

            /* Start of next section? */
            if (fieldInput.classList.contains('form_table_field_spacer')) {
                break; // End of section
            }

            /* Ok to proceed */
            fieldInput.style.display = newDisplayState;

            if ((newDisplayState2 !== 'none') && (count < 50/*Performance*/)) {
                $cms.dom.fadeIn(fieldInput);
                count++;
            }
        }
        
        if (helpId === undefined) {
            helpId = pic.parentNode.id + '_help';
        }
        
        var help = document.getElementById(helpId);

        while (help !== null) {
            help.style.display = newDisplayState2;
            help = help.nextElementSibling;
            if (help && (help.localName !== 'p')) {
                break;
            }
        }

        $cms.dom.triggerResize();
    }

    function choosePicture(jId, imgOb, name, event) {
        var j = document.getElementById(jId);
        if (!j) {
            return;
        }

        if (!imgOb) {
            imgOb = document.getElementById('w_' + jId.substring(2, jId.length)).querySelector('img');
            if (!imgOb) {
                return;
            }
        }

        var e = j.form.elements[name];
        for (var i = 0; i < e.length; i++) {
            if (e[i].disabled) continue;
            var img = e[i].parentNode.parentNode.querySelector('img');
            if (img && (img !== imgOb)) {
                if (img.parentNode.classList.contains('selected')) {
                    img.parentNode.classList.remove('selected');
                    img.style.outline = '0';
                    img.style.background = 'none';
                }
            }
        }

        if (j.disabled) {
            return;
        }
        j.checked = true;
        $cms.dom.trigger(j, 'change');
        
        imgOb.parentNode.classList.add('selected');
        imgOb.style.outline = '1px dotted';
    }

    function standardAlternateFieldsWithin(setName, somethingRequired, defaultSet) {
        var form = document.getElementById('set_wrapper_' + setName);

        while (form && (form.localName !== 'form')) {
            form = form.parentNode;
        }
        var fields = form.elements[setName];
        var fieldNames = [];
        for (var i = 0; i < fields.length; i++) {
            if (fields[i][0] === undefined) {
                if (fields[i].id.startsWith('choose_')) {
                    fieldNames.push(fields[i].id.replace(/^choose\_/, ''));
                }
            } else {
                if (fields[i][0].id.startsWith('choose_')) {
                    fieldNames.push(fields[i][0].id.replace(/^choose\_/, ''));
                }
            }
        }

        standardAlternateFields(fieldNames, somethingRequired, defaultSet);

        // Do dynamic $cms.form.setLocked/$cms.form.setRequired such that one of these must be set, but only one may be
        function standardAlternateFields(fieldNames, somethingRequired, secondRun, defaultSet) {
            secondRun = !!secondRun;

            // Look up field objects
            var fields = [], i, field;

            for (i = 0; i < fieldNames.length; i++) {
                field = _standardAlternateFieldsGetObject(fieldNames[i]);
                fields.push(field);
            }

            // Set up listeners...
            for (i = 0; i < fieldNames.length; i++) {
                field = fields[i];
                if ((!field) || (field.alternating === undefined)) { // ... but only if not already set
                    var selfFunction = function (e) {
                        standardAlternateFields(fieldNames, somethingRequired, true, '');
                    }; // We'll re-call ourself on change
                    _standardAlternateFieldCreateListeners(field, selfFunction);
                }
            }

            // Update things
            for (i = 0; i < fieldNames.length; i++) {
                field = fields[i];
                if ((defaultSet == '') && (_standardAlternateFieldIsFilledIn(field, secondRun, false)) || (defaultSet != '') && (fieldNames[i].indexOf('_' + defaultSet) != -1)) {
                    return _standardAlternateFieldUpdateEditability(field, fields, somethingRequired);
                }
            }

            // Hmm, force first one chosen then
            for (i = 0; i < fieldNames.length; i++) {
                if (fieldNames[i] == '') {
                    var radioButton = document.getElementById('choose_'); // Radio button handles field alternation
                    radioButton.checked = true;
                    return _standardAlternateFieldUpdateEditability(null, fields, somethingRequired);
                }

                field = fields[i];
                if ((field) && (_standardAlternateFieldIsFilledIn(field, secondRun, true)))
                    return _standardAlternateFieldUpdateEditability(field, fields, somethingRequired);
            }

            function _standardAlternateFieldUpdateEditability(chosen, choices, somethingRequired) {
                for (var i = 0; i < choices.length; i++) {
                    __standardAlternateFieldUpdateEditability(choices[i], chosen, choices[i] != chosen, choices[i] == chosen, somethingRequired);
                }

                // NB: is_chosen may only be null if is_locked is false
                function __standardAlternateFieldUpdateEditability(field, chosenField, isLocked, isChosen, somethingRequired) {
                    if ((!field) || (field.nodeName !== undefined)) {
                        ___standardAlternateFieldUpdateEditability(field, chosenField, isLocked, isChosen, somethingRequired);
                    } else { // List of fields (e.g. radio list, or just because standardAlternateFieldsWithin was used)
                        for (var i = 0; i < field.length; i++) {
                            if (field[i].name !== undefined) { // If it is an object, as opposed to some string in the collection
                                ___standardAlternateFieldUpdateEditability(field[i], chosenField, isLocked, isChosen, somethingRequired);
                                somethingRequired = false; // Only the first will be required
                            }
                        }
                    }

                    function ___standardAlternateFieldUpdateEditability(field, chosenField, isLocked, isChosen, somethingRequired) {
                        if (!field) return;

                        var radioButton = document.getElementById('choose_' + field.name.replace(/\[\]$/, ''));
                        if (!radioButton) {
                            radioButton = document.getElementById('choose_' + field.name.replace(/\_\d+$/, '_'));
                        }

                        $cms.form.setLocked(field, isLocked, chosenField);
                        if (somethingRequired) {
                            $cms.form.setRequired(field.name.replace(/\[\]$/, ''), isChosen);
                        }

                        var radioButton = $cms.dom.$('#choose_' + field.name);
                        if (radioButton) {
                            radioButton.checked = isChosen;
                        }
                    }
                }
            }

            function _standardAlternateFieldsGetObject(fieldName) {
                // Maybe it's an N/A so no actual field
                if (!fieldName) {
                    return null;
                }

                // Try and get direct field
                var field = document.getElementById(fieldName);
                if (field) {
                    return field;
                }

                // A radio field, so we need to create a virtual field object to return that will hold our value
                var radioButtons = [], i, j, e;
                /*JSLINT: Ignore errors*/
                radioButtons['name'] = fieldName;
                radioButtons['value'] = '';
                for (i = 0; i < document.forms.length; i++) {
                    for (j = 0; j < document.forms[i].elements.length; j++) {
                        e = document.forms[i].elements[j];
                        if (!e.name) {
                            continue;
                        }

                        if ((e.name.replace(/\[\]$/, '') == fieldName) || (e.name.replace(/\_\d+$/, '_') == fieldName)) {
                            radioButtons.push(e);
                            if (e.checked) {// This is the checked radio equivalent to our text field, copy the value through to the text field
                                radioButtons['value'] = e.value;
                            }
                            if (e.alternating) {
                                radioButtons.alternating = true;
                            }
                        }
                    }
                }

                if (radioButtons.length === 0) {
                    return null;
                }

                return radioButtons;
            }

            function _standardAlternateFieldIsFilledIn(field, secondRun, force) {
                if (!field) { // N/A input is considered unset
                    return false; 
                } 

                var isSet = force || ((field.value != '') && (field.value != '-1')) || ((field.virtualValue !== undefined) && (field.virtualValue != '') && (field.virtualValue != '-1'));

                var radioButton = document.getElementById('choose_' + (field ? field.name : '').replace(/\[\]$/, '')); // Radio button handles field alternation
                if (!radioButton) radioButton = document.getElementById('choose_' + field.name.replace(/\_\d+$/, '_'));
                if (secondRun) {
                    if (radioButton) {
                        return radioButton.checked;
                    }
                } else {
                    if (radioButton) {
                        radioButton.checked = isSet;
                    }
                }
                return isSet;
            }

            function _standardAlternateFieldCreateListeners(field, refreshFunction) {
                if ((!field) || (field.nodeName !== undefined)) {
                    __standardAlternateFieldCreateListeners(field, refreshFunction);
                } else {
                    var i;
                    for (i = 0; i < field.length; i++) {
                        if (field[i].name !== undefined)
                            __standardAlternateFieldCreateListeners(field[i], refreshFunction);
                    }
                    field.alternating = true;
                }

                return null;

                function __standardAlternateFieldCreateListeners(field, refreshFunction) {
                    var radioButton = document.getElementById('choose_' + (field ? field.name : '').replace(/\[\]$/, ''));
                    if (!radioButton) {
                        radioButton = document.getElementById('choose_' + field.name.replace(/\_\d+$/, '_'));
                    }
                    if (radioButton) { // Radio button handles field alternation
                        radioButton.addEventListener('change', refreshFunction);
                    } else { // Filling/blanking out handles field alternation
                        if (field) {
                            field.addEventListener('keyup', refreshFunction);
                            field.addEventListener('change', refreshFunction);
                        }
                    }
                    if (field) {
                        field.alternating = true;
                    }
                }
            }
        }
    }
}(window.$cms));

// ===========
// Multi-field
// ===========

function _ensureNextField(event, el) {
    if ($cms.dom.keyPressed(event, 'Enter')) {
        gotoNextField(el);
    } else if (!$cms.dom.keyPressed(event, 'Tab')) {
        ensureNextField(el);
    }

    function gotoNextField(thisField) {
        var mid = thisField.id.lastIndexOf('_'),
            nameStub = thisField.id.substring(0, mid + 1),
            thisNum = thisField.id.substring(mid + 1, thisField.id.length) - 0,
            nextNum = thisNum + 1,
            nextField = document.getElementById(nameStub + nextNum);

        if (nextField) {
            try {
                nextField.focus();
            } catch (e) {}
        }
    }
}

function ensureNextField(thisField) {
    var mid = thisField.id.lastIndexOf('_'),
        nameStub = thisField.id.substring(0, mid + 1),
        thisNum = thisField.id.substring(mid + 1, thisField.id.length) - 0,
        nextNum = thisNum + 1,
        nextField = document.getElementById(nameStub + nextNum),
        name = nameStub + nextNum,
        thisId = thisField.id;

    if (!nextField) {
        nextNum = thisNum + 1;
        thisField = document.getElementById(thisId);
        var nextFieldWrap = document.createElement('div');
        nextFieldWrap.className = thisField.parentNode.className;
        if (thisField.localName === 'textarea') {
            nextField = document.createElement('textarea');
        } else {
            nextField = document.createElement('input');
            nextField.setAttribute('size', thisField.getAttribute('size'));
        }
        nextField.className = thisField.className.replace(/\_required/g, '');
        if (thisField.form.elements['label_for__' + nameStub + '0']) {
            var nextLabel = document.createElement('input');
            nextLabel.setAttribute('type', 'hidden');
            nextLabel.value = thisField.form.elements['label_for__' + nameStub + '0'].value + ' (' + (nextNum + 1) + ')';
            nextLabel.name = 'label_for__' + nameStub + nextNum;
            nextFieldWrap.appendChild(nextLabel);
        }
        nextField.setAttribute('tabindex', thisField.getAttribute('tabindex'));
        nextField.setAttribute('id', nameStub + nextNum);
        if (thisField.onfocus) {
            nextField.onfocus = thisField.onfocus;
        }
        if (thisField.onblur) {
            nextField.onblur = thisField.onblur;
        }
        if (thisField.onkeyup) {
            nextField.onkeyup = thisField.onkeyup;
        }
        nextField.onkeypress = function (event) {
            _ensureNextField(event, nextField);
        };
        if (thisField.onchange) {
            nextField.onchange = thisField.onchange;
        }
        if (thisField.onrealchange != null) {
            nextField.onchange = thisField.onrealchange;
        }
        if (thisField.localName !== 'textarea') {
            nextField.type = thisField.type;
        }
        nextField.value = '';
        nextField.name = (thisField.name.includes('[]') ? thisField.name : (nameStub + nextNum));
        nextFieldWrap.appendChild(nextField);
        thisField.parentNode.parentNode.insertBefore(nextFieldWrap, thisField.parentNode.nextSibling);
    }
}
