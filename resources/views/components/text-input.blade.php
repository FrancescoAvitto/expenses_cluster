@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-gray-500 focus:ring-[#374151] rounded-md shadow-sm']) }}>
