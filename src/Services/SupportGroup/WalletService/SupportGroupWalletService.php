<?php

namespace Larapress\LCMS\Services\SupportGroup\WalletService;

use Carbon\Carbon;
use Larapress\ECommerce\IECommerceUser;
use Larapress\ECommerce\Models\WalletTransaction;
use Larapress\ECommerce\Services\Wallet\WalletService;
use Larapress\ECommerce\Services\Wallet\WalletTransactionEvent;
use Larapress\LCMS\Services\SupportGroup\ISupportGroupUser;

class SupportGroupWalletService extends WalletService {
    /**
     * Undocumented function
     *
     * @param IECommerceUser $user
     * @param float $amount
     * @param integer $currency
     * @param integer $type
     * @param integer $flags
     * @param string $desc
     * @param array $data
     *
     * @return WalletTransaction
     */
    public function addBalanceForUser(IECommerceUser $user, float $amount, int $currency, int $type, int $flags, string $desc, array $data)
    {
        /** @var ISupportGroupUser */
        $sUser = $user;

        $wallet = WalletTransaction::create([
            'user_id' => $user->id,
            'domain_id' => $user->getMembershipDomainId(),
            'amount' => $amount,
            'currency' => $currency,
            'type' => $type,
            'flags' => $flags,
            'data' => array_merge([
                'description' => $desc,
                'balance' => $this->getUserBalance($user, $currency),
                'support_id' => $sUser->getSupportUserId(),
            ], $data)
        ]);

        $this->resetBalanceCache($user->id);
        WalletTransactionEvent::dispatch($wallet, Carbon::now());

        return $wallet;
    }
}
