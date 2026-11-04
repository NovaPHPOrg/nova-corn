<title id="title">调度设置 - {$title}</title>
<style id="style">
    .scheduler-settings { max-width: 1120px; }
    .scheduler-settings [hidden] { display: none !important; }
    .scheduler-card { width: 100%; }
    .scheduler-section { padding: 1rem 0; }
    .scheduler-mode { display: block; }
    .scheduler-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
    .scheduler-option { min-width: 0; padding: 0.5rem 0; }
    .scheduler-option::part(label) { padding: 0; }
    .scheduler-option-title { display: block; margin-bottom: 0.35rem; }
    .scheduler-option-description { display: block; color: rgb(var(--mdui-color-on-surface-variant)); font-size: 0.8125rem; line-height: 1.5; }
    .scheduler-state { padding: 0.5rem 0; }
    .scheduler-state mdui-icon { font-size: 1.125rem; }
    .scheduler-panel-title { margin: 0 0 0.35rem; }
    .scheduler-panel-description { margin: 0 0 1.25rem; }
    .scheduler-command { padding: 0.5rem 0; }
    .scheduler-command pre { margin: 0; flex: 1; min-width: 0; white-space: pre-wrap; overflow-wrap: anywhere; }
    .scheduler-command code, .scheduler-auth { font: 0.8125rem/1.7 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .scheduler-help { margin: 0.75rem 0 0; line-height: 1.6; }
    .scheduler-footer { padding: 1rem 0; }
    .scheduler-details { margin-top: 1rem; }
    .scheduler-details mdui-collapse-item::part(header) { padding: 0.75rem 0; }
    .scheduler-details mdui-collapse-item::part(body) { padding: 0; }
    .scheduler-auth { display: block; overflow-wrap: anywhere; }
    @media (max-width: 840px) {
        .scheduler-options { grid-template-columns: 1fr; }
        .scheduler-section { padding: 0.75rem 0; }
        .scheduler-footer { padding: 1rem 0; }
        .scheduler-state { align-items: flex-start !important; }
    }
</style>

<div id="container" class="container p-4 scheduler-settings">
    <div class="row col-space16">
        <div class="col-xs12 title-large d-flex items-center flex-wrap gap-2 mb-4">
            <mdui-icon name="settings" class="mr-2"></mdui-icon>
            <span>调度设置</span>
        </div>
        <div class="col-xs12">
            <div class="body-medium text-on-surface-variant">选择任务的触发方式，运行中的任务不受切换影响。</div>
        </div>
    </div>

    <form id="schedulerForm">
        <div class="scheduler-card">
            <section class="scheduler-section">
                <div class="title-medium mb-1">调度方式</div>
                <div class="body-medium text-on-surface-variant mb-4">三选一，保存后生效。</div>
                <mdui-radio-group id="schedulerMode" name="mode" value="internal" class="scheduler-mode" aria-label="调度方式" required disabled>
                    <div class="scheduler-options">
                        <mdui-radio value="internal" class="scheduler-option" data-selected="true">
                            <div class="min-w-0">
                                <span class="scheduler-option-title label-large">内置调度</span>
                                <span class="scheduler-option-description">网站自动唤起，无需配置外部工具</span>
                            </div>
                        </mdui-radio>
                        <mdui-radio value="cli" class="scheduler-option" data-selected="false">
                            <div class="min-w-0">
                                <span class="scheduler-option-title label-large">CLI 调度 · 推荐</span>
                                <span class="scheduler-option-description">服务器独立运行，不依赖网页访问</span>
                            </div>
                        </mdui-radio>
                        <mdui-radio value="http" class="scheduler-option" data-selected="false">
                            <div class="min-w-0">
                                <span class="scheduler-option-title label-large">外部 URL</span>
                                <span class="scheduler-option-description">通过 HTTP 请求触发单次扫描</span>
                            </div>
                        </mdui-radio>
                    </div>
                </mdui-radio-group>
                <div class="scheduler-state d-flex items-center flex-wrap gap-2 mt-4 body-small text-on-surface-variant">
                    <mdui-icon name="schedule"></mdui-icon>
                    <span>每 10 秒检查，仅派发到期任务</span>
                    <span id="schedulerLastTick" class="ml-auto">正在读取状态…</span>
                </div>
            </section>

            <mdui-divider></mdui-divider>

            <section class="scheduler-section" id="schedulerInternal">
                <div class="scheduler-panel-title title-medium">无需额外配置</div>
                <p class="scheduler-panel-description body-medium text-on-surface-variant">访问网站时自动唤起调度器，不设运行超时，支持正常进程轮换。</p>
                <div class="body-medium text-on-surface-variant">需要独立、持续运行时，建议选择 CLI 调度。</div>
            </section>

            <section class="scheduler-section" id="schedulerCli" hidden>
                <div class="scheduler-panel-title title-medium">在服务器启动调度</div>
                <p class="scheduler-panel-description body-medium text-on-surface-variant">保存为 CLI 模式后，以网站运行用户执行命令，建议使用 systemd 或 Supervisor 守护。</p>
                <div class="label-medium text-on-surface-variant mb-2">常驻运行命令</div>
                <div class="scheduler-command d-flex items-center gap-3">
                    <mdui-icon name="terminal" class="text-on-surface-variant"></mdui-icon>
                    <pre><code id="schedulerCliCommand"></code></pre>
                    <mdui-button-icon id="copyCliCommand" type="button" icon="content_copy" title="复制运行命令" aria-label="复制运行命令" disabled></mdui-button-icon>
                </div>
                <p class="scheduler-help body-small text-on-surface-variant">需要 Linux / Unix CLI PHP 与 pcntl 扩展。务必使用与网站相同的系统用户，以共用运行目录和锁。</p>
                <mdui-collapse class="scheduler-details">
                    <mdui-collapse-item header="使用外部计划任务？查看单次扫描命令">
                        <div class="scheduler-command d-flex items-center gap-3">
                            <pre><code id="schedulerCliOnce"></code></pre>
                            <mdui-button-icon id="copyCliOnce" type="button" icon="content_copy" title="复制单次命令" aria-label="复制单次命令" disabled></mdui-button-icon>
                        </div>
                        <p class="scheduler-help body-small text-on-surface-variant">由外部工具每 10 秒调用一次。普通 crontab 不支持秒级粒度。</p>
                    </mdui-collapse-item>
                </mdui-collapse>
            </section>

            <section class="scheduler-section" id="schedulerHttp" hidden>
                <div class="scheduler-panel-title title-medium">配置外部 HTTP 触发</div>
                <p class="scheduler-panel-description body-medium text-on-surface-variant">保存后，由外部工具每 10 秒发送一次 GET 请求，扫描完成即返回。</p>
                <div class="label-medium text-on-surface-variant mb-2">触发地址</div>
                <div class="scheduler-command d-flex items-center gap-3">
                    <span class="label-small text-primary">GET</span>
                    <pre><code id="schedulerTriggerUrl"></code></pre>
                    <mdui-button-icon id="copyTriggerUrl" type="button" icon="content_copy" title="复制触发地址" aria-label="复制触发地址" disabled></mdui-button-icon>
                </div>
                <div class="d-flex items-center justify-between flex-wrap gap-2 mt-4">
                    <div class="min-w-0">
                        <div class="label-medium mb-1">调度 Token（独立于个人 Token）</div>
                        <code id="schedulerToken" class="scheduler-auth text-on-surface-variant"></code>
                    </div>
                    <mdui-button id="resetToken" type="button" variant="outlined" icon="refresh" disabled>重置 Token</mdui-button>
                </div>
                <mdui-collapse class="scheduler-details">
                    <mdui-collapse-item header="权限与返回状态">
                        <p class="body-small text-on-surface-variant">触发地址的 token 参数为上方调度 Token，无需登录；重置后旧地址立即失效。</p>
                        <p class="body-small text-on-surface-variant">200 表示扫描完成；403 表示 Token 无效；409 表示未启用 URL 模式或已有扫描执行。客户端断开不会终止后台扫描。</p>
                    </mdui-collapse-item>
                </mdui-collapse>
            </section>

            <mdui-divider></mdui-divider>
            <footer class="scheduler-footer d-flex items-center justify-between gap-3">
                <span id="schedulerSaveStatus" class="body-small text-on-surface-variant" role="status">正在加载设置…</span>
                <mdui-button id="saveScheduler" type="submit" icon="check" disabled>保存设置</mdui-button>
            </footer>
        </div>
    </form>
</div>

<script id="script" src="/corn/static/js/settings.js?v=scheduler-settings-2-{$__v}"></script>
