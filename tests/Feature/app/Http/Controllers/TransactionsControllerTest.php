<?php


namespace Feature\app\Http\Controllers;


use App\Events\SendNotification;
use App\Models\Shopkeeper;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase;

class TransactionsControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function createApplication()
    {
        return require './bootstrap/app.php';
    }

    public function setUp(): void
    {

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    //O usuário não deve enviar o provedor errado
    public function testUserShouldNotSendWrongProvider()
    {
        $this->artisan('passport:install');
        $user = User::factory()->create();
        $payload = [
            'provider' => 'errado',
            'payee_id' => 'naoexiste',
            'amount' => 123
        ];
        $request = $this->actingAs($user, 'users')
            ->post(route('postTransaction'), $payload);
        $request->assertResponseStatus(422);

    }

    //O usuário deve existir no provedor para transferir
    public function testUserShouldBeExistingOnProviderToTransfer()
    {
        $this->artisan('passport:install');
        $user = User::factory()->create();
        $payload = [
            'provider' => 'users',
            'payee_id' => 'naoexiste',
            'amount' => 123
        ];

        $request = $this->actingAs($user, 'users')
            ->post(route('postTransaction'), $payload);

        $request->assertResponseStatus(404);

    }

    //O usuário deve ser um usuário válido para transferir
    public function testUserShouldBeAValidUserToTransfer()
    {
        $this->artisan('passport:install');
        $user = User::factory()->create();
        $payload = [
            'provider' => 'users',
            'payee_id' => 'naoexiste',
            'amount' => 123
        ];
        $request = $this->actingAs($user, 'users')
            ->post(route('postTransaction'), $payload);

        $request->assertResponseStatus(404);

    }

    //O lojista não deve transferir
    public function testShopkeeperShouldNotTransfer()
    {
        $this->artisan('passport:install');
        $user = Shopkeeper::factory()->create();
        $payload = [
            'provider' => 'users',
            'payee_id' => 'naoexiste',
            'amount' => 123
        ];
        $request = $this->actingAs($user, 'shopkeepers')
            ->post(route('postTransaction'), $payload);

        $request->assertResponseStatus(401);
    }

    //O usuário deve ter dinheiro para realizar alguma transação
    public function testUserShouldHaveMoneyToPerformSomeTransaction()
    {
        $this->artisan('passport:install');
        $userPayer = User::factory()->create();
        $userPayed = User::factory()->create();

        $payload = [
            'provider' => 'users',
            'payee_id' => $userPayed->id,
            'amount' => 123
        ];
        $request = $this->actingAs($userPayer, 'users')
            ->post(route('postTransaction'), $payload);

        $request->assertResponseStatus(422);
    }

    //O usuário pode transferir dinheiro
    public function testUserCanTransferMoney()
    {
        $this->expectsEvents(SendNotification::class);
        $this->artisan('passport:install');
        $userPayer = User::factory()->create();
        $userPayer->wallet->deposit(1000);
        $userPayed = User::factory()->create();

        $payload = [
            'provider' => 'users',
            'payee_id' => $userPayed->id,
            'amount' => 100
        ];
        $request = $this->actingAs($userPayer, 'users')
            ->post(route('postTransaction'), $payload);


        $request->assertResponseStatus(200);

        $request->seeInDatabase('wallets', [
            'id' => $userPayer->wallet->id,
            'balance' => 900
        ]);

        $request->seeInDatabase('wallets', [
            'id' => $userPayed->wallet->id,
            'balance' => 100
        ]);



    }
}