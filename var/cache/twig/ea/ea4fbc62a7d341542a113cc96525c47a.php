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

/* partials/flashes.twig */
class __TwigTemplate_7f3a9ee998d1507bf9202e7b59418e8d extends Template
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
        // line 2
        if ((array_key_exists("error", $context) && ($context["error"] ?? null))) {
            // line 3
            yield "  <div class=\"alert alert-error\" role=\"alert\">
    <span>";
            // line 4
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error"] ?? null));
            yield "</span>
  </div>
";
        }
        // line 7
        yield "
";
        // line 8
        if ((array_key_exists("flashes", $context) && ($context["flashes"] ?? null))) {
            // line 9
            yield "  ";
            // line 10
            yield "  ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["flashes"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                // line 11
                yield "    ";
                $context["type"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["f"], "type", [], "any", true, true, false, 11)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["f"], "type", [], "any", false, false, false, 11), "info")) : ("info"));
                // line 12
                yield "    ";
                $context["cls"] = (((($context["type"] ?? null) == "success")) ? ("alert-success") : ((((($context["type"] ?? null) == "error")) ? ("alert-error") : ("alert-info"))));
                // line 13
                yield "    <div class=\"alert ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["cls"] ?? null), "html", null, true);
                yield "\" role=\"status\">
      <span>";
                // line 14
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, $context["f"], "message", [], "any", true, true, false, 14)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["f"], "message", [], "any", false, false, false, 14), "")) : ("")));
                yield "</span>
    </div>
  ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/flashes.twig";
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
        return array (  76 => 14,  71 => 13,  68 => 12,  65 => 11,  60 => 10,  58 => 9,  56 => 8,  53 => 7,  47 => 4,  44 => 3,  42 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "partials/flashes.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/partials/flashes.twig");
    }
}
