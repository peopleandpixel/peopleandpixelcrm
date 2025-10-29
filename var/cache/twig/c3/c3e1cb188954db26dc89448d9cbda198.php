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

/* partials/dynamic_search.twig */
class __TwigTemplate_e303cccd5e8a70560c8ca900a2b1ee21 extends Template
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

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 12
        yield "<form method=\"get\" action=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())));
        yield "\" class=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("classes", $context)) ? (Twig\Extension\CoreExtension::default(($context["classes"] ?? null), "flex items-center gap-2")) : ("flex items-center gap-2")));
        yield "\">
    <label for=\"q\" class=\"sr-only\">";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Search"));
        yield "</label>
    <label class=\"input input-bordered flex items-center gap-2\">
      <input class=\"grow\" type=\"search\" id=\"q\" name=\"q\" value=\"";
        // line 15
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")));
        yield "\" placeholder=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("placeholder", $context)) ? (Twig\Extension\CoreExtension::default(($context["placeholder"] ?? null), $this->env->getFunction('__')->getCallable()("Search…"))) : ($this->env->getFunction('__')->getCallable()("Search…"))));
        yield "\">
      <span aria-hidden=\"true\">🔍</span>
    </label>
    ";
        // line 18
        if (array_key_exists("sort", $context)) {
            yield "<input type=\"hidden\" name=\"sort\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["sort"] ?? null));
            yield "\">";
        }
        // line 19
        yield "    ";
        if (array_key_exists("dir", $context)) {
            yield "<input type=\"hidden\" name=\"dir\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["dir"] ?? null));
            yield "\">";
        }
        // line 20
        yield "    ";
        if ((array_key_exists("extra", $context) && ($context["extra"] ?? null))) {
            // line 21
            yield "        ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["extra"] ?? null));
            foreach ($context['_seq'] as $context["k"] => $context["v"]) {
                // line 22
                yield "            <input type=\"hidden\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["k"]);
                yield "\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["v"]);
                yield "\">
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['k'], $context['v'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 24
            yield "    ";
        }
        // line 25
        yield "    <button class=\"btn\" type=\"submit\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("button_label", $context)) ? (Twig\Extension\CoreExtension::default(($context["button_label"] ?? null), $this->env->getFunction('__')->getCallable()("Search"))) : ($this->env->getFunction('__')->getCallable()("Search"))));
        yield "</button>
</form>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/dynamic_search.twig";
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
        return array (  97 => 25,  94 => 24,  83 => 22,  78 => 21,  75 => 20,  68 => 19,  62 => 18,  54 => 15,  49 => 13,  42 => 12,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "partials/dynamic_search.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/partials/dynamic_search.twig");
    }
}
