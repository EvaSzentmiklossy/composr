{$REQUIRE_JAVASCRIPT,core_adminzone_dashboard}

<section id="tray_{!CHECK_LIST|}" class="box box---block-main-staff-checklist" data-toggleable-tray="{ save: true }" data-tpl="blockMainStaffChecklist">
	<h3 class="toggleable-tray-title js-tray-header">
		<a href="#!" id="checklist_show_all_link" class="top-left-toggle js-click-disable-task-hiding" title="{!SHOW_ALL}: {!CHECK_LIST}">{!SHOW_ALL}</a>
		<a href="#!" id="checklist_hide_done_link" class="top-left-toggle js-click-enable-task-hiding">{!HIDE_DONE}</a>

		<a class="toggleable-tray-button js-tray-onclick-toggle-tray" href="#!"><img alt="{!CONTRACT}: {$STRIP_TAGS,{!CHECK_LIST}}" title="{!CONTRACT}" width="24" height="24" src="{$IMG*,icons/trays/contract2}" /></a>
		<a class="toggleable-tray-button js-tray-onclick-toggle-tray" href="#!">{!CHECK_LIST}</a>
	</h3>

	<div class="toggleable-tray js-tray-content">
		{+START,IF_NON_EMPTY,{DATES}}
			<h4 class="checklist-header">{!REGULAR_TASKS}</h4>

			{DATES}
		{+END}

		{+START,IF_NON_EMPTY,{TODO_COUNTS}}
			<h4 class="checklist-header">{!ONE_OFF_TASKS}</h4>

			{TODO_COUNTS}
		{+END}

		<h4 class="checklist-header">{!CUSTOM_TASKS}</h4>

		<div id="custom-tasks-go-here">
			{CUSTOM_TASKS}
		</div>

		<form title="{!CUSTOM_TASKS}" action="{URL*}" method="post" data-submit-pd="1" class="add-custom-task js-submit-custom-task" autocomplete="off">
			{$INSERT_SPAMMER_BLACKHOLE}

			<div class="right">
				<label class="accessibility-hidden" for="recur-interval">{!TASK_LENGTH}</label>
				<label class="accessibility-hidden" for="recur_every">{!TASK_LENGTH_UNITS}</label>
				{!RECUR_EVERY,<input maxlength="8" type="number" id="recur-interval" name="recur_interval" size="3" />,<select id="recur_every" name="recur_every"><option value="mins">{!dates:DPLU_MINUTES}</option><option value="hours">{!dates:DPLU_HOURS}</option><option value="days">{!dates:DPLU_DAYS}</option><option value="months">{!dates:DPLU_MONTHS}</option></select>}
				<input class="button-micro admin--add" type="submit" name="save" title="{!ADD} ({!CUSTOM_TASKS})" value="{!ADD}" />
			</div>
			<div>
				<a data-open-as-overlay="{}" class="link-exempt" title="{!COMCODE_MESSAGE,Comcode} {!LINK_NEW_WINDOW}" target="_blank" href="{$PAGE_LINK*,_SEARCH:userguide_comcode}"><img class="vertical-alignment" alt="{!COMCODE_MESSAGE,Comcode}" width="16" height="16" src="{$IMG*,icons/editor/comcode}" /></a>
				<label class="accessibility-hidden" for="new_task">{!DESCRIPTION}</label>
				<input maxlength="255" type="text" id="new_task" name="new_task" size="32" />
			</div>
		</form>

		{+START,IF_NON_EMPTY,{NO_TIMES}}
			<h4 class="checklist-header">{!OTHER_MAINTENANCE}</h4>

			<div class="float-surrounder checklist-other-maintenance">
				{NO_TIMES}
			</div>
		{+END}
	</div>
</section>
