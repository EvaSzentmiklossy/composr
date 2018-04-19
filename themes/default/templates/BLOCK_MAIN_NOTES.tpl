{$REQUIRE_JAVASCRIPT,checking}
{$REQUIRE_JAVASCRIPT,core_adminzone_dashboard}

<div class="form-ajax-target" data-view="BlockMainNotes" data-view-params="{+START,PARAMS_JSON,BLOCK_NAME,MAP}{_*}{+END}">
	<section id="tray-{TITLE|}" data-toggleable-tray="{ save: true }" class="box box---block-main-notes">
		<h3 class="toggleable-tray-title js-tray-header">
			<a class="toggleable-tray-button" data-click-tray-toggle="#tray-{TITLE|}" href="#!" title="{!CONTRACT}">
				{+START,INCLUDE,ICON}
					NAME=trays/contract
					SIZE=24
				{+END}
			</a>

			{+START,IF_NON_EMPTY,{TITLE}}
				<a class="toggleable-tray-button" data-click-tray-toggle="#tray-{TITLE|}" href="#!">{TITLE*}</a>
			{+END}
		</h3>

		<div class="toggleable-tray js-tray-content">
			<form title="{$STRIP_TAGS,{TITLE}}" method="post" action="{URL*}" autocomplete="off" class="js-form-block-main-notes">
				{$INSERT_SPAMMER_BLACKHOLE}

				<div class="accessibility-hidden"><label for="n-block-{TITLE|}">{!NOTES}</label></div>
				<div>
					<textarea class="wide-field js-focus-textarea-expand js-blur-textarea-contract{+START,IF,{SCROLLS}} textarea-scroll{+END}" cols="80" id="n-block-{TITLE|}" rows="10" name="new">{CONTENTS*}</textarea>
				</div>

				<div class="buttons-group">
					<button data-disable-on-click="1" class="button-screen-item buttons--save js-hover-disable-textarea-size-change {+START,IF,{$HAS_PRIVILEGE,comcode_dangerous}}js-click-headless-submit{+END}" type="submit">{+START,INCLUDE,ICON}NAME=buttons/save{+END} {!SAVE}</button>
				</div>
			</form>
		</div>
	</section>
</div>
