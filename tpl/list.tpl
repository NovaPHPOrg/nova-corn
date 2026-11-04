<title id="title">定时任务 - {$title}</title>
<style id="style">
    .table-card {
        box-sizing: border-box;
    }

    mdui-card {
        width: 100%;
    }

    .corn-status {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .cron-tag {
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    }
</style>

<div id="container" class="container p-4">
    <div class="row col-space16">
        <div class="col-xs12 title-large d-flex items-center flex-wrap gap-2 mb-4">
            <mdui-icon name="schedule" class="mr-2"></mdui-icon>
            <span>定时任务</span>
            <div style="flex-grow: 1"></div>
            <mdui-button href="/corn/settings" variant="text" icon="settings" class="mr-2">调度设置</mdui-button>
            {if in_array('all', $user->role()->permissions) || in_array('task_manage', $user->role()->permissions)}
                <mdui-button href="/tasks/list" variant="text" icon="manage_history" class="mr-2">任务记录</mdui-button>
            {/if}
            <mdui-button-icon icon="refresh" id="refreshTable" variant="outlined"></mdui-button-icon>
        </div>


        <div class="col-xs12">
            <div id="dataTable" class="table-card mt-2" style="width: 100%;min-height: 10rem"></div>
        </div>
    </div>
</div>

<script id="script" src="/corn/static/js/list.js?v={$__v}"></script>
