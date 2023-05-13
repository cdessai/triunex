<?php

/**
 * @file
 * Contains \Drupal\triune\Controller\TriuneSchemaController.
 */
namespace Drupal\triune\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\triune\Entity\Customer;
use Drupal\triune\Entity\Office;
use Drupal\Core\Field\BaseFieldDefinition;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller routines for triune_customer page routes.
 */
class TriuneSchemaController implements ContainerInjectionInterface
{


    /**
     * The database connection.
     *
     * @var \Drupal\Core\Database\Connection;
     */
    protected $database; 


    /**
     * Constructs a \Drupal\triune\Controller\AscentisAPIController object.
     *
     * @param \Drupal\Core\Database\Connection $database
     *     The database connection.
     */
    public function __construct(Connection $database)
    {
        
        $this->database = $database;
        
    }
    
    
    /**
     * Delete all data from database tables if tables exist.
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        
        return new static($container->get('database'));
        
    }    

    /**
     * Provide database update for a triune entity table
     */
    public function triune_order_entity_update()
    {
      
        // Set Variables
        $bundle_of = 'triune_employee';
        $field_name = 'hire_date';
        $label = 'Hire Date';
        $description = 'Date the employee was hired.';

        // Check if already exists
        if (!$this->schemaFieldCheck($bundle_of, $field_name)) {
            // Update SCHEMA Definition
            $schema = Database::getConnection()->schema();
            $spec = array(
            'description' => $description,
            'type' => 'int',
            'length' => 11,
            'not null' => false,
            'default' => null,
            );
            $schema->addField($bundle_of, $field_name, $spec);
        
            // Update ENTITY definition
            $definition_manager = \Drupal::entityDefinitionUpdateManager();
            $entity = BaseFieldDefinition::create('string')
                ->setLabel(t($label))
                ->setDescription(t($description))
                ->setReadOnly(false)
                ->setSetting('unsigned', true)
                ->setRevisionable(true)
                ->setDisplayOptions(
                    'form', array(
                    'type' => 'text_textfield',
                    'settings' => array(
                    'size' => 50,
                    ),
                    'weight' => -5,
                    )
                )
                ->setDisplayConfigurable('form', true);

            // Install the new definition.
            $definition_manager->installFieldStorageDefinition($field_name, $bundle_of, $bundle_of, $entity);
        
        }
        return new RedirectResponse(Url::fromRoute('triune.order.list'));
    }

    /**
     * Provide database update for a triune entity table
     */
    public function schemaFieldCheck($entity, $field, $verbose = false)
    {
      
        // Update SCHEMA Definition
        $schema = Database::getConnection()->schema();
        $definition_manager = \Drupal::entityDefinitionUpdateManager();

        if ($verbose) {
            if ($schema->fieldExists($entity, $field)) {
                print $entity .'.'. $field .' exists';
            } else {
                print $entity .'.'. $field .' does not exist';
            }
            exit();
        } else {
            return $schema->fieldExists($entity, $field);
        }

    }
}
