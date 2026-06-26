window.pageLoadFiles = [
    'DataTable',
    'Request',
];

window.pageOnLoad = function () {


    const orderTable = new DataTable('#dataTable');
    orderTable.load({
        uri: '/corn/api/list',
        height: 'auto',
        lineHeight: 'auto',
        mobile: true,
        page: false,
        selectable: false,
        break: false,
        columns: [

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
                formatter: function (value) {
                    return '<span class="tag badge-neutral cron-tag">' + value + '</span>';
                },
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
                    return value
                        ? '<span class="tag badge-info">循环</span>'
                        : '<span class="tag badge-neutral">单次</span>';
                },
            },
            {
                field: 'times',
                name: '剩余执行次数',
                align: 'center',
                width: 'auto',
                formatter: function (value) {

                    if(value < 0){
                        // 无限次
                        return '<span class="badge badge-secondary">无限次（' + ( - value ) + '）</span>';
                    }else{
                        return '<span class="badge badge-primary">' + (value || 0) + ' 次</span>';
                    }


                },
            },
        ],
    });

    $('#refreshTable').on('click', function () {
        orderTable.reload({}, true);
    });

    window.pageOnUnLoad = function () {
    };
};
