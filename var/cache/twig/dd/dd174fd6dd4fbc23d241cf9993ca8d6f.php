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

/* tasks_add.twig */
class __TwigTemplate_1a34e3304f2c8acca8ca10199de2cba3 extends Template
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
        $context["isEdit"] = (array_key_exists("edit", $context) && ($context["edit"] ?? null));
        // line 4
        $context["data"] = ["id" => $this->env->getFilter('int')->getCallable()(((        // line 5
array_key_exists("id", $context)) ? (Twig\Extension\CoreExtension::default(($context["id"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "id", [], "any", true, true, false, 5)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "id", [], "any", false, false, false, 5), 0)) : (0)))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "id", [], "any", true, true, false, 5)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "id", [], "any", false, false, false, 5), 0)) : (0))))), "contact_id" => ((        // line 6
array_key_exists("contact_id", $context)) ? (Twig\Extension\CoreExtension::default(($context["contact_id"] ?? null), ((array_key_exists("contactId", $context)) ? (Twig\Extension\CoreExtension::default(($context["contactId"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", true, true, false, 6)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", false, false, false, 6), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", true, true, false, 6)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", false, false, false, 6), "")) : ("")))))) : (((array_key_exists("contactId", $context)) ? (Twig\Extension\CoreExtension::default(($context["contactId"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", true, true, false, 6)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", false, false, false, 6), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", true, true, false, 6)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "contact_id", [], "any", false, false, false, 6), "")) : ("")))))), "employee_id" => ((        // line 7
array_key_exists("employee_id", $context)) ? (Twig\Extension\CoreExtension::default(($context["employee_id"] ?? null), ((array_key_exists("employeeId", $context)) ? (Twig\Extension\CoreExtension::default(($context["employeeId"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", true, true, false, 7)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", false, false, false, 7), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", true, true, false, 7)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", false, false, false, 7), "")) : ("")))))) : (((array_key_exists("employeeId", $context)) ? (Twig\Extension\CoreExtension::default(($context["employeeId"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", true, true, false, 7)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", false, false, false, 7), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", true, true, false, 7)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "employee_id", [], "any", false, false, false, 7), "")) : ("")))))), "title" => ((        // line 8
array_key_exists("title", $context)) ? (Twig\Extension\CoreExtension::default(($context["title"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "title", [], "any", true, true, false, 8)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "title", [], "any", false, false, false, 8), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "title", [], "any", true, true, false, 8)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "title", [], "any", false, false, false, 8), "")) : ("")))), "due_date" => ((        // line 9
array_key_exists("due_date", $context)) ? (Twig\Extension\CoreExtension::default(($context["due_date"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "due_date", [], "any", true, true, false, 9)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "due_date", [], "any", false, false, false, 9), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "due_date", [], "any", true, true, false, 9)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "due_date", [], "any", false, false, false, 9), "")) : ("")))), "done_date" => ((        // line 10
array_key_exists("done_date", $context)) ? (Twig\Extension\CoreExtension::default(($context["done_date"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "done_date", [], "any", true, true, false, 10)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "done_date", [], "any", false, false, false, 10), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "done_date", [], "any", true, true, false, 10)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "done_date", [], "any", false, false, false, 10), "")) : ("")))), "status" => ((        // line 11
array_key_exists("status", $context)) ? (Twig\Extension\CoreExtension::default(($context["status"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "status", [], "any", true, true, false, 11)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "status", [], "any", false, false, false, 11), "open")) : ("open")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "status", [], "any", true, true, false, 11)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "status", [], "any", false, false, false, 11), "open")) : ("open")))), "notes" => ((        // line 12
array_key_exists("notes", $context)) ? (Twig\Extension\CoreExtension::default(($context["notes"] ?? null), ((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "notes", [], "any", true, true, false, 12)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "notes", [], "any", false, false, false, 12), "")) : ("")))) : (((CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "notes", [], "any", true, true, false, 12)) ? (Twig\Extension\CoreExtension::default(CoreExtension::getAttribute($this->env, $this->source, ($context["task"] ?? null), "notes", [], "any", false, false, false, 12), "")) : (""))))];
        // line 14
        yield from $this->load("partials/dynamic_form.twig", 14)->unwrap()->yield(CoreExtension::merge($context, ["title" => (((($tmp =         // line 15
($context["isEdit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getFunction('__')->getCallable()("Edit Task")) : ($this->env->getFunction('__')->getCallable()("Add Task"))), "submit_label" => $this->env->getFunction('__')->getCallable()("Save Task"), "form_action" => (((($tmp =         // line 17
($context["isEdit"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getFunction('url')->getCallable()("/tasks/edit")) : ($this->env->getFunction('url')->getCallable()("/tasks/new"))), "fields" =>         // line 18
($context["fields"] ?? null), "data" =>         // line 19
($context["data"] ?? null), "cancel_url" => ((        // line 20
array_key_exists("cancel_url", $context)) ? (Twig\Extension\CoreExtension::default(($context["cancel_url"] ?? null), $this->env->getFunction('url')->getCallable()("/tasks"))) : ($this->env->getFunction('url')->getCallable()("/tasks")))]));
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "tasks_add.twig";
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
        return array (  75 => 20,  74 => 19,  73 => 18,  72 => 17,  71 => 15,  70 => 14,  68 => 12,  67 => 11,  66 => 10,  65 => 9,  64 => 8,  63 => 7,  62 => 6,  61 => 5,  60 => 4,  58 => 3,  51 => 2,  40 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "tasks_add.twig", "/home/jens/PhpstormProjects/peopleandpixel/templates/tasks_add.twig");
    }
}
