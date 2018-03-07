<?php

namespace Nuwave\Lighthouse;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Schema;
use Nuwave\Lighthouse\Schema\Factories\DirectiveFactory;
use Nuwave\Lighthouse\Schema\MiddlewareManager;
use Nuwave\Lighthouse\Schema\SchemaBuilder;
use Nuwave\Lighthouse\Schema\Utils\SchemaStitcher;
use Nuwave\Lighthouse\Support\Traits\CanFormatError;

class GraphQL
{
    use CanFormatError;

    /**
     * Schema builder.
     *
     * @var SchemaBuilder
     */
    protected $schema;

    /**
     * Directive container.
     *
     * @var DirectiveFactory
     */
    protected $directives;

    /**
     * Middleware manager.
     *
     * @var MiddlewareManager
     */
    protected $middleware;

    /**
     * Schema Stitcher.
     *
     * @var SchemaStitcher
     */
    protected $stitcher;

    /**
     * Execute GraphQL query.
     *
     * @param string $query
     * @param mixed  $context
     * @param array  $variables
     * @param mixed  $rootValue
     *
     * @return array
     */
    public function execute($query, $context = null, $variables = [], $rootValue = null)
    {
        $result = $this->queryAndReturnResult($query, $context, $variables, $rootValue);

        if (! empty($result->errors)) {
            return [
                'data' => $result->data,
                'errors' => array_map([$this, 'formatError'], $result->errors),
            ];
        }

        return ['data' => $result->data];
    }

    /**
     * Execute GraphQL query.
     *
     * @param string $query
     * @param mixed  $context
     * @param array  $variables
     * @param mixed  $rootValue
     *
     * @return \GraphQL\Executor\ExecutionResult
     */
    public function queryAndReturnResult($query, $context = null, $variables = [], $rootValue = null)
    {
        return GraphQLBase::executeAndReturnResult(
            $this->buildSchema(),
            $query,
            $rootValue,
            $context,
            $variables
        );
    }

    /**
     * Build a new schema instance.
     *
     * @return Schema
     */
    public function buildSchema()
    {
        return $this->schema()->build(
            $this->stitcher()->stitch(
                config('lighthouse.global_id_field', '_id'),
                config('lighthouse.schema.register')
            )
        );
    }

    /**
     * Get an instance of the schema builder.
     *
     * @return SchemaBuilder
     */
    public function schema()
    {
        if (! $this->schema) {
            $this->schema = app(SchemaBuilder::class);
        }

        return $this->schema;
    }

    /**
     * Get an instance of the directive container.
     *
     * @return DirectiveFactory
     */
    public function directives()
    {
        if (! $this->directives) {
            $this->directives = app(DirectiveFactory::class);
        }

        return $this->directives;
    }

    /**
     * Get instance of middle manager.
     *
     * @return MiddlewareManager
     */
    public function middleware()
    {
        if (! $this->middleware) {
            $this->middleware = app(MiddlewareManager::class);
        }

        return $this->middleware;
    }

    /**
     * Get instance of schema stitcher.
     *
     * @return SchemaStitcher
     */
    public function stitcher()
    {
        if (! $this->stitcher) {
            $this->stitcher = app(SchemaStitcher::class);
        }

        return $this->stitcher;
    }
}
