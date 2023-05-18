<?php

namespace App\Validators;

use App\Models\TokenCacheProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PdnsValidate
{
    public static function recordType(string|array $recordType): array
    {
        $data['recordType'] = (array)$recordType;

        return Validator::validate($data, [
            'recordType' => 'required|array',
            'recordType.*' => Rule::in(['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT', 'CNAME'])
        ], [
            'recordType' => 'Invalid record-type!',
            'recordType.*' => 'Invalid record-type!',
        ])
        ['recordType'];
    }

    public static function provider(string $provider): string
    {
        $data['field'] = $provider;

        return Validator::validate($data, [
            'field' => Rule::in(TokenCacheProvider::get('name')
                ->map(fn($query) => $query->name)
                ->toarray()),
        ],['field'=>__('validation.provider_not_found')])
        ['field'];
    }
}