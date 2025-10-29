<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* timer.twig */
class __TwigTemplate_73f5d821ea16179251b597bbd9f18081 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("layout.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 2
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 3
        yield "<section class=\"max-w-4xl mx-auto\">
  <div class=\"flex items-center justify-between mb-6\">
    <h1 class=\"text-3xl font-extrabold tracking-tight flex items-center gap-3\">
      <span aria-hidden=\"true\">⏱️</span> ";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Timer"));
        yield "
    </h1>
    <div class=\"flex items-center gap-2\">
      <a class=\"btn\" href=\"";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/times"), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Times"));
        yield "</a>
    </div>
  </div>

  <div class=\"grid grid-cols-1 md:grid-cols-2 gap-6\">
    <!-- Beautiful animated timer card -->
    <div class=\"card bg-base-200 overflow-hidden relative\">
      <div class=\"pointer-events-none absolute inset-0 opacity-60\" aria-hidden=\"true\">
        <div class=\"animate-pulse w-[140%] h-[140%] -left-[20%] -top-[20%] absolute rounded-full bg-gradient-to-br from-primary/20 via-secondary/20 to-accent/20 blur-3xl\"></div>
      </div>
      <div class=\"card-body relative\">
        <div class=\"flex flex-col items-center justify-center py-6\">
          <div id=\"face\" class=\"relative w-56 h-56 md:w-64 md:h-64 rounded-full border-4 border-base-300 bg-base-100 shadow-xl grid place-items-center overflow-hidden\">
            <svg class=\"absolute inset-0 w-full h-full -rotate-90\" viewBox=\"0 0 100 100\" aria-hidden=\"true\">
              <circle cx=\"50\" cy=\"50\" r=\"46\" fill=\"none\" stroke=\"currentColor\" class=\"text-base-300\" stroke-width=\"6\" />
              <circle id=\"progress\" cx=\"50\" cy=\"50\" r=\"46\" fill=\"none\" stroke=\"url(#grad)\" stroke-width=\"6\" stroke-linecap=\"round\" stroke-dasharray=\"289\" stroke-dashoffset=\"289\"></circle>
              <defs>
                <linearGradient id=\"grad\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"0%\">
                  <stop offset=\"0%\" stop-color=\"hsl(var(--p))\" />
                  <stop offset=\"100%\" stop-color=\"hsl(var(--s))\" />
                </linearGradient>
              </defs>
            </svg>
            <div class=\"text-center select-none\">
              <div id=\"timer-display\" class=\"font-mono text-2xl md:text-3xl lg:text-4xl tabular-nums tracking-tight\">00:00:00</div>
              <div id=\"status-text\" class=\"text-sm mt-1 opacity-80\">";
        // line 34
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Ready"));
        yield "</div>
            </div>
            <div class=\"absolute inset-0\" aria-hidden=\"true\">
              <div class=\"w-2 h-2 bg-primary rounded-full absolute top-2 left-1/2 -translate-x-1/2 shadow\"></div>
            </div>
          </div>
          <div class=\"mt-6 flex items-center gap-2 flex-wrap justify-center\">
            <button id=\"btn-start\" class=\"btn btn-primary\">
              <span aria-hidden=\"true\">▶️</span> ";
        // line 42
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Start"));
        yield "
            </button>
            <button id=\"btn-pause\" class=\"btn\" disabled>
              <span aria-hidden=\"true\">⏸️</span> ";
        // line 45
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Pause"));
        yield "
            </button>
            <button id=\"btn-resume\" class=\"btn\" disabled>
              <span aria-hidden=\"true\">⏵</span> ";
        // line 48
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Resume"));
        yield "
            </button>
            <button id=\"btn-stop\" class=\"btn btn-error\" disabled>
              <span aria-hidden=\"true\">⏹️</span> ";
        // line 51
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Stop"));
        yield "
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignment and notes -->
    <div class=\"card bg-base-200\">
      <div class=\"card-body\">
        <h2 class=\"card-title\">";
        // line 61
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Assign to"));
        yield "</h2>
        <form id=\"timer-form\" class=\"space-y-4\" autocomplete=\"off\">
          ";
        // line 63
        yield $this->env->getFunction('csrf_field')->getCallable()();
        yield "
          <div class=\"form-control\">
            <label class=\"label\"><span class=\"label-text\">";
        // line 65
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Project"));
        yield "</span></label>
            <select id=\"project_id\" name=\"project_id\" class=\"select select-bordered\">
              <option value=\"0\">";
        // line 67
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No project"));
        yield "</option>
              ";
        // line 68
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["projects"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["p"]) {
            // line 69
            yield "                <option value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["p"], "id", [], "any", false, false, false, 69)), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["p"], "name", [], "any", false, false, false, 69));
            yield "</option>
              ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['p'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 71
        yield "            </select>
          </div>
          <div class=\"form-control\">
            <label class=\"label\"><span class=\"label-text\">";
        // line 74
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Task"));
        yield "</span></label>
            <select id=\"task_id\" name=\"task_id\" class=\"select select-bordered\">
              <option value=\"0\">";
        // line 76
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No task"));
        yield "</option>
              ";
        // line 77
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["tasks"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["t"]) {
            // line 78
            yield "                <option value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "id", [], "any", false, false, false, 78)), "html", null, true);
            yield "\" data-project=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, $context["t"], "project_id", [], "any", true, true, false, 78)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "project_id", [], "any", false, false, false, 78), 0)) : (0))), "html", null, true);
            yield "\">#";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "id", [], "any", false, false, false, 78)), "html", null, true);
            yield " — ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "title", [], "any", false, false, false, 78));
            yield "</option>
              ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['t'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 80
        yield "            </select>
          </div>
          <div class=\"form-control\">
            <label class=\"label\"><span class=\"label-text\">";
        // line 83
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contact"));
        yield "</span></label>
            <select id=\"contact_id\" name=\"contact_id\" class=\"select select-bordered\">
              ";
        // line 85
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["contacts"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["c"]) {
            // line 86
            yield "                <option value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["c"], "id", [], "any", false, false, false, 86)), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["c"], "name", [], "any", false, false, false, 86));
            yield "</option>
              ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['c'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 88
        yield "            </select>
          </div>
          <div class=\"form-control\">
            <label class=\"label\"><span class=\"label-text\">";
        // line 91
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Notes"));
        yield "</span></label>
            <textarea id=\"description\" name=\"description\" class=\"textarea textarea-bordered\" rows=\"3\" placeholder=\"";
        // line 92
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("What are you working on?"));
        yield "\"></textarea>
          </div>
          <p class=\"text-sm opacity-70\">";
        // line 94
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tip: You can pause/resume multiple times; each session is saved as a segment."));
        yield "</p>
        </form>
      </div>
    </div>
  </div>

  <!-- Recent segments (optional lightweight) -->
  <div class=\"mt-8 card bg-base-200\">
    <div class=\"card-body\">
      <h3 class=\"card-title\">";
        // line 103
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Today"));
        yield "</h3>
      <div id=\"segments\" class=\"text-sm opacity-80\">";
        // line 104
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Segments will appear here as you log time."));
        yield "</div>
    </div>
  </div>
</section>

<script>
(function(){
  const d = document;
  const display = d.getElementById('timer-display');
  const statusText = d.getElementById('status-text');
  const progress = d.getElementById('progress');
  const btnStart = d.getElementById('btn-start');
  const btnPause = d.getElementById('btn-pause');
  const btnResume = d.getElementById('btn-resume');
  const btnStop = d.getElementById('btn-stop');
  const form = d.getElementById('timer-form');
  const taskSel = d.getElementById('task_id');
  const projectSel = d.getElementById('project_id');
  const contactSel = d.getElementById('contact_id');
  const descEl = d.getElementById('description');
  const segments = d.getElementById('segments');
  
  function getCsrf(){
    const hidden = form ? form.querySelector('input[type=\"hidden\"]') : null;
    return hidden && hidden.value ? hidden.value : '';
  }

  // Filter tasks by chosen project
  function filterTasks(){
    const pid = parseInt(projectSel.value||'0');
    const opts = taskSel.querySelectorAll('option');
    opts.forEach(function(o){
      if (o.value === '0') { o.classList.remove('hidden'); return; }
      const p = parseInt(o.getAttribute('data-project')||'0');
      const show = (pid === 0) || (p === pid);
      o.classList.toggle('hidden', !show);
    });
    // if selected task hidden, reset to 0
    const sel = taskSel.selectedOptions[0];
    if (sel && sel.classList.contains('hidden')) { taskSel.value = '0'; }
  }
  if (projectSel) { projectSel.addEventListener('change', filterTasks); filterTasks(); }

  let timerIv = null; let startMs = null; let active = false;
  function fmt(n){ return n<10 ? '0'+n : ''+n; }
  function render(){
    if (!active || startMs == null) return;
    const now = Date.now();
    let diff = Math.floor((now - startMs)/1000);
    if (diff < 0) diff = 0;
    const h = Math.floor(diff/3600); diff -= h*3600;
    const m = Math.floor(diff/60); const s = diff - m*60;
    const text = fmt(h)+':'+fmt(m)+':'+fmt(s);
    display.textContent = text;
    // progress is purely aesthetic: loop every hour
    const circumference = 2 * Math.PI * 46; // r=46
    const mod = (m*60 + s) % 3600; // seconds within the hour
    const frac = mod / 3600;
    const dash = Math.max(0, circumference * (1 - frac));
    progress.setAttribute('stroke-dashoffset', dash.toFixed(2));
  }
  function setButtons(state){
    if (state === 'running'){
      btnStart.disabled = true; btnPause.disabled = false; btnResume.disabled = true; btnStop.disabled = false;
      statusText.textContent = '";
        // line 168
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Running"));
        yield "';
    } else if (state === 'paused'){
      btnStart.disabled = true; btnPause.disabled = true; btnResume.disabled = false; btnStop.disabled = false;
      statusText.textContent = '";
        // line 171
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Paused"));
        yield "';
    } else {
      btnStart.disabled = false; btnPause.disabled = true; btnResume.disabled = true; btnStop.disabled = true;
      statusText.textContent = '";
        // line 174
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Ready"));
        yield "';
      progress.setAttribute('stroke-dashoffset', '289');
      display.textContent = '00:00:00';
    }
  }
  async function fetchRunning(){
    try {
      const res = await fetch('";
        // line 181
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/times/running"), "html", null, true);
        yield "', { headers: { 'Accept': 'application/json' } });
      const json = await res.json();
      if (!json || !json.ok) throw new Error('bad');
      const r = json.running;
      if (r && r.iso_start){
        startMs = Date.parse(r.iso_start);
        active = true;
        setButtons('running');
        if (!timerIv) timerIv = setInterval(render, 1000);
        render();
      } else {
        active = false; startMs = null;
        if (timerIv) { clearInterval(timerIv); timerIv = null; }
        setButtons('idle');
      }
    } catch(e){ /* ignore */ }
  }
  fetchRunning();

  async function post(url, data){
    const token = (function(){ const hid = form.querySelector('input[type=\"hidden\"]'); return hid ? hid.value : ''; })();
    const body = new URLSearchParams();
    Object.keys(data||{}).forEach(k => body.append(k, String(data[k])));
    const headers = { 'Accept': 'application/json' };
    if (token) headers['X-CSRF-Token'] = token;
    const res = await fetch(url, { method: 'POST', headers, body });
    let json = null; try { json = await res.json(); } catch(e) {}
    return json;
  }

  btnStart.addEventListener('click', async function(){
    const taskId = parseInt(taskSel.value||'0');
    const projectId = parseInt(projectSel.value||'0');
    const contactId = parseInt(contactSel.value||'0');
    const desc = descEl.value||'';
    const data = { task_id: taskId>0?taskId:0, contact_id: contactId>0?contactId:0, description: desc };
    const json = await post('";
        // line 217
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/timer/start"), "html", null, true);
        yield "', data);
    if (!json || !json.ok){ alert('Failed to start'); return; }
    fetchRunning();
    // small burst animation
    const face = d.getElementById('face');
    face.classList.add('ring','ring-primary','ring-offset-2');
    setTimeout(()=>{ face.classList.remove('ring','ring-primary','ring-offset-2'); }, 500);
  });
  btnPause.addEventListener('click', async function(){
    const json = await post('";
        // line 226
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/timer/pause"), "html", null, true);
        yield "', {});
    if (!json || !json.ok){ alert('Failed to pause'); return; }
    active = false; startMs = null; if (timerIv) { clearInterval(timerIv); timerIv = null; }
    setButtons('paused');
    appendSegment(json.time);
  });
  btnResume.addEventListener('click', async function(){
    const json = await post('";
        // line 233
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/timer/resume"), "html", null, true);
        yield "', {});
    if (!json || !json.ok){ alert('Failed to resume'); return; }
    fetchRunning();
  });
  btnStop.addEventListener('click', async function(){
    const json = await post('";
        // line 238
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/timer/stop"), "html", null, true);
        yield "', {});
    if (!json || !json.ok){ alert('Failed to stop'); return; }
    active = false; startMs = null; if (timerIv) { clearInterval(timerIv); timerIv = null; }
    setButtons('idle');
    appendSegment(json.time);
  });

  function appendSegment(t){
    try{
      if (!t) return;
      const el = d.createElement('div');
      const date = (t.date||'');
      const desc = (t.description||'');
      const start = (t.start_time||'');
      const end = (t.end_time||'');
      const hours = (typeof t.hours === 'number') ? t.hours.toFixed(2) : '';
      el.className = 'mt-2';
      el.textContent = date + ' · ' + start + (end?('–'+end):'') + (hours?(' · '+hours+'h'):'') + (desc?(' · '+desc):'');
      segments.prepend(el);
    } catch(e){}
  }

  // Keyboard shortcuts
  d.addEventListener('keydown', function(ev){
    if (ev.target && ['INPUT','TEXTAREA','SELECT'].includes(ev.target.tagName)) return;
    if (ev.code === 'Space') { ev.preventDefault(); if (!btnPause.disabled) btnPause.click(); else if (!btnResume.disabled) btnResume.click(); else if (!btnStart.disabled) btnStart.click(); }
    if (ev.key === 's' || ev.key === 'S') { if (!btnStop.disabled) { ev.preventDefault(); btnStop.click(); } }
  });
})();
</script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "timer.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  421 => 238,  413 => 233,  403 => 226,  391 => 217,  352 => 181,  342 => 174,  336 => 171,  330 => 168,  263 => 104,  259 => 103,  247 => 94,  242 => 92,  238 => 91,  233 => 88,  222 => 86,  218 => 85,  213 => 83,  208 => 80,  193 => 78,  189 => 77,  185 => 76,  180 => 74,  175 => 71,  164 => 69,  160 => 68,  156 => 67,  151 => 65,  146 => 63,  141 => 61,  128 => 51,  122 => 48,  116 => 45,  110 => 42,  99 => 34,  69 => 9,  63 => 6,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "timer.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/timer.twig");
    }
}
