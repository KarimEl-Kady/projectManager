<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_api_root_links_to_the_swagger_documentation(): void
    {
        $this->getJson('/api')
            ->assertOk()
            ->assertJsonPath('documentation_url', url('/docs'));
    }

    public function test_swagger_ui_and_openapi_document_are_available(): void
    {
        $this->get('/docs')
            ->assertOk()
            ->assertSee('swagger-ui');

        $this->get('/docs/openapi.yaml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/yaml');
    }
}
