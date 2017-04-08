<?php

namespace TMPHP\RestApiGenerators\Compilers;


use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Support\Facades\DB;
use TMPHP\RestApiGenerators\Core\StubCompiler;

class CrudModelCompiler extends StubCompiler
{

    /**
     * @var AbstractSchemaManager
     */
    private $schema;

    /**
     * @var string
     */
    private $modelsNamespace;

    /**
     * CrudModelCompiler constructor.
     * @param null $saveToPath
     * @param null $saveFileName
     * @param null $stub
     */
    public function __construct($saveToPath = null, $saveFileName = null, $stub = null)
    {
        $saveToPath = storage_path('CRUD/Models/');
        $saveFileName = '';
        $this->schema= DB::getDoctrineSchemaManager();

        $this->modelsNamespace = config('rest-api-generator.models-namespace');

        parent::__construct($saveToPath, $saveFileName, $stub);
    }

    /**
     * @param array $params
     * @return bool|mixed|string
     */
    public function compile(array $params):string
    {
        //
        $this->saveFileName = ucfirst($params['modelName']).'.php';

        /**
         * @var \Doctrine\DBAL\Schema\Column[]
         */
        $columns = $this->schema->listTableColumns($params['tableName']);

        //{{ModelCapitalized}}
        $this->stub = str_replace(
            '{{ModelCapitalized}}',
            ucfirst($params['modelName']),
            $this->stub
        );

        //{{table_name}}
        $this->stub = str_replace(
            '{{table_name}}',
            $params['tableName'],
            $this->stub
        );

        //{{FillableArray}}
        $fillableArrayCompiler = new FillableArrayCompiler();
        $fillableArrayCompiled = $fillableArrayCompiler->compile(['columns' => $columns]);

        $this->stub = str_replace(
            '{{FillableArray}}',
            $fillableArrayCompiled,
            $this->stub
        );

        //{{RulesArray}}
        $rulesArrayCompiler = new RulesArrayCompiler();
        $rulesArrayCompiled = $rulesArrayCompiler->compile(['columns' => $columns]);

        $this->stub = str_replace(
            '{{RulesArray}}',
            $rulesArrayCompiled,
            $this->stub
        );

        //
        $this->stub = str_replace(
            '{{modelsNamespace}}',
            $this->modelsNamespace,
            $this->stub
        );

        //
        $this->saveStub();

        //
        return $this->stub;
    }

}