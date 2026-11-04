@props(['name','label','type'=>'text','value'=>null])
<label class="block text-sm font-semibold" for="{{ $name }}">{{ $label }}
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($name,$value) }}" {{ $attributes->merge(['class'=>'field']) }} @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif>
</label>@error($name)<p id="{{ $name }}-error" class="text-sm text-red-700">{{ $message }}</p>@enderror
