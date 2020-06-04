<?php

  namespace OCA\Wishlist\Migration;

  use Closure;
  use OCP\DB\ISchemaWrapper;
  use OCP\Migration\SimpleMigrationStep;
  use OCP\Migration\IOutput;

  class Version000000Date202006042020 extends SimpleMigrationStep {

    /**
    * @param IOutput $output
    * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
    * @param array $options
    * @return null|ISchemaWrapper
    */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('wishes')) {
            $table = $schema->createTable('wishes');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('title', 'string', [
                'notnull' => true,
                'length' => 255
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 200,
            ]);
            $table->addColumn('link', 'text', [
                'notnull' => false,
            ]);
            
            $table->addColumn('comment', 'text', [
                'notnull' => true,
                'default' => '',
            ]);

            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'wishlist_user_id_index');
        }
        return $schema;
    }
}