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

/* tasks_list.twig */
class __TwigTemplate_7d644b6dc22fbca7deddb347dcf5849b extends Template
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
        yield "<section class=\"space-y-4\">
  <div class=\"card bg-base-200 border border-base-300\">
    <div class=\"card-body p-4\">
      <div class=\"flex flex-wrap items-center justify-between gap-3\">
        <h2 id=\"tasks-title\" class=\"text-2xl font-bold\">";
        // line 7
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks"));
        yield "</h2>
        <div class=\"flex items-center gap-2\">
          ";
        // line 9
        yield from $this->load("partials/dynamic_search.twig", 9)->unwrap()->yield(CoreExtension::merge($context, ["path" => ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), "q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "due_date")) : ("due_date")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc"))]));
        // line 10
        yield "          <form method=\"get\" action=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), "html", null, true);
        yield "\" class=\"flex items-center gap-2\">
            <input type=\"hidden\" name=\"q\" value=\"";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")));
        yield "\">
            <label class=\"label hidden sm:flex\"><span class=\"label-text pr-2\">";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Sort"));
        yield ":</span>
              <select name=\"sort\" class=\"select select-bordered select-sm\" aria-label=\"";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Sort"));
        yield "\">
                ";
        // line 14
        $context["so"] = ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "due_date")) : ("due_date"));
        // line 15
        yield "                <option value=\"due_date\" ";
        yield (((($context["so"] ?? null) == "due_date")) ? ("selected") : (""));
        yield ">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Due date"));
        yield "</option>
                <option value=\"status\" ";
        // line 16
        yield (((($context["so"] ?? null) == "status")) ? ("selected") : (""));
        yield ">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Status"));
        yield "</option>
                <option value=\"title\" ";
        // line 17
        yield (((($context["so"] ?? null) == "title")) ? ("selected") : (""));
        yield ">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Title"));
        yield "</option>
                <option value=\"contact_name\" ";
        // line 18
        yield (((($context["so"] ?? null) == "contact_name")) ? ("selected") : (""));
        yield ">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contact"));
        yield "</option>
              </select>
            </label>
            <label class=\"label hidden sm:flex\"><span class=\"label-text pr-2\">";
        // line 21
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Dir"));
        yield ":</span>
              ";
        // line 22
        $context["di"] = ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc"));
        // line 23
        yield "              <select name=\"dir\" class=\"select select-bordered select-sm\" aria-label=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Direction"));
        yield "\">
                <option value=\"asc\" ";
        // line 24
        yield (((($context["di"] ?? null) == "asc")) ? ("selected") : (""));
        yield ">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Asc"));
        yield "</option>
                <option value=\"desc\" ";
        // line 25
        yield (((($context["di"] ?? null) == "desc")) ? ("selected") : (""));
        yield ">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Desc"));
        yield "</option>
              </select>
            </label>
            <label class=\"label hidden md:flex\"><span class=\"label-text pr-2\">";
        // line 28
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Per"));
        yield ":</span>
              ";
        // line 29
        $context["pp"] = $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10)));
        // line 30
        yield "              <select name=\"per\" class=\"select select-bordered select-sm\" aria-label=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Per page"));
        yield "\">
                ";
        // line 31
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable([10, 20, 50, 100]);
        foreach ($context['_seq'] as $context["_key"] => $context["n"]) {
            // line 32
            yield "                  <option value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["n"], "html", null, true);
            yield "\" ";
            yield (((($context["pp"] ?? null) == $context["n"])) ? ("selected") : (""));
            yield ">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["n"], "html", null, true);
            yield "</option>
                ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['n'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 34
        yield "              </select>
            </label>
            <button class=\"btn btn-sm\" type=\"submit\">";
        // line 36
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Apply"));
        yield "</button>
          </form>
          ";
        // line 38
        if ((($tmp = $this->env->getFunction('can')->getCallable()("tasks", "create")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<a class=\"btn btn-primary\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/new"), "html", null, true);
            yield "\">+ ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Add Task"));
            yield "</a>";
        }
        // line 39
        yield "        </div>
      </div>
      ";
        // line 41
        if ((array_key_exists("counts", $context) && ($context["counts"] ?? null))) {
            // line 42
            yield "        <p class=\"opacity-80\">
          <strong>";
            // line 43
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Open"));
            yield ":</strong> ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, ($context["counts"] ?? null), "open", [], "any", true, true, false, 43)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["counts"] ?? null), "open", [], "any", false, false, false, 43), 0)) : (0))), "html", null, true);
            yield "
          &nbsp;|&nbsp;
          <strong>";
            // line 45
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Done"));
            yield ":</strong> ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, ($context["counts"] ?? null), "done", [], "any", true, true, false, 45)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["counts"] ?? null), "done", [], "any", false, false, false, 45), 0)) : (0))), "html", null, true);
            yield "
        </p>
      ";
        }
        // line 48
        yield "    </div>
  </div>

  <div id=\"kanban-csrf\" class=\"hidden\">";
        // line 51
        yield $this->env->getFunction('csrf_field')->getCallable()();
        yield "</div>

  ";
        // line 54
        yield "  ";
        $context["statuses"] = [["key" => "open", "label" => $this->env->getFunction('__')->getCallable()("Open")], ["key" => "in_progress", "label" => $this->env->getFunction('__')->getCallable()("In progress")], ["key" => "review", "label" => $this->env->getFunction('__')->getCallable()("In review")], ["key" => "blocked", "label" => $this->env->getFunction('__')->getCallable()("Blocked")], ["key" => "done", "label" => $this->env->getFunction('__')->getCallable()("Done")]];
        // line 61
        yield "
  ";
        // line 62
        $context["grouped"] = [];
        // line 63
        yield "  ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["statuses"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["s"]) {
            // line 64
            yield "    ";
            $context["_"] = Twig\Extension\CoreExtension::merge(($context["grouped"] ?? null), [CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 64) => []]);
            // line 65
            yield "  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['s'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 66
        yield "  ";
        $context["grouped"] = ($context["grouped"] ?? null);
        // line 67
        yield "  ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["tasks"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["t"]) {
            // line 68
            yield "    ";
            $context["k"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["t"], "status", [], "any", true, true, false, 68)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "status", [], "any", false, false, false, 68), "open")) : ("open"));
            // line 69
            yield "    ";
            $context["col"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["grouped"] ?? null), ($context["k"] ?? null), [], "array", true, true, false, 69)) ? (Twig\Extension\CoreExtension::default((($_v0 = ($context["grouped"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0[($context["k"] ?? null)] ?? null) : null), [])) : ([]));
            // line 70
            yield "    ";
            $context["col"] = Twig\Extension\CoreExtension::merge(($context["col"] ?? null), [$context["t"]]);
            // line 71
            yield "    ";
            $context["grouped"] = Twig\Extension\CoreExtension::merge(($context["grouped"] ?? null), [ (string)($context["k"] ?? null) => ($context["col"] ?? null)]);
            // line 72
            yield "  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['t'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 73
        yield "
  ";
        // line 74
        $context["totalCount"] = $this->env->getFilter('int')->getCallable()(((array_key_exists("total", $context)) ? (Twig\Extension\CoreExtension::default(($context["total"] ?? null), Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["tasks"] ?? null)))) : (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["tasks"] ?? null)))));
        // line 75
        yield "  ";
        if ((($context["totalCount"] ?? null) == 0)) {
            // line 76
            yield "    <div class=\"card bg-base-200 border border-base-300\">
      <div class=\"card-body items-center text-center\">
        <div class=\"text-5xl mb-2\" aria-hidden=\"true\">✅</div>
        <h3 class=\"text-lg font-semibold mb-1\">";
            // line 79
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No tasks yet"));
            yield "</h3>
        <p class=\"opacity-80 mb-3\">";
            // line 80
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Create your first task to get started."));
            yield "</p>
        ";
            // line 81
            if ((($tmp = $this->env->getFunction('can')->getCallable()("tasks", "create")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 82
                yield "          <a class=\"btn btn-primary\" href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/new"), "html", null, true);
                yield "\">+ ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Add Task"));
                yield "</a>
        ";
            }
            // line 84
            yield "      </div>
    </div>
  ";
        }
        // line 87
        yield "
  <div id=\"kanban-board\" class=\"flex gap-4 overflow-x-auto pb-2 snap-x snap-mandatory\" role=\"list\" aria-labelledby=\"tasks-title\">
    ";
        // line 89
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["statuses"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["s"]) {
            // line 90
            yield "      ";
            $context["cards"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["grouped"] ?? null), CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 90), [], "array", true, true, false, 90)) ? (Twig\Extension\CoreExtension::default((($_v1 = ($context["grouped"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1[CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 90)] ?? null) : null), [])) : ([]));
            // line 91
            yield "      <div class=\"min-w-[240px] w-64 sm:w-72 bg-base-200 rounded-lg p-3 flex-shrink-0 snap-center\" data-status=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 91), "html", null, true);
            yield "\" role=\"group\" aria-labelledby=\"col-";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 91), "html", null, true);
            yield "-title\">
        <div class=\"flex items-center justify-between mb-2\">
          <h3 id=\"col-";
            // line 93
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 93), "html", null, true);
            yield "-title\" class=\"font-semibold inline-flex items-center gap-2\">
            <span class=\"w-2 h-2 rounded-full ";
            // line 94
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 94) == "done")) {
                yield "bg-green-500";
            } elseif ((CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 94) == "blocked")) {
                yield "bg-red-500";
            } elseif ((CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 94) == "review")) {
                yield "bg-sky-500";
            } elseif ((CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 94) == "in_progress")) {
                yield "bg-amber-500";
            } else {
                yield "bg-slate-400";
            }
            yield "\"></span>
            ";
            // line 95
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["s"], "label", [], "any", false, false, false, 95));
            yield "
          </h3>
          <span class=\"badge column-count\" aria-label=\"";
            // line 97
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Count"));
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["cards"] ?? null)), "html", null, true);
            yield "</span>
        </div>
        <div class=\"space-y-3 column-cards min-h-[20px]\" data-dropzone=\"true\" role=\"list\">
          ";
            // line 100
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["cards"] ?? null));
            $context['_iterated'] = false;
            foreach ($context['_seq'] as $context["_key"] => $context["it"]) {
                // line 101
                yield "            <div class=\"card bg-base-100 shadow-sm border border-base-300\" draggable=\"true\" data-id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 101)), "html", null, true);
                yield "\" role=\"listitem\">
              <div class=\"card-body p-3 gap-2\">
                <div class=\"flex items-start justify-between\">
                  <a href=\"";
                // line 104
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/view", ["id" => CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 104)]), "html", null, true);
                yield "\" class=\"font-medium hover:underline line-clamp-2\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "title", [], "any", true, true, false, 104)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "title", [], "any", false, false, false, 104), ("#" . CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 104)))) : (("#" . CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 104)))));
                yield "</a>
                  <a href=\"";
                // line 105
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/edit", ["id" => CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 105)]), "html", null, true);
                yield "\" class=\"btn btn-ghost btn-xs\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Edit"));
                yield "</a>
                </div>
                <div class=\"flex flex-wrap gap-2 text-sm opacity-80\">
                  ";
                // line 108
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["it"], "project_name", [], "any", false, false, false, 108)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "<span class=\"badge badge-ghost\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "project_name", [], "any", false, false, false, 108));
                    yield "</span>";
                }
                // line 109
                yield "                  ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["it"], "contact_name", [], "any", false, false, false, 109)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "<span class=\"badge badge-outline\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "contact_name", [], "any", false, false, false, 109));
                    yield "</span>";
                }
                // line 110
                yield "                  ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["it"], "employee_name", [], "any", false, false, false, 110)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "<span class=\"badge badge-info badge-outline\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "employee_name", [], "any", false, false, false, 110));
                    yield "</span>";
                }
                // line 111
                yield "                  ";
                if ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "tags", [], "any", true, true, false, 111) && CoreExtension::getAttribute($this->env, $this->source, $context["it"], "tags", [], "any", false, false, false, 111))) {
                    // line 112
                    yield "                    ";
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "tags", [], "any", false, false, false, 112));
                    foreach ($context['_seq'] as $context["_key"] => $context["tg"]) {
                        // line 113
                        yield "                      <span class=\"badge badge-ghost\">#";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["tg"]);
                        yield "</span>
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['tg'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 115
                    yield "                  ";
                }
                // line 116
                yield "                </div>
                <div class=\"flex items-center justify-between text-sm\">
                  <div class=\"flex items-center gap-2\">
                    ";
                // line 119
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["it"], "due_date", [], "any", false, false, false, 119)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 120
                    yield "                      <span class=\"badge ";
                    if ((CoreExtension::getAttribute($this->env, $this->source, $context["s"], "key", [], "any", false, false, false, 120) == "done")) {
                        yield "badge-success";
                    } elseif ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "due_date", [], "any", false, false, false, 120) < $this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y-m-d"))) {
                        yield "badge-error";
                    } else {
                        yield "badge-warning";
                    }
                    yield " badge-outline\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Due"));
                    yield " ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "due_date", [], "any", false, false, false, 120));
                    yield "</span>
                    ";
                }
                // line 122
                yield "                    ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["it"], "reminder_at", [], "any", false, false, false, 122)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 123
                    yield "                      <span class=\"badge ";
                    if ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "reminder_at", [], "any", false, false, false, 123) <= $this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "c"))) {
                        yield "badge-error";
                    } else {
                        yield "badge-info";
                    }
                    yield " badge-outline\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Reminder"));
                    yield " ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "reminder_at", [], "any", false, false, false, 123));
                    yield "</span>
                    ";
                }
                // line 125
                yield "                    ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["it"], "done_date", [], "any", false, false, false, 125)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 126
                    yield "                      <span class=\"badge badge-success badge-outline\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Done"));
                    yield " ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "done_date", [], "any", false, false, false, 126));
                    yield "</span>
                    ";
                }
                // line 128
                yield "                    <span class=\"badge badge-warning gap-1 hidden\" data-running-info data-task-id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 128)), "html", null, true);
                yield "\">
                      <span aria-hidden=\"true\">⏱️</span>
                      <span data-elapsed>00:00:00</span>
                      <span class=\"opacity-70\">• ";
                // line 131
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Total"));
                yield " <span data-total>0.00</span>h</span>
                    </span>
                  </div>
                  <div class=\"flex flex-wrap items-center gap-2\">
                    <form method=\"post\" action=\"";
                // line 135
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/time/start"), "html", null, true);
                yield "\" class=\"inline\">
                      ";
                // line 136
                yield $this->env->getFunction('csrf_field')->getCallable()();
                yield "
                      <input type=\"hidden\" name=\"id\" value=\"";
                // line 137
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 137)), "html", null, true);
                yield "\">
                      <button type=\"submit\" class=\"btn btn-xs btn-success inline-flex whitespace-nowrap\" data-action=\"time-start\" data-id=\"";
                // line 138
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 138)), "html", null, true);
                yield "\"><span aria-hidden=\"true\">⏱️</span> ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Start Timer"));
                yield "</button>
                    </form>
                    <form method=\"post\" action=\"";
                // line 140
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/time/stop"), "html", null, true);
                yield "\" class=\"inline\">
                      ";
                // line 141
                yield $this->env->getFunction('csrf_field')->getCallable()();
                yield "
                      <input type=\"hidden\" name=\"id\" value=\"";
                // line 142
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 142)), "html", null, true);
                yield "\">
                      <button type=\"submit\" class=\"btn btn-xs inline-flex whitespace-nowrap\" data-action=\"time-stop\" data-id=\"";
                // line 143
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 143)), "html", null, true);
                yield "\"><span aria-hidden=\"true\">⏹</span> ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Stop Timer"));
                yield "</button>
                    </form>
                    <form method=\"post\" action=\"";
                // line 145
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/delete"), "html", null, true);
                yield "\" onsubmit=\"return confirm('";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Are you sure?"));
                yield "');\">
                      ";
                // line 146
                yield $this->env->getFunction('csrf_field')->getCallable()();
                yield "
                      <input type=\"hidden\" name=\"id\" value=\"";
                // line 147
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 147)), "html", null, true);
                yield "\">
                      <button class=\"btn btn-ghost btn-xs\" type=\"submit\">";
                // line 148
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Delete"));
                yield "</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          ";
                $context['_iterated'] = true;
            }
            // line 154
            if (!$context['_iterated']) {
                // line 155
                yield "            <div class=\"text-sm opacity-70\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No tasks"));
                yield "</div>
          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['it'], $context['_parent'], $context['_iterated']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 157
            yield "        </div>
      </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['s'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 160
        yield "  </div>

  <script>
    (function(){
      const board = document.getElementById('kanban-board');
      if (!board) return;
      let dragEl = null;
      let originCol = null;
      let originNext = null;
      const getToken = () => {
        const c1 = document.querySelector('#kanban-csrf input[type=\"hidden\"]');
        if (c1 && c1.value) return c1.value;
        const c2 = document.querySelector('input[name=\"_csrf\"]');
        return c2 ? c2.value : '';
      };
      const updateCounts = () => {
        board.querySelectorAll('[data-status]').forEach(col => {
          const countEl = col.querySelector('.column-count');
          const cardsEl = col.querySelector('.column-cards');
          const n = cardsEl ? cardsEl.querySelectorAll('.card').length : 0;
          if (countEl) countEl.textContent = n;
        });
      };
      const clearHighlights = () => {
        board.querySelectorAll('.column-cards').forEach(z => z.classList.remove('ring', 'ring-primary'));
      };
      board.addEventListener('dragstart', (e) => {
        const target = e.target;
        const card = target && target.closest ? target.closest('.card') : null;
        if (!card) return;
        dragEl = card;
        originCol = card.parentElement; // .column-cards
        originNext = card.nextElementSibling;
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', card.getAttribute('data-id') || ''); } catch(_){}
        setTimeout(() => card.classList.add('opacity-50'), 0);
      });
      board.addEventListener('dragend', () => {
        if (dragEl) dragEl.classList.remove('opacity-50');
        dragEl = null; originCol = null; originNext = null; clearHighlights();
      });
      board.addEventListener('dragover', (e) => {
        const zone = e.target && e.target.closest ? e.target.closest('.column-cards') : null;
        if (!zone) return;
        e.preventDefault();
        clearHighlights();
        zone.classList.add('ring', 'ring-primary');
      });
      board.addEventListener('drop', async (e) => {
        const zone = e.target && e.target.closest ? e.target.closest('.column-cards') : null;
        if (!zone || !dragEl) return;
        e.preventDefault();
        clearHighlights();
        const col = zone.closest('[data-status]');
        const status = col ? col.getAttribute('data-status') : '';
        const id = dragEl.getAttribute('data-id');
        if (!status || !id) return;
        // Optimistic move
        zone.appendChild(dragEl);
        updateCounts();
        try {
          const res = await fetch('";
        // line 221
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/move"), "html", null, true);
        yield "', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
              'X-CSRF-Token': getToken()
            },
            body: new URLSearchParams({ id: id, status: status })
          });
          const json = await res.json().catch(() => ({}));
          if (!res.ok || !json.ok) {
            // rollback
            if (originCol) {
              if (originNext && originNext.parentElement === originCol) {
                originCol.insertBefore(dragEl, originNext);
              } else {
                originCol.appendChild(dragEl);
              }
              updateCounts();
            }
            alert((json && json.error) ? ('Error: ' + json.error) : 'Failed to move task');
          }
        } catch(err) {
          // rollback
          if (originCol) {
            if (originNext && originNext.parentElement === originCol) {
              originCol.insertBefore(dragEl, originNext);
            } else {
              originCol.appendChild(dragEl);
            }
            updateCounts();
          }
          alert('Network error');
        }
      });

      // Time tracking buttons (Start/Stop) via event delegation
      board.addEventListener('click', async (e) => {
        const btn = e.target && e.target.closest ? e.target.closest('button[data-action]') : null;
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');
        if (!action || !id) return;
        const endpoint = action === 'time-start' ? '";
        // line 263
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/time/start"), "html", null, true);
        yield "' : (action === 'time-stop' ? '";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/time/stop"), "html", null, true);
        yield "' : null);
        if (!endpoint) return;
        const form = btn.closest ? btn.closest('form') : null;
        if (form) { e.preventDefault(); }
        btn.disabled = true;
        try {
          const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-Token': getToken()
            },
            body: new URLSearchParams({ id: id })
          });
          const json = await res.json().catch(() => ({}));
          if (!res.ok || !json.ok) {
            alert((json && json.error) ? ('Error: ' + json.error) : 'Failed to perform action');
          } else {
            // Optional: brief visual feedback
            btn.classList.add('btn-active');
            setTimeout(() => btn.classList.remove('btn-active'), 500);
            // Refresh running state shortly after action
            setTimeout(checkRunning, 300);
          }
        } catch(err) {
          alert('Network error');
        } finally {
          btn.disabled = false;
        }
      });

      // Running timer handling on board: hide start buttons if any timer is running,
      // and show per-task badge with current elapsed and user total for that task
      const startBtnsSelector = 'button[data-action=\"time-start\"]';
      let tickIv = null;
      let startMs = null;
      let runningTaskId = 0;
      let totalSeconds = 0;

      function fmt(n){ return n < 10 ? '0' + n : '' + n; }
      function renderElapsed(){
        if (startMs == null || !runningTaskId) return;
        const now = Date.now();
        let diff = Math.max(0, Math.floor((now - startMs) / 1000));
        const h = Math.floor(diff / 3600); diff -= h*3600;
        const m = Math.floor(diff / 60); const s = diff - m*60;
        const text = fmt(h) + ':' + fmt(m) + ':' + fmt(s);
        const badge = board.querySelector('[data-running-info][data-task-id=\"' + runningTaskId + '\"]');
        if (badge){
          const el = badge.querySelector('[data-elapsed]');
          if (el) el.textContent = text;
        }
      }
      function applyNoRunning(){
        // show all start buttons
        board.querySelectorAll(startBtnsSelector).forEach(b => b.classList.remove('hidden'));
        // hide all running info badges
        board.querySelectorAll('[data-running-info]').forEach(el => el.classList.add('hidden'));
        if (tickIv){ clearInterval(tickIv); tickIv = null; }
        startMs = null; runningTaskId = 0; totalSeconds = 0;
      }
      function applyRunning(r){
        // Validate running payload before hiding Start buttons
        const tid = r && r.task_id ? r.task_id : 0;
        let iso = r && (r.iso_start || (r.date && r.start_time ? (r.date + 'T' + r.start_time + ':00') : null));
        const parsed = iso ? Date.parse(iso) : NaN;
        if (!tid || isNaN(parsed)) { applyNoRunning(); return; }
        // hide all start buttons when there is a valid running timer
        board.querySelectorAll(startBtnsSelector).forEach(b => b.classList.add('hidden'));
        // hide all badges, then show only for the running task
        board.querySelectorAll('[data-running-info]').forEach(el => el.classList.add('hidden'));
        runningTaskId = tid;
        const badge = board.querySelector('[data-running-info][data-task-id=\"' + runningTaskId + '\"]');
        startMs = parsed;
        totalSeconds = (r.user_total_seconds_for_task || 0);
        if (badge){
          const totalEl = badge.querySelector('[data-total]');
          if (totalEl){ totalEl.textContent = (totalSeconds/3600).toFixed(2); }
          badge.classList.remove('hidden');
        }
        if (tickIv){ clearInterval(tickIv); }
        tickIv = setInterval(renderElapsed, 1000);
        renderElapsed();
      }
      async function checkRunning(){
        try {
          const res = await fetch('";
        // line 351
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/times/running"), "html", null, true);
        yield "', { headers: { 'Accept': 'application/json' } });
          const json = await res.json().catch(() => null);
          if (!json || !json.ok){ throw new Error('bad'); }
          const r = json.running;
          if (!r){ applyNoRunning(); return; }
          applyRunning(r);
        } catch(e){
          // On any error, assume no running timer to keep Start buttons visible
          applyNoRunning();
        }
      }
      // initial check and polling
      applyNoRunning();
      checkRunning();
      setInterval(checkRunning, 15000);
    })();
  </script>

  <div class=\"mt-4\">
    ";
        // line 370
        $context["extra"] = ["q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "per" => $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10)))];
        // line 371
        yield "    ";
        yield $this->env->getFunction('paginate')->getCallable()($this->env->getFilter('int')->getCallable()(((array_key_exists("total", $context)) ? (Twig\Extension\CoreExtension::default(($context["total"] ?? null), Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["tasks"] ?? null)))) : (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["tasks"] ?? null))))), $this->env->getFilter('int')->getCallable()(((array_key_exists("page", $context)) ? (Twig\Extension\CoreExtension::default(($context["page"] ?? null), 1)) : (1))), $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10))), ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), Twig\Extension\CoreExtension::merge(($context["extra"] ?? null), ["sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "due_date")) : ("due_date")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc"))]));
        yield "
  </div>
</section>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "tasks_list.twig";
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
        return array (  793 => 371,  791 => 370,  769 => 351,  676 => 263,  631 => 221,  568 => 160,  560 => 157,  551 => 155,  549 => 154,  538 => 148,  534 => 147,  530 => 146,  524 => 145,  517 => 143,  513 => 142,  509 => 141,  505 => 140,  498 => 138,  494 => 137,  490 => 136,  486 => 135,  479 => 131,  472 => 128,  464 => 126,  461 => 125,  447 => 123,  444 => 122,  428 => 120,  426 => 119,  421 => 116,  418 => 115,  409 => 113,  404 => 112,  401 => 111,  394 => 110,  387 => 109,  381 => 108,  373 => 105,  367 => 104,  360 => 101,  355 => 100,  347 => 97,  342 => 95,  328 => 94,  324 => 93,  316 => 91,  313 => 90,  309 => 89,  305 => 87,  300 => 84,  292 => 82,  290 => 81,  286 => 80,  282 => 79,  277 => 76,  274 => 75,  272 => 74,  269 => 73,  263 => 72,  260 => 71,  257 => 70,  254 => 69,  251 => 68,  246 => 67,  243 => 66,  237 => 65,  234 => 64,  229 => 63,  227 => 62,  224 => 61,  221 => 54,  216 => 51,  211 => 48,  203 => 45,  196 => 43,  193 => 42,  191 => 41,  187 => 39,  179 => 38,  174 => 36,  170 => 34,  157 => 32,  153 => 31,  148 => 30,  146 => 29,  142 => 28,  134 => 25,  128 => 24,  123 => 23,  121 => 22,  117 => 21,  109 => 18,  103 => 17,  97 => 16,  90 => 15,  88 => 14,  84 => 13,  80 => 12,  76 => 11,  71 => 10,  69 => 9,  64 => 7,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "tasks_list.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/tasks_list.twig");
    }
}
