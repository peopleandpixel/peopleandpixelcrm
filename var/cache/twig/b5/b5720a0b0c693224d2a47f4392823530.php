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
        yield "\" data-theme=\"light\">
<head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <title>";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("People & Pixel - Basic CRM"));
        yield "</title>
    <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
    <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
    <link rel=\"canonical\" href=\"";
        // line 9
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('canonical_url')->getCallable()(), "html", null, true);
        yield "\">
    <meta name=\"theme-color\" content=\"#2563eb\">
    <link rel=\"manifest\" href=\"";
        // line 11
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/manifest.webmanifest"), "html", null, true);
        yield "\">
    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\">
    <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black-translucent\">
    <meta name=\"apple-mobile-web-app-title\" content=\"People & Pixel\">
    <link rel=\"icon\" href=\"";
        // line 15
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/icons/app-icon.svg"), "html", null, true);
        yield "\" type=\"image/svg+xml\">
    <link rel=\"apple-touch-icon\" href=\"";
        // line 16
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/icons/app-icon.svg"), "html", null, true);
        yield "\">
    <!-- Tailwind CSS + DaisyUI -->
    <script>
      // Disable Tailwind preflight to avoid global CSS resets that can break existing layout
      window.tailwind = window.tailwind || {};
      window.tailwind.config = { corePlugins: { preflight: false } };
    </script>
    <script src=\"https://cdn.tailwindcss.com\"></script>
    <link id=\"daisyui-css\" href=\"https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css\" rel=\"stylesheet\" type=\"text/css\" />
    <script>
      // Runtime fallback: if DaisyUI failed to load from jsDelivr, try unpkg mirror
      (function(){
        function ready(fn){ if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
        function hasDaisy(){
          try {
            var el = document.createElement('button');
            el.className = 'btn';
            el.style.position = 'absolute'; el.style.left = '-9999px';
            document.body.appendChild(el);
            var cs = window.getComputedStyle(el);
            var pad = parseFloat(cs.paddingLeft) || 0;
            var rad = (cs.borderRadius || '0px');
            document.body.removeChild(el);
            return pad > 8 && rad !== '0px';
          } catch(e){ return false; }
        }
        function injectFallback(){
          var link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = 'https://unpkg.com/daisyui@4.12.10/dist/full.min.css';
          link.crossOrigin = 'anonymous';
          document.head.appendChild(link);
        }
        function injectLocal(){
          var link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = '";
        // line 52
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/vendor/daisyui.min.css"), "html", null, true);
        yield "';
          document.head.appendChild(link);
        }
        ready(function(){
          // wait a tick to allow primary CSS to apply
          setTimeout(function(){
            if (!hasDaisy()) {
              injectFallback();
              // Try mirror; if still missing, try local lightweight fallback; finally enable minimal emergency styles
              setTimeout(function(){
                if (!hasDaisy()) {
                  injectLocal();
                  setTimeout(function(){ if (!hasDaisy()) { try { document.documentElement.classList.add('no-daisyui'); } catch(_){} } }, 800);
                }
              }, 800);
            }
          }, 600);
        });
      })();
    </script>
    <!-- Custom overrides (optional) -->
    <link rel=\"stylesheet\" href=\"";
        // line 73
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/style.css"), "html", null, true);
        yield "\">
</head>
<body class=\"min-h-screen bg-base-100 text-base-content\">
";
        // line 76
        yield from $this->load("partials/header.twig", 76)->unwrap()->yield($context);
        // line 77
        yield "<div id=\"main\" class=\"container mx-auto p-4\" tabindex=\"-1\">
    ";
        // line 78
        yield from $this->load("partials/flashes.twig", 78)->unwrap()->yield($context);
        // line 79
        yield "    <div class=\"flex gap-4\">
        <aside class=\"w-56 shrink-0\">
            <nav class=\"menu bg-base-200 rounded-box\" role=\"navigation\" aria-label=\"";
        // line 81
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Primary"));
        yield "\">
                <ul>
                    <li><a href=\"";
        // line 83
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/"), "html", null, true);
        yield "\" class=\"";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield ">";
        yield from $this->load("partials/icon.twig", 83)->unwrap()->yield(CoreExtension::merge($context, ["name" => "home", "classes" => "w-5 h-5 mr-2"]));
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Home"));
        yield "</a></li>
                    ";
        // line 84
        if ((($tmp = $this->env->getFunction('can')->getCallable()("contacts", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 85
            yield "                    <li>
                      <details ";
            // line 86
            yield ((($this->env->getFunction('active_class')->getCallable()("/contacts") || $this->env->getFunction('active_class')->getCallable()("/contacts/dedupe"))) ? ("open") : (""));
            yield ">
                        <summary>
                          <span class=\"inline-flex items-center\">
                            ";
            // line 89
            yield from $this->load("partials/icon.twig", 89)->unwrap()->yield(CoreExtension::merge($context, ["name" => "users", "classes" => "w-5 h-5 mr-2"]));
            // line 90
            yield "                            ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contacts"));
            yield "
                          </span>
                        </summary>
                        <ul>
                          <li>
                            <a href=\"";
            // line 95
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/contacts")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/contacts")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 96
            yield from $this->load("partials/icon.twig", 96)->unwrap()->yield(CoreExtension::merge($context, ["name" => "users", "classes" => "w-5 h-5 mr-2"]));
            // line 97
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("All Contacts"));
            yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
            // line 101
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/contacts/dedupe"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/contacts/dedupe")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/contacts/dedupe")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 102
            yield from $this->load("partials/icon.twig", 102)->unwrap()->yield(CoreExtension::merge($context, ["name" => "search", "classes" => "w-5 h-5 mr-2"]));
            // line 103
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Find duplicates"));
            yield "
                            </a>
                          </li>
                        </ul>
                      </details>
                    </li>
                    ";
        }
        // line 110
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("tasks", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/tasks"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/tasks")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/tasks")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 110)->unwrap()->yield(CoreExtension::merge($context, ["name" => "checklist", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks"));
            yield "</a></li>";
        }
        // line 111
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("deals", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/deals"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/deals")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/deals")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 111)->unwrap()->yield(CoreExtension::merge($context, ["name" => "chart-up", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Deals"));
            yield "</a></li>";
        }
        // line 112
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("projects", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/projects"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/projects")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/projects")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 112)->unwrap()->yield(CoreExtension::merge($context, ["name" => "box", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Projects"));
            yield "</a></li>";
        }
        // line 113
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can_url')->getCallable()("/calendar")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/calendar"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/calendar")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/calendar")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 113)->unwrap()->yield(CoreExtension::merge($context, ["name" => "calendar", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Calendar"));
            yield "</a></li>";
        }
        // line 114
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("reports", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/reports"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/reports")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/reports")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 114)->unwrap()->yield(CoreExtension::merge($context, ["name" => "chart-pie", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Reports"));
            yield "</a></li>";
        }
        // line 115
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can_url')->getCallable()("/search")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/search"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/search")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/search")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 115)->unwrap()->yield(CoreExtension::merge($context, ["name" => "search", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Search"));
            yield "</a></li>";
        }
        // line 116
        yield "
                    <li class=\"menu-title\"><span>";
        // line 117
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("More"));
        yield "</span></li>
                    ";
        // line 118
        if ((($tmp = $this->env->getFunction('can')->getCallable()("employees", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/employees"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/employees")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/employees")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 118)->unwrap()->yield(CoreExtension::merge($context, ["name" => "briefcase", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Employees"));
            yield "</a></li>";
        }
        // line 119
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("times", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/times"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/times")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/times")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 119)->unwrap()->yield(CoreExtension::merge($context, ["name" => "stopwatch", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Working Times"));
            yield "</a></li>";
        }
        // line 120
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("candidates", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/candidates"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/candidates")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/candidates")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 120)->unwrap()->yield(CoreExtension::merge($context, ["name" => "magnet", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Recruiting"));
            yield "</a></li>";
        }
        // line 121
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("payments", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/payments"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/payments")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/payments")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 121)->unwrap()->yield(CoreExtension::merge($context, ["name" => "credit-card", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Payments"));
            yield "</a></li>";
        }
        // line 122
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("documents", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<li><a href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/documents"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/documents")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/documents")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">";
            yield from $this->load("partials/icon.twig", 122)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
            yield " ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Documents"));
            yield "</a></li>";
        }
        // line 123
        yield "                    ";
        if ((($tmp = $this->env->getFunction('can')->getCallable()("storage", "view")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 124
            yield "                    <li>
                      <details ";
            // line 125
            yield ((($this->env->getFunction('active_class')->getCallable()("/storage") || $this->env->getFunction('active_class')->getCallable()("/storage/history"))) ? ("open") : (""));
            yield ">
                        <summary>
                          <a href=\"";
            // line 127
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/storage"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/storage")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/storage")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                            ";
            // line 128
            yield from $this->load("partials/icon.twig", 128)->unwrap()->yield(CoreExtension::merge($context, ["name" => "archive", "classes" => "w-5 h-5 mr-2"]));
            // line 129
            yield "                            ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Storage"));
            yield "
                          </a>
                        </summary>
                        <ul>
                          <li>
                            <a href=\"";
            // line 134
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/storage/history"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/storage/history")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/storage/history")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 135
            yield from $this->load("partials/icon.twig", 135)->unwrap()->yield(CoreExtension::merge($context, ["name" => "archive", "classes" => "w-5 h-5 mr-2"]));
            // line 136
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Storage history"));
            yield "
                            </a>
                          </li>
                        </ul>
                      </details>
                    </li>
                    ";
        }
        // line 143
        yield "                    <li>
                      <details ";
        // line 144
        yield ((($this->env->getFunction('active_class')->getCallable()("/import") || $this->env->getFunction('active_class')->getCallable()("/export"))) ? ("open") : (""));
        yield ">
                        <summary>
                          <span class=\"inline-flex items-center\">
                            ";
        // line 147
        yield from $this->load("partials/icon.twig", 147)->unwrap()->yield(CoreExtension::merge($context, ["name" => "archive", "classes" => "w-5 h-5 mr-2"]));
        // line 148
        yield "                            ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Data I/O"));
        yield "
                          </span>
                        </summary>
                        <ul>
                          <li>
                            <a href=\"";
        // line 153
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/import"), "html", null, true);
        yield "\" class=\"";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/import")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
        yield "\" ";
        yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/import")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
        yield ">
                              ";
        // line 154
        yield from $this->load("partials/icon.twig", 154)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 155
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Import"));
        yield "
                            </a>
                          </li>
                          <li class=\"px-4 py-1 text-xs text-base-content/60\">";
        // line 158
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Export"));
        yield "</li>
                          <li>
                            <a href=\"";
        // line 160
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/export/contacts.csv"), "html", null, true);
        yield "\">
                              ";
        // line 161
        yield from $this->load("partials/icon.twig", 161)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 162
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contacts CSV"));
        yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
        // line 166
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/export/tasks.csv"), "html", null, true);
        yield "\">
                              ";
        // line 167
        yield from $this->load("partials/icon.twig", 167)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 168
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Tasks CSV"));
        yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
        // line 172
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/export/times.csv"), "html", null, true);
        yield "\">
                              ";
        // line 173
        yield from $this->load("partials/icon.twig", 173)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 174
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Times CSV"));
        yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
        // line 178
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/export/employees.csv"), "html", null, true);
        yield "\">
                              ";
        // line 179
        yield from $this->load("partials/icon.twig", 179)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 180
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Employees CSV"));
        yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
        // line 184
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/export/candidates.csv"), "html", null, true);
        yield "\">
                              ";
        // line 185
        yield from $this->load("partials/icon.twig", 185)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 186
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Candidates CSV"));
        yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
        // line 190
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/export/payments.csv"), "html", null, true);
        yield "\">
                              ";
        // line 191
        yield from $this->load("partials/icon.twig", 191)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 192
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Payments CSV"));
        yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
        // line 196
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/export/storage.csv"), "html", null, true);
        yield "\">
                              ";
        // line 197
        yield from $this->load("partials/icon.twig", 197)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
        // line 198
        yield "                              ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Storage CSV"));
        yield "
                            </a>
                          </li>
                        </ul>
                      </details>
                    </li>

                    ";
        // line 205
        if ((($tmp = $this->env->getFunction('is_admin')->getCallable()()) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 206
            yield "                    <li>
                      <details ";
            // line 207
            yield ((($this->env->getFunction('active_class')->getCallable()("/admin") || $this->env->getFunction('active_class')->getCallable()("/audit"))) ? ("open") : (""));
            yield ">
                        <summary>
                          <span class=\"inline-flex items-center\">
                            ";
            // line 210
            yield from $this->load("partials/icon.twig", 210)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
            // line 211
            yield "                            ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Administration"));
            yield "
                          </span>
                        </summary>
                        <ul>
                          <li>
                            <a href=\"";
            // line 216
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/admin/health"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/health")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/health")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 217
            yield from $this->load("partials/icon.twig", 217)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
            // line 218
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Health"));
            yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
            // line 222
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/admin/logs"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/logs")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/logs")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 223
            yield from $this->load("partials/icon.twig", 223)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
            // line 224
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Logs"));
            yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
            // line 228
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/admin/backups"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/backups")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/backups")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 229
            yield from $this->load("partials/icon.twig", 229)->unwrap()->yield(CoreExtension::merge($context, ["name" => "archive", "classes" => "w-5 h-5 mr-2"]));
            // line 230
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Backups"));
            yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
            // line 234
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/admin/users"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/users")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/users")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 235
            yield from $this->load("partials/icon.twig", 235)->unwrap()->yield(CoreExtension::merge($context, ["name" => "users", "classes" => "w-5 h-5 mr-2"]));
            // line 236
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Users"));
            yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
            // line 240
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/admin/settings"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/settings")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/admin/settings")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 241
            yield from $this->load("partials/icon.twig", 241)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
            // line 242
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Settings"));
            yield "
                            </a>
                          </li>
                          <li>
                            <a href=\"";
            // line 246
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/audit"), "html", null, true);
            yield "\" class=\"";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/audit")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("active") : (""));
            yield "\" ";
            yield (((($tmp = $this->env->getFunction('active_class')->getCallable()("/audit")) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("aria-current=\"page\"") : (""));
            yield ">
                              ";
            // line 247
            yield from $this->load("partials/icon.twig", 247)->unwrap()->yield(CoreExtension::merge($context, ["name" => "file", "classes" => "w-5 h-5 mr-2"]));
            // line 248
            yield "                              ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Audit log"));
            yield "
                            </a>
                          </li>
                        </ul>
                      </details>
                    </li>
                    ";
        }
        // line 255
        yield "                    <li class=\"mt-2 px-4 pb-3\">
                        <label class=\"label\" for=\"lang-select\"><span class=\"label-text\">";
        // line 256
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Language"));
        yield "</span></label>
                        <select id=\"lang-select\" class=\"select select-bordered w-full max-w-xs\" aria-label=\"";
        // line 257
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Language"));
        yield "\">
                            ";
        // line 258
        $context["cl"] = ((array_key_exists("currentLang", $context)) ? (Twig\Extension\CoreExtension::default(($context["currentLang"] ?? null), "en")) : ("en"));
        // line 259
        yield "                            <option value=\"en\" ";
        yield (((($context["cl"] ?? null) == "en")) ? ("selected") : (""));
        yield ">EN</option>
                            <option value=\"de\" ";
        // line 260
        yield (((($context["cl"] ?? null) == "de")) ? ("selected") : (""));
        yield ">DE</option>
                            <option value=\"pt\" ";
        // line 261
        yield (((($context["cl"] ?? null) == "pt")) ? ("selected") : (""));
        yield ">PT</option>
                        </select>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class=\"flex-1\">
            ";
        // line 268
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 269
        yield "        </main>
    </div>
</div>
";
        // line 272
        yield from $this->load("partials/footer.twig", 272)->unwrap()->yield($context);
        // line 273
        yield "
<!-- Install banner -->
<div id=\"install-banner\" class=\"fixed inset-x-0 bottom-0 z-40 hidden\">
  <div class=\"mx-auto max-w-3xl px-4 pb-4\">
    <div class=\"alert shadow-lg bg-base-200\">
      <span>";
        // line 278
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Install People & Pixel for a faster, app-like experience."));
        yield "</span>
      <div>
        <button id=\"install-dismiss\" class=\"btn btn-ghost btn-sm mr-2\" type=\"button\" aria-label=\"";
        // line 280
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Dismiss"));
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Not now"));
        yield "</button>
        <button id=\"install-accept\" class=\"btn btn-primary btn-sm\" type=\"button\">";
        // line 281
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Install"));
        yield "</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // Language selector change handler
  const sel = document.getElementById('lang-select');
  if (sel){
    sel.addEventListener('change', function(){
      const value = this.value;
      try {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', value);
        window.location.href = url.toString();
      } catch (e) {
        const loc = window.location;
        const query = loc.search ? loc.search.substring(1) : '';
        const params = new URLSearchParams(query);
        params.set('lang', value);
        const q = params.toString();
        window.location.href = loc.pathname + (q ? ('?' + q) : '');
      }
    });
  }

  // Register Service Worker for offline-first
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function(){
      try { navigator.serviceWorker.register('";
        // line 312
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/sw.js"), "html", null, true);
        yield "'); } catch(e) { /* ignore */ }
    });
  }

  // A2HS Install prompt UX
  (function(){
    var deferred; // beforeinstallprompt event
    var banner = document.getElementById('install-banner');
    var acceptBtn = document.getElementById('install-accept');
    var dismissBtn = document.getElementById('install-dismiss');
    var storageKey = 'pp.install.dismissedAt';

    function isStandalone(){
      return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
    }
    function show(){ if (banner) banner.classList.remove('hidden'); }
    function hide(){ if (banner) banner.classList.add('hidden'); }
    function recentlyDismissed(){
      try { var t = parseInt(localStorage.getItem(storageKey)||'0',10); return Date.now() - t < 7*24*3600*1000; } catch(e){ return false; }
    }

    window.addEventListener('beforeinstallprompt', function(e){
      // Chrome/Edge will fire this when installable
      e.preventDefault();
      deferred = e;
      if (!isStandalone() && !recentlyDismissed()) { show(); }
    });
    window.addEventListener('appinstalled', function(){ hide(); deferred = null; try{ localStorage.removeItem(storageKey); }catch(e){} });

    if (acceptBtn) acceptBtn.addEventListener('click', function(){
      if (!deferred) { hide(); return; }
      deferred.prompt();
      deferred.userChoice.then(function(){ hide(); deferred = null; });
    });
    if (dismissBtn) dismissBtn.addEventListener('click', function(){ hide(); try{ localStorage.setItem(storageKey, String(Date.now())); }catch(e){} });

    // iOS Safari doesn’t support beforeinstallprompt; show guidance if eligible
    var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
    if (isIOS && !isStandalone() && !recentlyDismissed()) {
      // Delay slightly to avoid flashing on every page load
      setTimeout(function(){ show(); }, 1200);
      if (acceptBtn) acceptBtn.textContent = '";
        // line 353
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("How to install"));
        yield "';
      if (acceptBtn) acceptBtn.addEventListener('click', function(){
        alert('To install: tap the Share button in Safari, then \"Add to Home Screen\".');
        hide();
      });
    }
  })();

  // THEME: apply saved or preferred theme on load using DaisyUI data-theme
  const storageKey = 'pp.theme';
  const btn = document.getElementById('theme-toggle');
  function getStoredTheme(){
    try { return localStorage.getItem(storageKey); } catch(e){ return null; }
  }
  function storeTheme(t){ try { localStorage.setItem(storageKey, t); } catch(e){} }
  function systemPrefersDark(){
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  function applyTheme(theme){
    const t = theme || 'light';
    document.documentElement.setAttribute('data-theme', t);
    document.body.setAttribute('data-theme', t);
    if (btn){
      btn.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false');
      const icon = btn.querySelector('[data-icon]');
      if (icon){ icon.textContent = (t === 'dark') ? '🌙' : '☀️'; }
    }
  }
  let initial = getStoredTheme();
  if (!initial) { initial = systemPrefersDark() ? 'dark' : 'light'; }
  applyTheme(initial);
  if (btn){
    btn.addEventListener('click', function(ev){
      ev.preventDefault();
      const cur = document.documentElement.getAttribute('data-theme') || 'light';
      const next = (cur === 'dark') ? 'light' : 'dark';
      applyTheme(next);
      storeTheme(next);
      try {
        const live = document.getElementById('sr-live');
        if (live) live.textContent = (next === 'dark') ? 'Dark theme enabled' : 'Light theme enabled';
      } catch(e){}
    });
  }
  if (!getStoredTheme() && window.matchMedia) {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    if (mq.addEventListener) mq.addEventListener('change', function(e){ applyTheme(e.matches ? 'dark' : 'light'); });
    else if (mq.addListener) mq.addListener(function(e){ applyTheme(e.matches ? 'dark' : 'light'); });
  }
})();
  
  // RUNNING TIMER indicator
  (function(){
    const header = document.getElementById('app-header');
    const badge = document.getElementById('running-timer');
    const timeEl = badge ? badge.querySelector('[data-time]') : null;
    let timer = null;
    let fetchIv = null;
    let startMs = null; // epoch ms
    let lastDocTitle = document.title;

    function fmt(n){ return n < 10 ? '0' + n : '' + n; }
    function render(){
      if (!badge || startMs == null) return;
      const now = Date.now();
      let diff = Math.max(0, Math.floor((now - startMs) / 1000));
      const h = Math.floor(diff / 3600); diff -= h*3600;
      const m = Math.floor(diff / 60); const s = diff - m*60;
      const text = fmt(h) + ':' + fmt(m) + ':' + fmt(s);
      if (timeEl) timeEl.textContent = text;
      document.title = '⏱ ' + text + ' — ' + (lastDocTitle || '');
    }
    function setActive(on){
      if (!header || !badge) return;
      if (on){
        badge.classList.remove('hidden');
        // subtle animation on the navbar
        header.classList.add('animate-pulse');
      } else {
        badge.classList.add('hidden');
        header.classList.remove('animate-pulse');
        if (timer){ clearInterval(timer); timer = null; }
        document.title = lastDocTitle;
      }
    }
    async function check(){
      try {
        const res = await fetch('";
        // line 440
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/times/running"), "html", null, true);
        yield "', { headers: { 'Accept': 'application/json' } });
        const json = await res.json().catch(() => null);
        if (!json || !json.ok){ throw new Error('bad'); }
        const r = json.running;
        if (!r){
          startMs = null;
          setActive(false);
          return;
        }
        // Prefer iso_start if provided
        let iso = r.iso_start;
        if (!iso && r.date && r.start_time){
          const st = r.start_time;
          iso = r.date + 'T' + (st && st.length === 5 ? (st + ':00') : st);
        }
        const start = Date.parse(iso);
        if (isNaN(start)){
          startMs = null; setActive(false); return;
        }
        if (startMs == null || Math.abs(startMs - start) > 1000){
          startMs = start; lastDocTitle = lastDocTitle || document.title; setActive(true);
          const sr = document.getElementById('sr-live'); if (sr) { sr.textContent = 'Timer started'; }
          // Try to show a local notification when timer starts
          if (window.ppNotify) {
            window.ppNotify('Timer started', { body: 'Your working timer is now running.', icon: '/website/img/placeholder-16x9.svg', badge: '/website/img/placeholder-16x9.svg' });
          }
        }
        if (!timer){ timer = setInterval(render, 1000); }
        render();
      } catch(e){ /* ignore transient errors */ }
    }
    // Initial and periodic checks
    check();
    fetchIv = setInterval(check, 15000);
    // If page becomes visible again, refresh immediately
    document.addEventListener('visibilitychange', function(){ if (!document.hidden) check(); });
  })();
</script>
<script>
// Register service worker for PWA
(function(){
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function(){
      navigator.serviceWorker.register('";
        // line 483
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/sw.js"), "html", null, true);
        yield "').catch(function(){ /* ignore */ });
    });
  }
  // Notifications helper used by timer code
  window.ppNotify = function(title, options){
    try{
      if (!('Notification' in window)) return;
      if (Notification.permission === 'default') {
        Notification.requestPermission().then(function(p){ if (p !== 'granted') return; window.ppNotify(title, options); });
        return;
      }
      if (Notification.permission !== 'granted') return;
      if (navigator.serviceWorker && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'notify', title: title, options: options || {} });
      } else if (navigator.serviceWorker) {
        navigator.serviceWorker.getRegistration().then(function(reg){ if (reg && reg.showNotification) reg.showNotification(title || 'Notification', options || {}); });
      }
    } catch(e) { /* ignore */ }
  };
})();
</script>
<script>
// Accessibility: focus search on '/'
(function(){
  document.addEventListener('keydown', function(e){
    if (e.key === '/' && !e.ctrlKey && !e.metaKey && !e.altKey) {
      var el = document.querySelector('input[name=\"q\"]');
      if (el) { e.preventDefault(); el.focus(); try{ el.select(); }catch(_){} }
    }
  });
})();
</script>
</body>
</html>
";
        yield from [];
    }

    // line 268
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
        return array (  1044 => 268,  1004 => 483,  958 => 440,  868 => 353,  824 => 312,  790 => 281,  784 => 280,  779 => 278,  772 => 273,  770 => 272,  765 => 269,  763 => 268,  753 => 261,  749 => 260,  744 => 259,  742 => 258,  738 => 257,  734 => 256,  731 => 255,  720 => 248,  718 => 247,  710 => 246,  702 => 242,  700 => 241,  692 => 240,  684 => 236,  682 => 235,  674 => 234,  666 => 230,  664 => 229,  656 => 228,  648 => 224,  646 => 223,  638 => 222,  630 => 218,  628 => 217,  620 => 216,  611 => 211,  609 => 210,  603 => 207,  600 => 206,  598 => 205,  587 => 198,  585 => 197,  581 => 196,  573 => 192,  571 => 191,  567 => 190,  559 => 186,  557 => 185,  553 => 184,  545 => 180,  543 => 179,  539 => 178,  531 => 174,  529 => 173,  525 => 172,  517 => 168,  515 => 167,  511 => 166,  503 => 162,  501 => 161,  497 => 160,  492 => 158,  485 => 155,  483 => 154,  475 => 153,  466 => 148,  464 => 147,  458 => 144,  455 => 143,  444 => 136,  442 => 135,  434 => 134,  425 => 129,  423 => 128,  415 => 127,  410 => 125,  407 => 124,  404 => 123,  389 => 122,  374 => 121,  359 => 120,  344 => 119,  330 => 118,  326 => 117,  323 => 116,  308 => 115,  293 => 114,  278 => 113,  263 => 112,  248 => 111,  233 => 110,  222 => 103,  220 => 102,  212 => 101,  204 => 97,  202 => 96,  194 => 95,  185 => 90,  183 => 89,  177 => 86,  174 => 85,  172 => 84,  160 => 83,  155 => 81,  151 => 79,  149 => 78,  146 => 77,  144 => 76,  138 => 73,  114 => 52,  75 => 16,  71 => 15,  64 => 11,  59 => 9,  53 => 6,  46 => 2,  43 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "layout.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/layout.twig");
    }
}
