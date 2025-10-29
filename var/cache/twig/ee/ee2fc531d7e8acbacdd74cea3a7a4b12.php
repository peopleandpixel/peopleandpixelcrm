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

/* partials/icon.twig */
class __TwigTemplate_bd1d105a81f7eb5a84dca87c7fa6ab5b extends Template
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
        // line 7
        $context["cls"] = ((array_key_exists("classes", $context)) ? (Twig\Extension\CoreExtension::default(($context["classes"] ?? null), "w-5 h-5 inline-block align-middle")) : ("w-5 h-5 inline-block align-middle"));
        // line 8
        $context["labelled"] = (array_key_exists("title", $context) && ($context["title"] ?? null));
        // line 9
        yield "<svg class=\"icon ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["cls"] ?? null), "html", null, true);
        yield "\" viewBox=\"0 0 24 24\" role=\"img\" aria-hidden=\"";
        yield (((($tmp = ($context["labelled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("false") : ("true"));
        yield "\">
  ";
        // line 10
        if ((($tmp = ($context["labelled"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<title>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["title"] ?? null));
            yield "</title>";
        }
        // line 11
        yield "  <use href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/icons/sprite.svg"), "html", null, true);
        yield "#";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null));
        yield "\"></use>
</svg>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/icon.twig";
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
        return array (  59 => 11,  53 => 10,  46 => 9,  44 => 8,  42 => 7,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "partials/icon.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/partials/icon.twig");
    }
}
