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

/* times_list.twig */
class __TwigTemplate_377ee10256a8280ede1998f3db57b9e5 extends Template
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
  <div class=\"flex flex-wrap items-center justify-between gap-3\">
    <h2 class=\"text-2xl font-bold\">";
        // line 5
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Working Times"));
        yield "</h2>
    <div class=\"flex items-center gap-2\">
      ";
        // line 7
        yield from $this->load("partials/dynamic_search.twig", 7)->unwrap()->yield(CoreExtension::merge($context, ["path" => ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), "q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "date")) : ("date")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "desc")) : ("desc"))]));
        // line 8
        yield "      <a class=\"btn\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/times/new"), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("+ Add Time Entry"));
        yield "</a>
    </div>
  </div>

  ";
        // line 12
        $context["totalsByContact"] = ((array_key_exists("totalsByContact", $context)) ? (Twig\Extension\CoreExtension::default(($context["totalsByContact"] ?? null), [])) : ([]));
        // line 13
        yield "  ";
        $context["totalsByMonth"] = ((array_key_exists("totalsByMonth", $context)) ? (Twig\Extension\CoreExtension::default(($context["totalsByMonth"] ?? null), [])) : ([]));
        // line 14
        yield "  ";
        if ((($tmp = ($context["totalsByContact"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 15
            yield "    <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
      <div class=\"card bg-base-200\">
        <div class=\"card-body\">
          <h3 class=\"card-title\">";
            // line 18
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Totals by Contact"));
            yield "</h3>
          <div class=\"overflow-x-auto\">
            <table class=\"table table-compact\">
              <thead><tr><th>";
            // line 21
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contact"));
            yield "</th><th>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Hours"));
            yield "</th></tr></thead>
              <tbody>
              ";
            // line 23
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["totalsByContact"] ?? null));
            foreach ($context['_seq'] as $context["name"] => $context["h"]) {
                // line 24
                yield "                <tr><td>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["name"]);
                yield "</td><td>";
                yield (((($tmp =  !(null === $context["h"])) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatNumber($context["h"], 2, ".", ","), "html", null, true)) : (""));
                yield "</td></tr>
              ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['name'], $context['h'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 26
            yield "              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class=\"card bg-base-200\">
        <div class=\"card-body\">
          <h3 class=\"card-title\">";
            // line 33
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Totals by Month"));
            yield "</h3>
          <div class=\"overflow-x-auto\">
            <table class=\"table table-compact\">
              <thead><tr><th>";
            // line 36
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Month"));
            yield "</th><th>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Hours"));
            yield "</th></tr></thead>
              <tbody>
              ";
            // line 38
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["totalsByMonth"] ?? null));
            foreach ($context['_seq'] as $context["ym"] => $context["h"]) {
                // line 39
                yield "                <tr><td>";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["ym"]);
                yield "</td><td>";
                yield (((($tmp =  !(null === $context["h"])) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatNumber($context["h"], 2, ".", ","), "html", null, true)) : (""));
                yield "</td></tr>
              ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['ym'], $context['h'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 41
            yield "              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  ";
        }
        // line 48
        yield "
  ";
        // line 49
        yield from $this->load("partials/dynamic_list.twig", 49)->unwrap()->yield(CoreExtension::merge($context, ["title" => "", "items" => ((        // line 51
array_key_exists("times", $context)) ? (Twig\Extension\CoreExtension::default(($context["times"] ?? null), [])) : ([])), "columns" =>         // line 52
($context["columns"] ?? null), "actions" => ["edit_url" => $this->env->getFunction('url')->getCallable()("/times/edit"), "delete_url" => $this->env->getFunction('url')->getCallable()("/times/delete"), "id_field" => "id"]]));
        // line 55
        yield "  <div class=\"mt-4\">
    ";
        // line 56
        $context["extra"] = ["q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "per" => $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10)))];
        // line 57
        yield "    ";
        yield $this->env->getFunction('paginate')->getCallable()($this->env->getFilter('int')->getCallable()(((array_key_exists("total", $context)) ? (Twig\Extension\CoreExtension::default(($context["total"] ?? null), Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["times"] ?? null)))) : (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["times"] ?? null))))), $this->env->getFilter('int')->getCallable()(((array_key_exists("page", $context)) ? (Twig\Extension\CoreExtension::default(($context["page"] ?? null), 1)) : (1))), $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10))), ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), Twig\Extension\CoreExtension::merge(($context["extra"] ?? null), ["sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "date")) : ("date")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "desc")) : ("desc"))]));
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
        return "times_list.twig";
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
        return array (  178 => 57,  176 => 56,  173 => 55,  171 => 52,  170 => 51,  169 => 49,  166 => 48,  157 => 41,  146 => 39,  142 => 38,  135 => 36,  129 => 33,  120 => 26,  109 => 24,  105 => 23,  98 => 21,  92 => 18,  87 => 15,  84 => 14,  81 => 13,  79 => 12,  69 => 8,  67 => 7,  62 => 5,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "times_list.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/times_list.twig");
    }
}
