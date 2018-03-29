{$REQUIRE_JAVASCRIPT,core_form_interfaces}

<div data-tpl="comcodeEditor" data-tpl-params="{+START,PARAMS_JSON,POSTING_FIELD}{_*}{+END}">
	<div class="posting-form-insert-buttons">
		<span>{!ADD}:</span>
		{BUTTONS}
	</div>

	{+START,IF_NON_EMPTY,{MICRO_BUTTONS}}
		<p class="accessibility-hidden"><label for="f_face">{!FONT}</label></p>
		<p class="accessibility-hidden"><label for="f_size">{!SIZE}</label></p>
		<p class="accessibility-hidden"><label for="f_colour">{!COLOUR}</label></p>

		<div class="posting-form-wrap-buttons">
			{MICRO_BUTTONS}

			<select id="f_face" name="f_face">
				<option value="/">[{!FONT}]</option>
				{+START,LOOP,={$FONTS}}
					<option value="{_loop_var*}" style="font-family: '{_loop_var;*}'">{_loop_var*}</option>
				{+END}
			</select>
			<select id="f_size" name="f_size">
				<option value="">[{!SIZE}]</option>
				{+START,IF,{$EQ,{$CONFIG_OPTION,wysiwyg_font_units},em}}
					{+START,LOOP,0.8\,1\,1.5\,2\,2.5\,3\,4}
						<option value="{_loop_var*}">{_loop_var*}em</option>
					{+END}
				{+END}
				{+START,IF,{$NEQ,{$CONFIG_OPTION,wysiwyg_font_units},em}}
					{+START,LOOP,8\,9\,10\,11\,12\,14\,16\,18\,20\,22\,24\,36\,48\,72}
						<option value="{_loop_var*}px">{_loop_var*}px</option>
					{+END}
				{+END}
			</select>
			<select id="f_colour" name="f_colour">
				<option value="">[{!COLOUR}]</option>
				<option value="black" style="color: black">{!BLACK}</option>
				<option value="blue" style="color: blue">{!BLUE}</option>
				<option value="gray" style="color: gray">{!GRAY}</option>
				<option value="green" style="color: green">{!GREEN}</option>
				<option value="orange" style="color: orange">{!ORANGE}</option>
				<option value="purple" style="color: purple">{!PURPLE}</option>
				<option value="red" style="color: red">{!RED}</option>
				<option value="white" style="color: white">{!WHITE}</option>
				<option value="yellow" style="color: yellow">{!YELLOW}</option>
			</select>

			<a href="#!" class="js-click-do-input-font-posting-field"><img title="{!INPUT_COMCODE_font}" alt="{!INPUT_COMCODE_font}" height="34" src="{$IMG*,comcode_editor/apply_changes}" /></a>
		</div>
	{+END}
</div>
