<div class="float-surrounder zebra-{$CYCLE,addon_export,0,1}">
	<form title="{!EXPORT_ADDON}: {NAME*}" action="{URL*}" method="post" class="{$CYCLE*,zz,zebra-0,zebra-1}" autocomplete="off">
		{$INSERT_SPAMMER_BLACKHOLE}

		<div class="right float-separation">
			<button data-disable-on-click="1" class="btn btn-primary btn-scri admin--export" type="submit" title="{!EXPORT_ADDON}: {NAME*}">{+START,INCLUDE,ICON}NAME=admin/export{+END} {!EXPORT_ADDON}</button>
		</div>

		{FILES}

		<p>{NAME*}</p>
	</form>
</div>
