<{if $error_message|default:'' != ''}>
    <div class="errorMsg" style="text-align: left;">
        <{$error_message}>
    </div>
<{else}>
	<{$form}>
<{/if}>