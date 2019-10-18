<?php

namespace App\Contracts;

interface UserServiceContract {
    public function getAllUsers($role);
    public function setToken(string $token, $deviceToken = null);
    public function createUser(array $userDetails, int $role, $status = null);
    public function updateUser(string $userGuid, array $userDetails);
    public function updatePassword(string $userGuid, array $userDetails);
    public function refundToWallet(\App\User $user, float $priceToReturn, \App\Models\Booking $booking, int $percentage);
    public function initiateWalletAndComSetting(\App\User $user);
}
