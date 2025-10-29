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

/* errors/404.twig */
class __TwigTemplate_ef885bd8c2bff34ae6ac3a507c786140 extends Template
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
        // line 1
        yield "<!doctype html>
<html lang=\"";
        // line 2
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("currentLang", $context)) ? (Twig\Extension\CoreExtension::default(($context["currentLang"] ?? null), "en")) : ("en")));
        yield "\">
<head>
  <meta charset=\"utf-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <title>404 – Not Found</title>
  <link rel=\"stylesheet\" href=\"";
        // line 7
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/style.css"), "html", null, true);
        yield "\">
</head>
<body>
  <main class=\"container\" style=\"padding:24px;\">
    <h1>404 – ";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Not Found"));
        yield "</h1>
    <p>";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("The requested resource could not be found."));
        yield "</p>
    ";
        // line 13
        if ((array_key_exists("errorDetails", $context) && ($context["errorDetails"] ?? null))) {
            // line 14
            yield "      <details open>
        <summary>Details (debug)</summary>
        <pre style=\"white-space:pre-wrap\">";
            // line 16
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((((((((CoreExtension::getAttribute($this->env, $this->source, ($context["errorDetails"] ?? null), "class", [], "any", false, false, false, 16) . ": ") . CoreExtension::getAttribute($this->env, $this->source, ($context["errorDetails"] ?? null), "message", [], "any", false, false, false, 16)) . "
") . CoreExtension::getAttribute($this->env, $this->source, ($context["errorDetails"] ?? null), "file", [], "any", false, false, false, 16)) . ":") . CoreExtension::getAttribute($this->env, $this->source, ($context["errorDetails"] ?? null), "line", [], "any", false, false, false, 16)) . "
") . CoreExtension::getAttribute($this->env, $this->source, ($context["errorDetails"] ?? null), "trace", [], "any", false, false, false, 16)));
            yield "</pre>
      </details>
    ";
        }
        // line 19
        yield "    <p><a href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/"), "html", null, true);
        yield "\">&larr; ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Back to home"));
        yield "</a></p>
  </main>
</body>
</html>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "errors/404.twig";
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
        return array (  82 => 19,  74 => 16,  70 => 14,  68 => 13,  64 => 12,  60 => 11,  53 => 7,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "errors/404.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/errors/404.twig");
    }
}
