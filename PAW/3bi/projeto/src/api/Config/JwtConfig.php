<?php

declare(strict_types=1);

namespace Api\Config;

class JwtConfig
{
    public const SECRET = 'evento_secret_3bi_2026_super_chave_segura_helio_esperidiao_paw';
    public const ALGO = 'HS256';
    public const EXPIRATION_SECONDS = 7200; // 2h
    public const ISSUER = 'eventos-api-3bi';
}
