{$SET,icon_id,{$REPLACE,/,__,{NAME}}}{$SET,icon_size,{+START,IF_PASSED,ICON_SIZE}{ICON_SIZE}{+END}{+START,IF_NON_PASSED,ICON_SIZE}16{+END}}{+START,IF,{$IS_ICON_IN_SVG_SPRITE,{NAME}}}<svg {+START,IF_PASSED,ICON_ID}id="{ICON_ID*}"{+END} class="icon icon-{$REPLACE,_,-,{$REPLACE,/,--,{NAME}}}{+START,IF_PASSED,ICON_CLASS} {ICON_CLASS*}{+END}" role="presentation" width="{$GET*,icon_size}" height="{$GET*,icon_size}"{+START,IF_PASSED,ICON_ATTRS}{ICON_ATTRS}{+END}>{+START,IF_PASSED,ICON_TITLE}<title>{ICON_TITLE*}</title>{+END}{+START,IF_PASSED,ICON_DESCRIPTION}<desc>{ICON_DESCRIPTION*}</desc>{+END}<use xlink:href="{$PREG_REPLACE,^https?://[^/]+,,{$IMG,icons{$?,{$CONFIG_OPTION,use_monochrome_icons},_monochrome}_sprite}}#{$GET,icon_id}"/></svg>{+END}{+START,IF,{$NOT,{$IS_ICON_IN_SVG_SPRITE,{NAME}}}}<img {+START,IF_PASSED,ICON_ID}id="{ICON_ID*}"{+END} class="icon icon-{$REPLACE,_,-,{$REPLACE,/,--,{NAME}}}{+START,IF_PASSED,ICON_CLASS} {ICON_CLASS*}{+END}" width="{$GET*,icon_size}" height="{$GET*,icon_size}"{+START,IF_PASSED,ICON_TITLE}title="{ICON_TITLE*}"{+END} alt="{+START,IF_PASSED,ICON_DESCRIPTION}{ICON_DESCRIPTION*}{+END}" src="{$IMG*,icons{$?,{$CONFIG_OPTION,use_monochrome_icons},_monochrome}/{NAME}}"{+START,IF_PASSED,ICON_ATTRS}{ICON_ATTRS}{+END}>{+END}