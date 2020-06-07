<?php

  namespace OCA\Wishlist\Migration;

  use Closure;
  use OCP\DB\ISchemaWrapper;
  use OCP\Migration\SimpleMigrationStep;
  use OCP\Migration\IOutput;

  class Version000002Date202006072120 extends SimpleMigrationStep {

    /**
    * @param IOutput $output
    * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
    * @param array $options
    * @return null|ISchemaWrapper
    */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        $table = $schema->getTable('wishes');
        
        $table->addColumn('price', 'string', [
            'notnull' => false,
            'length' => 200,
            'default' => '',
        ]);


        $table->addColumn('grabbed_by', 'string', [
            'notnull' => false,
            'length' => 200,
        ]);

        $userTable = $schema->getTable('users');
        $table->addForeignKeyConstraint($userTable, array("grabbed_by"), array("uid"), array("onUpdate" => "CASCADE"));
        $table->addForeignKeyConstraint($userTable, array("created_by"), array("uid"), array("onUpdate" => "CASCADE"));
        return $schema;
    }
}