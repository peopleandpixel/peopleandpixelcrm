<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use App\Util\Phone;

final class DataQualityService
{
    public function __construct(
        private readonly Config $config,
        private readonly object $contactsStore,
        private readonly object $employeesStore,
        private readonly object $candidatesStore,
    ) {}

    /**
     * Run light background data quality checks. Safe to run in request path.
     * - Email format validity
     * - Optional DNS/MX lookup (ENV EMAIL_DNS_CHECK=true)
     * - Phone normalization to E.164 shape
     * @return array<string,mixed>
     */
    public function summary(): array
    {
        $emailDns = $this->isEmailDnsEnabled();
        $entities = [
            'contacts' => $this->contactsStore->all(),
            'employees' => $this->employeesStore->all(),
            'candidates' => $this->candidatesStore->all(),
        ];
        $out = [
            'emailDnsEnabled' => $emailDns,
            'totals' => ['contacts' => 0, 'employees' => 0, 'candidates' => 0],
            'issues' => [
                'invalidEmail' => 0,
                'emailDnsFail' => 0,
                'phoneNotE164' => 0,
            ],
            'sample' => [
                'invalidEmail' => [],
                'emailDnsFail' => [],
                'phoneNotE164' => [],
            ],
        ];
        foreach ($entities as $name => $rows) {
            $out['totals'][$name] = is_array($rows) ? count($rows) : 0;
            foreach ($rows as $r) {
                // Email format (support flat 'email' and structured arrays under 'emails')
                $emails = [];
                if (!empty($r['email'])) { $emails[] = (string)$r['email']; }
                if (!empty($r['emails']) && is_array($r['emails'])) {
                    foreach ($r['emails'] as $e) {
                        $val = (string)($e['value'] ?? '');
                        if ($val !== '') { $emails[] = $val; }
                    }
                }
                foreach (array_unique($emails) as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $out['issues']['invalidEmail']++;
                        if (count($out['sample']['invalidEmail']) < 5) { $out['sample']['invalidEmail'][] = $this->ref($name, $r, $email); }
                        continue;
                    }
                    if ($emailDns && !$this->hasDns($email)) {
                        $out['issues']['emailDnsFail']++;
                        if (count($out['sample']['emailDnsFail']) < 5) { $out['sample']['emailDnsFail'][] = $this->ref($name, $r, $email); }
                    }
                }
                // Phone normalization (flat 'phone' and phones array)
                $phones = [];
                if (!empty($r['phone'])) { $phones[] = (string)$r['phone']; }
                if (!empty($r['phones']) && is_array($r['phones'])) {
                    foreach ($r['phones'] as $p) {
                        $val = (string)($p['value'] ?? '');
                        if ($val !== '') { $phones[] = $val; }
                    }
                }
                foreach (array_unique($phones) as $p) {
                    $norm = Phone::normalizeE164($p);
                    if ($norm === '' || !Phone::isE164($norm)) {
                        $out['issues']['phoneNotE164']++;
                        if (count($out['sample']['phoneNotE164']) < 5) { $out['sample']['phoneNotE164'][] = $this->ref($name, $r, $p); }
                    }
                }
            }
        }
        return $out;
    }

    private function isEmailDnsEnabled(): bool
    {
        $v = $this->config->getEnv('EMAIL_DNS_CHECK');
        $v = $v !== '' ? $v : $this->config->getEnv('VALIDATE_EMAIL_DNS');
        $v = strtolower(trim($v));
        return in_array($v, ['1','true','yes','on'], true);
    }

    private function hasDns(string $email): bool
    {
        $at = strrpos($email, '@');
        if ($at === false) return false;
        $domain = substr($email, $at + 1);
        // check MX first, fallback to A
        return (function_exists('checkdnsrr') && (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')));
    }

    private function ref(string $entity, array $row, string $value): array
    {
        return [
            'entity' => $entity,
            'id' => $row['id'] ?? null,
            'name' => $row['name'] ?? ($row['email'] ?? ''),
            'value' => $value,
        ];
    }
}
