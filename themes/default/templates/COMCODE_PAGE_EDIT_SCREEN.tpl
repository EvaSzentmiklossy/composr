{TITLE}

{+START,INCLUDE,HANDLE_CONFLICT_RESOLUTION}{+END}
{+START,IF_PASSED,WARNING_DETAILS}
	{WARNING_DETAILS}
{+END}

{TEXT}

{+START,IF,{$NOT,{NEW}}}
	{$SET,extra_buttons,<a class="btn btn-danger btn-scr" href="{DELETE_URL*}"><span>{+START,INCLUDE,ICON}NAME=admin/delete3{+END} {!DELETE}</span></a>}
{+END}

{POSTING_FORM}

{REVISIONS}
