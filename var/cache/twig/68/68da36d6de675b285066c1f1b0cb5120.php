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

/* contacts_dedupe.twig */
class __TwigTemplate_83b5aa0bf3192a1624cde3b3ff679612 extends Template
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
        yield "<h1>";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Find duplicates"), "html", null, true);
        yield "</h1>
<p class=\"text-sm text-base-content/70\">";
        // line 4
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Suspected duplicates based on identical email, phone, or exact name. Choose which contact to keep and merge the other into it."), "html", null, true);
        yield "</p>

<table class=\"table w-full mt-3\">
  <thead>
    <tr>
      <th>";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Reason"), "html", null, true);
        yield "</th>
      <th>";
        // line 10
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contact A"), "html", null, true);
        yield "</th>
      <th></th>
      <th>";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contact B"), "html", null, true);
        yield "</th>
      <th>";
        // line 13
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Actions"), "html", null, true);
        yield "</th>
    </tr>
  </thead>
  <tbody>
  ";
        // line 17
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["pairs"] ?? null));
        $context['_iterated'] = false;
        foreach ($context['_seq'] as $context["_key"] => $context["p"]) {
            // line 18
            yield "    <tr>
      <td class=\"uppercase text-xs\">";
            // line 19
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["p"], "reason", [], "any", false, false, false, 19), "html", null, true);
            yield "</td>
      <td>
        <div><strong>#";
            // line 21
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 21), "id", [], "any", false, false, false, 21), "html", null, true);
            yield "</strong> ";
            yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, true, false, 21), "name", [], "any", true, true, false, 21) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 21), "name", [], "any", false, false, false, 21)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 21), "name", [], "any", false, false, false, 21), "html", null, true)) : (""));
            yield "</div>
        <div class=\"text-sm text-base-content/70\">";
            // line 22
            yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, true, false, 22), "email", [], "any", true, true, false, 22) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 22), "email", [], "any", false, false, false, 22)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 22), "email", [], "any", false, false, false, 22), "html", null, true)) : (""));
            yield " ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 22), "phone", [], "any", false, false, false, 22)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((" · " . CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 22), "phone", [], "any", false, false, false, 22)), "html", null, true)) : (""));
            yield "</div>
      </td>
      <td class=\"text-center\">↔</td>
      <td>
        <div><strong>#";
            // line 26
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 26), "id", [], "any", false, false, false, 26), "html", null, true);
            yield "</strong> ";
            yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, true, false, 26), "name", [], "any", true, true, false, 26) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 26), "name", [], "any", false, false, false, 26)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 26), "name", [], "any", false, false, false, 26), "html", null, true)) : (""));
            yield "</div>
        <div class=\"text-sm text-base-content/70\">";
            // line 27
            yield (((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, true, false, 27), "email", [], "any", true, true, false, 27) &&  !(null === CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 27), "email", [], "any", false, false, false, 27)))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 27), "email", [], "any", false, false, false, 27), "html", null, true)) : (""));
            yield " ";
            yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 27), "phone", [], "any", false, false, false, 27)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((" · " . CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 27), "phone", [], "any", false, false, false, 27)), "html", null, true)) : (""));
            yield "</div>
      </td>
      <td>
        <form method=\"post\" action=\"";
            // line 30
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts/merge"), "html", null, true);
            yield "\" class=\"flex flex-col gap-1\">
          ";
            // line 31
            yield $this->env->getFunction('csrf_field')->getCallable()();
            yield "
          <input type=\"hidden\" name=\"merge_id\" value=\"";
            // line 32
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 32), "id", [], "any", false, false, false, 32), "html", null, true);
            yield "\">
          <input type=\"hidden\" name=\"keep_id\" value=\"";
            // line 33
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 33), "id", [], "any", false, false, false, 33), "html", null, true);
            yield "\">
          <button class=\"btn btn-sm\" type=\"submit\">";
            // line 34
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Keep B, merge A → B"), "html", null, true);
            yield "</button>
        </form>
        <form method=\"post\" action=\"";
            // line 36
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts/merge"), "html", null, true);
            yield "\" class=\"flex flex-col gap-1 mt-1\">
          ";
            // line 37
            yield $this->env->getFunction('csrf_field')->getCallable()();
            yield "
          <input type=\"hidden\" name=\"merge_id\" value=\"";
            // line 38
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "b", [], "any", false, false, false, 38), "id", [], "any", false, false, false, 38), "html", null, true);
            yield "\">
          <input type=\"hidden\" name=\"keep_id\" value=\"";
            // line 39
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["p"], "a", [], "any", false, false, false, 39), "id", [], "any", false, false, false, 39), "html", null, true);
            yield "\">
          <button class=\"btn btn-sm\" type=\"submit\">";
            // line 40
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Keep A, merge B → A"), "html", null, true);
            yield "</button>
        </form>
      </td>
    </tr>
  ";
            $context['_iterated'] = true;
        }
        // line 44
        if (!$context['_iterated']) {
            // line 45
            yield "    <tr><td colspan=\"5\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No duplicates found"), "html", null, true);
            yield "</td></tr>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['p'], $context['_parent'], $context['_iterated']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 47
        yield "  </tbody>
</table>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "contacts_dedupe.twig";
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
        return array (  190 => 47,  181 => 45,  179 => 44,  170 => 40,  166 => 39,  162 => 38,  158 => 37,  154 => 36,  149 => 34,  145 => 33,  141 => 32,  137 => 31,  133 => 30,  125 => 27,  119 => 26,  110 => 22,  104 => 21,  99 => 19,  96 => 18,  91 => 17,  84 => 13,  80 => 12,  75 => 10,  71 => 9,  63 => 4,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "contacts_dedupe.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/contacts_dedupe.twig");
    }
}
