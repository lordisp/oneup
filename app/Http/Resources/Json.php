<?php

namespace App\Http\Resources;

/**
 * This trait is to manage json attributes in a resoucre-controller. You can show, hide and decrypt
 * checkout App\Http\Resources\V1\TokenCacheProvider for a reference implementation
 */
trait Json
{
    protected function json(
        string $attribute,
        null|string|array $hidden = null,
        null|string|array $decrypt = null
    ) {
        $json = json_decode($attribute, true);
        if (isset($hidden)) {
            $json = $this->hiddenJsonProperty($json, $hidden);
        }
        if (isset($decrypt)) {
            $json = $this->decryptJsonProperty($json, $decrypt);
        }

        return $json;
    }

    protected function decryptJsonProperty(array $json, string|array|null $properties = null): array
    {
        if (is_string($properties)) {
            $json[$properties] = decrypt($json[$properties]);
        } else {
            foreach ($properties as $property) {
                $json[$property] = decrypt($json[$property]);
            }
        }

        return $json;
    }

    protected function hiddenJsonProperty(array $json, string|array $hidden)
    {
        switch ($hidden) {
            case is_string($hidden):
                unset($json[$hidden]);

                return $json;
            case is_array($hidden):
                foreach ($hidden as $value) {
                    unset($json[$value]);
                }

                return $json;
        }
    }
}
