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

/* partials/dynamic_form.twig */
class __TwigTemplate_64d30a27035ad0286b6a6fe233d4cdcb extends Template
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
        // line 9
        yield "<section class=\"space-y-6\">
    <h2 class=\"text-2xl font-semibold\">";
        // line 10
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("title", $context)) ? (Twig\Extension\CoreExtension::default(($context["title"] ?? null), "")) : ("")));
        yield "</h2>
    ";
        // line 12
        yield "    ";
        $context["hasFile"] = false;
        // line 13
        yield "    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["fields"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
            // line 14
            yield "        ";
            if (((CoreExtension::getAttribute($this->env, $this->source, $context["f"], "type", [], "any", true, true, false, 14) && (CoreExtension::getAttribute($this->env, $this->source, $context["f"], "type", [], "any", false, false, false, 14) == "file")) || (CoreExtension::getAttribute($this->env, $this->source, $context["f"], "name", [], "any", true, true, false, 14) && (CoreExtension::getAttribute($this->env, $this->source, $context["f"], "name", [], "any", false, false, false, 14) == "picture")))) {
                // line 15
                yield "            ";
                $context["hasFile"] = true;
                // line 16
                yield "        ";
            }
            // line 17
            yield "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 18
        yield "
    ";
        // line 20
        yield "    ";
        $context["primaryNames"] = ["title", "name", "subject", "company", "project", "deal"];
        // line 21
        yield "    ";
        $context["statusNames"] = ["status", "stage", "phase", "priority"];
        // line 22
        yield "    ";
        $context["idNames"] = ["code", "slug"];
        // line 23
        yield "    ";
        $context["moneyNames"] = ["amount", "budget", "price", "cost", "rate", "value"];
        // line 24
        yield "    ";
        $context["contactNames"] = ["email", "emails", "emails_text", "phone", "phones", "phones_text", "website", "websites", "websites_text", "socials", "socials_text", "address"];
        // line 25
        yield "    ";
        $context["dateHints"] = ["date", "_at", "due", "start", "end", "birth"];
        // line 26
        yield "    ";
        $context["textNames"] = ["description", "notes", "comment", "comments", "summary"];
        // line 27
        yield "
    ";
        // line 28
        $context["prim"] = [];
        // line 29
        yield "    ";
        $context["status"] = [];
        // line 30
        yield "    ";
        $context["ids"] = [];
        // line 31
        yield "    ";
        $context["media"] = [];
        // line 32
        yield "    ";
        $context["money"] = [];
        // line 33
        yield "    ";
        $context["contacts"] = [];
        // line 34
        yield "    ";
        $context["dates"] = [];
        // line 35
        yield "    ";
        $context["texts"] = [];
        // line 36
        yield "    ";
        $context["others"] = [];
        // line 37
        yield "
    ";
        // line 38
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["fields"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
            // line 39
            yield "        ";
            $context["n"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["f"], "name", [], "any", true, true, false, 39)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["f"], "name", [], "any", false, false, false, 39), "")) : (""));
            // line 40
            yield "        ";
            $context["t"] = ((CoreExtension::getAttribute($this->env, $this->source, $context["f"], "type", [], "any", true, true, false, 40)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, $context["f"], "type", [], "any", false, false, false, 40), "text")) : ("text"));
            // line 41
            yield "        ";
            $context["lname"] = Twig\Extension\CoreExtension::lower($this->env->getCharset(), ($context["n"] ?? null));
            // line 42
            yield "        ";
            if (CoreExtension::inFilter(($context["lname"] ?? null), ($context["primaryNames"] ?? null))) {
                // line 43
                yield "            ";
                $context["prim"] = Twig\Extension\CoreExtension::merge(($context["prim"] ?? null), [$context["f"]]);
                // line 44
                yield "        ";
            } elseif (CoreExtension::inFilter(($context["lname"] ?? null), ($context["statusNames"] ?? null))) {
                // line 45
                yield "            ";
                $context["status"] = Twig\Extension\CoreExtension::merge(($context["status"] ?? null), [$context["f"]]);
                // line 46
                yield "        ";
            } elseif ((CoreExtension::inFilter(($context["lname"] ?? null), ($context["idNames"] ?? null)) || (($context["lname"] ?? null) == "id"))) {
                // line 47
                yield "            ";
                $context["ids"] = Twig\Extension\CoreExtension::merge(($context["ids"] ?? null), [$context["f"]]);
                // line 48
                yield "        ";
            } elseif (CoreExtension::inFilter(($context["lname"] ?? null), ($context["contactNames"] ?? null))) {
                // line 49
                yield "            ";
                $context["contacts"] = Twig\Extension\CoreExtension::merge(($context["contacts"] ?? null), [$context["f"]]);
                // line 50
                yield "        ";
            } elseif (((($context["t"] ?? null) == "file") || CoreExtension::inFilter(($context["lname"] ?? null), ["picture", "avatar", "image", "logo"]))) {
                // line 51
                yield "            ";
                $context["media"] = Twig\Extension\CoreExtension::merge(($context["media"] ?? null), [$context["f"]]);
                // line 52
                yield "        ";
            } elseif (CoreExtension::inFilter(($context["lname"] ?? null), ($context["moneyNames"] ?? null))) {
                // line 53
                yield "            ";
                $context["money"] = Twig\Extension\CoreExtension::merge(($context["money"] ?? null), [$context["f"]]);
                // line 54
                yield "        ";
            } elseif (((Twig\Extension\CoreExtension::length($this->env->getCharset(), Twig\Extension\CoreExtension::filter($this->env, ($context["dateHints"] ?? null), function ($__h__) use ($context, $macros) { $context["h"] = $__h__; return CoreExtension::inFilter(($context["h"] ?? null), ($context["lname"] ?? null)); })) > 0) || CoreExtension::inFilter(($context["t"] ?? null), ["date", "datetime-local", "time"]))) {
                // line 55
                yield "            ";
                $context["dates"] = Twig\Extension\CoreExtension::merge(($context["dates"] ?? null), [$context["f"]]);
                // line 56
                yield "        ";
            } elseif ((CoreExtension::inFilter(($context["lname"] ?? null), ($context["textNames"] ?? null)) || (($context["t"] ?? null) == "textarea"))) {
                // line 57
                yield "            ";
                $context["texts"] = Twig\Extension\CoreExtension::merge(($context["texts"] ?? null), [$context["f"]]);
                // line 58
                yield "        ";
            } else {
                // line 59
                yield "            ";
                $context["others"] = Twig\Extension\CoreExtension::merge(($context["others"] ?? null), [$context["f"]]);
                // line 60
                yield "        ";
            }
            // line 61
            yield "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 62
        yield "    ";
        $context["ordered"] = Twig\Extension\CoreExtension::merge(Twig\Extension\CoreExtension::merge(Twig\Extension\CoreExtension::merge(Twig\Extension\CoreExtension::merge(Twig\Extension\CoreExtension::merge(Twig\Extension\CoreExtension::merge(Twig\Extension\CoreExtension::merge(Twig\Extension\CoreExtension::merge(($context["prim"] ?? null), ($context["status"] ?? null)), ($context["ids"] ?? null)), ($context["media"] ?? null)), ($context["money"] ?? null)), ($context["contacts"] ?? null)), ($context["dates"] ?? null)), ($context["others"] ?? null)), ($context["texts"] ?? null));
        // line 63
        yield "
    <form method=\"post\" class=\"space-y-6\"";
        // line 64
        if ((array_key_exists("form_action", $context) && ($context["form_action"] ?? null))) {
            yield " action=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["form_action"] ?? null), "html", null, true);
            yield "\"";
        }
        if ((($tmp = ($context["hasFile"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " enctype=\"multipart/form-data\"";
        }
        yield ">
        ";
        // line 65
        yield $this->env->getFunction('csrf_field')->getCallable()();
        yield "
        ";
        // line 66
        if ((array_key_exists("error", $context) && ($context["error"] ?? null))) {
            // line 67
            yield "            <div class=\"alert alert-error\"><span>";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["error"] ?? null));
            yield "</span></div>
        ";
        }
        // line 69
        yield "        ";
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), "id", [], "any", true, true, false, 69) && CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), "id", [], "any", false, false, false, 69))) {
            // line 70
            yield "            <input type=\"hidden\" name=\"id\" value=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFilter('int')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), "id", [], "any", false, false, false, 70)), "html", null, true);
            yield "\">
        ";
        }
        // line 72
        yield "
        ";
        // line 74
        yield "        ";
        // line 138
        yield "        ";
        $macros["self"] = $this->macros["self"] = $this;
        // line 139
        yield "
        ";
        // line 140
        if ((($context["prim"] ?? null) || ($context["status"] ?? null))) {
            // line 141
            yield "        <div class=\"card bg-base-100 border border-base-300\">
            <div class=\"card-body\">
                <h3 class=\"card-title text-lg mb-2\">";
            // line 143
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Basics"));
            yield "</h3>
                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                    ";
            // line 145
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["prim"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 145, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 146
            yield "                    ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["status"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 146, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 147
            yield "                </div>
            </div>
        </div>
        ";
        }
        // line 151
        yield "
        ";
        // line 152
        if ((($tmp = ($context["media"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 153
            yield "        <div class=\"card bg-base-100 border border-base-300\">
            <div class=\"card-body\">
                <h3 class=\"card-title text-lg mb-2\">";
            // line 155
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Media"));
            yield "</h3>
                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                    ";
            // line 157
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["media"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 157, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 158
            yield "                </div>
            </div>
        </div>
        ";
        }
        // line 162
        yield "
        ";
        // line 163
        if ((($context["money"] ?? null) || ($context["ids"] ?? null))) {
            // line 164
            yield "        <div class=\"card bg-base-100 border border-base-300\">
            <div class=\"card-body\">
                <h3 class=\"card-title text-lg mb-2\">";
            // line 166
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Numbers & IDs"));
            yield "</h3>
                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                    ";
            // line 168
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["money"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 168, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 169
            yield "                    ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["ids"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 169, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 170
            yield "                </div>
            </div>
        </div>
        ";
        }
        // line 174
        yield "
        ";
        // line 175
        if ((($tmp = ($context["contacts"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 176
            yield "        <div class=\"card bg-base-100 border border-base-300\">
            <div class=\"card-body\">
                <h3 class=\"card-title text-lg mb-2\">";
            // line 178
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Contacts & Links"));
            yield "</h3>
                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                    ";
            // line 180
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["contacts"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 180, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 181
            yield "                </div>
            </div>
        </div>
        ";
        }
        // line 185
        yield "
        ";
        // line 186
        if ((($tmp = ($context["dates"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 187
            yield "        <div class=\"card bg-base-100 border border-base-300\">
            <div class=\"card-body\">
                <h3 class=\"card-title text-lg mb-2\">";
            // line 189
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Dates"));
            yield "</h3>
                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                    ";
            // line 191
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["dates"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 191, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 192
            yield "                </div>
            </div>
        </div>
        ";
        }
        // line 196
        yield "
        ";
        // line 197
        if ((($tmp = ($context["others"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 198
            yield "        <div class=\"card bg-base-100 border border-base-300\">
            <div class=\"card-body\">
                <h3 class=\"card-title text-lg mb-2\">";
            // line 200
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Details"));
            yield "</h3>
                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                    ";
            // line 202
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["others"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 202, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 203
            yield "                </div>
            </div>
        </div>
        ";
        }
        // line 207
        yield "
        ";
        // line 208
        if ((($tmp = ($context["texts"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 209
            yield "        <div class=\"card bg-base-100 border border-base-300\">
            <div class=\"card-body space-y-4\">
                <h3 class=\"card-title text-lg mb-2\">";
            // line 211
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Notes"));
            yield "</h3>
                ";
            // line 212
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["texts"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["f"]) {
                yield $macros["self"]->getTemplateForMacro("macro_render_field", $context, 212, $this->getSourceContext())->macro_render_field(...[$context["f"], ($context["data"] ?? null), ($context["errors"] ?? null)]);
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['f'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 213
            yield "            </div>
        </div>
        ";
        }
        // line 216
        yield "
        <div class=\"flex items-center gap-2 justify-end\">
            ";
        // line 218
        if ((array_key_exists("cancel_url", $context) && ($context["cancel_url"] ?? null))) {
            // line 219
            yield "                <a class=\"btn\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["cancel_url"] ?? null), "html", null, true);
            yield "\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Cancel"));
            yield "</a>
            ";
        }
        // line 221
        yield "            <button class=\"btn btn-primary\" type=\"submit\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((array_key_exists("submit_label", $context)) ? (Twig\Extension\CoreExtension::default(($context["submit_label"] ?? null), $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Save")))) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Save")))), "html", null, true);
        yield "</button>
        </div>
    </form>
    ";
        // line 224
        if ((($tmp = ($context["hasFile"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 225
            yield "    <script>
    (function(){
      function findPreview(urlInput){
        // find an img preview near the URL input
        var wrap = urlInput.closest('.field') || document;
        var img = wrap.querySelector('img');
        return img;
      }
      async function uploadFile(input){
        const file = input.files && input.files[0];
        if(!file) return;
        const form = input.closest('form');
        const fd = new FormData();
        fd.append('file', file);
        const csrf = form ? form.querySelector('input[type=\"hidden\"]') : null;
        const headers = {};
        if (csrf && csrf.value) headers['X-CSRF-Token'] = csrf.value;
        input.disabled = true;
        input.style.opacity = '0.6';
        try {
          const res = await fetch(\"";
            // line 245
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('url')->getCallable()("/upload"), "html", null, true);
            yield "\", { method: 'POST', body: fd, headers: headers });
          const data = await res.json();
          if(!res.ok || !data.ok){ throw new Error(data && data.error ? data.error : 'upload_failed'); }
          const targetSel = input.getAttribute('data-url-target');
          if (targetSel){
            const urlInput = form ? form.querySelector(targetSel) : document.querySelector(targetSel);
            if (urlInput){ urlInput.value = data.url; }
            const img = urlInput ? findPreview(urlInput) : null;
            if (img){ img.src = data.url; }
          }
        } catch (e) {
          alert('";
            // line 256
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Upload failed"));
            yield ": ' + (e && e.message ? e.message : e));
        } finally {
          input.disabled = false;
          input.style.opacity = '';
        }
      }
      document.addEventListener('change', function(ev){
        const el = ev.target;
        if (el && el.matches('input[type=\"file\"][data-upload]')){
          uploadFile(el);
        }
      });
    })();
    </script>
    ";
        }
        // line 271
        yield "</section>
";
        yield from [];
    }

    // line 74
    public function macro_render_field($f = null, $data = null, $errors = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "f" => $f,
            "data" => $data,
            "errors" => $errors,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 75
            yield "            ";
            $macros["self"] = $this;
            // line 76
            yield "            ";
            $context["fname"] = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "name", [], "any", false, false, false, 76);
            // line 77
            yield "            ";
            $context["ftype"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "type", [], "any", true, true, false, 77)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "type", [], "any", false, false, false, 77), "text")) : ("text"));
            // line 78
            yield "            ";
            $context["flabel"] = $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "label", [], "any", true, true, false, 78)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "label", [], "any", false, false, false, 78), ($context["fname"] ?? null))) : (($context["fname"] ?? null))));
            // line 79
            yield "            ";
            $context["fval"] = ((CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), ($context["fname"] ?? null), [], "any", true, true, false, 79)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["data"] ?? null), ($context["fname"] ?? null), [], "any", false, false, false, 79), "")) : (""));
            // line 80
            yield "            ";
            $context["ferr"] = ((array_key_exists("errors", $context)) ? (((CoreExtension::getAttribute($this->env, $this->source, ($context["errors"] ?? null), ($context["fname"] ?? null), [], "any", true, true, false, 80)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["errors"] ?? null), ($context["fname"] ?? null), [], "any", false, false, false, 80), null)) : (null))) : (null));
            // line 81
            yield "            ";
            $context["ferrMsg"] = null;
            // line 82
            yield "            ";
            if (is_iterable(($context["ferr"] ?? null))) {
                // line 83
                yield "                ";
                $context["first"] = Twig\Extension\CoreExtension::first($this->env->getCharset(), ($context["ferr"] ?? null));
                // line 84
                yield "                ";
                if (is_iterable(($context["first"] ?? null))) {
                    // line 85
                    yield "                    ";
                    if (CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "key", [], "any", true, true, false, 85)) {
                        // line 86
                        yield "                        ";
                        $context["ferrMsg"] = $this->env->getFunction('__')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "key", [], "any", false, false, false, 86), ((CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "params", [], "any", true, true, false, 86)) ? (CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "params", [], "any", false, false, false, 86)) : ([])));
                        // line 87
                        yield "                    ";
                    } elseif (CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "code", [], "any", true, true, false, 87)) {
                        // line 88
                        yield "                        ";
                        $context["ferrMsg"] = $this->env->getFunction('__')->getCallable()(("validation." . CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "code", [], "any", false, false, false, 88)));
                        // line 89
                        yield "                    ";
                    } elseif (CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "message", [], "any", true, true, false, 89)) {
                        // line 90
                        yield "                        ";
                        $context["ferrMsg"] = CoreExtension::getAttribute($this->env, $this->source, ($context["first"] ?? null), "message", [], "any", false, false, false, 90);
                        // line 91
                        yield "                    ";
                    } else {
                        // line 92
                        yield "                        ";
                        $context["ferrMsg"] = ((array_key_exists("first", $context)) ? (Twig\Extension\CoreExtension::default(($context["first"] ?? null), "")) : (""));
                        // line 93
                        yield "                    ";
                    }
                    // line 94
                    yield "                ";
                } else {
                    // line 95
                    yield "                    ";
                    $context["ferrMsg"] = ($context["first"] ?? null);
                    // line 96
                    yield "                ";
                }
                // line 97
                yield "            ";
            } else {
                // line 98
                yield "                ";
                $context["ferrMsg"] = ($context["ferr"] ?? null);
                // line 99
                yield "            ";
            }
            // line 100
            yield "            <div class=\"form-control\">
                <label class=\"label\" for=\"";
            // line 101
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
            yield "\"><span class=\"label-text\">";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["flabel"] ?? null), "html", null, true);
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "required", [], "any", false, false, false, 101)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " *";
            }
            yield "</span></label>
                ";
            // line 102
            if ((($context["fname"] ?? null) == "picture")) {
                // line 103
                yield "                    <div class=\"grid grid-cols-1 md:grid-cols-3 gap-3 items-start\">
                        <div class=\"md:col-span-2 space-y-2\">
                            <input class=\"input input-bordered w-full\" id=\"";
                // line 105
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\" type=\"text\" value=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fval"] ?? null));
                yield "\"";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "placeholder", [], "any", false, false, false, 105)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " placeholder=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "placeholder", [], "any", false, false, false, 105));
                    yield "\"";
                }
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "required", [], "any", false, false, false, 105)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " required";
                }
                yield ">
                            <small class=\"opacity-70\">";
                // line 106
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("You can paste an image URL or upload a file below."));
                yield "</small>
                            ";
                // line 107
                if ((($tmp = ($context["fval"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 108
                    yield "                                <img class=\"mt-2 rounded border border-base-300 max-w-[120px] max-h-[120px] object-cover\" src=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fval"] ?? null));
                    yield "\" alt=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getFunction('__')->getCallable()("Current picture"));
                    yield "\">
                            ";
                }
                // line 110
                yield "                        </div>
                        <div>
                            <input class=\"file-input file-input-bordered w-full\" name=\"picture_file\" type=\"file\" accept=\"image/*\" data-upload=\"image\" data-url-target=\"#picture\">
                        </div>
                    </div>
                ";
            } elseif ((            // line 115
($context["ftype"] ?? null) == "textarea")) {
                // line 116
                yield "                    <textarea class=\"textarea textarea-bordered w-full\" id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\" rows=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(((CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "rows", [], "any", true, true, false, 116)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "rows", [], "any", false, false, false, 116), 4)) : (4)), "html", null, true);
                yield "\"";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "required", [], "any", false, false, false, 116)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " required";
                }
                yield ">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fval"] ?? null));
                yield "</textarea>
                ";
            } elseif ((            // line 117
($context["ftype"] ?? null) == "select")) {
                // line 118
                yield "                    ";
                $context["cur"] = ((array_key_exists("fval", $context)) ? (Twig\Extension\CoreExtension::default(($context["fval"] ?? null), "")) : (""));
                // line 119
                yield "                    <select class=\"select select-bordered w-full\" id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\"";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "required", [], "any", false, false, false, 119)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " required";
                }
                yield ">
                        ";
                // line 120
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "options", [], "any", false, false, false, 120));
                foreach ($context['_seq'] as $context["key"] => $context["label"]) {
                    // line 121
                    yield "                            <option value=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["key"]);
                    yield "\" ";
                    yield (((($context["cur"] ?? null) == $context["key"])) ? ("selected") : (""));
                    yield ">";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($context["label"]);
                    yield "</option>
                        ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['key'], $context['label'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 123
                yield "                    </select>
                ";
            } elseif ((            // line 124
($context["ftype"] ?? null) == "file")) {
                // line 125
                yield "                    <input class=\"file-input file-input-bordered w-full\" id=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\" name=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                yield "\" type=\"file\"";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "accept", [], "any", false, false, false, 125)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " accept=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "accept", [], "any", false, false, false, 125));
                    yield "\"";
                }
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "required", [], "any", false, false, false, 125)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " required";
                }
                yield ">
                ";
            } else {
                // line 127
                yield "                    ";
                if ((($context["ftype"] ?? null) == "time")) {
                    // line 128
                    yield "                        <input class=\"input input-bordered w-full\" id=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                    yield "\" name=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                    yield "\" type=\"time\" step=\"1\" value=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fval"] ?? null));
                    yield "\"";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "placeholder", [], "any", false, false, false, 128)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        yield " placeholder=\"";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "placeholder", [], "any", false, false, false, 128));
                        yield "\"";
                    }
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "required", [], "any", false, false, false, 128)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        yield " required";
                    }
                    yield ">
                    ";
                } else {
                    // line 130
                    yield "                        <input class=\"input input-bordered w-full\" id=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                    yield "\" name=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fname"] ?? null), "html", null, true);
                    yield "\" type=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["ftype"] ?? null), "html", null, true);
                    yield "\" value=\"";
                    yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["fval"] ?? null));
                    yield "\"";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "placeholder", [], "any", false, false, false, 130)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        yield " placeholder=\"";
                        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "placeholder", [], "any", false, false, false, 130));
                        yield "\"";
                    }
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["f"] ?? null), "required", [], "any", false, false, false, 130)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        yield " required";
                    }
                    yield ">
                    ";
                }
                // line 132
                yield "                ";
            }
            // line 133
            yield "                ";
            if ((($tmp = ($context["ferrMsg"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 134
                yield "                    <div class=\"text-error text-sm mt-1\">";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["ferrMsg"] ?? null));
                yield "</div>
                ";
            }
            // line 136
            yield "            </div>
        ";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "partials/dynamic_form.twig";
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
        return array (  826 => 136,  820 => 134,  817 => 133,  814 => 132,  793 => 130,  774 => 128,  771 => 127,  754 => 125,  752 => 124,  749 => 123,  736 => 121,  732 => 120,  721 => 119,  718 => 118,  716 => 117,  701 => 116,  699 => 115,  692 => 110,  684 => 108,  682 => 107,  678 => 106,  661 => 105,  657 => 103,  655 => 102,  646 => 101,  643 => 100,  640 => 99,  637 => 98,  634 => 97,  631 => 96,  628 => 95,  625 => 94,  622 => 93,  619 => 92,  616 => 91,  613 => 90,  610 => 89,  607 => 88,  604 => 87,  601 => 86,  598 => 85,  595 => 84,  592 => 83,  589 => 82,  586 => 81,  583 => 80,  580 => 79,  577 => 78,  574 => 77,  571 => 76,  568 => 75,  554 => 74,  548 => 271,  530 => 256,  516 => 245,  494 => 225,  492 => 224,  485 => 221,  477 => 219,  475 => 218,  471 => 216,  466 => 213,  457 => 212,  453 => 211,  449 => 209,  447 => 208,  444 => 207,  438 => 203,  429 => 202,  424 => 200,  420 => 198,  418 => 197,  415 => 196,  409 => 192,  400 => 191,  395 => 189,  391 => 187,  389 => 186,  386 => 185,  380 => 181,  371 => 180,  366 => 178,  362 => 176,  360 => 175,  357 => 174,  351 => 170,  341 => 169,  332 => 168,  327 => 166,  323 => 164,  321 => 163,  318 => 162,  312 => 158,  303 => 157,  298 => 155,  294 => 153,  292 => 152,  289 => 151,  283 => 147,  273 => 146,  264 => 145,  259 => 143,  255 => 141,  253 => 140,  250 => 139,  247 => 138,  245 => 74,  242 => 72,  236 => 70,  233 => 69,  227 => 67,  225 => 66,  221 => 65,  210 => 64,  207 => 63,  204 => 62,  198 => 61,  195 => 60,  192 => 59,  189 => 58,  186 => 57,  183 => 56,  180 => 55,  177 => 54,  174 => 53,  171 => 52,  168 => 51,  165 => 50,  162 => 49,  159 => 48,  156 => 47,  153 => 46,  150 => 45,  147 => 44,  144 => 43,  141 => 42,  138 => 41,  135 => 40,  132 => 39,  128 => 38,  125 => 37,  122 => 36,  119 => 35,  116 => 34,  113 => 33,  110 => 32,  107 => 31,  104 => 30,  101 => 29,  99 => 28,  96 => 27,  93 => 26,  90 => 25,  87 => 24,  84 => 23,  81 => 22,  78 => 21,  75 => 20,  72 => 18,  66 => 17,  63 => 16,  60 => 15,  57 => 14,  52 => 13,  49 => 12,  45 => 10,  42 => 9,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "partials/dynamic_form.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/partials/dynamic_form.twig");
    }
}
