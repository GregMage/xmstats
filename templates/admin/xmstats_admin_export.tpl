<script type="text/javascript">
    IMG_ON = "<{xoAdminIcons 'success.png'}>";
    IMG_OFF = "<{xoAdminIcons 'cancel.png'}>";
</script>
<div>
    <{$renderbutton|default:''}>
</div>
<{if $error_message|default:'' != ''}>
    <div class="errorMsg" style="text-align: left;">
        <{$error_message}>
    </div>
<{/if}>
<{if $form|default:'' != ''}>
    <div>
        <{$form}>
        <script type="text/javascript">
        (function () {
            var form = document.forms['form'];
            if (!form) return;
            var typeEl = form.querySelector('[name="export_type"]');
            var fieldEl = form.querySelector('[name="export_fid"]');
            var sidEl   = form.querySelector('[name="export_sid[]"]');
            if (!typeEl) return;

            // trouve la ligne contenant le champ (prend tr si présent, sinon parent direct)
            function findRow(el) {
                if (!el) return null;
                var row = el.closest ? el.closest('tr') : null;
                if (!row) {
                    var p = el.parentNode;
                    while (p && p !== form && p.nodeName.toLowerCase() !== 'tr') {
                        p = p.parentNode;
                    }
                    row = (p && p.nodeName && p.nodeName.toLowerCase() === 'tr') ? p : el.parentNode;
                }
                return row;
            }

            var fidRow = findRow(fieldEl);
            var sidRow = findRow(sidEl);

            if (fidRow) fidRow.id = fidRow.id || 'xmstats_export_fid_row';
            if (sidRow) sidRow.id = sidRow.id || 'xmstats_export_sid_row';

            function update() {
                var val = typeEl.value || (typeEl.options && typeEl.options[typeEl.selectedIndex] && typeEl.options[typeEl.selectedIndex].value) || '';
                // cacher le champ "field" quand type === 'CPS'
                if (fidRow) fidRow.style.display = (val === 'CPS') ? 'none' : '';
                // afficher le champ "export_sid" uniquement quand type === 'CPS'
                if (sidRow) sidRow.style.display = (val === 'CPS') ? '' : 'none';
            }

            typeEl.addEventListener('change', update, false);
            update();
        })();
        </script>
    </div>
<{/if}>
<{if $export_count|default:0 != 0}>
    <table id="xo-xmcontact-sorter" cellspacing="1" class="outer tablesorter">
        <thead>
        <tr>
            <th class="txtcenter width5"><{$smarty.const._MA_XMSTATS_EXPORT_ID}></th>
            <th class="txtleft width10"><{$smarty.const._MA_XMSTATS_EXPORT_TYPE}></th>
            <th class="txtleft"><{$smarty.const._MA_XMSTATS_EXPORT_FIELD}></th>
            <th class="txtleft"><{$smarty.const._MA_XMSTATS_EXPORT_STOCK_AREA}></th>
            <th class="txtcenter width5"><{$smarty.const._MA_XMSTATS_STATUS}></th>
            <th class="txtcenter width10"><{$smarty.const._MA_XMSTATS_ACTION}></th>
        </tr>
        </thead>
        <tbody>
        <{foreach item=export from=$exports}>
            <tr class="<{cycle values='even,odd'}> alignmiddle">
                <td class="txtcenter">#<{$export.id}></td>
                <td class="txtleft"><{$export.type}></td>
                <td class="txtleft"><{if $export.fname != ''}><{$export.fname}><{else}>/<{/if}></td>
                <td class="txtleft"><{if $export.sname != ''}><{$export.sname}><{else}>/<{/if}></td>
                <td class="xo-actions txtcenter">
                    <img id="loading_sml<{$export.id}>" src="../assets/images/spinner.gif" style="display:none;" title="<{$smarty.const._AM_SYSTEM_LOADING}>"
                    alt="<{$smarty.const._AM_SYSTEM_LOADING}>"><img class="cursorpointer tooltip" id="sml<{$export.id}>"
                    onclick="system_setStatus( { op: 'export_update_status', export_id: <{$export.id}> }, 'sml<{$export.id}>', 'export.php' )"
                    src="<{if $export.status}><{xoAdminIcons 'success.png'}><{else}><{xoAdminIcons 'cancel.png'}><{/if}>"
                    alt="<{if $export.status}><{$smarty.const._MA_XMSTATS_STATUS_NA}><{else}><{$smarty.const._MA_XMSTATS_STATUS_A}><{/if}>"
                    title="<{if $export.status}><{$smarty.const._MA_XMSTATS_STATUS_NA}><{else}><{$smarty.const._MA_XMSTATS_STATUS_A}><{/if}>">
                </td>
                <td class="xo-actions txtcenter">
                    <a class="tooltip" href="export.php?op=edit&amp;export_id=<{$export.id}>" title="<{$smarty.const._MA_XMSTATS_EDIT}>">
                        <img src="<{xoAdminIcons 'edit.png'}>" alt="<{$smarty.const._MA_XMSTATS_EDIT}>"></a>
                    <a class="tooltip" href="export.php?op=del&amp;export_id=<{$export.id}>" title="<{$smarty.const._MA_XMSTATS_DEL}>">
                        <img src="<{xoAdminIcons 'delete.png'}>" alt="<{$smarty.const._MA_XMSTATS_DEL}>"></a>
                </td>
            </tr>
        <{/foreach}>
        </tbody>
    </table>
    <div class="clear spacer"></div>
    <{if $nav_menu|default:false}>
        <div class="floatright"><{$nav_menu}></div>
        <div class="clear spacer"></div>
    <{/if}>
<{/if}>