Channel (#mychannel) or User (@myuser): <i>optional</i>
<div style="margin-left:10px;margin-bottom:10px;">
	<input type="text" name="{$namePrefix}[room]" size="24" style="width:100%;" value="{if !empty($params.room)}{$params.room}{else}{$default_room}{/if}" class="placeholders">
</div>

From: <i>optional</i>
<div style="margin-left:10px;margin-bottom:10px;">
	<input type="text" name="{$namePrefix}[from]" size="15" maxlength="15" style="width:100%;" value="{$params.from}" class="placeholders">
</div>

<b>Message:</b>
<label><input type="radio" name="{$namePrefix}[is_markdown]" value="0" {if empty($params.is_markdown)}checked="checked"{/if}> Text</label>
<label><input type="radio" name="{$namePrefix}[is_markdown]" value="1" {if $params.is_markdown}checked="checked"{/if}> Markdown</label>
<div style="margin-left:10px;margin-bottom:10px;">
	<textarea name="{$namePrefix}[content]" rows="3" cols="45" required="required" style="width:100%;" class="placeholders">{$params.content}</textarea>
</div>

<b>Color:</b>
<div style="margin-left:10px;margin-bottom:10px;">
	{$colors = ['','yellow','red','green','purple','gray']}
	<select name="{$namePrefix}[color]">
		{foreach from=$colors item=color}
		<option value="{$color}" {if $color==$params.color}selected="selected"{/if}>{$color}</option>
		{/foreach}
	</select>
</div>

<b>Also run behavior in simulator mode:</b>
<div style="margin-left:10px;margin-bottom:10px;">
	<label><input type="radio" name="{$namePrefix}[run_in_simulator]" value="1" {if $params.run_in_simulator}checked="checked"{/if}> {'common.yes'|devblocks_translate|capitalize}</label>
	<label><input type="radio" name="{$namePrefix}[run_in_simulator]" value="0" {if !$params.run_in_simulator}checked="checked"{/if}> {'common.no'|devblocks_translate|capitalize}</label>
</div>

<script type="text/javascript">
$action = $('fieldset#{$namePrefix}');
$action.find('textarea').autosize();
</script>
