{$REQUIRE_JAVASCRIPT,iotds}

<div class="box box___iotd_box" data-require-javascript="iotds" data-tpl="iotdBox">
	<div class="box-inner">
		{+START,IF,{GIVE_CONTEXT}}
			{+START,SET,content_box_title}
				{+START,IF_NON_EMPTY,{I_TITLE}}{!CONTENT_IS_OF_TYPE,{!IOTD},{I_TITLE}}{+END}
				{+START,IF_EMPTY,{I_TITLE}}{!IOTD}{+END}
			{+END}
			{+START,IF,{$NOT,{$GET,skip_content_box_title}}}
				<h3>{$GET,content_box_title}</h3>
			{+END}
		{+END}

		{+START,IF_NON_EMPTY,{THUMB_URL}}
			<div class="right float-separation">
				{+START,IF_NON_EMPTY,{VIEW_URL}}<a title="{I_TITLE*}" href="{VIEW_URL*}">{+END}<img alt="{!THUMBNAIL}" src="{THUMB_URL*}" />{+START,IF_NON_EMPTY,{VIEW_URL}}</a>{+END}
			</div>
		{+END}

		{+START,IF_PASSED,USERNAME}{+START,IF_NON_EMPTY,{USERNAME}}
			<div class="meta-details" role="note">
				<ul class="meta-details-list">
					<li>{!SUBMITTED_BY,<a href="{$MEMBER_PROFILE_URL*,{SUBMITTER}}">{$DISPLAYED_USERNAME*,{USERNAME}}</a>}</li>
				</ul>
			</div>
		{+END}{+END}

		{+START,IF_NON_EMPTY,{CAPTION}}
			<div class="associated-details">
				{$PARAGRAPH,{CAPTION}}
			</div>
		{+END}

		{+START,IF_PASSED,CHOOSE_URL}{+START,IF_PASSED,EDIT_URL}{+START,IF_PASSED,IS_CURRENT}
			<div style="margin-right: {$CONFIG_OPTION,thumb_width}px" class="buttons-group">
				{+START,IF,{$NOT,{IS_CURRENT}}}<form title="{!CHOOSE} {!IOTD} #{ID*}" class="inline" action="{CHOOSE_URL*}" method="post" autocomplete="off"><input type="hidden" name="id" value="{ID*}" /><input class="button-screen-item buttons--choose" type="submit" value="{!CHOOSE}" title="{!CHOOSE} {!IOTD} #{ID*}" /></form>{+END}
				<a class="button-screen-item buttons--edit" rel="edit" href="{EDIT_URL*}"><span>{!EDIT}: {!IOTD} #{ID*}</span></a>
				<form class="inline js-submit-confirm-iotd-deletion" title="{!DELETE} {!IOTD} #{ID*}" action="{DELETE_URL*}" method="post" autocomplete="off">{$INSERT_SPAMMER_BLACKHOLE}<input type="hidden" name="id" value="{ID*}" />
					<input class="button-screen-item menu___generic_admin__delete" type="submit" value="{!DELETE}" title="{!DELETE} {!IOTD} #{ID*}" />
				</form>
			</div>
		{+END}{+END}{+END}
	</div>
</div>
