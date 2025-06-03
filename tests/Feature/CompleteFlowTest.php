<?php
// tests/Feature/ProductFlowTest.php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cria e autentica um usuário para todos os testes
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        $this->token = $loginResponse->json('data.access_token');
    }

    #[Test]
    public function pode_criar_produto_com_dados_validos()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/products', [
            'name' => 'Produto Válido',
            'description' => 'Descrição válida',
            'price' => 10000,
            'stock' => 50
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'name' => 'Produto Válido',
                'user_id' => $this->user->id
            ]);
    }

    #[Test]
    public function falha_ao_criar_produto_sem_autenticacao()
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Produto Não Autorizado',
            'price' => 10000
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function falha_ao_criar_produto_com_dados_invalidos()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/products', [
            'name' => '', // Nome vazio
            'price' => -100 // Preço negativo
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    #[Test]
    public function pode_listar_produtos_do_usuario()
    {
        Product::factory()->count(3)->create(['user_id' => $this->user->id]);
        Product::factory()->create(); // Produto de outro usuário

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/my-products');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    #[Test]
    public function pode_atualizar_produto_do_usuario()
    {
        $product = Product::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/products/{$product->id}", [
            'name' => 'Nome Atualizado',
            'price' => 20000
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Nome Atualizado',
                'price' => 20000
            ]);
    }

    #[Test]
    public function falha_ao_atualizar_produto_de_outro_usuario()
    {
        $otherUser = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/products/{$product->id}", [
            'name' => 'Tentativa de Alteração'
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function pode_deletar_produto_do_usuario()
    {
        $product = Product::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    #[Test]
    public function falha_ao_deletar_produto_de_outro_usuario()
    {
        $otherUser = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function falha_ao_acessar_produto_inexistente()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/products/9999');

        $response->assertStatus(404);
    }

    #[Test]
    public function produtos_tem_dono_correto_ao_criar()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/products', [
            'name' => 'Produto com Dono',
            'price' => 10000
        ]);

        $productId = $response->json('id');
        $product = Product::find($productId);
        
        $this->assertEquals($this->user->id, $product->user_id);
    }
}