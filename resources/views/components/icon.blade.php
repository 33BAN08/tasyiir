@php
    $class = $class ?? 'w-5 h-5';
    $path = public_path('icons/' . $name . '.svg');
    $svg = is_file($path) ? file_get_contents($path) : '';
    // Force our own sizing/stroke classes onto the root <svg> tag from lucide-static.
    if ($svg) {
        $svg = preg_replace('/class="[^"]*"/', 'class="' . $class . '"', $svg, 1);
        if (!str_contains($svg, 'class=')) {
            $svg = preg_replace('/<svg /', '<svg class="' . $class . '" ', $svg, 1);
        }
    }
@endphp
{!! $svg !!}
