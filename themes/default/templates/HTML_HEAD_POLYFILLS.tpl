{$,Required for $cms.requireJavascript() to work properly as DOM does not currently provide any way to check if a particular script has been already loaded}
<script {$CSP_NONCE_HTML} src="{$BASE_URL*}/data/polyfills/dom_init.js"></script>

{$,Load classList and ES6 Promise polyfill for Internet Explorer LEGACY}
{+START,IF,{$BROWSER_MATCHES,ie}}
<script {$CSP_NONCE_HTML} src="{$BASE_URL*}/data/polyfills/class_list.js"></script>
<script {$CSP_NONCE_HTML} src="{$BASE_URL*}/data/polyfills/promise.js"></script>
<script {$CSP_NONCE_HTML} src="{$BASE_URL*}/data/polyfills/svg4everybody.js"></script>
{+END}
