<?php

  namespace OCA\Wishlist\Migration;

  use Closure;
  use OCP\DB\ISchemaWrapper;
  use OCP\Migration\SimpleMigrationStep;
  use OCP\Migration\IOutput;

  class Version000001Date202006052120 extends SimpleMigrationStep {

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
        
        $table->addColumn('created_at', 'datetime', [
            'notnull' => false,
        ]);
        
        $table->addColumn('created_by', 'string', [
            'notnull' => true,
            'length' => 200,
        ]);

        $userTable = $schema->getTable('users');
        $table->addForeignKeyConstraint($userTable, array("user_id"), array("uid"), array("onUpdate" => "CASCADE"));
        return $schema;
    }
}