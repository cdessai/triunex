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

/* themes/triune2019/templates/layout/html.html.twig */
class __TwigTemplate_91e21d8625aa413f3f61d49556608188 extends \Twig\Template
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
        // line 28
        echo "<!DOCTYPE html>
<html lang=\"en\">
  <head>
    <head-placeholder token=\"";
        // line 31
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 31, $this->source));
        echo "\">
    <title>";
        // line 32
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->extensions['Drupal\Core\Template\TwigExtension']->safeJoin($this->env, $this->sandbox->ensureToStringAllowed(($context["head_title"] ?? null), 32, $this->source), " | "));
        echo "</title>
    <css-placeholder token=\"";
        // line 33
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 33, $this->source));
        echo "\">
    <js-placeholder token=\"";
        // line 34
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 34, $this->source));
        echo "\">
  </head>
  <body";
        // line 36
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["attributes"] ?? null), 36, $this->source), "html", null, true);
        echo ">
    <a href=\"#main-content\" class=\"visually-hidden focusable\">
      ";
        // line 38
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Skip to main content"));
        echo "
    </a>
    ";
        // line 40
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["page_top"] ?? null), 40, $this->source), "html", null, true);
        echo "
    ";
        // line 41
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["page"] ?? null), 41, $this->source), "html", null, true);
        echo "
    ";
        // line 42
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["page_bottom"] ?? null), 42, $this->source), "html", null, true);
        echo "
    <js-bottom-placeholder token=\"";
        // line 43
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($this->sandbox->ensureToStringAllowed(($context["placeholder_token"] ?? null), 43, $this->source));
        echo "\">
    <script>
      window.addEventListener(\"load\", function(){
      window.cookieconsent.initialise({
        \"palette\": {
          \"popup\": {
            \"background\": \"#090909\",
            \"text\": \"#f7f7f7\"
          },
          \"button\": {
            \"background\": \"#993835\",
            \"text\": \"#f7f7f7\"
          }
        },
        \"content\": {
          \"href\": \"/privacy-policy\"
        }
      })});
    </script>
  </body>
</html>
";
    }

    public function getTemplateName()
    {
        return "themes/triune2019/templates/layout/html.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  83 => 43,  79 => 42,  75 => 41,  71 => 40,  66 => 38,  61 => 36,  56 => 34,  52 => 33,  48 => 32,  44 => 31,  39 => 28,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * @file
 * Default theme implementation for the basic structure of a single Drupal page.
 *
 * Variables:
 * - logged_in: A flag indicating if user is logged in.
 * - root_path: The root path of the current page (e.g., node, admin, user).
 * - node_type: The content type for the current node, if the page is a node.
 * - head_title: List of text elements that make up the head_title variable.
 *   May contain or more of the following:
 *   - title: The title of the page.
 *   - name: The name of the site.
 *   - slogan: The slogan of the site.
 * - page_top: Initial rendered markup. This should be printed before 'page'.
 * - page: The rendered page markup.
 * - page_bottom: Closing rendered markup. This variable should be printed after
 *   'page'.
 * - db_offline: A flag indicating if the database is offline.
 * - placeholder_token: The token for generating head, css, js and js-bottom
 *   placeholders.
 *
 * @see template_preprocess_html()
 *
 * @ingroup themeable
 */
#}
<!DOCTYPE html>
<html lang=\"en\">
  <head>
    <head-placeholder token=\"{{ placeholder_token|raw }}\">
    <title>{{ head_title|safe_join(' | ') }}</title>
    <css-placeholder token=\"{{ placeholder_token|raw }}\">
    <js-placeholder token=\"{{ placeholder_token|raw }}\">
  </head>
  <body{{ attributes }}>
    <a href=\"#main-content\" class=\"visually-hidden focusable\">
      {{ 'Skip to main content'|t }}
    </a>
    {{ page_top }}
    {{ page }}
    {{ page_bottom }}
    <js-bottom-placeholder token=\"{{ placeholder_token|raw }}\">
    <script>
      window.addEventListener(\"load\", function(){
      window.cookieconsent.initialise({
        \"palette\": {
          \"popup\": {
            \"background\": \"#090909\",
            \"text\": \"#f7f7f7\"
          },
          \"button\": {
            \"background\": \"#993835\",
            \"text\": \"#f7f7f7\"
          }
        },
        \"content\": {
          \"href\": \"/privacy-policy\"
        }
      })});
    </script>
  </body>
</html>
", "themes/triune2019/templates/layout/html.html.twig", "/app/web/themes/triune2019/templates/layout/html.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array();
        static $filters = array("raw" => 31, "safe_join" => 32, "escape" => 36, "t" => 38);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                [],
                ['raw', 'safe_join', 'escape', 't'],
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
