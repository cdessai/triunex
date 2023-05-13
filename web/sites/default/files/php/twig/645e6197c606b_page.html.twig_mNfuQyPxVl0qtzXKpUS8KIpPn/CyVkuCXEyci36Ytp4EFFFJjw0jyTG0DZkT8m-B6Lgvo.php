<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* themes/triune2019/templates/layout/page.html.twig */
class __TwigTemplate_10f3caa4555fb646b5dd335ffa3c4e52 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 6
        echo "<div class=\"container\">

  <!-- Top Info Bar Content -->
  <div class=\"top-bar\" id=\"top\">
    <div class=\"top-inner\">     
      <div class=\"top-right\">
        ";
        // line 12
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "top_right", [], "any", false, false, true, 12), 12, $this->source), "html", null, true);
        echo "
      </div>
    </div>
  </div>
  
  <!-- Header Content START -->
  <header class=\"main-header\">
    <div class=\"header-inner\"> 
      
      <!-- Site Logo -->
      <div class=\"logo\"><a href=\"/\"><img src=\"";
        // line 22
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["logopath"] ?? null), 22, $this->source), "html", null, true);
        echo "\" alt=\"";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Home"));
        echo "\" /></a></div> 
      
      <!-- Main Menu / Responsive Dropdown -->
      <nav class=\"main-menu\">
        ";
        // line 26
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "main_menu", [], "any", false, false, true, 26), 26, $this->source), "html", null, true);
        echo "  
      </nav>
      
    </div>
  </header>
  <div class=\"clear-fl\">&nbsp;</div>
  <!-- Header Content END -->
  
  <!-- Section 1 -->
  <section id=\"main-content\">    
      <div class=\"sec-inner\">
        
      ";
        // line 38
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "breadcrumb", [], "any", false, false, true, 38), 38, $this->source), "html", null, true);
        echo "
      ";
        // line 39
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 39), 39, $this->source), "html", null, true);
        echo "
        
      </div>
  </section>

</div>

<footer id=\"footer\">
\t<div class=\"sec-inner\">      
\t\t<div class=\"footer-bottom\">
\t\t\t";
        // line 49
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["page"] ?? null), "footer_bottom", [], "any", false, false, true, 49), 49, $this->source), "html", null, true);
        echo "
\t\t</div>
\t</div>     
</footer>";
    }

    public function getTemplateName()
    {
        return "themes/triune2019/templates/layout/page.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  101 => 49,  88 => 39,  84 => 38,  69 => 26,  60 => 22,  47 => 12,  39 => 6,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * Triune custom page theme for Drupal 8.x
 */
#}
<div class=\"container\">

  <!-- Top Info Bar Content -->
  <div class=\"top-bar\" id=\"top\">
    <div class=\"top-inner\">     
      <div class=\"top-right\">
        {{ page.top_right }}
      </div>
    </div>
  </div>
  
  <!-- Header Content START -->
  <header class=\"main-header\">
    <div class=\"header-inner\"> 
      
      <!-- Site Logo -->
      <div class=\"logo\"><a href=\"/\"><img src=\"{{ logopath }}\" alt=\"{{ 'Home'|t }}\" /></a></div> 
      
      <!-- Main Menu / Responsive Dropdown -->
      <nav class=\"main-menu\">
        {{ page.main_menu }}  
      </nav>
      
    </div>
  </header>
  <div class=\"clear-fl\">&nbsp;</div>
  <!-- Header Content END -->
  
  <!-- Section 1 -->
  <section id=\"main-content\">    
      <div class=\"sec-inner\">
        
      {{ page.breadcrumb }}
      {{ page.content }}
        
      </div>
  </section>

</div>

<footer id=\"footer\">
\t<div class=\"sec-inner\">      
\t\t<div class=\"footer-bottom\">
\t\t\t{{ page.footer_bottom }}
\t\t</div>
\t</div>     
</footer>", "themes/triune2019/templates/layout/page.html.twig", "/app/web/themes/triune2019/templates/layout/page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array();
        static $filters = array("escape" => 12, "t" => 22);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape', 't'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
