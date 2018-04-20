{$REQUIRE_JAVASCRIPT,core_cns}

<section id="tray-{!MEMBER|}" data-tpl="cnsGuestBar" data-toggleable-tray="{ save: true }" class="box cns-information-bar-outer">
	<h2 class="toggleable-tray-title js-tray-header">
		<a class="toggleable-tray-button js-tray-onclick-toggle-tray inline-desktop" href="#!" title="{!CONTRACT}">
			{+START,INCLUDE,ICON}
				NAME=trays/contract
				ICON_SIZE=24
			{+END}
		</a>

		<a class="toggleable-tray-button js-tray-onclick-toggle-tray" href="#!">{!_LOGIN}{+START,IF,{$ADDON_INSTALLED,search}}{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,search}} / {!SEARCH}{+END}{+END}</a>
	</h2>

	<div class="toggleable-tray js-tray-content">
		<div class="cns-information-bar float-surrounder">
			<div class="cns-guest-column cns-guest-column-a">
				<form title="{!_LOGIN}" class="inline js-submit-check-field-login-username" action="{LOGIN_URL*}" method="post" autocomplete="on">
					{$INSERT_SPAMMER_BLACKHOLE}

					<div>
						<div class="accessibility-hidden"><label for="member-bar-login-username">{$LOGIN_LABEL}</label></div>
						<div class="accessibility-hidden"><label for="member-bar-s-password">{!PASSWORD}</label></div>
						<input size="15" type="text" placeholder="{!USERNAME}" id="member-bar-login-username" name="login_username" />
						<input size="15" type="password" placeholder="{!PASSWORD}" name="password" id="member-bar-s-password" />
						{+START,IF,{$CONFIG_OPTION,password_cookies}}
							<label for="remember">{!REMEMBER_ME}:</label>
							<input class="{+START,IF,{$NOT,{$CONFIG_OPTION,remember_me_by_default}}}js-click-checkbox-remember-me-confirm{+END}"{+START,IF,{$CONFIG_OPTION,remember_me_by_default}} checked="checked"{+END} type="checkbox" value="1" id="remember" name="remember" />
						{+END}
						<button class="button-screen-item menu--site-meta--user-actions--login" type="submit">{+START,INCLUDE,ICON}NAME=menu/site_meta/user_actions/login{+END} {!_LOGIN}</button>

						<ul class="horizontal-links associated-links-block-group">
							<li><a href="{JOIN_URL*}">{!_JOIN}</a></li>
							<li><a data-open-as-overlay="{}" rel="nofollow" href="{FULL_LOGIN_URL*}" title="{!MORE}: {!_LOGIN}">{!MORE}</a></li>
						</ul>
					</div>
				</form>
			</div>
			{+START,IF,{$ADDON_INSTALLED,search}}{+START,IF,{$HAS_ACTUAL_PAGE_ACCESS,search}}
				<div class="cns-guest-column cns-guest-column-c">
					{+START,INCLUDE,MEMBER_BAR_SEARCH}{+END}
				</div>
			{+END}{+END}

			<nav class="cns-guest-column cns-member-column-d">
				{$,<p class="cns-member-column-title">{!VIEW}:</p>}
				<ul class="actions-list">
					<li><a data-open-as-overlay="{}" href="{NEW_POSTS_URL*}">{!POSTS_SINCE}</a></li>
					<li><a data-open-as-overlay="{}" href="{UNANSWERED_TOPICS_URL*}">{!UNANSWERED_TOPICS}</a></li>
				</ul>
			</nav>
		</div>
	</div>
</section>
