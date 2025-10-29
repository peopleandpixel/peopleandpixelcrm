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

/* dashboard.twig */
class __TwigTemplate_02fd0730a61c7f23e48dbfb4ca2bcf46 extends Template
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
        yield "<section class=\"space-y-6\">
  <div class=\"flex items-center justify-between\">
    <h1 class=\"text-3xl font-bold\">";
        // line 5
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Dashboard"));
        yield "</h1>
    <a class=\"btn\" href=\"";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/"), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Home"));
        yield "</a>
  </div>

  <div class=\"grid grid-cols-1 md:grid-cols-3 gap-4\">
    <div class=\"stats shadow bg-base-100\">
      <div class=\"stat\">
        <div class=\"stat-title\">";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks: Open"));
        yield "</div>
        <div class=\"stat-value\">";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, ($context["tasksByStatus"] ?? null), "open", [], "any", true, true, false, 13)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["tasksByStatus"] ?? null), "open", [], "any", false, false, false, 13), 0)) : (0))), "html", null, true);
        yield "</div>
        <div class=\"stat-actions\"><a href=\"";
        // line 14
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks", ["q" => ""]), "html", null, true);
        yield "\" class=\"btn btn-xs\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("View"));
        yield "</a></div>
      </div>
    </div>
    <div class=\"stats shadow bg-base-100\">
      <div class=\"stat\">
        <div class=\"stat-title\">";
        // line 19
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks: In progress"));
        yield "</div>
        <div class=\"stat-value\">";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, ($context["tasksByStatus"] ?? null), "in_progress", [], "any", true, true, false, 20)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["tasksByStatus"] ?? null), "in_progress", [], "any", false, false, false, 20), 0)) : (0))), "html", null, true);
        yield "</div>
      </div>
    </div>
    <div class=\"stats shadow bg-base-100\">
      <div class=\"stat\">
        <div class=\"stat-title\">";
        // line 25
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks: Done"));
        yield "</div>
        <div class=\"stat-value\">";
        // line 26
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, ($context["tasksByStatus"] ?? null), "done", [], "any", true, true, false, 26)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["tasksByStatus"] ?? null), "done", [], "any", false, false, false, 26), 0)) : (0))), "html", null, true);
        yield "</div>
      </div>
    </div>
  </div>

  <div class=\"grid grid-cols-1 lg:grid-cols-2 gap-6\">
    <div class=\"card bg-base-100 shadow\">
      <div class=\"card-body\">
        <h2 class=\"card-title\">";
        // line 34
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Upcoming reminders (7 days)"));
        yield "</h2>
        <ul class=\"space-y-2\">
          ";
        // line 36
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["upcoming"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["t"]) {
            // line 37
            yield "            <li class=\"flex items-start justify-between gap-3\">
              <div>
                <a class=\"link\" href=\"";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/view", ["id" => CoreExtension::getAttribute($this->env, $this->source, $context["t"], "id", [], "any", false, false, false, 39)]), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["t"], "title", [], "any", true, true, false, 39)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "title", [], "any", false, false, false, 39), ("#" . CoreExtension::getAttribute($this->env, $this->source, $context["t"], "id", [], "any", false, false, false, 39)))) : (("#" . CoreExtension::getAttribute($this->env, $this->source, $context["t"], "id", [], "any", false, false, false, 39)))));
            yield "</a>
                ";
            // line 40
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["t"], "contact_name", [], "any", false, false, false, 40)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 41
                yield "                  <span class=\"opacity-70\">— ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "contact_name", [], "any", false, false, false, 41));
                yield "</span>
                ";
            }
            // line 43
            yield "              </div>
              <span class=\"badge ";
            // line 44
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["t"], "__overdue", [], "any", false, false, false, 44)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("badge-error") : ("badge-warning"));
            yield " badge-outline\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["t"], "reminder_at", [], "any", false, false, false, 44));
            yield "</span>
            </li>
          ";
            $context['_iterated'] = true;
        }
        // line 46
        if (!$context['_iterated']) {
            // line 47
            yield "            <li class=\"opacity-70\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No reminders"));
            yield "</li>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['t'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 49
        yield "        </ul>
      </div>
    </div>

    <div class=\"card bg-base-100 shadow\">
      <div class=\"card-body\">
        <h2 class=\"card-title\">";
        // line 55
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Recent contacts"));
        yield "</h2>
        <ul class=\"space-y-2\">
          ";
        // line 57
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["recentContacts"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["c"]) {
            // line 58
            yield "            <li class=\"flex items-center justify-between\">
              <a class=\"link\" href=\"";
            // line 59
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts/view", ["id" => CoreExtension::getAttribute($this->env, $this->source, $context["c"], "id", [], "any", false, false, false, 59)]), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["c"], "name", [], "any", true, true, false, 59)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["c"], "name", [], "any", false, false, false, 59), ("#" . CoreExtension::getAttribute($this->env, $this->source, $context["c"], "id", [], "any", false, false, false, 59)))) : (("#" . CoreExtension::getAttribute($this->env, $this->source, $context["c"], "id", [], "any", false, false, false, 59)))));
            yield "</a>
              ";
            // line 60
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["c"], "created_at", [], "any", false, false, false, 60)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield "<span class=\"badge badge-ghost\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["c"], "created_at", [], "any", false, false, false, 60));
                yield "</span>";
            }
            // line 61
            yield "            </li>
          ";
            $context['_iterated'] = true;
        }
        // line 62
        if (!$context['_iterated']) {
            // line 63
            yield "            <li class=\"opacity-70\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No contacts"));
            yield "</li>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['c'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 65
        yield "        </ul>
      </div>
    </div>

    <div class=\"card bg-base-100 shadow lg:col-span-2\">
      <div class=\"card-body\">
        <h2 class=\"card-title\">";
        // line 71
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Low stock items"));
        yield "</h2>
        <div class=\"overflow-x-auto\">
          <table class=\"table table-zebra w-full\">
            <thead>
              <tr>
                <th>";
        // line 76
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Name"));
        yield "</th>
                <th>";
        // line 77
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Category"));
        yield "</th>
                <th>";
        // line 78
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Quantity"));
        yield "</th>
                <th>";
        // line 79
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Threshold"));
        yield "</th>
              </tr>
            </thead>
            <tbody>
              ";
        // line 83
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["lowStock"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["it"]) {
            // line 84
            yield "                <tr>
                  <td>";
            // line 85
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "name", [], "any", true, true, false, 85)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "name", [], "any", false, false, false, 85), ("#" . CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 85)))) : (("#" . CoreExtension::getAttribute($this->env, $this->source, $context["it"], "id", [], "any", false, false, false, 85)))));
            yield "</td>
                  <td>";
            // line 86
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "category", [], "any", true, true, false, 86)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "category", [], "any", false, false, false, 86), "")) : ("")));
            yield "</td>
                  <td>";
            // line 87
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "quantity", [], "any", true, true, false, 87)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "quantity", [], "any", false, false, false, 87), ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "stock", [], "any", true, true, false, 87)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "stock", [], "any", false, false, false, 87), 0)) : (0)))) : (((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "stock", [], "any", true, true, false, 87)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "stock", [], "any", false, false, false, 87), 0)) : (0)))));
            yield "</td>
                  <td>";
            // line 88
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["it"], "low_stock_threshold", [], "any", true, true, false, 88)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], "low_stock_threshold", [], "any", false, false, false, 88), 0)) : (0)));
            yield "</td>
                </tr>
              ";
            $context['_iterated'] = true;
        }
        // line 90
        if (!$context['_iterated']) {
            // line 91
            yield "                <tr><td colspan=\"4\" class=\"text-center opacity-70\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No low stock items"));
            yield "</td></tr>
              ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['it'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 93
        yield "            </tbody>
          </table>
        </div>
      </div>
    </div>
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
        return "dashboard.twig";
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
        return array (  295 => 93,  286 => 91,  284 => 90,  277 => 88,  273 => 87,  269 => 86,  265 => 85,  262 => 84,  257 => 83,  250 => 79,  246 => 78,  242 => 77,  238 => 76,  230 => 71,  222 => 65,  213 => 63,  211 => 62,  206 => 61,  200 => 60,  194 => 59,  191 => 58,  186 => 57,  181 => 55,  173 => 49,  164 => 47,  162 => 46,  153 => 44,  150 => 43,  144 => 41,  142 => 40,  136 => 39,  132 => 37,  127 => 36,  122 => 34,  111 => 26,  107 => 25,  99 => 20,  95 => 19,  85 => 14,  81 => 13,  77 => 12,  66 => 6,  62 => 5,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "dashboard.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/dashboard.twig");
    }
}
