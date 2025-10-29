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

/* deals_list.twig */
class __TwigTemplate_8369ee71019f3c04ef4b353fe2e2dc79 extends Template
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
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Deals"));
        yield "</h2>
    <div class=\"flex items-center gap-2\">
      ";
        // line 7
        yield from $this->load("partials/dynamic_search.twig", 7)->unwrap()->yield(CoreExtension::merge($context, ["path" => ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), "q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "expected_close")) : ("expected_close")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc"))]));
        // line 8
        yield "      <a class=\"btn\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/deals/board"), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Board"));
        yield "</a>
      ";
        // line 9
        if ((($tmp = $this->env->getFunction('can')->getCallable()("deals", "create")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<a class=\"btn btn-primary\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/deals/new"), "html", null, true);
            yield "\">+ ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Add Deal"));
            yield "</a>";
        }
        // line 10
        yield "    </div>
  </div>

  ";
        // line 13
        if (array_key_exists("rollups", $context)) {
            // line 14
            yield "    <div class=\"grid grid-cols-1 md:grid-cols-3 gap-3\">
      <div class=\"stat bg-base-100 shadow\">
        <div class=\"stat-title\">";
            // line 16
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Total value"));
            yield "</div>
        <div class=\"stat-value\">";
            // line 17
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('format_number')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, ($context["rollups"] ?? null), "total", [], "any", true, true, false, 17)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["rollups"] ?? null), "total", [], "any", false, false, false, 17), 0)) : (0))), "html", null, true);
            yield "</div>
      </div>
      <div class=\"stat bg-base-100 shadow\">
        <div class=\"stat-title\">";
            // line 20
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Weighted forecast"));
            yield "</div>
        <div class=\"stat-value\">";
            // line 21
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('format_number')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, ($context["rollups"] ?? null), "weighted", [], "any", true, true, false, 21)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["rollups"] ?? null), "weighted", [], "any", false, false, false, 21), 0)) : (0))), "html", null, true);
            yield "</div>
      </div>
      <div class=\"stat bg-base-100 shadow\">
        <div class=\"stat-title\">";
            // line 24
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("By stage"));
            yield "</div>
        <div class=\"stat-desc\">
          ";
            // line 26
            if (CoreExtension::getAttribute($this->env, $this->source, ($context["rollups"] ?? null), "byStage", [], "any", true, true, false, 26)) {
                // line 27
                yield "            <div class=\"flex flex-wrap gap-2\">
              ";
                // line 28
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["rollups"] ?? null), "byStage", [], "any", false, false, false, 28));
                foreach ($context['_seq'] as $context["k"] => $context["v"]) {
                    // line 29
                    yield "                <span class=\"badge badge-ghost\">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["k"]);
                    yield ": ";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["v"], "html", null, true);
                    yield "</span>
              ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['k'], $context['v'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 31
                yield "            </div>
          ";
            }
            // line 33
            yield "        </div>
      </div>
    </div>
  ";
        }
        // line 37
        yield "
  ";
        // line 38
        if ((array_key_exists("columns", $context) && ($context["columns"] ?? null))) {
            // line 39
            yield "    ";
            yield from $this->load("partials/dynamic_list.twig", 39)->unwrap()->yield(CoreExtension::merge($context, ["title" => "", "items" => ((            // line 41
array_key_exists("items", $context)) ? (Twig\Extension\CoreExtension::default(($context["items"] ?? null), [])) : ([])), "columns" =>             // line 42
($context["columns"] ?? null), "path" => ((            // line 43
array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), "sort" => ((            // line 44
array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "expected_close")) : ("expected_close")), "dir" => ((            // line 45
array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc")), "q" => ((            // line 46
array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "per" => ((            // line 47
array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10)), "actions" => ["view_url" => $this->env->getFunction('url')->getCallable()("/deals/view"), "edit_url" => $this->env->getFunction('url')->getCallable()("/deals/edit"), "delete_url" => $this->env->getFunction('url')->getCallable()("/deals/delete"), "id_field" => "id"], "bulk" => ["post_url" => $this->env->getFunction('url')->getCallable()("/deals/bulk"), "actions" => [["value" => "delete", "label" => $this->env->getFunction('__')->getCallable()("Delete")], ["value" => "change_stage", "label" => $this->env->getFunction('__')->getCallable()("Change stage")]], "placeholders" => ["change_stage" => $this->env->getFunction('__')->getCallable()("Stage (e.g., qualified)")]]]));
            // line 55
            yield "    <div class=\"mt-4\">
      ";
            // line 56
            $context["extra"] = ["q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "per" => $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10)))];
            // line 57
            yield "      ";
            yield $this->env->getFunction('paginate')->getCallable()($this->env->getFilter('int')->getCallable()(((array_key_exists("total", $context)) ? (Twig\Extension\CoreExtension::default(($context["total"] ?? null), Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["items"] ?? null)))) : (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["items"] ?? null))))), $this->env->getFilter('int')->getCallable()(((array_key_exists("page", $context)) ? (Twig\Extension\CoreExtension::default(($context["page"] ?? null), 1)) : (1))), $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10))), ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), Twig\Extension\CoreExtension::merge(($context["extra"] ?? null), ["sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "expected_close")) : ("expected_close")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc"))]));
            yield "
    </div>
  ";
        }
        // line 60
        yield "</section>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "deals_list.twig";
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
        return array (  177 => 60,  170 => 57,  168 => 56,  165 => 55,  163 => 47,  162 => 46,  161 => 45,  160 => 44,  159 => 43,  158 => 42,  157 => 41,  155 => 39,  153 => 38,  150 => 37,  144 => 33,  140 => 31,  129 => 29,  125 => 28,  122 => 27,  120 => 26,  115 => 24,  109 => 21,  105 => 20,  99 => 17,  95 => 16,  91 => 14,  89 => 13,  84 => 10,  76 => 9,  69 => 8,  67 => 7,  62 => 5,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "deals_list.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/deals_list.twig");
    }
}
