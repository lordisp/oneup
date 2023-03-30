@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded-md shadow-sm border-gray-300 focus:border-lhg-yellow focus:ring focus:ring-lhg-yellow focus:ring-opacity-50']) !!}>
