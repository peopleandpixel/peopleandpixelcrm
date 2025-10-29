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

/* payments_list.twig */
class __TwigTemplate_c91fb73faf5417322ca224e5e19d8c49 extends Template
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
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Payments"));
        yield "</h2>
    <div class=\"flex items-center gap-2\">
      ";
        // line 7
        yield from $this->load("partials/dynamic_search.twig", 7)->unwrap()->yield(CoreExtension::merge($context, ["path" => ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), "q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "date")) : ("date")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "desc")) : ("desc"))]));
        // line 8
        yield "      <a class=\"btn\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/payments/new"), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("+ Add Payment"));
        yield "</a>
    </div>
  </div>

  ";
        // line 12
        yield from $this->load("partials/dynamic_list.twig", 12)->unwrap()->yield(CoreExtension::merge($context, ["title" => "", "items" => ((        // line 14
array_key_exists("payments", $context)) ? (Twig\Extension\CoreExtension::default(($context["payments"] ?? null), [])) : ([])), "columns" =>         // line 15
($context["columns"] ?? null), "actions" => ["edit_url" => $this->env->getFunction('url')->getCallable()("/payments/edit"), "delete_url" => $this->env->getFunction('url')->getCallable()("/payments/delete"), "id_field" => "id"]]));
        // line 18
        yield "  <div class=\"mt-4\">
    ";
        // line 19
        $context["extra"] = ["q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "per" => $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10)))];
        // line 20
        yield "    ";
        yield $this->env->getFunction('paginate')->getCallable()($this->env->getFilter('int')->getCallable()(((array_key_exists("total", $context)) ? (Twig\Extension\CoreExtension::default(($context["total"] ?? null), Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["payments"] ?? null)))) : (Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["payments"] ?? null))))), $this->env->getFilter('int')->getCallable()(((array_key_exists("page", $context)) ? (Twig\Extension\CoreExtension::default(($context["page"] ?? null), 1)) : (1))), $this->env->getFilter('int')->getCallable()(((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10))), ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), Twig\Extension\CoreExtension::merge(($context["extra"] ?? null), ["sort" => ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "date")) : ("date")), "dir" => ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "desc")) : ("desc"))]));
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
        return "payments_list.twig";
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
        return array (  88 => 20,  86 => 19,  83 => 18,  81 => 15,  80 => 14,  79 => 12,  69 => 8,  67 => 7,  62 => 5,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "payments_list.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/payments_list.twig");
    }
}
