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

/* partials/header.twig */
class __TwigTemplate_9594be00826208012b68634c48072642 extends Template
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
        yield "<header id=\"app-header\" class=\"navbar bg-base-200\">
  <div class=\"flex-1 items-center gap-3\">
    <a href=\"";
        // line 3
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/"), "html", null, true);
        yield "\" class=\"btn btn-ghost text-xl brand\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("People & Pixel"));
        yield "</a>
    <span id=\"running-timer\" class=\"hidden badge badge-warning gap-1\" aria-live=\"off\">
      <span class=\"inline-flex items-center\" aria-hidden=\"true\">⏱️</span>
      <span data-time>00:00:00</span>
    </span>
  </div>
  <div class=\"flex-none flex items-center gap-2 quick\">
    <a href=\"";
        // line 10
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/timer"), "html", null, true);
        yield "\" class=\"btn btn-sm btn-accent\" title=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Open Timer"));
        yield "\">⏱️ ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Timer"));
        yield "</a>
    <a href=\"";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts/edit"), "html", null, true);
        yield "\" class=\"btn btn-sm btn-primary\" title=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("New Contact"));
        yield "\">➕ ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contact"));
        yield "</a>
    <a href=\"";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks/edit"), "html", null, true);
        yield "\" class=\"btn btn-sm\" title=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("New Task"));
        yield "\">➕ ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Task"));
        yield "</a>
    <button id=\"theme-toggle\" class=\"btn btn-ghost btn-circle\" title=\"";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Toggle theme"));
        yield "\" aria-label=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Toggle theme"));
        yield "\" aria-pressed=\"false\">
      <span data-icon role=\"img\" aria-hidden=\"true\">☀️</span>
    </button>
    ";
        // line 16
        $context["u"] = $this->env->getFunction('current_user')->getCallable()();
        // line 17
        yield "    ";
        if ((($tmp = ($context["u"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 18
            yield "      ";
            // line 19
            yield "      ";
            $context["name"] = ((((CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "fullname", [], "any", true, true, false, 19)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "fullname", [], "any", false, false, false, 19), "")) : (""))) ? (((CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "fullname", [], "any", true, true, false, 19)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "fullname", [], "any", false, false, false, 19), "")) : (""))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "username", [], "any", true, true, false, 19)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "username", [], "any", false, false, false, 19), "")) : (""))));
            // line 20
            yield "      ";
            $context["initials"] = (((($tmp = ($context["name"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (Twig\Extension\CoreExtension::split($this->env->getCharset(), ($context["name"] ?? null), " ")) : ([]));
            // line 21
            yield "      ";
            $context["initials"] = (((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["initials"] ?? null)) > 1)) ? ((Twig\Extension\CoreExtension::slice($this->env->getCharset(), (($_v0 = ($context["initials"] ?? null)) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0[0] ?? null) : null), 0, 1) . Twig\Extension\CoreExtension::slice($this->env->getCharset(), (($_v1 = ($context["initials"] ?? null)) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1[1] ?? null) : null), 0, 1))) : (Twig\Extension\CoreExtension::slice($this->env->getCharset(), ($context["name"] ?? null), 0, 2)));
            // line 22
            yield "      <div class=\"tooltip tooltip-bottom\" data-tip=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null));
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "role", [], "any", false, false, false, 22)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " · ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["u"] ?? null), "role", [], "any", false, false, false, 22));
            }
            yield "\">
        <div class=\"avatar placeholder\">
          <div class=\"bg-neutral text-neutral-content rounded-full w-9 h-9 flex items-center justify-center border border-base-300\" title=\"";
            // line 24
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Logged in as"));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["name"] ?? null));
            yield "\">
            <span class=\"text-sm\">";
            // line 25
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::upper($this->env->getCharset(), ($context["initials"] ?? null)), "html", null, true);
            yield "</span>
          </div>
        </div>
      </div>
    ";
        }
        // line 30
        yield "    <a href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/logout"), "html", null, true);
        yield "\" class=\"btn btn-ghost btn-circle\" title=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Logout"));
        yield "\" aria-label=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Logout"));
        yield "\">
      <span aria-hidden=\"true\">⏻</span>
    </a>
    <span id=\"sr-live\" class=\"sr-only\" aria-live=\"polite\"></span>
  </div>
</header>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/header.twig";
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
        return array (  130 => 30,  122 => 25,  116 => 24,  106 => 22,  103 => 21,  100 => 20,  97 => 19,  95 => 18,  92 => 17,  90 => 16,  82 => 13,  74 => 12,  66 => 11,  58 => 10,  46 => 3,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "partials/header.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/partials/header.twig");
    }
}
