'use strict';

window.previousCommands || (window.previousCommands = []);
(window.currentCommand !== undefined) ||  (window.currentCommand = null);

(function ($cms) {
    'use strict';

    $cms.templates.commandrMain = function commandrMain(params, container) {
        $cms.dom.on(container, 'submit', '.js-submit-commandr-form-submission', function (e, form) {
            $cms.requireJavascript('core_form_interfaces').then(function () {
                commandrFormSubmission($cms.dom.$('#commandr_command').value, form);
            });
        });

        $cms.dom.on(container, 'keyup', '.js-keyup-input-commandr-handle-history', function (e, input) {
            if (commandrHandleHistory(input, e.keyCode ? e.keyCode : e.charCode, e) === false) {
                e.preventDefault();
            }
        });
    };

    $cms.templates.commandrLs = function commandrLs(params, container) {
        $cms.dom.on(container, 'click', '.js-click-set-directory-command', function (e, clicked) {
            var filename = strVal(clicked.dataset.tpFilename),
                commandInput = $cms.dom.$('#commandr_command');

            commandInput.value = 'cd "' + filename + '"';
            $cms.dom.trigger(commandInput.nextElementSibling, 'click');
        });

        $cms.dom.on(container, 'click', '.js-click-set-file-command', function (e, clicked) {
            var filename = strVal(clicked.dataset.tpFilename),
                commandInput = $cms.dom.$('#commandr_command');

            if (commandInput.value !== '') {
                commandInput.value = commandInput.value.replace(/\s*$/, '') + ' "' + filename + '"';
                commandInput.focus();
            } else {
                commandInput.value = 'cat "' + filename + '"';
                $cms.dom.trigger(commandInput.nextElementSibling, 'click');
            }
        });
    };

    $cms.templates.commandrCommand = function commandrCommand(params) {
        var stdcommand = strVal(params.stdcommand);

        if (stdcommand) {
            eval(stdcommand);
        }
    };

    $cms.templates.commandrCommands = function commandrCommands(params, container) {
        var commandInput = $cms.dom.$('#commandr_command');
        
        $cms.dom.on(container, 'click', '.js-click-enter-command', function (e, target) {
            var command = strVal(target.dataset.tpCommand);
            commandInput.value = command;
            commandInput.focus();
        });
    };

    $cms.templates.commandrEdit = function commandrEdit(params, container) {
        var file = strVal(params.file);

        $cms.dom.on(container, 'submit', '.js-submit-commandr-form-submission', function (e, form) {
            var command = 'write "' + file + '" "' + form.elements['edit_content'].value.replace(/\\/g, '\\\\').replace(/</g, '\\<').replace(/>/g, '\\>').replace(/"/g, '\\"') + '"';
            $cms.requireJavascript('core_form_interfaces').then(function () {
                commandrFormSubmission(command, form);
            });
        });
    };

    // Deal with Commandr history
    function commandrHandleHistory(element, keyCode, e) {
        if ((keyCode == 38) && (window.previousCommands.length > 0)) { // Up button
            e && event.stopPropagation();
            if (e.cancelable) {
                e.preventDefault();
            }

            if (window.currentCommand == null) {
                window.currentCommand = window.previousCommands.length - 1;
                element.value = window.previousCommands[window.currentCommand];
            }
            else if (window.currentCommand > 0) {
                window.currentCommand--;
                element.value = window.previousCommands[window.currentCommand];
            }
            return false;
        } else if ((keyCode == 40) && (window.previousCommands.length > 0)) { // Down button
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }

            if (window.currentCommand != null) {
                if (window.currentCommand < window.previousCommands.length - 1) {
                    window.currentCommand++;
                    element.value = window.previousCommands[window.currentCommand];
                }
                else {
                    window.currentCommand = null;
                    element.value = '';
                }
            }
            return false;
        } else {
            window.currentCommand = null;
            return true;
        }
    }

    // Submit an Commandr command
    function commandrFormSubmission(command, form) {
        // Catch the data being submitted by the form, and send it through XMLHttpRequest if possible. Stop the form submission if this is achieved.
        // var command=document.getElementById('commandr_command').value;
        // Send it through XMLHttpRequest, and append the results.
        document.getElementById('commandr_command').focus();
        document.getElementById('commandr_command').disabled = true;

        var post = 'command=' + encodeURIComponent(command);
        post = $cms.form.modSecurityWorkaroundAjax(post);
        $cms.doAjaxRequest('{$FIND_SCRIPT;,commandr}' + $cms.keepStub(true), commandrCommandResponse, post);

        window.disableTimeout = setTimeout(function () {
            document.getElementById('commandr_command').disabled = false;
            document.getElementById('commandr_command').focus();
            if (window.disableTimeout) {
                clearTimeout(window.disableTimeout);
                window.disableTimeout = null;
            }
        }, 5000);
        window.previousCommands.push(command);
    }


    // Deal with the response to a command
    function commandrCommandResponse(responseXml) {
        var ajaxResult = responseXml && responseXml.querySelector('result');

        if (window.disableTimeout) {
            clearTimeout(window.disableTimeout);
            window.disableTimeout = null;
        }

        document.getElementById('commandr_command').disabled = false;
        document.getElementById('commandr_command').focus();

        var command = document.getElementById('commandr_command');
        var commandPrompt = document.getElementById('command_prompt');
        var cl = document.getElementById('commands_go_here');
        var newCommand = document.createElement('div');
        var pastCommandPrompt = document.createElement('p');
        var pastCommand = document.createElement('div');

        newCommand.setAttribute('class', 'command float_surrounder');
        pastCommandPrompt.setAttribute('class', 'past_command_prompt');
        pastCommand.setAttribute('class', 'past_command');

        if (!ajaxResult) {
            var stderrText = document.createTextNode('{!commandr:ERROR_NON_TERMINAL;^}\n{!INTERNAL_ERROR;^}');
            var stderrTextP = document.createElement('p');
            stderrTextP.setAttribute('class', 'error_output');
            stderrTextP.appendChild(stderrText);
            pastCommand.appendChild(stderrTextP);

            newCommand.appendChild(pastCommand);
            cl.appendChild(newCommand);

            command.value = '';
            var cl2 = document.getElementById('command_line');
            cl2.scrollTop = cl2.scrollHeight;

            return;
        }

        // Deal with the response: add the result to the command_line
        var method = ajaxResult.querySelector('command').textContent;
        var stdcommand = ajaxResult.querySelector('stdcommand').textContent;
        var stdhtml = ajaxResult.querySelector('stdhtml').firstElementChild;
        var stdout = ajaxResult.querySelector('stdout').textContent;
        var stderr = ajaxResult.querySelector('stderr').textContent;

        var pastCommandText = document.createTextNode(method + ' \u2192 ');
        pastCommandPrompt.appendChild(pastCommandText);

        newCommand.appendChild(pastCommandPrompt);

        if (stdout != '') {
            // Text-only. Any HTML should've been escaped server-side. Escaping it over here with the DOM getting in the way is too complex.
            var stdoutText = document.createTextNode(stdout);
            var stdoutTextP = document.createElement('p');
            stdoutTextP.setAttribute('class', 'text_output');
            stdoutTextP.appendChild(stdoutText);
            pastCommand.appendChild(stdoutTextP);
        }

        if (stdhtml.childNodes) {
            var childNode, newChild, clonedNode;
            for (var i = 0; i < stdhtml.childNodes.length; i++) {
                childNode = stdhtml.childNodes[i];

                newChild = childNode;
                try {
                    newChild = document.importNode(childNode, true);
                } catch (ignore) {}

                clonedNode = newChild.cloneNode(true);
                pastCommand.appendChild(clonedNode);
            }
        }

        if (stdcommand != '') {
            // JavaScript commands; eval() them.
            eval(stdcommand);

            var stdcommandText = document.createTextNode('{!commandr:JAVASCRIPT_EXECUTED;^}');
            var stdcommandTextP = document.createElement('p');
            stdcommandTextP.setAttribute('class', 'command_output');
            stdcommandTextP.appendChild(stdcommandText);
            pastCommand.appendChild(stdcommandTextP);
        }

        if ((stdcommand == '') && (!stdhtml.childNodes) && (stdout == '')) {
            // Exit with an error.
            if (stderr != '') var stderrText = document.createTextNode('{!commandr:PROBLEM_ACCESSING_RESPONSE;^}\n' + stderr);
            else var stderrText = document.createTextNode('{!commandr:TERMINAL_PROBLEM_ACCESSING_RESPONSE;^}');
            var stderrTextP = document.createElement('p');
            stderrTextP.setAttribute('class', 'error_output');
            stderrTextP.appendChild(stderrText);
            pastCommand.appendChild(stderrTextP);

            return false;
        }
        else if (stderr != '') {
            var stderrText = document.createTextNode('{!commandr:ERROR_NON_TERMINAL;^}\n' + stderr);
            var stderrTextP = document.createElement('p');
            stderrTextP.setAttribute('class', 'error_output');
            stderrTextP.appendChild(stderrText);
            pastCommand.appendChild(stderrTextP);
        }

        newCommand.appendChild(pastCommand);
        cl.appendChild(newCommand);

        command.value = '';
        var cl2 = document.getElementById('command_line');
        cl2.scrollTop = cl2.scrollHeight;

        return true;
    }


    // Clear the command line
    window.clearCl = clearCl;
    function clearCl() {
        // Clear all results from the CL
        var commandLine = document.getElementById('commands_go_here');
        var elements = commandLine.querySelectorAll('.command');

        for (var i = 0; i < elements.length; i++) {
            commandLine.removeChild(elements[i]);
        }
    }

    // Fun stuff...
    window.commandrFoxyTextnodes || (window.commandrFoxyTextnodes = []);

    window.bsod = bsod;
    function bsod() {
        // Nothing to see here, move along.
        var commandLine = document.getElementById('commands_go_here');
        commandLine.style.backgroundColor = '#0000FF';
        bsodTraverseNode(window.document.documentElement);
        setInterval(foxy, 1);

        function bsodTraverseNode(node) {
            var i, t;
            for (i = 0; i < node.childNodes.length; i++) {
                t = node.childNodes[i];
                if (t.nodeType === 3) {
                    if ((t.data.length > 1) && (Math.random() < 0.3)) {
                        window.commandrFoxyTextnodes[window.commandrFoxyTextnodes.length] = t;
                    }
                }
                else bsodTraverseNode(t);
            }
        }

        function foxy() {
            var rand = Math.round(Math.random() * (window.commandrFoxyTextnodes.length - 1));
            var t = window.commandrFoxyTextnodes[rand];
            var at = Math.round(Math.random() * (t.data.length - 1));
            var aChar = t.data.charCodeAt(at);
            if ((aChar > 33) && (aChar < 126)) {
                var string = 'The quick brown fox jumps over the lazy dog.';
                var rep = string.charAt(at % string.length);
                t.replaceData(at, 1, rep);
            }
        }
    }

}(window.$cms));