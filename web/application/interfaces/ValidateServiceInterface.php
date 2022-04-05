<?php

namespace Interfaces;

interface ValidateServiceInterface
{
    public static function checkPostData(array $data): bool;
    public static function getUser(array $data);
    public static function validateCredentials(array $data): bool;
    public static function getHashForPassword(string $password): string;
    public static function validateEmail(array $data): bool;
    public static function validatePassword(array $data): bool;
    public static function validateEntityId(array $data): bool;
}