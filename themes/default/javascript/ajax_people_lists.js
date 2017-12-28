(function ($cms, $util, $dom) {
    'use strict';

    /**
     * @memberof $cms.form
     * @param target
     * @param e
     * @param searchType
     */
    $cms.form.updateAjaxSearchList = function updateAjaxSearchList(target, e, searchType) {
        var special = 'search';
        searchType = strVal(searchType);
        if (searchType) {
            special += '&search_type=' + encodeURIComponent(searchType);
        }
        $cms.form.updateAjaxMemberList(target, special, false, e);
    };

    var currentlyDoingListTimer = 0,
        currentListForEl = null;
    /**
     * @memberof $cms.form
     * @param target
     * @param special
     * @param delayed
     * @param event
     */
    $cms.form.updateAjaxMemberList = function updateAjaxMemberList(target, special, delayed, event) {
        if ((event && $dom.keyPressed(event, 'Enter')) || target.disabled) {
            return;
        }

        if (!$cms.browserMatches('ios') && !target.onblur) {
            target.onblur = function () {
                setTimeout(function () {
                    closeDownAjaxList();
                }, 300);
            };
        }

        if (!delayed) { // A delay, so as not to throw out too many requests
            if (currentlyDoingListTimer) {
                clearTimeout(currentlyDoingListTimer);
            }
            var eCopy = { 'keyCode': event.keyCode, 'which': event.which };

            currentlyDoingListTimer = setTimeout(function () {
                $cms.form.updateAjaxMemberList(target, special, true, eCopy);
            }, 400);
            return;
        } else {
            currentlyDoingListTimer = 0;
        }

        target.special = special;

        var v = target.value;

        currentListForEl = target;
        var script = '{$FIND_SCRIPT_NOHTTP;,namelike}?id=' + encodeURIComponent(v);
        if (special) {
            script = script + '&special=' + special;
        }

        $cms.doAjaxRequest(script + $cms.keep()).then(function (xhr) {
            if (xhr.responseXML) {
                updateAjaxNemberListResponse(xhr.responseXML);
            }
        });

        function closeDownAjaxList() {
            var current = $dom.$('#ajax_list');
            if (current) {
                current.parentNode.removeChild(current);
            }
        }

        function updateAjaxNemberListResponse(responseXml) {
            var listContents = responseXml && responseXml.querySelector('result');
            
            if (!listContents || !currentListForEl) {
                return;
            }

            closeDownAjaxList();

            var isDataList = false;//(document.createElement('datalist').options!==undefined); Still too buggy in browsers

            //if (list_contents.childNodes.length==0) return;
            var list = document.createElement(isDataList ? 'datalist' : 'select');
            list.className = 'people-list';
            list.setAttribute('id', 'ajax_list');
            if (isDataList) {
                currentListForEl.setAttribute('list', 'ajax_list');
            } else {
                if (listContents.childNodes.length == 1) { // We need to make sure it is not a dropdown. Normally we'd use size (multiple isn't correct, but we'll try this for 1 as it may be more stable on some browsers with no side effects)
                    list.setAttribute('multiple', 'multiple');
                } else {
                    list.setAttribute('size', listContents.childNodes.length + 1);
                }
                list.style.position = 'absolute';
                list.style.left = ($dom.findPosX(currentListForEl)) + 'px';
                list.style.top = ($dom.findPosY(currentListForEl) + currentListForEl.offsetHeight) + 'px';
            }
            list.style.zIndex++;

            if (listContents.children.length === 0) {
                return;
            }

            var i, item, displaytext;
            
            for (i = 0; i < listContents.children.length; i++) {
                item = document.createElement('option');
                item.value = listContents.children[i].getAttribute('value');
                displaytext = item.value;
                if (listContents.children[i].getAttribute('displayname') != '') {
                    displaytext = listContents.children[i].getAttribute('displayname');
                }
                item.text = displaytext;
                item.textContent = displaytext;
                list.appendChild(item);
            }
            item = document.createElement('option');
            item.disabled = true;
            item.text = '{!javascript:SUGGESTIONS_ONLY;^}'.toUpperCase();
            item.textContent = '{!javascript:SUGGESTIONS_ONLY;^}'.toUpperCase();
            list.appendChild(item);
            currentListForEl.parentNode.appendChild(list);

            if (isDataList) {
                return;
            }
            
            $dom.fadeIn(list);

            var currentListForCopy = currentListForEl;

            if (currentListForEl.oldOnkeyup === undefined) {
                currentListForEl.oldOnkeyup = currentListForEl.onkeyup;
            }

            if (currentListForEl.oldOnchange === undefined) {
                currentListForEl.oldOnchange = currentListForEl.onchange;
            }

            currentListForEl.downOnce = false;

            currentListForEl.onkeyup = function (event) {
                var ret = handleArrowUsage(event);
                if (ret != null) {
                    return ret;
                }
                return $cms.form.updateAjaxMemberList(currentListForCopy, currentListForCopy.special, false, event);
            };
            currentListForEl.onchange = function (event) {
                currentListForCopy.onkeyup = currentListForCopy.oldOnkeyup;
                currentListForCopy.onchange = currentListForCopy.oldOnchange;
                if (currentListForCopy.onchange) {
                    currentListForCopy.onchange(event);
                }
            };
            list.onkeyup = function (event) {
                var ret = handleArrowUsage(event);
                if (ret != null) {
                    return ret;
                }

                if ($dom.keyPressed(event, 'Enter')) { // ENTER
                    makeSelection(event);
                    currentListForCopy.disabled = true;
                    setTimeout(function () {
                        currentListForCopy.disabled = false;
                    }, 200);

                    return true;
                }
                if (!event.shiftKey && $dom.keyPressed(event, ['ArrowUp', 'ArrowDown'])) {
                    if (event.cancelable) {
                        event.preventDefault();
                    }
                    return true;
                }
                return null;
            };

            currentListForEl.onkeypress = function (event) {
                if (!event.shiftKey && $dom.keyPressed(event, ['ArrowUp', 'ArrowDown'])) {
                    if (event.cancelable) {
                        event.preventDefault();
                    }
                    return true;
                }
                return null;
            };
            list.onkeypress = function (event) {
                if (!event.shiftKey && $dom.keyPressed(event, ['Enter', 'ArrowUp', 'ArrowDown'])) {
                    if (event.cancelable) {
                        event.preventDefault();
                    }
                    return true;
                }
                return null;
            };

            list.addEventListener($cms.browserMatches('ios') ? 'change' : 'click', makeSelection, false);

            currentListForEl = null;

            function handleArrowUsage(event) {
                var temp;

                if (!event.shiftKey && $dom.keyPressed(event, 'ArrowDown')) { // DOWN
                    currentListForCopy.disabled = true;
                    setTimeout(function () {
                        currentListForCopy.disabled = false;
                    }, 1000);

                    temp = currentListForCopy.onblur;
                    currentListForCopy.onblur = function () {
                    };
                    list.focus();
                    currentListForCopy.onblur = temp;
                    if (!currentListForCopy.downOnce) {
                        currentListForCopy.downOnce = true;
                        list.selectedIndex = 0;
                    } else {
                        if (list.selectedIndex < list.options.length - 1) {
                            list.selectedIndex++;
                        }
                    }
                    list.options[list.selectedIndex].selected = true;
                    return true;
                }

                if (!event.shiftKey && $dom.keyPressed(event, 'ArrowUp')) { // UP
                    currentListForCopy.disabled = true;
                    setTimeout(function () {
                        currentListForCopy.disabled = false;
                    }, 1000);

                    temp = currentListForCopy.onblur;
                    currentListForCopy.onblur = function () {};
                    list.focus();
                    currentListForCopy.onblur = temp;
                    if (!currentListForCopy.downOnce) {
                        currentListForCopy.downOnce = true;
                        list.selectedIndex = 0;
                    } else {
                        if (list.selectedIndex > 0) {
                            list.selectedIndex--;
                        }
                    }
                    list.options[list.selectedIndex].selected = true;
                    return true;
                }
                return null;
            }

            function makeSelection(e) {
                var el = e.target;

                currentListForCopy.value = el.value;
                currentListForCopy.onkeyup = currentListForCopy.oldOnkeyup;
                currentListForCopy.onchange = currentListForCopy.oldOnchange;
                currentListForCopy.onkeypress = function () {};
                if (currentListForCopy.onrealchange) {
                    currentListForCopy.onrealchange(e);
                }
                if (currentListForCopy.onchange) {
                    currentListForCopy.onchange(e);
                }
                var al = $dom.$id('ajax_list');
                al.parentNode.removeChild(al);
                setTimeout(function () {
                    currentListForCopy.focus();
                }, 300);
            }
        }
    };
}(window.$cms, window.$util, window.$dom));
