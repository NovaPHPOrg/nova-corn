<title id="title">定时任务 - {$title}</title>
<style id="style">
    .table-card {
        box-sizing: border-box;
    }

    mdui-card {
        width: 100%;
    }

    .corn-status {
        font-size: 0.875rem;
        color: rgb(var(--mdui-color-on-surface-variant));
        margin-bottom: 1rem;
    }
</style>

<div id="container" class="container p-4">
    <div class="row col-space16">
        <div class="col-xs-12 title-large center-vertical mb-4">
            <mdui-icon name="schedule" class="mr-2"></mdui-icon>
            <span>定时任务</span>
        </div>

        <div class="col-xs-12 corn-status" id="cornStatus"></div>

        <div class="col-xs-12">
            <div id="dataTable" class="table-card mt-2" style="width: 100%;min-height: 10rem"></div>
        </div>
    </div>
</div>

<script id="script" src="/corn/static/js/list.js?v={$__v}"></script>
