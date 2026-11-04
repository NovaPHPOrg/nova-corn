window.pageLoadFiles = ['Request', 'Toaster'];

window.pageOnLoad = function () {
    const form = document.getElementById('schedulerForm');
    const mode = document.getElementById('schedulerMode');
    const save = document.getElementById('saveScheduler');
    const lastTick = document.getElementById('schedulerLastTick');
    const saveStatus = document.getElementById('schedulerSaveStatus');
    const panels = {
        internal: document.getElementById('schedulerInternal'),
        cli: document.getElementById('schedulerCli'),
        http: document.getElementById('schedulerHttp'),
    };
    const fields = {
        cli_command: document.getElementById('schedulerCliCommand'),
        cli_once_command: document.getElementById('schedulerCliOnce'),
        trigger_url: document.getElementById('schedulerTriggerUrl'),
        token: document.getElementById('schedulerToken'),
    };
    const resetToken = document.getElementById('resetToken');
    const request = new Request().setSilent();
    let disposed = false;
    let activeRequest = null;
    let saving = false;
    let loaded = false;
    let savedMode = null;

    function updatePanels() {
        Object.entries(panels).forEach(([value, panel]) => { panel.hidden = mode.value !== value; });
        mode.querySelectorAll('mdui-radio').forEach(option => {
            option.dataset.selected = String(option.value === mode.value);
        });
        const dirty = loaded && mode.value !== savedMode;
        save.disabled = !dirty || saving;
        if (loaded && !saving) saveStatus.textContent = dirty ? '有未保存的更改' : '设置已保存';
    }

    function load() {
        loaded = false;
        mode.disabled = true;
        save.disabled = true;
        saveStatus.textContent = '正在读取设置…';
        activeRequest = request.get('/corn/api/config', {}, function (ret) {
            if (disposed) return;
            if (ret.code !== 200) {
                $.toaster.error(ret.msg);
                saveStatus.textContent = '加载失败，请刷新页面重试';
                return;
            }
            mode.value = ret.data.mode;
            savedMode = ret.data.mode;
            Object.entries(fields).forEach(([name, field]) => { field.textContent = ret.data[name]; });
            lastTick.textContent = ret.data.last_tick
                ? '最近检查 ' + new Date(ret.data.last_tick * 1000).toLocaleString()
                : '暂无扫描记录';
            loaded = true;
            mode.disabled = false;
            copies.forEach(([button]) => { button.disabled = false; });
            resetToken.disabled = false;
            updatePanels();
        }, function () {
            if (!disposed) {
                saveStatus.textContent = '加载失败，请刷新页面重试';
                $.toaster.error('调度设置加载失败，请重新进入此页面');
            }
        });
    }

    function submit(event) {
        event.preventDefault();
        if (!loaded || saving || mode.value === savedMode) return;
        saving = true;
        save.disabled = true;
        save.loading = true;
        mode.disabled = true;
        saveStatus.textContent = '正在保存…';
        function unlock() {
            saving = false;
            save.loading = false;
            mode.disabled = false;
            updatePanels();
        }
        activeRequest = request.postForm('/corn/api/config', {mode: mode.value}, function (ret) {
            if (disposed) return;
            unlock();
            if (ret.code !== 200) {
                $.toaster.error(ret.msg);
                return;
            }
            $.toaster.success(ret.msg);
            load();
        }, function () {
            if (disposed) return;
            unlock();
            $.toaster.error('调度设置保存失败，请重试');
        });
    }

    const copies = [
        [document.getElementById('copyCliCommand'), fields.cli_command],
        [document.getElementById('copyCliOnce'), fields.cli_once_command],
        [document.getElementById('copyTriggerUrl'), fields.trigger_url],
    ].map(([button, field]) => {
        async function copy() {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(field.textContent);
                } else if (!$.copy(field.textContent)) {
                    throw new Error('Clipboard unavailable');
                }
                if (!disposed) $.toaster.success('已复制');
            } catch (error) {
                if (!disposed) $.toaster.error('复制失败，请手动选中并复制');
            }
        }
        button.addEventListener('click', copy);
        return [button, copy];
    });
    function reset() {
        if (!loaded || saving) return;
        resetToken.disabled = true;
        resetToken.loading = true;
        activeRequest = request.postForm('/corn/api/token', {}, function (ret) {
            if (disposed) return;
            resetToken.loading = false;
            if (ret.code !== 200) {
                resetToken.disabled = false;
                $.toaster.error(ret.msg);
                return;
            }
            $.toaster.success(ret.msg);
            load();
        }, function () {
            if (disposed) return;
            resetToken.loading = false;
            resetToken.disabled = false;
            $.toaster.error('Token 重置失败，请重试');
        });
    }

    mode.addEventListener('change', updatePanels);
    form.addEventListener('submit', submit);
    resetToken.addEventListener('click', reset);
    load();

    window.pageOnUnLoad = function () {
        disposed = true;
        activeRequest?.abort?.();
        mode.removeEventListener('change', updatePanels);
        form.removeEventListener('submit', submit);
        resetToken.removeEventListener('click', reset);
        copies.forEach(([button, copy]) => { button.removeEventListener('click', copy); });
    };
};
