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

/* layout.twig */
class __TwigTemplate_804803717b4d3b555bca9af26f497c63 extends Template
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
            'content' => [$this, 'block_content'],
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
    <title>";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("People & Pixel - Basic CRM"));
        yield "</title>
    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
    <link href=\"https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap\" rel=\"stylesheet\">
    <link href=\"https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@200..700\" rel=\"stylesheet\" />
    <link rel=\"canonical\" href=\"";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('canonical_url')->getCallable()(), "html", null, true);
        yield "\">
    <link rel=\"stylesheet\" href=\"";
        // line 12
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/style.css"), "html", null, true);
        yield "\">
    <script>
    (function(){
      try{
        var saved = localStorage.getItem('pp-theme');
        if(!saved){ saved = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light'; }
        if(saved === 'dark'){ document.documentElement.setAttribute('data-theme','dark'); }
      }catch(e){}
    })();
    </script>
</head>
<body>
<a href=\"#main\" class=\"skip-link\">Skip to main content</a>
<div id=\"sr-live\" class=\"visually-hidden\" aria-live=\"polite\" aria-atomic=\"true\"></div>
<header>
    <div class=\"container\">
        <h1>";
        // line 28
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("People & Pixel"));
        yield "</h1>
        <form method=\"get\" style=\"margin-left:auto; display:flex; align-items:center; gap:8px;\">
            <label for=\"lang\" class=\"visually-hidden\">";
        // line 30
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Language"));
        yield "</label>
            <select id=\"lang\" name=\"lang\" onchange=\"this.form.submit()\">
                <option value=\"en\" ";
        // line 32
        yield (((($context["currentLang"] ?? null) == "en")) ? ("selected") : (""));
        yield ">EN</option>
                <option value=\"de\" ";
        // line 33
        yield (((($context["currentLang"] ?? null) == "de")) ? ("selected") : (""));
        yield ">DE</option>
                <option value=\"pt\" ";
        // line 34
        yield (((($context["currentLang"] ?? null) == "pt")) ? ("selected") : (""));
        yield ">PT</option>
            </select>
        </form>
        <a href=\"#\" id=\"theme-toggle\" class=\"icon-btn\" title=\"";
        // line 37
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Toggle theme"));
        yield "\" aria-label=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Toggle theme"));
        yield "\">
            <span class=\"material-symbols-outlined\" id=\"theme-toggle-icon\" aria-hidden=\"true\">dark_mode</span>
        </a>
        <div style=\"margin-left:16px; display:flex; align-items:center; gap:8px;\">
            ";
        // line 42
        yield "            <a class=\"btn btn-secondary\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/login"), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Login"));
        yield "</a>
        </div>
    </div>
    <div class=\"container\">
        <nav>
            <a href=\"";
        // line 47
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">home</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Home"));
        yield "</a>
            <a href=\"";
        // line 48
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/contacts"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/contacts")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">group</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contacts"));
        yield "</a>
            <a href=\"";
        // line 49
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/times"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/times"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/times")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">schedule</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Working Times"));
        yield "</a>
            <a href=\"";
        // line 50
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/tasks"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/tasks")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">checklist</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks"));
        yield "</a>
            <a href=\"";
        // line 51
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/employees"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/employees"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/employees")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">badge</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Employees"));
        yield "</a>
            <a href=\"";
        // line 52
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/candidates"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/candidates"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/candidates")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">group_add</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Recruiting"));
        yield "</a>
            <a href=\"";
        // line 53
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/payments"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/payments"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/payments")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">payments</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Payments"));
        yield "</a>
            <a href=\"";
        // line 54
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/storage"), "html", null, true);
        yield "\" class=\"nav-link ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('active_class')->getCallable()("/storage"), "html", null, true);
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/storage")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield "><span class=\"material-symbols-outlined\" aria-hidden=\"true\">inventory_2</span> ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Storage"));
        yield "</a>
        </nav>
    </div>
</header>
<main id=\"main\" class=\"container\" tabindex=\"-1\">
    ";
        // line 59
        if ((array_key_exists("error", $context) && ($context["error"] ?? null))) {
            // line 60
            yield "        <div class=\"alert alert-error\" role=\"alert\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error"] ?? null));
            yield "</div>
    ";
        }
        // line 62
        yield "    ";
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 63
        yield "</main>
<footer>
    <div class=\"container\">
        <small>&copy; ";
        // line 66
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y"), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("People & Pixel"));
        yield ". ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("All rights reserved."));
        yield "</small>
    </div>
</footer>
<script>
(function(){
  function applyIcon(theme){
    var icon = document.getElementById('theme-toggle-icon');
    if(!icon) return; icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
  }
  function getTheme(){ return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'; }
  document.addEventListener('DOMContentLoaded', function(){
    var btn = document.getElementById('theme-toggle'); if(!btn) return; applyIcon(getTheme());
    btn.addEventListener('click', function(e){ e.preventDefault(); var isDark = document.documentElement.getAttribute('data-theme') === 'dark'; var next = isDark ? 'light' : 'dark'; if(next === 'dark'){ document.documentElement.setAttribute('data-theme','dark'); } else { document.documentElement.removeAttribute('data-theme'); } try{ localStorage.setItem('pp-theme', next); }catch(e){} applyIcon(next); var live = document.getElementById('sr-live'); if(live){ live.textContent = next==='dark' ? 'Dark mode enabled' : 'Light mode enabled'; }});
  });
})();
</script>
</body>
</html>
";
        yield from [];
    }

    // line 62
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layout.twig";
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
        return array (  256 => 62,  228 => 66,  223 => 63,  220 => 62,  214 => 60,  212 => 59,  198 => 54,  188 => 53,  178 => 52,  168 => 51,  158 => 50,  148 => 49,  138 => 48,  128 => 47,  117 => 42,  108 => 37,  102 => 34,  98 => 33,  94 => 32,  89 => 30,  84 => 28,  65 => 12,  61 => 11,  53 => 6,  46 => 2,  43 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/layout.twig");
    }
}
