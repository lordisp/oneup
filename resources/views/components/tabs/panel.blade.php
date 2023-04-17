@props([
    'name'=>'',
])
<div {{$attributes}}>
    <section x-show="current==='{{\Illuminate\Support\Str::lower($name)}}'" role="tabpanel" aria-labelledby="tabs">
        {{$slot}}
    </section>
</div>