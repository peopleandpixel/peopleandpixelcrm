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

/* contacts_add.twig */
class __TwigTemplate_197f4df2b85a295c0a50d29b33f6ea6b extends Template
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
        yield "<section>
    <h2>";
        // line 4
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Add Contact"));
        yield "</h2>
    <form method=\"post\" class=\"form\">
        ";
        // line 6
        yield $this->env->getFunction('csrf_field')->getCallable()();
        yield "
        <div class=\"form-row\">
            <label for=\"name\">";
        // line 8
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Name"));
        yield " *</label>
            <input type=\"text\" id=\"name\" name=\"name\" value=\"";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("name", $context)) ? (Twig\Extension\CoreExtension::default(($context["name"] ?? null), "")) : ("")));
        yield "\" required>
        </div>
        <div class=\"form-row\">
            <label for=\"company\">";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Company"));
        yield "</label>
            <input type=\"text\" id=\"company\" name=\"company\" value=\"";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("company", $context)) ? (Twig\Extension\CoreExtension::default(($context["company"] ?? null), "")) : ("")));
        yield "\">
        </div>
        <div class=\"form-row\">
            <label for=\"email\">";
        // line 16
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Email"));
        yield "</label>
            <input type=\"email\" id=\"email\" name=\"email\" value=\"";
        // line 17
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("email", $context)) ? (Twig\Extension\CoreExtension::default(($context["email"] ?? null), "")) : ("")));
        yield "\">
        </div>
        <div class=\"form-row\">
            <label for=\"phone\">";
        // line 20
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Phone"));
        yield "</label>
            <input type=\"text\" id=\"phone\" name=\"phone\" value=\"";
        // line 21
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("phone", $context)) ? (Twig\Extension\CoreExtension::default(($context["phone"] ?? null), "")) : ("")));
        yield "\">
        </div>
        <div class=\"form-row\">
            <label for=\"notes\">";
        // line 24
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Notes"));
        yield "</label>
            <textarea id=\"notes\" name=\"notes\" rows=\"4\">";
        // line 25
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("notes", $context)) ? (Twig\Extension\CoreExtension::default(($context["notes"] ?? null), "")) : ("")));
        yield "</textarea>
        </div>
        <div class=\"form-actions\">
            <a class=\"btn btn-secondary\" href=\"";
        // line 28
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts"), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Cancel"));
        yield "</a>
            <button class=\"btn\" type=\"submit\">";
        // line 29
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Save Contact"));
        yield "</button>
        </div>
    </form>
</section>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "contacts_add.twig";
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
        return array (  127 => 29,  121 => 28,  115 => 25,  111 => 24,  105 => 21,  101 => 20,  95 => 17,  91 => 16,  85 => 13,  81 => 12,  75 => 9,  71 => 8,  66 => 6,  61 => 4,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "contacts_add.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/contacts_add.twig");
    }
}
