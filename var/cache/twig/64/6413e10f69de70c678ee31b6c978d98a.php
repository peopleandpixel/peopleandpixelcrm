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

/* import_form.twig */
class __TwigTemplate_8c7ce369c354ada2e06b69e910acb4e7 extends Template
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
        yield "<h1>Data Import</h1>
";
        // line 4
        if ((($tmp = ($context["error"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<div class=\"alert alert-error\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error"] ?? null));
            yield "</div>";
        }
        // line 5
        yield "<form method=\"post\" action=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/import"), "html", null, true);
        yield "\">
  ";
        // line 6
        yield $this->env->getFunction('csrf_field')->getCallable()();
        yield "
  <div class=\"field\">
    <label for=\"entity\">Entity</label>
    <select id=\"entity\" name=\"entity\" required>
      ";
        // line 10
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["entities"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["e"]) {
            // line 11
            yield "        <option value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["e"]);
            yield "\" ";
            if ((($context["old"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "entity", [], "any", false, false, false, 11) == $context["e"]))) {
                yield "selected";
            }
            yield ">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["e"]);
            yield "</option>
      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['e'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 13
        yield "    </select>
  </div>
  <div class=\"field\">
    <label>Format</label>
    <label><input type=\"radio\" name=\"format\" value=\"json\" ";
        // line 17
        if (( !($context["old"] ?? null) || (CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "format", [], "any", false, false, false, 17) != "csv"))) {
            yield "checked";
        }
        yield "> JSON</label>
    <label><input type=\"radio\" name=\"format\" value=\"csv\" ";
        // line 18
        if ((($context["old"] ?? null) && (CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "format", [], "any", false, false, false, 18) == "csv"))) {
            yield "checked";
        }
        yield "> CSV</label>
  </div>
  <div class=\"field\">
    <label for=\"strategy\">Strategy</label>
    <select id=\"strategy\" name=\"strategy\">
      ";
        // line 23
        $context["s"] = (((($context["old"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "strategy", [], "any", false, false, false, 23))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "strategy", [], "any", false, false, false, 23)) : ("insert"));
        // line 24
        yield "      <option value=\"insert\" ";
        if ((($context["s"] ?? null) == "insert")) {
            yield "selected";
        }
        yield ">Insert only (skip if exists)</option>
      <option value=\"upsert\" ";
        // line 25
        if ((($context["s"] ?? null) == "upsert")) {
            yield "selected";
        }
        yield ">Upsert (update if exists)</option>
      <option value=\"merge\" ";
        // line 26
        if ((($context["s"] ?? null) == "merge")) {
            yield "selected";
        }
        yield ">Merge (fill empty fields; contacts: union tags)</option>
    </select>
  </div>
  <div class=\"field\">
    <label for=\"key_field\">Match by</label>
    ";
        // line 31
        $context["k"] = (((($context["old"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "key_field", [], "any", false, false, false, 31))) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "key_field", [], "any", false, false, false, 31)) : ("id"));
        // line 32
        yield "    <select id=\"key_field\" name=\"key_field\">
      <option value=\"id\" ";
        // line 33
        if ((($context["k"] ?? null) == "id")) {
            yield "selected";
        }
        yield ">ID</option>
      <option value=\"email\" ";
        // line 34
        if ((($context["k"] ?? null) == "email")) {
            yield "selected";
        }
        yield ">Email (contacts/candidates/employees)</option>
    </select>
    <small>Used for upsert/merge to find existing records.</small>
  </div>
  <div class=\"field\">
    <label for=\"payload\">Data (JSON array or CSV with header)</label>
    <textarea id=\"payload\" name=\"payload\" rows=\"12\" required>";
        // line 40
        yield (((($context["old"] ?? null) && CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "payload", [], "any", false, false, false, 40))) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "payload", [], "any", false, false, false, 40), "html", null, true)) : (""));
        yield "</textarea>
  </div>
  <div class=\"field\">
    <label><input type=\"checkbox\" name=\"dry_run\" value=\"1\" ";
        // line 43
        if (( !($context["old"] ?? null) || CoreExtension::getAttribute($this->env, $this->source, ($context["old"] ?? null), "dryRun", [], "any", false, false, false, 43))) {
            yield "checked";
        }
        yield "> Dry-run (validate only, show preview, do not save)</label>
  </div>
  <div class=\"actions\">
    <button type=\"submit\" class=\"btn\">Validate Import</button>
  </div>
</form>
<p class=\"mt\">
  Tip: You can export current data using the export endpoints, e.g.:<br>
  <code>/export/contacts.json</code>, <code>/export/contacts.csv</code>
</p>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "import_form.twig";
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
        return array (  173 => 43,  167 => 40,  156 => 34,  150 => 33,  147 => 32,  145 => 31,  135 => 26,  129 => 25,  122 => 24,  120 => 23,  110 => 18,  104 => 17,  98 => 13,  83 => 11,  79 => 10,  72 => 6,  67 => 5,  61 => 4,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "import_form.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/import_form.twig");
    }
}
