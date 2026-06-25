window.pageLoadFiles = [
    'DataTable',
    'Request',
];

window.pageOnLoad = function () {
    $.request.get('/corn/api/list', {}, function (res) {
        if (res.code !== 200 || !res.server) {
            return;
        }
        const status = res.server.running
            ? '调度服务运行中 (PID: ' + res.server.pid + ')'
            : '调度服务未运行';
        $('#cornStatus').text(status);
    });

    const orderTable = new DataTable('#dataTable');
    orderTable.load({
        uri: '/corn/api/list',
        height: 'auto',
        lineHeight: 'auto',
        mobile: true,
        page: true,
        pageSizes: [10, 20, 50, 100],
        selectable: false,
        break: false,
        columns: [
            {
                field: 'key',
                name: '任务ID',
                align: 'center',
                width: "100px",
            },
            {
                field: 'name',
                name: '任务名称',
                align: 'center',
                width: "200px",
            },
            {
                field: 'cron',
                name: 'Cron表达式',
                align: 'center',
                width: "150px",
            },
            {
                field: 'next',
                name: '下次执行时间',
                align: 'center',
                width: "180px",
                formatter: function (value) {
                    return value ? new Date(value * 1000).toLocaleString() : '-';
                },
            },
            {
                field: 'loop',
                name: '是否循环',
                align: 'center',
                width: "100px",
                formatter: function (value) {
                    return value ? '是' : '否';
                },
            },
            {
                field: 'times',
                name: '执行次数',
                align: 'center',
                width: 'auto'
            },
        ],
    });

    window.pageOnUnLoad = function () {
    };
};
