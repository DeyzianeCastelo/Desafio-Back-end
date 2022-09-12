<?php


namespace App\Repositories\Transaction;


use App\Events\SendNotification;
use App\Exceptions\WithoutMoneyException;
use App\Exceptions\IdleServiceException;
use App\Exceptions\TransactionDeniedException;
use App\Models\Shopkeeper;
use App\Models\Transactions\Transaction;
use App\Models\Transactions\Wallet;
use App\Models\User;
use App\Services\MockyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\InvalidDataProviderException;
use Ramsey\Uuid\Uuid;

class TransactionRepository
{
    public function handle(array $data): Transaction
    {
        //guarda pode transferir
        if (!$this->guardCanTransfer()) {
            throw new TransactionDeniedException('Shopkeeper is not authorized to make transactions', 401);
        }

        //recuperar o beneficiário
        if (!$payee = $this->retrievePayee($data)) {
            throw new InvalidDataProviderException('User Not Found', 404);
        }

        $myWallet = Auth::guard($data['provider'])->user()->wallet;

        //verifique o saldo do usuário
        if (!$this->checkUserBalance($myWallet, $data['amount'])) {
            throw new WithoutMoneyException('You dont have this amount to transfer.', 422);
        }

        //serviço capaz de fazer transações
        if (!$this->isServiceAbleToMakeTransaction()) {
            throw new IdleServiceException('Service is not responding. Try again later.');
        }

        //fazer transação
        return $this->makeTransaction($payee, $data);
    }

    //guarda pode transferir
    public function guardCanTransfer(): bool
    {
        if (Auth::guard('users')->check()) {
            return true;
        } else if (Auth::guard('shopkeepers')->check()) {
            return false;
        } else {
            throw new InvalidDataProviderException('Provider Not found', 422);
        }
    }

    public function getProvider(string $provider)
    {
        if ($provider == "users") {
            return new User();
        } else if ($provider == "shopkeepers") {
            return new Shopkeeper();
        } else {
            throw new InvalidDataProviderException('Provider Not found', 422);
        }
    }

    //verifique o saldo do usuário
    private function checkUserBalance(Wallet $wallet, $money)
    {
        return $wallet->balance >= $money;
    }


    /**
     * Function to know if the user exists on provider
     * both functions should trigger an exception
     * when something is wrong
     *
     * @param array $data
     */
    //recuperar o beneficiário
    private function retrievePayee(array $data)
    {
        try {
            $model = $this->getProvider($data['provider']);
            return $model->find($data['payee_id']);
        } catch (InvalidDataProviderException | \Exception $e) {
            return false;
        }

    }

    //fazer transação
    private function makeTransaction($payee, array $data)
    {
        $payload = [
            'id' => Uuid::uuid4()->toString(),
            'payer_wallet_id' => Auth::guard($data['provider'])->user()->wallet->id,
            'payee_wallet_id' => $payee->wallet->id,
            'amount' => $data['amount']
        ];

        return DB::transaction(function () use ($payload) {
            $transaction = Transaction::create($payload);

            $transaction->walletPayer->withdraw($payload['amount']);
            $transaction->walletPayee->deposit($payload['amount']);

            event(new SendNotification($transaction));

            return $transaction;
        });
    }

    //serviço capaz de fazer transações
    private function isServiceAbleToMakeTransaction(): bool
    {
        $service = app(MockyService::class)->authorizeTransaction();
        return $service['message'] == 'Autorizado';
    }
}
