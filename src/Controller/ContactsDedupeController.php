<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\Flash;

final class ContactsDedupeController
{
    public function __construct(
        private readonly object $contactsStore,
        private readonly ?object $timesStore = null,
        private readonly ?object $tasksStore = null,
        private readonly ?object $activitiesStore = null,
    ) {}

    /** Show suspected duplicates by same email/phone or identical name. */
    public function list(): void
    {
        $contacts = $this->contactsStore->all();
        $pairs = $this->findDuplicates($contacts);
        render('contacts_dedupe', [
            'title' => __('Find duplicates'),
            'pairs' => $pairs,
        ]);
    }

    /** Merge two contacts: keep primary, copy missing fields, union tags, note activity. */
    public function merge(): void
    {
        $a = (int)($_POST['keep_id'] ?? 0);
        $b = (int)($_POST['merge_id'] ?? 0);
        if ($a <= 0 || $b <= 0 || $a === $b) {
            Flash::error(__('Invalid merge selection'));
            redirect(url('/contacts/dedupe'));
        }
        $all = $this->contactsStore->all();
        $ka = $this->findById($all, $a);
        $kb = $this->findById($all, $b);
        if (!$ka || !$kb) { Flash::error(__('Contact not found')); redirect(url('/contacts/dedupe')); }
        $merged = $ka;
        // Merge scalar fields if empty on primary
        foreach ($kb as $k => $v) {
            if (!isset($merged[$k]) || $merged[$k] === '' || $merged[$k] === null) {
                $merged[$k] = $v;
            }
        }
        // Merge tags arrays
        $tagsA = is_array($ka['tags'] ?? null) ? $ka['tags'] : [];
        $tagsB = is_array($kb['tags'] ?? null) ? $kb['tags'] : [];
        $merged['tags'] = array_values(array_unique(array_map('strval', array_merge($tagsA, $tagsB))));
        // Persist: update primary and delete secondary
        $merged['id'] = $ka['id'];
        $this->contactsStore->update((int)$merged['id'], $merged);
        $primaryId = (int)$merged['id'];
        $secondaryId = (int)$kb['id'];
        // Reassign references in child entities before deleting the secondary
        if ($this->timesStore) {
            try {
                $times = $this->timesStore->all();
                foreach ($times as $t) {
                    $tid = (int)($t['id'] ?? 0);
                    if ($tid > 0 && (int)($t['contact_id'] ?? 0) === $secondaryId) {
                        $this->timesStore->update($tid, ['contact_id' => $primaryId]);
                    }
                }
            } catch (\Throwable $e) { /* ignore reassign errors */ }
        }
        if ($this->tasksStore) {
            try {
                $tasks = $this->tasksStore->all();
                foreach ($tasks as $t) {
                    $tid = (int)($t['id'] ?? 0);
                    if ($tid > 0 && (int)($t['contact_id'] ?? 0) === $secondaryId) {
                        $this->tasksStore->update($tid, ['contact_id' => $primaryId]);
                    }
                }
            } catch (\Throwable $e) { /* ignore reassign errors */ }
        }
        $this->contactsStore->delete($secondaryId);
        // Activity note
        if ($this->activitiesStore) {
            try {
                $this->activitiesStore->add([
                    'contact_id' => $merged['id'],
                    'type' => 'system',
                    'title' => 'Merged duplicate',
                    'message' => 'Merged contact #' . $kb['id'] . ' into this contact.',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable $e) { /* ignore */ }
        }
        Flash::success(__('Contacts merged'));
        redirect(url('/contacts/view', ['id' => $merged['id']]));
    }

    /** @return array<int, array{a:array,b:array,reason:string}> */
    private function findDuplicates(array $contacts): array
    {
        $byEmail = [];
        $byPhone = [];
        $pairs = [];
        foreach ($contacts as $c) {
            $id = $c['id'] ?? null;
            if ($id === null) continue;
            $email = strtolower(trim((string)($c['email'] ?? '')));
            if ($email !== '') {
                if (isset($byEmail[$email])) {
                    $pairs[] = ['a' => $byEmail[$email], 'b' => $c, 'reason' => 'email'];
                } else {
                    $byEmail[$email] = $c;
                }
            }
            $phone = $this->normPhone((string)($c['phone'] ?? ''));
            if ($phone !== '') {
                if (isset($byPhone[$phone])) {
                    $pairs[] = ['a' => $byPhone[$phone], 'b' => $c, 'reason' => 'phone'];
                } else {
                    $byPhone[$phone] = $c;
                }
            }
        }
        // Same exact name
        $seen = [];
        foreach ($contacts as $c) {
            $name = strtolower(trim((string)($c['name'] ?? '')));
            if ($name === '') continue;
            if (isset($seen[$name])) {
                $pairs[] = ['a' => $seen[$name], 'b' => $c, 'reason' => 'name'];
            } else {
                $seen[$name] = $c;
            }
        }
        // Deduplicate pairs by IDs
        $out = [];
        $seenPairs = [];
        foreach ($pairs as $p) {
            $ka = (string)($p['a']['id'] ?? ''); $kb = (string)($p['b']['id'] ?? '');
            if ($ka === '' || $kb === '' || $ka === $kb) continue;
            $key = $ka < $kb ? ($ka . ':' . $kb) : ($kb . ':' . $ka);
            if (isset($seenPairs[$key])) continue;
            $seenPairs[$key] = true;
            $out[] = $p;
        }
        return $out;
    }

    private function normPhone(string $p): string
    {
        $d = preg_replace('/[^0-9+]/', '', $p) ?? '';
        // strip leading 00 -> +
        if (str_starts_with($d, '00')) { $d = '+' . substr($d, 2); }
        return $d;
    }

    private function findById(array $items, int $id): ?array
    {
        foreach ($items as $row) { if ((int)($row['id'] ?? 0) === $id) return $row; }
        return null;
    }
}
