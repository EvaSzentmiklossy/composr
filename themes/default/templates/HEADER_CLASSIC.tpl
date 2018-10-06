<header itemscope="itemscope" itemtype="http://schema.org/WPHeader" class="header-classic with-seed-navbar">
	{$,This allows screen-reader users (e.g. blind users) to jump past the panels etc to the main content}
	<a accesskey="s" class="accessibility-hidden" href="#maincontent">{!SKIP_NAVIGATION}</a>

	{$,The banner}
	{+START,IF,{$DESKTOP}}
		{$SET-,BANNER,{$BANNER}} {$,This is to avoid evaluating the banner twice}
		{+START,IF_NON_EMPTY,{$GET,BANNER}}
			<div class="global-banner block-desktop container">{$GET,BANNER}</div>
		{+END}
	{+END}

	{$,The main logo}
	<h1 class="logo-outer container">
		<a target="_self" href="{$PAGE_LINK*,:}" rel="home" title="{!HOME}">
			{+START,IF,{$NOT,{$THEME_OPTION,use_site_name_text_as_logo}}}
			<img class="logo" src="{$LOGO_URL*}" alt="{$SITE_NAME*}" />
			{+END}
			{+START,IF,{$THEME_OPTION,use_site_name_text_as_logo}}
			<span class="logo">{$SITE_NAME*}</span>
			{+END}
		</a>
	</h1>

	{$,Main menu}
	<div class="global-navigation">
		<div class="container">
			<div class="global-navigation-inner">
				{$BLOCK,block=menu,param={$CONFIG_OPTION,header_menu_call_string},type=dropdown}

				{$,Login form for guests}
				{+START,IF,{$IS_GUEST}}{+START,IF,{$CONFIG_OPTION,block_top_login}}
				<div class="top-form top-login">
					{$BLOCK,block=top_login}
				</div>
				{+END}{+END}

				{+START,IF,{$NOT,{$IS_GUEST}}}{+START,IF,{$OR,{$CONFIG_OPTION,block_top_notifications},{$CONFIG_OPTION,block_top_personal_stats}}}
				<div class="top-buttons">
					{+START,IF,{$CONFIG_OPTION,block_top_notifications}}{$BLOCK,block=top_notifications}{+END}

					{+START,IF,{$CONFIG_OPTION,block_top_personal_stats}}{$BLOCK,block=top_personal_stats}{+END}

					{$,Search box for logged in users [could show to guests, except space is lacking]}
					{+START,IF,{$AND,{$ADDON_INSTALLED,search},{$DESKTOP},{$NOT,{$IS_GUEST}}}}{+START,IF,{$CONFIG_OPTION,block_top_search,1}}
						{$BLOCK,block=top_search,block_id=desktop,failsafe=1,limit_to={$?,{$MATCH_KEY_MATCH,forum:_WILD},cns_posts,all_defaults}}
					{+END}{+END}
				</div>
				{+END}{+END}
			</div>
		</div>
	</div>
</header>