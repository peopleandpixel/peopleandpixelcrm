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

/* calendar/index.twig */
class __TwigTemplate_5f8920ddfecf8ccb8918e81662bf6e1c extends Template
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

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 4
        yield "<h1 class=\"text-2xl font-bold mb-4\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("title", $context)) ? (Twig\Extension\CoreExtension::default(($context["title"] ?? null), $this->env->getFunction('__')->getCallable()("Calendar"))) : ($this->env->getFunction('__')->getCallable()("Calendar"))));
        yield "</h1>

<div class=\"card bg-base-200 shadow mb-4\">
  <div class=\"card-body\">
    <div class=\"flex flex-wrap gap-4 items-end\">
      <div>
        <span class=\"label-text block mb-1\">";
        // line 10
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Types"));
        yield "</span>
        <label class=\"label cursor-pointer mr-4\">
          <span class=\"label-text mr-2\">";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Birthdays"));
        yield "</span>
          <input id=\"f-birthday\" type=\"checkbox\" class=\"checkbox\" checked>
        </label>
        <label class=\"label cursor-pointer mr-4\">
          <span class=\"label-text mr-2\">";
        // line 16
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Projects"));
        yield "</span>
          <input id=\"f-project\" type=\"checkbox\" class=\"checkbox\" checked>
        </label>
        <label class=\"label cursor-pointer mr-4\">
          <span class=\"label-text mr-2\">";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks"));
        yield "</span>
          <input id=\"f-task\" type=\"checkbox\" class=\"checkbox\" checked>
        </label>
      </div>
      <div>
        <label class=\"label\" for=\"from\"><span class=\"label-text\">";
        // line 25
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("From"));
        yield "</span></label>
        <input id=\"from\" type=\"date\" class=\"input input-bordered\" />
      </div>
      <div>
        <label class=\"label\" for=\"to\"><span class=\"label-text\">";
        // line 29
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("To"));
        yield "</span></label>
        <input id=\"to\" type=\"date\" class=\"input input-bordered\" />
      </div>
      <div class=\"ml-auto flex gap-2\">
        <button id=\"prev-month\" class=\"btn\">‹ ";
        // line 33
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Prev"));
        yield "</button>
        <button id=\"today\" class=\"btn\">";
        // line 34
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Today"));
        yield "</button>
        <button id=\"next-month\" class=\"btn\">";
        // line 35
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Next"));
        yield " ›</button>
        <button id=\"apply\" class=\"btn btn-primary\">";
        // line 36
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Apply"));
        yield "</button>
      </div>
    </div>
  </div>
</div>

<div id=\"calendar\" class=\"grid grid-cols-1 gap-4\"></div>

<script>
(function(){
  const elCal = document.getElementById('calendar');
  const elFrom = document.getElementById('from');
  const elTo = document.getElementById('to');
  const cbBirthday = document.getElementById('f-birthday');
  const cbProject = document.getElementById('f-project');
  const cbTask = document.getElementById('f-task');
  const btnApply = document.getElementById('apply');
  const btnPrev = document.getElementById('prev-month');
  const btnNext = document.getElementById('next-month');
  const btnToday = document.getElementById('today');

  let currentYear, currentMonth; // month: 0..11

  function pad(n){ return n < 10 ? '0'+n : ''+n; }
  function fmtDate(d){ return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate()); }

  function setRangeToMonth(y, m){
    const first = new Date(Date.UTC(y, m, 1));
    const next = new Date(Date.UTC(y, m+1, 1));
    const last = new Date(next.getTime() - 24*3600*1000);
    elFrom.value = fmtDate(first);
    elTo.value = fmtDate(last);
    currentYear = y; currentMonth = m;
  }

  function initDefaultRange(){
    const now = new Date();
    setRangeToMonth(now.getUTCFullYear(), now.getUTCMonth());
  }

  function selectedTypes(){
    const types = [];
    if (cbBirthday.checked) types.push('birthday');
    if (cbProject.checked) types.push('project');
    if (cbTask.checked) types.push('task');
    return types;
  }

  async function loadAndRender(){
    const params = new URLSearchParams();
    const types = selectedTypes();
    if (types.length) params.set('types', types.join(','));
    if (elFrom.value) params.set('from', elFrom.value);
    if (elTo.value) params.set('to', elTo.value);
    const url = '";
        // line 90
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/calendar/events"), "html", null, true);
        yield "' + (params.toString() ? ('?'+params.toString()) : '');
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json().catch(()=>null);
    const events = data && data.events ? data.events : [];
    renderMonth(events);
  }

  function groupByDate(events){
    const map = {};
    for (const ev of events){
      const s = ev.start;
      const e = ev.end;
      // for ranges, add to each day between start..end
      let d = new Date(s + 'T00:00:00Z');
      const last = new Date(e + 'T00:00:00Z');
      while (d <= last){
        const key = fmtDate(d);
        if (!map[key]) map[key] = [];
        map[key].push(ev);
        d = new Date(d.getTime() + 86400000);
      }
    }
    return map;
  }

  function renderMonth(events){
    const y = currentYear, m = currentMonth; // 0..11
    const first = new Date(Date.UTC(y, m, 1));
    const next = new Date(Date.UTC(y, m+1, 1));
    const last = new Date(next.getTime() - 24*3600*1000);
    const days = (last.getUTCDate());

    const byDate = groupByDate(events);

    let html = '';
    html += '<div class=\"text-xl font-semibold\">' + y + '-' + pad(m+1) + '</div>';
    html += '<div class=\"grid grid-cols-7 gap-2\">';
    const weekdayNames = ['Mo','Tu','We','Th','Fr','Sa','Su'];
    for (const w of weekdayNames){ html += '<div class=\"text-sm opacity-70\">'+w+'</div>'; }

    // leading blanks (Mon=1..Sun=7)
    let lead = first.getUTCDay(); if (lead === 0) lead = 7;
    for (let i=1;i<lead;i++){ html += '<div></div>'; }

    for (let day=1; day<=days; day++){
      const d = new Date(Date.UTC(y, m, day));
      const key = fmtDate(d);
      const list = byDate[key] || [];
      html += '<div class=\"border rounded p-2 min-h-28\">';
      html += '<div class=\"text-sm font-semibold mb-1\">'+day+'</div>';
      if (list.length === 0){
        html += '<div class=\"text-xs opacity-50\">-</div>';
      } else {
        for (const ev of list.slice(0,5)){
          const color = ev.color || '#999';
          const t = (ev.type || '').toUpperCase();
          const title = (ev.title || '').replace(/</g,'&lt;').replace(/>/g,'&gt;');
          const href = ev.url ? ev.url : '#';
          html += '<a href=\"'+href+'\" class=\"block text-xs mb-1\" style=\"border-left: 3px solid '+color+'; padding-left:4px;\">['+t+'] '+title+'</a>';
        }
        if (list.length > 5){ html += '<div class=\"text-xs opacity-60\">+'+(list.length-5)+' more</div>'; }
      }
      html += '</div>';
    }
    html += '</div>';
    elCal.innerHTML = html;
  }

  // Navigation
  btnPrev.addEventListener('click', function(){
    let y = currentYear, m = currentMonth - 1;
    if (m < 0){ m = 11; y--; }
    setRangeToMonth(y, m);
    loadAndRender();
  });
  btnNext.addEventListener('click', function(){
    let y = currentYear, m = currentMonth + 1;
    if (m > 11){ m = 0; y++; }
    setRangeToMonth(y, m);
    loadAndRender();
  });
  btnToday.addEventListener('click', function(){ initDefaultRange(); loadAndRender(); });
  btnApply.addEventListener('click', function(){
    // if user set custom range belonging to some month, update currentYear/month to the from date
    if (elFrom.value){ const d = new Date(elFrom.value + 'T00:00:00Z'); currentYear = d.getUTCFullYear(); currentMonth = d.getUTCMonth(); }
    loadAndRender();
  });
  cbBirthday.addEventListener('change', loadAndRender);
  cbProject.addEventListener('change', loadAndRender);
  cbTask.addEventListener('change', loadAndRender);

  // Init
  initDefaultRange();
  loadAndRender();
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
        return "calendar/index.twig";
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
        return array (  178 => 90,  121 => 36,  117 => 35,  113 => 34,  109 => 33,  102 => 29,  95 => 25,  87 => 20,  80 => 16,  73 => 12,  68 => 10,  58 => 4,  51 => 3,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "calendar/index.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/calendar/index.twig");
    }
}
