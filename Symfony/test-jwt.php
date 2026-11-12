<?php
// Тимчасова перевірка JWT тими самими класами, що й lexik. Після перевірки файл видалити.
require __DIR__.'/vendor/autoload.php';

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Component\Clock\NativeClock;

$token = $argv[1] ?? '';
echo 'Час PHP зараз (UTC): ', gmdate('Y-m-d H:i:s'), ' / ', time(), PHP_EOL;

try {
    $jwt = (new Parser(new JoseEncoder()))->parse($token);
    echo 'Токен розібрано, alg = ', $jwt->headers()->get('alg'), PHP_EOL;

    (new Validator())->assert(
        $jwt,
        new SignedWith(new Sha256(), InMemory::plainText(file_get_contents(__DIR__.'/config/jwt/public.pem'))),
        new LooseValidAt(new NativeClock(new DateTimeZone('UTC')), new DateInterval('PT0S'))
    );
    echo 'УСІ ПЕРЕВІРКИ ПРОЙДЕНО', PHP_EOL;
} catch (RequiredConstraintsViolated $e) {
    foreach ($e->violations() as $violation) {
        echo 'ПОРУШЕННЯ: ', $violation->getMessage(), PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'ПОМИЛКА: ', get_class($e), ': ', $e->getMessage(), PHP_EOL;
}