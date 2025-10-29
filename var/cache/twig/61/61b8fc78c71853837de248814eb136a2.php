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

/* partials/dynamic_list.twig */
class __TwigTemplate_abfe0a9908effaac994530d115912515 extends Template
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
        // line 8
        yield "<section class=\"space-y-3\">
    ";
        // line 9
        $context["listTitle"] = ((array_key_exists("title", $context)) ? (Twig\Extension\CoreExtension::default(($context["title"] ?? null), "")) : (""));
        // line 10
        yield "    ";
        if ((($tmp = ($context["listTitle"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<h2 class=\"text-2xl font-semibold\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["listTitle"] ?? null));
            yield "</h2>";
        }
        // line 11
        yield "
    <div class=\"overflow-x-auto\">
      ";
        // line 13
        $context["bulkAllowed"] = ((array_key_exists("bulk", $context) && CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "post_url", [], "any", false, false, false, 13)) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "post_url", [], "any", false, false, false, 13), "POST"));
        // line 14
        yield "      ";
        if ((($tmp = ($context["bulkAllowed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 15
            yield "      <form method=\"post\" action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "post_url", [], "any", false, false, false, 15), "html", null, true);
            yield "\" id=\"bulkForm\">
        ";
            // line 16
            yield $this->env->getFunction('csrf_field')->getCallable()();
            yield "
      ";
        }
        // line 18
        yield "      <table class=\"table table-zebra w-full\" aria-label=\"";
        yield (((($tmp = ($context["listTitle"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["listTitle"] ?? null))) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("List"))));
        yield "\">
        ";
        // line 19
        if ((($tmp = ($context["listTitle"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield "<caption class=\"sr-only\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["listTitle"] ?? null));
            yield "</caption>";
        }
        // line 20
        yield "        <thead>
        <tr>
            ";
        // line 22
        if ((array_key_exists("bulk", $context) && CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "post_url", [], "any", false, false, false, 22))) {
            // line 23
            yield "                <th scope=\"col\">
                    <input type=\"checkbox\" id=\"bulkCheckAll\" class=\"checkbox\" onclick=\"(function(cb){var boxes=document.querySelectorAll('#bulkForm input[name=\\'ids[]\\']'); boxes.forEach(function(b){b.checked=cb.checked;});})(this)\">
                </th>
            ";
        }
        // line 27
        yield "            ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["columns"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["col"]) {
            // line 28
            yield "                ";
            $context["colName"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["col"], "name", [], "any", true, true, false, 28)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["col"], "name", [], "any", false, false, false, 28), "")) : (""));
            // line 29
            yield "                ";
            $context["isSorted"] = (((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), "")) : ("")) == ($context["colName"] ?? null));
            // line 30
            yield "                ";
            $context["aria"] = (((($tmp = ($context["isSorted"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ((((((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc")) == "asc")) ? ("ascending") : ("descending"))) : ("none"));
            // line 31
            yield "                <th scope=\"col\" aria-sort=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["aria"] ?? null), "html", null, true);
            yield "\">
                    ";
            // line 32
            yield $this->env->getFunction('sort_link')->getCallable()(((CoreExtension::getAttribute($this->env, $this->source, $context["col"], "label", [], "any", true, true, false, 32)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["col"], "label", [], "any", false, false, false, 32), ($context["colName"] ?? null))) : (($context["colName"] ?? null))), ($context["colName"] ?? null), ((array_key_exists("sort", $context)) ? (Twig\Extension\CoreExtension::default(($context["sort"] ?? null), null)) : (null)), ((array_key_exists("dir", $context)) ? (Twig\Extension\CoreExtension::default(($context["dir"] ?? null), "asc")) : ("asc")), ((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())), ["q" => ((array_key_exists("q", $context)) ? (Twig\Extension\CoreExtension::default(($context["q"] ?? null), "")) : ("")), "tag" => ((array_key_exists("tag", $context)) ? (Twig\Extension\CoreExtension::default(($context["tag"] ?? null), "")) : ("")), "per" => ((array_key_exists("per", $context)) ? (Twig\Extension\CoreExtension::default(($context["per"] ?? null), 10)) : (10))]);
            yield "
                </th>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['col'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 35
        yield "            ";
        if (array_key_exists("actions", $context)) {
            // line 36
            yield "                ";
            $context["actionsVisible"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "edit_url", [], "any", false, false, false, 36) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "edit_url", [], "any", false, false, false, 36))) || (CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "delete_url", [], "any", false, false, false, 36) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "delete_url", [], "any", false, false, false, 36), "POST")));
            // line 37
            yield "                ";
            if ((($tmp = ($context["actionsVisible"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 38
                yield "                <th scope=\"col\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Actions"));
                yield "</th>
                ";
            }
            // line 40
            yield "            ";
        }
        // line 41
        yield "        </tr>
        </thead>
        <tbody>
        ";
        // line 44
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["items"] ?? null)) == 0)) {
            // line 45
            yield "            <tr>
                ";
            // line 46
            $context["actionsVisible"] = (array_key_exists("actions", $context) && ((CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "edit_url", [], "any", false, false, false, 46) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "edit_url", [], "any", false, false, false, 46))) || (CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "delete_url", [], "any", false, false, false, 46) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "delete_url", [], "any", false, false, false, 46), "POST"))));
            // line 47
            yield "                <td class=\"text-center opacity-70\" colspan=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["columns"] ?? null)) + (((($tmp = ($context["actionsVisible"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (1) : (0))) + (((($tmp = ($context["bulkAllowed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (1) : (0))), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("No records found"));
            yield "</td>
            </tr>
        ";
        } else {
            // line 50
            yield "            ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["items"] ?? null));
            $context['loop'] = [
              'parent' => $context['_parent'],
              'index0' => 0,
              'index'  => 1,
              'first'  => true,
            ];
            if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                $length = count($context['_seq']);
                $context['loop']['revindex0'] = $length - 1;
                $context['loop']['revindex'] = $length;
                $context['loop']['length'] = $length;
                $context['loop']['last'] = 1 === $length;
            }
            foreach ($context['_seq'] as $context["_key"] => $context["it"]) {
                // line 51
                yield "                <tr>
                    ";
                // line 52
                if (((array_key_exists("bulk", $context) && CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "post_url", [], "any", false, false, false, 52)) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "post_url", [], "any", false, false, false, 52), "POST"))) {
                    // line 53
                    yield "                        ";
                    $context["idf"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "id_field", [], "any", true, true, false, 53)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "id_field", [], "any", false, false, false, 53), "id")) : ("id"));
                    // line 54
                    yield "                        ";
                    $context["idv"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], ($context["idf"] ?? null), [], "any", true, true, false, 54)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], ($context["idf"] ?? null), [], "any", false, false, false, 54), "")) : (""));
                    // line 55
                    yield "                        <td><input type=\"checkbox\" class=\"checkbox\" name=\"ids[]\" value=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["idv"] ?? null));
                    yield "\" aria-label=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Select row"));
                    yield "\"></td>
                    ";
                }
                // line 57
                yield "                    ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["columns"] ?? null));
                $context['loop'] = [
                  'parent' => $context['_parent'],
                  'index0' => 0,
                  'index'  => 1,
                  'first'  => true,
                ];
                if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                    $length = count($context['_seq']);
                    $context['loop']['revindex0'] = $length - 1;
                    $context['loop']['revindex'] = $length;
                    $context['loop']['length'] = $length;
                    $context['loop']['last'] = 1 === $length;
                }
                foreach ($context['_seq'] as $context["_key"] => $context["col"]) {
                    // line 58
                    yield "                        ";
                    $context["value"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], CoreExtension::getAttribute($this->env, $this->source, $context["col"], "name", [], "any", false, false, false, 58), [], "any", true, true, false, 58)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], CoreExtension::getAttribute($this->env, $this->source, $context["col"], "name", [], "any", false, false, false, 58), [], "any", false, false, false, 58), "")) : (""));
                    // line 59
                    yield "                        ";
                    if (((CoreExtension::getAttribute($this->env, $this->source, $context["loop"], "first", [], "any", false, false, false, 59) && array_key_exists("actions", $context)) && CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "view_url", [], "any", false, false, false, 59))) {
                        // line 60
                        yield "                            ";
                        $context["idf"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "id_field", [], "any", true, true, false, 60)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "id_field", [], "any", false, false, false, 60), "id")) : ("id"));
                        // line 61
                        yield "                            ";
                        $context["idv"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], ($context["idf"] ?? null), [], "any", true, true, false, 61)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], ($context["idf"] ?? null), [], "any", false, false, false, 61), "")) : (""));
                        // line 62
                        yield "                            ";
                        $context["viewHref"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "view_url", [], "any", false, false, false, 62) . "?id=") . ($context["idv"] ?? null));
                        // line 63
                        yield "                            <td>
                                ";
                        // line 64
                        if ((CoreExtension::inFilter(CoreExtension::getAttribute($this->env, $this->source, $context["col"], "name", [], "any", false, false, false, 64), ["picture", "image", "avatar", "photo"]) && ($context["value"] ?? null))) {
                            // line 65
                            yield "                                    ";
                            if ((($tmp = $this->env->getFunction('can_url')->getCallable()(($context["viewHref"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                // line 66
                                yield "                                    <a href=\"";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["viewHref"] ?? null), "html", null, true);
                                yield "\" aria-label=\"";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("View details"));
                                yield "\">
                                        <img src=\"";
                                // line 67
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                                yield "\" alt=\"\" class=\"w-9 h-9 rounded-full object-cover border border-base-300\">
                                    </a>
                                    ";
                            } else {
                                // line 70
                                yield "                                    <img src=\"";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                                yield "\" alt=\"\" class=\"w-9 h-9 rounded-full object-cover border border-base-300\">
                                    ";
                            }
                            // line 72
                            yield "                                ";
                        } else {
                            // line 73
                            yield "                                    ";
                            if ((($tmp = $this->env->getFunction('can_url')->getCallable()(($context["viewHref"] ?? null))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                // line 74
                                yield "                                    <a href=\"";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["viewHref"] ?? null), "html", null, true);
                                yield "\" class=\"link link-primary\">";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                                yield "</a>
                                    ";
                            } else {
                                // line 76
                                yield "                                    ";
                                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                                yield "
                                    ";
                            }
                            // line 78
                            yield "                                ";
                        }
                        // line 79
                        yield "                            </td>
                        ";
                    } else {
                        // line 81
                        yield "                            <td>
                                ";
                        // line 82
                        if ((CoreExtension::inFilter(CoreExtension::getAttribute($this->env, $this->source, $context["col"], "name", [], "any", false, false, false, 82), ["picture", "image", "avatar", "photo"]) && ($context["value"] ?? null))) {
                            // line 83
                            yield "                                    <img src=\"";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                            yield "\" alt=\"\" class=\"w-9 h-9 rounded-full object-cover border border-base-300\">
                                ";
                        } else {
                            // line 85
                            yield "                                    ";
                            if (is_iterable(($context["value"] ?? null))) {
                                // line 86
                                yield "                                        <div class=\"flex flex-wrap gap-1\">
                                            ";
                                // line 87
                                $context['_parent'] = $context;
                                $context['_seq'] = CoreExtension::ensureTraversable(($context["value"] ?? null));
                                foreach ($context['_seq'] as $context["_key"] => $context["t"]) {
                                    // line 88
                                    yield "                                                ";
                                    if (is_iterable($context["t"])) {
                                        // line 89
                                        yield "                                                    ";
                                        $context['_parent'] = $context;
                                        $context['_seq'] = CoreExtension::ensureTraversable($context["t"]);
                                        foreach ($context['_seq'] as $context["k"] => $context["v"]) {
                                            // line 90
                                            yield "                                                        <span class=\"badge badge-ghost\">";
                                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["k"]);
                                            yield ": ";
                                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["v"]);
                                            yield "</span>
                                                    ";
                                        }
                                        $_parent = $context['_parent'];
                                        unset($context['_seq'], $context['k'], $context['v'], $context['_parent']);
                                        $context = array_intersect_key($context, $_parent) + $_parent;
                                        // line 92
                                        yield "                                                ";
                                    } else {
                                        // line 93
                                        yield "                                                    ";
                                        if ((CoreExtension::getAttribute($this->env, $this->source, $context["col"], "name", [], "any", false, false, false, 93) == "tags")) {
                                            // line 94
                                            yield "                                                        <a class=\"badge badge-ghost\" href=\"";
                                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("path", $context)) ? (Twig\Extension\CoreExtension::default(($context["path"] ?? null), $this->env->getFunction('current_path')->getCallable()())) : ($this->env->getFunction('current_path')->getCallable()())));
                                            yield "?tag=";
                                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(Twig\Extension\CoreExtension::urlencode($context["t"]), "html", null, true);
                                            yield "\">";
                                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["t"]);
                                            yield "</a>
                                                    ";
                                        } else {
                                            // line 96
                                            yield "                                                        <span class=\"badge badge-ghost\">";
                                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["t"]);
                                            yield "</span>
                                                    ";
                                        }
                                        // line 98
                                        yield "                                                ";
                                    }
                                    // line 99
                                    yield "                                            ";
                                }
                                $_parent = $context['_parent'];
                                unset($context['_seq'], $context['_key'], $context['t'], $context['_parent']);
                                $context = array_intersect_key($context, $_parent) + $_parent;
                                // line 100
                                yield "                                        </div>
                                    ";
                            } else {
                                // line 102
                                yield "                                        ";
                                // line 103
                                yield "                                        ";
                                if (((is_string($_v0 = ($context["value"] ?? null)) && is_string($_v1 = "http://") && str_starts_with($_v0, $_v1)) || (is_string($_v2 = ($context["value"] ?? null)) && is_string($_v3 = "https://") && str_starts_with($_v2, $_v3)))) {
                                    // line 104
                                    yield "                                            <a href=\"";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                                    yield "\" target=\"_blank\" rel=\"noopener\" class=\"link\">";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                                    yield "</a>
                                        ";
                                } else {
                                    // line 106
                                    yield "                                            ";
                                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["value"] ?? null));
                                    yield "
                                        ";
                                }
                                // line 108
                                yield "                                    ";
                            }
                            // line 109
                            yield "                                ";
                        }
                        // line 110
                        yield "                            </td>
                        ";
                    }
                    // line 112
                    yield "                    ";
                    ++$context['loop']['index0'];
                    ++$context['loop']['index'];
                    $context['loop']['first'] = false;
                    if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                        --$context['loop']['revindex0'];
                        --$context['loop']['revindex'];
                        $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['col'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 113
                yield "                    ";
                if (array_key_exists("actions", $context)) {
                    // line 114
                    yield "                        ";
                    $context["idf"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "id_field", [], "any", true, true, false, 114)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "id_field", [], "any", false, false, false, 114), "id")) : ("id"));
                    // line 115
                    yield "                        ";
                    $context["idv"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["it"], ($context["idf"] ?? null), [], "any", true, true, false, 115)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["it"], ($context["idf"] ?? null), [], "any", false, false, false, 115), "")) : (""));
                    // line 116
                    yield "                        ";
                    $context["showEdit"] = (CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "edit_url", [], "any", false, false, false, 116) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "edit_url", [], "any", false, false, false, 116)));
                    // line 117
                    yield "                        ";
                    $context["showDelete"] = (CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "delete_url", [], "any", false, false, false, 117) && $this->env->getFunction('can_url')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "delete_url", [], "any", false, false, false, 117), "POST"));
                    // line 118
                    yield "                        ";
                    if ((($context["showEdit"] ?? null) || ($context["showDelete"] ?? null))) {
                        // line 119
                        yield "                        <td class=\"whitespace-nowrap space-x-2\">
                            ";
                        // line 120
                        if ((($tmp = ($context["showEdit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                            // line 121
                            yield "                                <a class=\"btn btn-xs\" href=\"";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "edit_url", [], "any", false, false, false, 121), "html", null, true);
                            yield "?id=";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["idv"] ?? null));
                            yield "\" aria-label=\"";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Edit"));
                            yield "\">";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Edit"));
                            yield "</a>
                            ";
                        }
                        // line 123
                        yield "                            ";
                        if ((($tmp = ($context["showDelete"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                            // line 124
                            yield "                                <form method=\"post\" action=\"";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["actions"] ?? null), "delete_url", [], "any", false, false, false, 124), "html", null, true);
                            yield "\" class=\"inline\" onsubmit=\"return confirm('";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Are you sure?"));
                            yield "');\">
                                    ";
                            // line 125
                            yield $this->env->getFunction('csrf_field')->getCallable()();
                            yield "
                                    <input type=\"hidden\" name=\"id\" value=\"";
                            // line 126
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["idv"] ?? null));
                            yield "\">
                                    <button class=\"btn btn-xs btn-error\" type=\"submit\" aria-label=\"";
                            // line 127
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Delete"));
                            yield "\">";
                            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Delete"));
                            yield "</button>
                                </form>
                            ";
                        }
                        // line 130
                        yield "                        </td>
                        ";
                    }
                    // line 132
                    yield "                    ";
                }
                // line 133
                yield "                </tr>
            ";
                ++$context['loop']['index0'];
                ++$context['loop']['index'];
                $context['loop']['first'] = false;
                if (isset($context['loop']['revindex0'], $context['loop']['revindex'])) {
                    --$context['loop']['revindex0'];
                    --$context['loop']['revindex'];
                    $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['it'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 135
            yield "        ";
        }
        // line 136
        yield "        </tbody>
      </table>
      ";
        // line 138
        if ((($tmp = ($context["bulkAllowed"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 139
            yield "      <div class=\"mt-3 flex flex-wrap items-center gap-2\">
        <label for=\"bulkAction\" class=\"sr-only\">";
            // line 140
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Bulk action"));
            yield "</label>
        <select class=\"select select-bordered\" id=\"bulkAction\" name=\"action\" onchange=\"(function(sel){var v=sel.value;var ph='';try{var map=JSON.parse(sel.dataset.placeholders||'{}');ph=map[v]||'';}catch(e){} var inp=document.getElementById('bulkValue'); if(inp){inp.placeholder=ph; inp.classList.toggle('hidden', ph==='');} })(this)\" data-placeholders='";
            // line 141
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(json_encode(((CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "placeholders", [], "any", true, true, false, 141)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "placeholders", [], "any", false, false, false, 141), [])) : ([]))));
            yield "'>
          <option value=\"\">-- ";
            // line 142
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Select action"));
            yield " --</option>
          ";
            // line 143
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["bulk"] ?? null), "actions", [], "any", false, false, false, 143));
            foreach ($context['_seq'] as $context["_key"] => $context["act"]) {
                // line 144
                yield "            <option value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["act"], "value", [], "any", false, false, false, 144));
                yield "\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["act"], "label", [], "any", false, false, false, 144));
                yield "</option>
          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['act'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 146
            yield "        </select>
        <input id=\"bulkValue\" name=\"value\" type=\"text\" class=\"input input-bordered hidden\" placeholder=\"\">
        <button class=\"btn\" type=\"submit\" onclick=\"return (function(){var any=false; document.querySelectorAll('#bulkForm input[name=\\'ids[]\\']').forEach(function(b){ if(b.checked){ any=true; }}); if(!any){ alert('";
            // line 148
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Please select at least one item."));
            yield "'); return false;} if(!document.getElementById('bulkAction').value){ alert('";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Please select an action."));
            yield "'); return false;} return confirm('";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Are you sure?"));
            yield "'); })();\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Apply"));
            yield "</button>
      </div>
      </form>
      ";
        }
        // line 152
        yield "    </div>
</section>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/dynamic_list.twig";
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
        return array (  544 => 152,  531 => 148,  527 => 146,  516 => 144,  512 => 143,  508 => 142,  504 => 141,  500 => 140,  497 => 139,  495 => 138,  491 => 136,  488 => 135,  473 => 133,  470 => 132,  466 => 130,  458 => 127,  454 => 126,  450 => 125,  443 => 124,  440 => 123,  428 => 121,  426 => 120,  423 => 119,  420 => 118,  417 => 117,  414 => 116,  411 => 115,  408 => 114,  405 => 113,  391 => 112,  387 => 110,  384 => 109,  381 => 108,  375 => 106,  367 => 104,  364 => 103,  362 => 102,  358 => 100,  352 => 99,  349 => 98,  343 => 96,  333 => 94,  330 => 93,  327 => 92,  316 => 90,  311 => 89,  308 => 88,  304 => 87,  301 => 86,  298 => 85,  292 => 83,  290 => 82,  287 => 81,  283 => 79,  280 => 78,  274 => 76,  266 => 74,  263 => 73,  260 => 72,  254 => 70,  248 => 67,  241 => 66,  238 => 65,  236 => 64,  233 => 63,  230 => 62,  227 => 61,  224 => 60,  221 => 59,  218 => 58,  200 => 57,  192 => 55,  189 => 54,  186 => 53,  184 => 52,  181 => 51,  163 => 50,  154 => 47,  152 => 46,  149 => 45,  147 => 44,  142 => 41,  139 => 40,  133 => 38,  130 => 37,  127 => 36,  124 => 35,  115 => 32,  110 => 31,  107 => 30,  104 => 29,  101 => 28,  96 => 27,  90 => 23,  88 => 22,  84 => 20,  78 => 19,  73 => 18,  68 => 16,  63 => 15,  60 => 14,  58 => 13,  54 => 11,  47 => 10,  45 => 9,  42 => 8,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "partials/dynamic_list.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/partials/dynamic_list.twig");
    }
}
